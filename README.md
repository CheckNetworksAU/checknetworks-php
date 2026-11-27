# Check Networks API for PHP

The official PHP client for the [Check Networks API](https://api.checknetworks.tools/docs).
It is generated from the API's OpenAPI document, so every endpoint and field
is there, and it adds what every integration needs:

- **Signing in.** It gets an access token with your API key's Client ID and
  Client Secret, keeps it, and gets a new one shortly before it expires (a
  token lasts an hour). A `401` makes it get a new token and try once more.
- **Safe retries.** Every write (`POST`, `PUT`, `PATCH`, `DELETE`) carries an
  `Idempotency-Key`, so a retry never does the work twice. Send your own key
  to keep one across your own retries.
- **Waiting when asked.** A `429`, `503` or `409` with a `Retry-After` header
  is waited out and sent again, up to twice by default.

The client's version is the API document's version.

## Install

```
composer require checknetworks/checknetworks-php
```

Until the API's launch the client is a pre-release, which Composer installs
only when asked: `composer require checknetworks/checknetworks-php:^2.0@RC`. The API's
`2.0.0-pre.3` is Composer's `2.0.0-RC3`.

PHP 8.1 or later, with the curl, json and mbstring extensions. It uses Guzzle 7.

## Get a key

Build against the **sandbox** first: a separate copy of the platform where
nothing reaches a carrier. Its base URL is `https://api.checknetworks.dev/v2`.
Ask your Account Manager for one, then create an API key in the sandbox portal
under **Company Settings → Integrations → API Keys**. A sandbox secret starts
`cn_test_`. See [The API sandbox](https://api.checknetworks.tools/docs/start/sandbox/).

## Token, ping and a first read

```php
use CheckNetworks\Api\AccountApi;
use CheckNetworks\Api\AuthenticationApi;
use CheckNetworks\CheckNetworks;

$cn = CheckNetworks::create(
    clientId: getenv('CN_CLIENT_ID'),
    clientSecret: getenv('CN_CLIENT_SECRET'), // cn_test_…: the sandbox
    scope: ['account:read'],
    baseUrl: CheckNetworks::SANDBOX_BASE_URL, // https://api.checknetworks.dev/v2
);

// The token: fetched on the first call, or now if you ask for it.
$cn->tokens()->token();
print_r($cn->tokens()->scopes()); // what the token really carries

// GET /ping: which key, account and environment answered.
$ping = $cn->api(AuthenticationApi::class)->ping()->getResult();
echo $ping->getAccountId(), ' ', $ping->getEnvironment(), "\n"; // … sandbox

// GET /account: your account's details.
$account = $cn->api(AccountApi::class)->getAccount()->getResult();
echo $account->getClientNumber(), ' ', $account->getStatus(), "\n";
```

Leave out `baseUrl` and the client picks it from the secret: `cn_test_` is the
sandbox and `cn_live_` production. In production every call runs against your
real account, and a carrier write places a real order.

Every endpoint is a method on one of the classes in
`CheckNetworks\Api`, one class per tag of the reference;
`$cn->api(SomeApi::class)` gives you one that signs in and retries. Each
method also has a `…WithHttpInfo` form that returns the status and headers, and
an `…Async` form. An error answer throws
`CheckNetworks\ApiException`, with the error body in
`getResponseObject()`. A refused token request throws
`CheckNetworks\AuthException`. What a call returns is a class in
`CheckNetworks\Model`.

## Options

`CheckNetworks::create($clientId, $clientSecret, $scope, $baseUrl,
$maxRetries = 2, $maxRetryWait = 60, $guzzle = [])`. A `Retry-After` longer
than `$maxRetryWait` seconds is not waited: the response comes back to you.
`$guzzle` passes options such as `timeout` to Guzzle. `$cn->http()` is the
Guzzle client itself, signing in and retrying, for a call of your own.

## More

- [API reference](https://api.checknetworks.tools/docs/reference/): every endpoint, its scope and fields
- [Getting started, errors and limits](https://api.checknetworks.tools/docs/start/overview/)
- Support: support@checknetworks.com.au. Quote the `CN-Message-Id` header of the call.

## Licence

This library is MIT licensed: see [LICENSE](LICENSE). The Check Networks API
it calls is used under your agreement with Check Networks and the
[API Terms](https://help.checknetworks.tools/api/terms/).
