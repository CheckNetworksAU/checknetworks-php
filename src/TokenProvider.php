<?php

declare(strict_types=1);

namespace CheckNetworks\Api;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

/**
 * Fetches an access token with the client-credentials grant and keeps it
 * until shortly before it expires. A token lasts an hour; asking for one per
 * call would run into POST /oauth/token's own limit of 30 a minute.
 *
 * This file is hand-written; everything under src/Generated is generated.
 */
final class TokenProvider
{
    private ?string $token = null;

    private int $expiresAt = 0;

    /** @var list<string> */
    private array $scopes = [];

    /**
     * @param  string  $tokenUrl  the base URL with /oauth/token after it
     * @param  string  $scope  space-separated; a token asked for with none carries none
     * @param  int  $refreshMargin  seconds before expiry at which a new token is fetched
     */
    public function __construct(
        private readonly string $tokenUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $scope = '',
        private readonly int $refreshMargin = 60,
        private readonly ClientInterface $http = new Guzzle,
    ) {}

    /** The current token, fetching a new one when it is missing or about to expire. */
    public function token(): string
    {
        if ($this->token === null || time() >= $this->expiresAt - $this->refreshMargin) {
            $this->fetch();
        }

        return (string) $this->token;
    }

    /** Drops the token, so the next call fetches a new one (after a 401). */
    public function invalidate(): void
    {
        $this->token = null;
        $this->expiresAt = 0;
    }

    /**
     * The scopes the current token carries, from the token response. It can
     * be narrower than asked for: check it first when a call answers 403.
     *
     * @return list<string>
     */
    public function scopes(): array
    {
        return $this->scopes;
    }

    private function fetch(): void
    {
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
        if ($this->scope !== '') {
            $body['scope'] = $this->scope;
        }

        $response = $this->http->request('POST', $this->tokenUrl, [
            RequestOptions::JSON => $body,
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
            RequestOptions::HTTP_ERRORS => false,
        ]);

        $raw = (string) $response->getBody();
        $data = json_decode($raw, true);
        if ($response->getStatusCode() !== 200 || ! is_array($data) || ! is_string($data['access_token'] ?? null)) {
            throw new AuthException($response->getStatusCode(), $raw);
        }

        $this->token = $data['access_token'];
        $this->expiresAt = time() + (int) ($data['expires_in'] ?? 3600);
        $scope = is_string($data['scope'] ?? null) ? $data['scope'] : '';
        $this->scopes = $scope === '' ? [] : array_values(array_filter(explode(' ', $scope), fn (string $s): bool => $s !== ''));
    }
}
