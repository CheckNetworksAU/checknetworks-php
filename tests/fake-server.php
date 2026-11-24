<?php

// A stand-in for the API, for `php -S` in the tests: POST /v2/oauth/token,
// GET /v2/ping, and POST /v2/test/write, which answers 429 with Retry-After
// the first time it sees an Idempotency-Key and 201 after. php -S runs each
// request afresh, so what it has seen lives in the file CN_FAKE_STATE names.

declare(strict_types=1);

$stateFile = (string) getenv('CN_FAKE_STATE');
$state = is_file($stateFile) ? json_decode((string) file_get_contents($stateFile), true) : null;
$state = is_array($state) ? $state : ['tokens' => 0, 'pings' => 0, 'writes' => []];

$respond = static function (int $status, array $body, array $headers = []) use (&$state, $stateFile): void {
    file_put_contents($stateFile, json_encode($state));
    http_response_code($status);
    header('Content-Type: application/json');
    foreach ($headers as $name => $value) {
        header("{$name}: {$value}");
    }
    echo json_encode($body);
};

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if ($method === 'POST' && $path === '/v2/oauth/token') {
    $body = json_decode((string) file_get_contents('php://input'), true);
    if (($body['grant_type'] ?? '') !== 'client_credentials' || ($body['client_id'] ?? '') !== 'test-client' || ($body['client_secret'] ?? '') !== 'cn_test_secret') {
        $respond(401, ['error' => 'invalid_client', 'error_description' => 'Client authentication failed']);

        return;
    }
    $state['tokens']++;
    $respond(200, [
        'token_type' => 'Bearer',
        'expires_in' => 3600,
        'access_token' => 'tok-'.$state['tokens'],
        'scope' => $body['scope'] ?? '',
    ]);

    return;
}

if (! str_starts_with($auth, 'Bearer tok-')) {
    $respond(401, ['code' => 'unauthenticated', 'reason' => 'No valid access token.', 'messageId' => 'm-1']);

    return;
}

if ($method === 'GET' && $path === '/v2/ping') {
    $state['pings']++;
    $respond(200, ['result' => [
        'keyId' => 'key-1',
        'name' => 'Test key',
        'scopes' => ['account:read'],
        'accountId' => 'acc-1',
        'ip' => '127.0.0.1',
        'ipAllowlistActive' => false,
        'environment' => 'sandbox',
        'tokenExpiresAt' => gmdate('Y-m-d\TH:i:s\Z', time() + 3600),
    ]]);

    return;
}

if ($method === 'POST' && $path === '/v2/test/write') {
    $key = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? '';
    $seen = $state['writes'][$key] ?? 0;
    $state['writes'][$key] = $seen + 1;
    if ($key !== '' && $seen === 0) {
        $respond(429, ['code' => 'rate_limited', 'reason' => 'Too many requests.', 'messageId' => 'm-2'], ['Retry-After' => '0']);

        return;
    }
    $respond(201, ['result' => ['idempotencyKey' => $key, 'attempts' => $seen + 1]]);

    return;
}

$respond(404, ['code' => 'not_found', 'reason' => 'No such path.', 'messageId' => 'm-3']);
