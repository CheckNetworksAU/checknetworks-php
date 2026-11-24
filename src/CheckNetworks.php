<?php

declare(strict_types=1);

namespace CheckNetworks\Api;

use CheckNetworks\Api\Generated\Configuration;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use InvalidArgumentException;

/**
 * The entry point: an HTTP client that signs in, keys its writes and waits
 * out Retry-After, and the generated API classes built on it.
 *
 *     $cn = CheckNetworks::create($clientId, $clientSecret, 'account:read');
 *     $ping = $cn->api(AuthenticationApi::class)->ping();
 *
 * Hand-written; the API classes under src/Generated are generated from the
 * OpenAPI document.
 */
final class CheckNetworks
{
    public const PRODUCTION_BASE_URL = 'https://api.checknetworks.tools/v2';

    public const SANDBOX_BASE_URL = 'https://api.checknetworks.dev/v2';

    public function __construct(
        private readonly ClientInterface $http,
        private readonly Configuration $config,
        private readonly TokenProvider $tokens,
    ) {}

    /**
     * @param  string|list<string>  $scope  the scopes the token needs; it carries no others
     * @param  string|null  $baseUrl  defaults from the secret: `cn_test_` is the sandbox, `cn_live_` production
     * @param  int  $maxRetries  how many times one call waits out a Retry-After
     * @param  int  $maxRetryWait  the longest Retry-After, in seconds, worth waiting
     * @param  array<string, mixed>  $guzzle  extra Guzzle options, such as `timeout`
     */
    public static function create(
        string $clientId,
        string $clientSecret,
        string|array $scope = '',
        ?string $baseUrl = null,
        int $maxRetries = 2,
        int $maxRetryWait = 60,
        array $guzzle = [],
    ): self {
        $baseUrl = rtrim($baseUrl ?? self::baseUrlFor($clientSecret), '/');
        $tokens = new TokenProvider(
            $baseUrl.'/oauth/token',
            $clientId,
            $clientSecret,
            is_array($scope) ? implode(' ', $scope) : $scope,
            http: new Guzzle($guzzle),
        );

        $stack = HandlerStack::create();
        $stack->push(Middleware::idempotencyKey(), 'cn_idempotency_key');
        $stack->push(Middleware::retryAfter($maxRetries, $maxRetryWait), 'cn_retry_after');
        $stack->push(Middleware::refreshOn401($tokens), 'cn_refresh_on_401');
        $stack->push(Middleware::bearer($tokens), 'cn_bearer');

        $config = (new Configuration)->setHost($baseUrl);

        return new self(new Guzzle(['handler' => $stack] + $guzzle), $config, $tokens);
    }

    /**
     * One of the generated API classes, such as AuthenticationApi or AccountApi.
     *
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     */
    public function api(string $class): object
    {
        return new $class($this->http, $this->config);
    }

    /** The HTTP client every call goes through, for a call the API classes do not cover. */
    public function http(): ClientInterface
    {
        return $this->http;
    }

    public function config(): Configuration
    {
        return $this->config;
    }

    public function tokens(): TokenProvider
    {
        return $this->tokens;
    }

    private static function baseUrlFor(string $clientSecret): string
    {
        return match (true) {
            str_starts_with($clientSecret, 'cn_test_') => self::SANDBOX_BASE_URL,
            str_starts_with($clientSecret, 'cn_live_') => self::PRODUCTION_BASE_URL,
            default => throw new InvalidArgumentException('Pass baseUrl: the secret starts with neither cn_test_ (sandbox) nor cn_live_ (production).'),
        };
    }
}
