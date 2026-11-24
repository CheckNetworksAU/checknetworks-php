<?php

declare(strict_types=1);

namespace CheckNetworks\Api;

use RuntimeException;

/**
 * POST /oauth/token refused the credentials. The body is the OAuth2 error
 * (`invalid_client`, `unsupported_grant_type`) or, for a 403 such as
 * `ip_not_allowed`, the API's own error body.
 */
final class AuthException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $body,
    ) {
        parent::__construct("Check Networks refused the token request ({$status}): {$body}");
    }
}
