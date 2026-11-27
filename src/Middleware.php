<?php

declare(strict_types=1);

namespace CheckNetworks;

use GuzzleHttp\Middleware as GuzzleMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The Guzzle middleware the client runs every call through. Hand-written.
 *
 * Outermost first, as CheckNetworks::create() pushes them:
 *   idempotencyKey  one key per call, so every retry below reuses it
 *   retryAfter      waits out a 429, 503 or 409 that says how long to wait
 *   refreshOn401    fetches a new token once when one is refused
 *   bearer          the current token, fresh on every attempt
 */
final class Middleware
{
    /** The writes that take an Idempotency-Key. */
    public const WRITES = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * The statuses worth waiting out: 429 (a rate limit), 503 (busy) and
     * 409 `idempotency_in_progress`. A 409 for anything else carries no
     * Retry-After, and is returned as it is.
     */
    public const RETRY_STATUSES = [409, 429, 503];

    /**
     * Adds an `Idempotency-Key` (a random UUID) to every write that has none,
     * so that retrying it never does the work twice. Set your own on a call
     * to keep one key across your own retries.
     */
    public static function idempotencyKey(): callable
    {
        return static fn (callable $handler): callable => static function (RequestInterface $request, array $options) use ($handler) {
            if (in_array(strtoupper($request->getMethod()), self::WRITES, true) && ! $request->hasHeader('Idempotency-Key')) {
                $request = $request->withHeader('Idempotency-Key', self::uuid());
            }

            return $handler($request, $options);
        };
    }

    /** Sends the current access token, fetching a new one when it is due. */
    public static function bearer(TokenProvider $tokens): callable
    {
        return static fn (callable $handler): callable => static function (RequestInterface $request, array $options) use ($handler, $tokens) {
            // A retried request is sent again from the start of its body.
            if ($request->getBody()->isSeekable()) {
                $request->getBody()->rewind();
            }

            return $handler($request->withHeader('Authorization', 'Bearer '.$tokens->token()), $options);
        };
    }

    /** On a 401, drops the token and sends the request once more with a new one. */
    public static function refreshOn401(TokenProvider $tokens): callable
    {
        return GuzzleMiddleware::retry(
            static function (int $retries, RequestInterface $request, ?ResponseInterface $response = null) use ($tokens): bool {
                if ($retries === 0 && $response?->getStatusCode() === 401) {
                    $tokens->invalidate();

                    return true;
                }

                return false;
            },
            static fn (): int => 0,
        );
    }

    /**
     * Waits the seconds a response's `Retry-After` asks for, and sends the
     * request again, up to $maxRetries times. A wait longer than $maxWait
     * seconds is not waited: the response comes back to you instead.
     */
    public static function retryAfter(int $maxRetries = 2, int $maxWait = 60): callable
    {
        return GuzzleMiddleware::retry(
            static function (int $retries, RequestInterface $request, ?ResponseInterface $response = null) use ($maxRetries, $maxWait): bool {
                if ($response === null || $retries >= $maxRetries || ! in_array($response->getStatusCode(), self::RETRY_STATUSES, true)) {
                    return false;
                }
                $wait = self::retryAfterSeconds($response);

                return $wait !== null && $wait <= $maxWait;
            },
            static fn (int $retries, ?ResponseInterface $response = null): int => 1000 * (int) ($response === null ? 0 : self::retryAfterSeconds($response)),
        );
    }

    /** `Retry-After` in seconds, as a number or an HTTP date; null when absent or unreadable. */
    public static function retryAfterSeconds(ResponseInterface $response): ?int
    {
        $value = trim($response->getHeaderLine('Retry-After'));
        if ($value === '') {
            return null;
        }
        if (ctype_digit($value)) {
            return (int) $value;
        }
        $at = strtotime($value);

        return $at === false ? null : max(0, $at - time());
    }

    private static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
