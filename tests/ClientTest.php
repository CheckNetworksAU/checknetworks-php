<?php

declare(strict_types=1);

use CheckNetworks\Api\AuthException;
use CheckNetworks\Api\CheckNetworks;
use CheckNetworks\Api\Generated\Api\AuthenticationApi;

// Runs in the release build (#4916) against tests/fake-server.php, a
// stand-in for the API on a local port: nothing here reaches Check Networks.

beforeAll(function (): void {
    $probe = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int) substr((string) strrchr((string) stream_socket_get_name($probe, false), ':'), 1);
    fclose($probe);

    $state = tempnam(sys_get_temp_dir(), 'cn-fake-');
    $process = proc_open(
        [PHP_BINARY, '-S', "127.0.0.1:{$port}", __DIR__.'/fake-server.php'],
        [['pipe', 'r'], ['file', sys_get_temp_dir().'/cn-fake-server.log', 'a'], ['file', sys_get_temp_dir().'/cn-fake-server.log', 'a']],
        $pipes,
        null,
        ['CN_FAKE_STATE' => $state] + getenv(),
    );

    for ($i = 0; $i < 100; $i++) {
        $socket = @fsockopen('127.0.0.1', $port);
        if ($socket !== false) {
            fclose($socket);
            break;
        }
        usleep(50_000);
    }

    $GLOBALS['cn_fake'] = ['process' => $process, 'state' => $state, 'url' => "http://127.0.0.1:{$port}/v2"];
});

afterAll(function (): void {
    proc_terminate($GLOBALS['cn_fake']['process']);
    @unlink($GLOBALS['cn_fake']['state']);
});

function fakeState(): array
{
    return json_decode((string) file_get_contents($GLOBALS['cn_fake']['state']), true) ?? [];
}

function fakeClient(string $secret = 'cn_test_secret'): CheckNetworks
{
    return CheckNetworks::create('test-client', $secret, ['account:read'], baseUrl: $GLOBALS['cn_fake']['url']);
}

it('fetches a token and calls GET /ping with it', function (): void {
    $before = fakeState()['tokens'] ?? 0;
    $cn = fakeClient();

    $ping = $cn->api(AuthenticationApi::class)->ping();

    expect($ping->getResult()->getAccountId())->toBe('acc-1')
        ->and($ping->getResult()->getEnvironment())->toBe('sandbox')
        ->and($cn->tokens()->scopes())->toBe(['account:read'])
        ->and(fakeState()['tokens'])->toBe($before + 1);
});

it('keeps one token across calls until it is due', function (): void {
    $cn = fakeClient();
    $api = $cn->api(AuthenticationApi::class);
    $api->ping();
    $tokens = fakeState()['tokens'];

    $api->ping();

    expect(fakeState()['tokens'])->toBe($tokens);
});

it('keys a write and retries it after Retry-After with the same key', function (): void {
    $response = fakeClient()->http()->request('POST', $GLOBALS['cn_fake']['url'].'/test/write', ['json' => ['x' => 1]]);
    $result = json_decode((string) $response->getBody(), true)['result'];

    expect($response->getStatusCode())->toBe(201)
        ->and($result['attempts'])->toBe(2)
        ->and($result['idempotencyKey'])->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
});

it('says why a token was refused', function (): void {
    fakeClient('cn_test_wrong')->tokens()->token();
})->throws(AuthException::class, 'invalid_client');

it('picks the sandbox from a cn_test_ secret', function (): void {
    expect(CheckNetworks::create('id', 'cn_test_x')->config()->getHost())->toBe(CheckNetworks::SANDBOX_BASE_URL)
        ->and(CheckNetworks::create('id', 'cn_live_x')->config()->getHost())->toBe(CheckNetworks::PRODUCTION_BASE_URL);
});
