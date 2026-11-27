<?php
/**
 * WebhooksApi
 * PHP version 8.1
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Check Networks API
 *
 * The Check Networks API lets you run your services from your own systems: qualify and order NBN and Opticomm data services, manage phone numbers, SIP trunks and SMS, raise support tickets, receive webhooks, and read your account and billing.   The reference is published at `https://api.checknetworks.tools/docs`. The OpenAPI document behind it, for generating a client, is at `https://api.checknetworks.tools/docs/openapi.json`.   Endpoints marked **Coming Soon** are published early so you can build against them. Until they go live they answer `501 - Not Implemented`.  # Getting Started  1. **Create an API Client** in the Wholesale Portal, under Company    Settings → Integrations → API Keys, and choose the scopes it may use.    The Client Secret is shown once. 2. **Request an access token** from `POST /oauth/token` with    `grant_type=client_credentials`, your `client_id` and `client_secret`,    and the scopes you need as a space-separated `scope`    (e.g. `scope=data:read tickets:read`). 3. **Call `GET /ping`** with the token to confirm it works and see the    key, account and scopes it carries, the address we saw and which    environment answered. 4. **Send the token on every request**:    `Authorization: Bearer <access token>`. Tokens last one hour; request a    new one when it expires.  The API is for Check Networks customers. If you do not have access to the Wholesale Portal, contact your Account Manager or our Sales Team.  **Build against the sandbox** (`https://api.checknetworks.dev/v2`): a separate copy of the platform, with its own account, keys and test addresses, where nothing reaches a carrier. Orders there move through the carriers' own steps in minutes and fire your webhooks as production does. Ask your Account Manager for one. In production every call runs against your real account, and a carrier write places a real order. A few calls that act on our live network answer `501` in the sandbox.  To test your retries, send `CN-Sandbox-Simulate: 429`, `502` or `503` on any sandbox call: it answers that status in the usual error shape, with `Retry-After: 60` on a 429 or 503, and runs nothing. Production ignores the header.  # Scopes  Each endpoint lists the scope it needs. A token carries only the scopes you asked for **and** your API Client is allowed; any others are dropped without an error. With no `scope` at all, a token carries none and every endpoint refuses it. The `scope` field of the token response is the record of what a token carries, so check it first when you get a `403 - Forbidden`.  Every data endpoint, under `/nbn`, `/opticomm` and `/data` alike, uses the `data:*` scopes.  # Conventions  - Field names are `camelCase`. - Money is a decimal string in Australian dollars (`\"49.95\"`), with a   `currency` field alongside (always `AUD` today). - A single object comes back in `result`, and a list in `results`. - Every list of your own records takes `page` and `perPage` and returns   `total`, `page` and `perPage` with `results`. `perPage` defaults to   **50** and runs from 1 to **200**; a value outside that, or a `page`   that is not a whole number of at least 1, is `422 - Unprocessable   Entity` naming the field, never quietly changed. - Lists take `sort` (a field the list names, `-` in front for   descending), `q` (a case-insensitive search, at most 100 characters,   over the fields the list names), `updatedSince`, `createdFrom` and   `createdTo` (an ISO 8601 date or date-time; a date alone is that day in   UTC, or in your zone when you send `CN-Time-Zone`). A filter such as `status` takes one or several values,   comma-separated, in any case. An unknown sort field or filter value is   `422`. - Lists that grow while you read them (payments, tickets, SMS, webhook   deliveries, call records) also return `nextCursor`, newest first: pass   it back as `cursor` until it is `null`. A cursor page carries `perPage`,   `nextCursor` and `results` only. A sorted list has no cursor. - An end user is named by its number, as `endUser`, everywhere a   response or a request names one; where a response shows its name too,   that is `endUserName`. - `expand` inlines a related record in full where a list or read names   it, such as `expand=endUser` on your services, which adds   `endUserDetail`. - Every time is UTC unless you ask for your own zone: send   `CN-Time-Zone` with an IANA zone name, such as `Australia/Perth`, and   every time in a JSON response comes in that zone with its offset   (`2026-11-22T09:30:00+08:00`), a date alone in a list filter is that day   in that zone, and the response echoes the header. A name that is not a   zone is `422` naming `CN-Time-Zone`. Downloads (CSV, NDJSON, PDF),   webhook deliveries and the carrier-shaped `/nbn` and `/opticomm` bodies   stay as they are. What you set comes back as you set it, whatever it   looks like: `metadata`, `externalRef`, your references   (`orderReference`, `customerReference`, `customerRef`,   `salesReference`, `paymentReference`, `paidReference`) and your own   `name`, `description`, `note` and `notes` text. - A create answers `201 - Created` with the new resource. Every other   success answers `200 - OK`.  **Carrier-shaped endpoints.** Some `/nbn` and `/opticomm` endpoints mirror the carrier's own API, so an integration written for the carrier works here unchanged. These return the carrier's own bodies and status codes, without the `result` wrapper, and are not paginated: the `/nbn` service qualifications (`single_site`, `fibre_uplift`, `enterprise_ethernet`), `/nbn/address/search`, `/nbn/address/geocode`, `/nbn/address/{locId}`, `/nbn/planning/{locId}`, the `/nbn/product_qualification` `service_types` and `bandwidths`, the diagnostic test catalogue and Opticomm `productOrders`. Our account identifiers are removed and links point at this API. The `/nbn` ones also take Kinetix's query parameter names (`loc_id`, `service_ref`, `customer_authority_date`, `location_ref`, `service_type`) as well as ours. Lists of your own records under those paths, such as your NBN inventory, are paginated as usual. Where we add to a carrier's shape, the field or parameter says \"Ours\": the premises `address` and `fibreBuildCategory` on an EE quote, and the search, filters, sort and paging of `GET /nbn/quotes`.  # Errors  Errors use standard HTTP status codes. The body tells you more:  - **`code`** says what went wrong, such as `not_found`,   `key_revoked` or `duplicate_order`. Your code should check this. The   full list is on the `Error` schema. - **`reason`** explains it in words, for a person to read. The wording   can change. - **`messageId`** is this request's id, the same as the `CN-Message-Id`   header. Quote it to Support. - **`docUrl`** links to the code's entry in the   [error reference](https://help.checknetworks.tools/api/errors/),   which says what to do about it. - **`fields`** appears when the request failed validation, and names   each input that was wrong. - **`carrierCode`** appears when nbn or Opticomm refused the request,   and holds their own error code.  | Status | Meaning | |---|---| | `400` | The body could not be parsed, or a required header is missing. On the carrier-shaped `/nbn` and `/opticomm` endpoints, also a bad input, as the carrier answers. | | `401` | The access token is missing, expired or invalid, or its key is past its end date (`key_expired`). | | `403` | Refused: `code` says why, such as `missing_scope`, `key_revoked`, `ip_not_allowed` or `account_suspended`. | | `404` | Not found. We give the same answer for something on another account. | | `405` | The path exists, but not for this method. | | `409` | The resource isn't in a state that allows this right now. | | `422` | An input was wrong (`validation_failed`, with `fields` naming it), or the request was refused on its merits. `reason` says why. | | `429` | Too many requests. See Rate Limits. | | `501` | The endpoint is Coming Soon, or isn't offered in the sandbox. Only the operations that list it can answer it. | | `5xx` | A problem on our side, or with the carrier. Try again shortly. |  Every error, an unknown path included, comes back as this JSON body, whatever your `Accept` header says.  # Idempotency  A write (`POST`, `PUT`, `PATCH` or `DELETE`) can carry an `Idempotency-Key` header so that retrying it never does the work twice, for example after a timeout where you never saw the response. Use a new unique value, such as a UUID, for each operation:  `Idempotency-Key: 1f8e6b1a-9c4d-4a2e-8b3a-2f6d0c9e7a51`  The key is required wherever a repeated request could do real harm, in three groups.  **Calls that reach a carrier**, where a repeat could place a second real order: placing, answering, changing or cancelling an order (`/data/orders`, `/nbn/orders/...`, `/opticomm/productOrders`), submitting a bundle (`POST /bundles/{id}/submit`), ordering a number (`POST /numbers`), reserving, changing or cancelling a technician appointment, the carrier service tests and health reports (and `POST /data/services/{ref}/sessions/refresh`, `POST /data/services/{ref}/diagnostics` and `POST /data/radius/{ref}/session-check`), and Opticomm's `static_ip` GET. These share a limit of **30 per minute per account**, on top of the general rate limit. A replay of a stored answer does not count against it. A batch qualification (`POST /data/sq/batches`) asks the carrier once for each row, so it needs the key too, but is not on that limit.  **Calls that bill or send**, where a repeat could charge you twice or send twice: creating a trunk (`POST /trunks`), ordering a fax plan (`POST /fax/plans`), sending a fax (`POST /fax/documents`), requesting an NBN quote (`POST /nbn/quotes`), sending an SMS (`POST /sms`), ordering colocation (`POST /colocation/orders`), and rotating a webhook secret (`PATCH /webhooks/{webhookId}` with `rotateSecret: true`).  **Writes on our side that must not run twice**: recording an end user's usage (`POST /end-users/{endUser}/usage` and `POST /usage/batch`), which would bill them twice; importing end users (`POST /end-users/imports`), which would import the file twice; rotating your own API key (`POST /account/api-keys/self/rotate`), which would rotate it twice; a bulk change to numbers (`POST /numbers/bulk`), which could release them twice; and hanging up a call (`POST /voice/calls/{callId}/hangup`), which could hang up a second one.  Each of these operations lists the header as required (the webhook secret rotation says so in its description, as it needs the key only with `rotateSecret`). Without a key they all answer `400` with the same body.  Every other write (users, tickets, webhooks, a single number, RADIUS, provisioning and the rest) accepts the key and honours it the same way, but runs without one.  How a key behaves:  | You send | You get | |---|---| | A new key | The request runs, and its response is stored. | | The same key and the same request | The stored response, with `Idempotent-Replayed: true`. Nothing runs again. | | The same key with a different request | `422` `idempotency_key_reused`. | | The same key while its first request is still running | `409` `idempotency_in_progress`, with `Retry-After`. Retry then. | | A key longer than 255 characters | `400`. |  A `5xx` is stored like any other response, so retrying with the same key returns the same `5xx` rather than running the write again. A request that timed out may still have **succeeded**, so we keep the key spent rather than risk a duplicate. Once you know the operation did not happen, send it under a **new** key. If a key keeps answering `409` long after the first call, contact Support: we never release a key on our own, because we can't tell a slow request from one that stopped.  Keys belong to your API Client and are kept for 30 days. A write sent without the header, where it is optional, is not deduplicated.  # Rate Limits  Each API Client may make **120 requests per minute** across the whole API, and your account **600 per minute** across all its API Clients together, so adding clients does not add capacity. Over either, requests answer `429 - Too Many Requests` until the minute passes. If you need more, contact Support: we can raise your account's figure.  Three groups of calls have a lower limit of their own, per account, on top of those. Each of these operations names its limit in its description, as `Limit: ... (carrier write)`, `(webhook send)` or `(domain verify)`:  | Calls | Limit | |---|---| | Carrier-reaching writes (listed under Idempotency) | 30 per minute. A replay of a stored answer does not count. | | Webhook test, redeliver and replay-dead, and sending an event again (`POST /events/{eventId}/deliveries`) | 10 per minute, shared | | Verifying an end-user portal domain | 5 per minute |  `POST /end-users/lookup` allows 20 per minute for each API key, and says so as `Limit: 20 per minute for the key.`  `POST /oauth/token` has its own limit: **30 per minute for each `client_id` from one address**, so systems behind one NAT do not share it, and 120 per minute from one address whatever the `client_id`.  Every response carries the limit that will stop you first, whichever of these it is:  | Header | What it says | |---|---| | `X-RateLimit-Limit` | The calls that limit allows in its minute. | | `X-RateLimit-Remaining` | The calls you have left in it. | | `X-RateLimit-Reset` | When it starts again, as a Unix time. | | `Retry-After` | On a `429` only: the seconds to wait before retrying. |  A `429` always means a rate: wait and retry. A cap on what you hold, such as the 5 open install reservations, answers `409` instead, because waiting does not change it.  # Versioning  The current version is **v2**, at `/v2` (`https://api.checknetworks.tools/v2/account`). Every path also answers without the prefix (`https://api.checknetworks.tools/account`), and those unprefixed paths mean v2 **for good**: a later version gets its own prefix (`/v3`) and never changes what an unprefixed call does.  Every response, errors included, names the version that answered:  ``` API-Version: 2 ```  There is no `/v1` here. v1 is the legacy API at `api.checknetworks.au`, a separate platform being retired over time. If you still use it, talk to us about moving across.  **What counts as a breaking change.** Within v2 we only add: a new endpoint, a new optional parameter, a new field in a response, a new webhook event type or a new error `code`. Build your client to ignore fields it does not know. Removing or renaming a field, endpoint or parameter, changing a field's type or meaning, making an optional input required, or refusing a request we used to accept is breaking, and happens only through the deprecation below or a new version.  # Deprecation  Nothing is deprecated today. When an endpoint or field is to be retired, you get **at least 12 months' notice** before it stops working. We tell you three ways: an entry here and in the changelog saying what to use instead, an email to your account, and three headers on every response from the call:  ``` Deprecation: @1798761599 Sunset: Fri, 31 Dec 2027 23:59:59 GMT Link: <https://api.checknetworks.tools/docs#section/Deprecation>; rel=\"deprecation\"; type=\"text/html\" ```  The `Sunset` is at least 12 months after the `Deprecation`. The one exception is a security fix: when keeping a call would put accounts at risk, we may retire it sooner, and say why.  | Header | What it says | |---|---| | `Deprecation` | When it was deprecated: `@` and a Unix timestamp ([RFC 9745](https://www.rfc-editor.org/rfc/rfc9745.html)). | | `Sunset` | The last moment it answers, as an HTTP date ([RFC 8594](https://www.rfc-editor.org/rfc/rfc8594.html)). After that it answers `404`. | | `Link` | This section, which says what to use instead. |  It keeps working normally until the `Sunset`. We suggest logging a warning whenever a response carries `Deprecation`, and raising an alert when the `Sunset` falls inside your next release cycle. Deprecated operations are also marked `deprecated: true` in this document, so a generated client can flag them.  # Support  Every response carries a **`CN-Message-Id`** header, our identifier for that request. Quote it when you contact Support about a call and we can find the exact request. It is on error responses too. We issue it; you can't set it. For your own handle on a write, use `Idempotency-Key`.  For help with the API, email support@checknetworks.com.au. Our team can answer questions and help you understand the API, but can't write your integration for you.
 *
 * The version of the OpenAPI document: 2.0.0-pre.6
 * Contact: support@checknetworks.com.au
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.26.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace CheckNetworks\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use CheckNetworks\ApiException;
use CheckNetworks\Configuration;
use CheckNetworks\FormDataProcessor;
use CheckNetworks\HeaderSelector;
use CheckNetworks\ObjectSerializer;

/**
 * WebhooksApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class WebhooksApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /** @var string[] $contentTypes **/
    public const contentTypes = [
        'deleteWebhook' => [
            'application/json',
        ],
        'getAllWebhookDeliveries' => [
            'application/json',
        ],
        'getEvent' => [
            'application/json',
        ],
        'getEvents' => [
            'application/json',
        ],
        'getWebhook' => [
            'application/json',
        ],
        'getWebhookDeliveries' => [
            'application/json',
        ],
        'getWebhookDelivery' => [
            'application/json',
        ],
        'getWebhooks' => [
            'application/json',
        ],
        'listWebhookEventTypes' => [
            'application/json',
        ],
        'patchWebhook' => [
            'application/json',
        ],
        'postEventDelivery' => [
            'application/json',
        ],
        'postWebhookDeliveriesReplayDead' => [
            'application/json',
        ],
        'postWebhookDeliveryRedeliver' => [
            'application/json',
        ],
        'postWebhookTest' => [
            'application/json',
        ],
        'postWebhooks' => [
            'application/json',
        ],
    ];

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?Configuration $config = null,
        ?HeaderSelector $selector = null,
        int $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: Configuration::getDefaultConfiguration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation deleteWebhook
     *
     * Remove a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteWebhook'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function deleteWebhook($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteWebhook'][0])
    {
        $this->deleteWebhookWithHttpInfo($webhook_id, $idempotency_key, $contentType);
    }

    /**
     * Operation deleteWebhookWithHttpInfo
     *
     * Remove a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteWebhook'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function deleteWebhookWithHttpInfo($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteWebhook'][0])
    {
        $request = $this->deleteWebhookRequest($webhook_id, $idempotency_key, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            return [null, $statusCode, $response->getHeaders()];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation deleteWebhookAsync
     *
     * Remove a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deleteWebhookAsync($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteWebhook'][0])
    {
        return $this->deleteWebhookAsyncWithHttpInfo($webhook_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation deleteWebhookAsyncWithHttpInfo
     *
     * Remove a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deleteWebhookAsyncWithHttpInfo($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteWebhook'][0])
    {
        $returnType = '';
        $request = $this->deleteWebhookRequest($webhook_id, $idempotency_key, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    return [null, $response->getStatusCode(), $response->getHeaders()];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'deleteWebhook'
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function deleteWebhookRequest($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteWebhook'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling deleteWebhook'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling WebhooksApi.deleteWebhook, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/webhooks/{webhookId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }

        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'DELETE',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getAllWebhookDeliveries
     *
     * List All Webhook Deliveries
     *
     * @param  string|null $endpoint Return only deliveries to this endpoint, by its &#x60;id&#x60;. Another account&#39;s endpoint, or anything that is not an endpoint id, matches nothing. (optional)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAllWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetAllWebhookDeliveries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getAllWebhookDeliveries($endpoint = null, $status = null, $event_id = null, $event_type = null, $from = null, $to = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getAllWebhookDeliveries'][0])
    {
        list($response) = $this->getAllWebhookDeliveriesWithHttpInfo($endpoint, $status, $event_id, $event_type, $from, $to, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $contentType);
        return $response;
    }

    /**
     * Operation getAllWebhookDeliveriesWithHttpInfo
     *
     * List All Webhook Deliveries
     *
     * @param  string|null $endpoint Return only deliveries to this endpoint, by its &#x60;id&#x60;. Another account&#39;s endpoint, or anything that is not an endpoint id, matches nothing. (optional)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAllWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetAllWebhookDeliveries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getAllWebhookDeliveriesWithHttpInfo($endpoint = null, $status = null, $event_id = null, $event_type = null, $from = null, $to = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getAllWebhookDeliveries'][0])
    {
        $request = $this->getAllWebhookDeliveriesRequest($endpoint, $status, $event_id, $event_type, $from, $to, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetAllWebhookDeliveries200Response',
                        $request,
                        $response,
                    );
                case 400:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetAllWebhookDeliveries200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetAllWebhookDeliveries200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getAllWebhookDeliveriesAsync
     *
     * List All Webhook Deliveries
     *
     * @param  string|null $endpoint Return only deliveries to this endpoint, by its &#x60;id&#x60;. Another account&#39;s endpoint, or anything that is not an endpoint id, matches nothing. (optional)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAllWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAllWebhookDeliveriesAsync($endpoint = null, $status = null, $event_id = null, $event_type = null, $from = null, $to = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getAllWebhookDeliveries'][0])
    {
        return $this->getAllWebhookDeliveriesAsyncWithHttpInfo($endpoint, $status, $event_id, $event_type, $from, $to, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getAllWebhookDeliveriesAsyncWithHttpInfo
     *
     * List All Webhook Deliveries
     *
     * @param  string|null $endpoint Return only deliveries to this endpoint, by its &#x60;id&#x60;. Another account&#39;s endpoint, or anything that is not an endpoint id, matches nothing. (optional)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAllWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAllWebhookDeliveriesAsyncWithHttpInfo($endpoint = null, $status = null, $event_id = null, $event_type = null, $from = null, $to = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getAllWebhookDeliveries'][0])
    {
        $returnType = '\CheckNetworks\Model\GetAllWebhookDeliveries200Response';
        $request = $this->getAllWebhookDeliveriesRequest($endpoint, $status, $event_id, $event_type, $from, $to, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getAllWebhookDeliveries'
     *
     * @param  string|null $endpoint Return only deliveries to this endpoint, by its &#x60;id&#x60;. Another account&#39;s endpoint, or anything that is not an endpoint id, matches nothing. (optional)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAllWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getAllWebhookDeliveriesRequest($endpoint = null, $status = null, $event_id = null, $event_type = null, $from = null, $to = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getAllWebhookDeliveries'][0])
    {







        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling WebhooksApi.getAllWebhookDeliveries, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getAllWebhookDeliveries, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getAllWebhookDeliveries, must be bigger than or equal to 1.');
        }
        


        if ($q !== null && strlen($q) > 100) {
            throw new \InvalidArgumentException('invalid length for "$q" when calling WebhooksApi.getAllWebhookDeliveries, must be smaller than or equal to 100.');
        }
        




        $resourcePath = '/webhooks/deliveries';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $endpoint,
            'endpoint', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $status,
            'status', // param base name
            'array', // openApiType
            'form', // style
            false, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $event_id,
            'eventId', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $event_type,
            'eventType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $from,
            'from', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $to,
            'to', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $page,
            'page', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $per_page,
            'perPage', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $cursor,
            'cursor', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $sort,
            'sort', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $q,
            'q', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $updated_since,
            'updatedSince', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_from,
            'createdFrom', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_to,
            'createdTo', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);




        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getEvent
     *
     * Get an Event
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvent'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetEvent200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getEvent($event_id, string $contentType = self::contentTypes['getEvent'][0])
    {
        list($response) = $this->getEventWithHttpInfo($event_id, $contentType);
        return $response;
    }

    /**
     * Operation getEventWithHttpInfo
     *
     * Get an Event
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvent'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetEvent200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getEventWithHttpInfo($event_id, string $contentType = self::contentTypes['getEvent'][0])
    {
        $request = $this->getEventRequest($event_id, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetEvent200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetEvent200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetEvent200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getEventAsync
     *
     * Get an Event
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvent'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getEventAsync($event_id, string $contentType = self::contentTypes['getEvent'][0])
    {
        return $this->getEventAsyncWithHttpInfo($event_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getEventAsyncWithHttpInfo
     *
     * Get an Event
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvent'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getEventAsyncWithHttpInfo($event_id, string $contentType = self::contentTypes['getEvent'][0])
    {
        $returnType = '\CheckNetworks\Model\GetEvent200Response';
        $request = $this->getEventRequest($event_id, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getEvent'
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvent'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getEventRequest($event_id, string $contentType = self::contentTypes['getEvent'][0])
    {

        // verify the required parameter 'event_id' is set
        if ($event_id === null || (is_array($event_id) && count($event_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $event_id when calling getEvent'
            );
        }


        $resourcePath = '/events/{eventId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($event_id !== null) {
            $resourcePath = str_replace(
                '{eventId}',
                ObjectSerializer::toPathValue($event_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getEvents
     *
     * List Events
     *
     * @param  string|null $type Only these event types: one or several, comma-separated, each a type (&#x60;ticket.replied&#x60;) or a family (&#x60;ticket.*&#x60;). A type that does not exist is &#x60;422&#x60;. (optional)
     * @param  string|null $since Only events at or after this time, an ISO 8601 date or date-time. A date alone means the start of that day. (optional)
     * @param  string|null $after Only events after this one, by its &#x60;eventId&#x60;. An &#x60;eventId&#x60; that is not your account&#39;s, or is older than 30 days, is &#x60;422&#x60;: start again with &#x60;since&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvents'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetEvents200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getEvents($type = null, $since = null, $after = null, $page = 1, $per_page = 50, $cursor = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getEvents'][0])
    {
        list($response) = $this->getEventsWithHttpInfo($type, $since, $after, $page, $per_page, $cursor, $created_from, $created_to, $contentType);
        return $response;
    }

    /**
     * Operation getEventsWithHttpInfo
     *
     * List Events
     *
     * @param  string|null $type Only these event types: one or several, comma-separated, each a type (&#x60;ticket.replied&#x60;) or a family (&#x60;ticket.*&#x60;). A type that does not exist is &#x60;422&#x60;. (optional)
     * @param  string|null $since Only events at or after this time, an ISO 8601 date or date-time. A date alone means the start of that day. (optional)
     * @param  string|null $after Only events after this one, by its &#x60;eventId&#x60;. An &#x60;eventId&#x60; that is not your account&#39;s, or is older than 30 days, is &#x60;422&#x60;: start again with &#x60;since&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvents'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetEvents200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getEventsWithHttpInfo($type = null, $since = null, $after = null, $page = 1, $per_page = 50, $cursor = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getEvents'][0])
    {
        $request = $this->getEventsRequest($type, $since, $after, $page, $per_page, $cursor, $created_from, $created_to, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetEvents200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetEvents200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetEvents200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getEventsAsync
     *
     * List Events
     *
     * @param  string|null $type Only these event types: one or several, comma-separated, each a type (&#x60;ticket.replied&#x60;) or a family (&#x60;ticket.*&#x60;). A type that does not exist is &#x60;422&#x60;. (optional)
     * @param  string|null $since Only events at or after this time, an ISO 8601 date or date-time. A date alone means the start of that day. (optional)
     * @param  string|null $after Only events after this one, by its &#x60;eventId&#x60;. An &#x60;eventId&#x60; that is not your account&#39;s, or is older than 30 days, is &#x60;422&#x60;: start again with &#x60;since&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvents'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getEventsAsync($type = null, $since = null, $after = null, $page = 1, $per_page = 50, $cursor = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getEvents'][0])
    {
        return $this->getEventsAsyncWithHttpInfo($type, $since, $after, $page, $per_page, $cursor, $created_from, $created_to, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getEventsAsyncWithHttpInfo
     *
     * List Events
     *
     * @param  string|null $type Only these event types: one or several, comma-separated, each a type (&#x60;ticket.replied&#x60;) or a family (&#x60;ticket.*&#x60;). A type that does not exist is &#x60;422&#x60;. (optional)
     * @param  string|null $since Only events at or after this time, an ISO 8601 date or date-time. A date alone means the start of that day. (optional)
     * @param  string|null $after Only events after this one, by its &#x60;eventId&#x60;. An &#x60;eventId&#x60; that is not your account&#39;s, or is older than 30 days, is &#x60;422&#x60;: start again with &#x60;since&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvents'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getEventsAsyncWithHttpInfo($type = null, $since = null, $after = null, $page = 1, $per_page = 50, $cursor = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getEvents'][0])
    {
        $returnType = '\CheckNetworks\Model\GetEvents200Response';
        $request = $this->getEventsRequest($type, $since, $after, $page, $per_page, $cursor, $created_from, $created_to, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getEvents'
     *
     * @param  string|null $type Only these event types: one or several, comma-separated, each a type (&#x60;ticket.replied&#x60;) or a family (&#x60;ticket.*&#x60;). A type that does not exist is &#x60;422&#x60;. (optional)
     * @param  string|null $since Only events at or after this time, an ISO 8601 date or date-time. A date alone means the start of that day. (optional)
     * @param  string|null $after Only events after this one, by its &#x60;eventId&#x60;. An &#x60;eventId&#x60; that is not your account&#39;s, or is older than 30 days, is &#x60;422&#x60;: start again with &#x60;since&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEvents'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getEventsRequest($type = null, $since = null, $after = null, $page = 1, $per_page = 50, $cursor = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getEvents'][0])
    {




        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling WebhooksApi.getEvents, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getEvents, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getEvents, must be bigger than or equal to 1.');
        }
        




        $resourcePath = '/events';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $type,
            'type', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $since,
            'since', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $after,
            'after', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $page,
            'page', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $per_page,
            'perPage', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $cursor,
            'cursor', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_from,
            'createdFrom', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_to,
            'createdTo', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);




        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getWebhook
     *
     * Get a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhook'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetWebhook200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getWebhook($webhook_id, string $contentType = self::contentTypes['getWebhook'][0])
    {
        list($response) = $this->getWebhookWithHttpInfo($webhook_id, $contentType);
        return $response;
    }

    /**
     * Operation getWebhookWithHttpInfo
     *
     * Get a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhook'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetWebhook200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getWebhookWithHttpInfo($webhook_id, string $contentType = self::contentTypes['getWebhook'][0])
    {
        $request = $this->getWebhookRequest($webhook_id, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetWebhook200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetWebhook200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetWebhook200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getWebhookAsync
     *
     * Get a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhookAsync($webhook_id, string $contentType = self::contentTypes['getWebhook'][0])
    {
        return $this->getWebhookAsyncWithHttpInfo($webhook_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getWebhookAsyncWithHttpInfo
     *
     * Get a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhookAsyncWithHttpInfo($webhook_id, string $contentType = self::contentTypes['getWebhook'][0])
    {
        $returnType = '\CheckNetworks\Model\GetWebhook200Response';
        $request = $this->getWebhookRequest($webhook_id, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getWebhook'
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getWebhookRequest($webhook_id, string $contentType = self::contentTypes['getWebhook'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling getWebhook'
            );
        }


        $resourcePath = '/webhooks/{webhookId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getWebhookDeliveries
     *
     * List Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event, to answer \&quot;did you send event X?\&quot;. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetAllWebhookDeliveries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getWebhookDeliveries($webhook_id, $status = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, $event_id = null, $event_type = null, $from = null, $to = null, string $contentType = self::contentTypes['getWebhookDeliveries'][0])
    {
        list($response) = $this->getWebhookDeliveriesWithHttpInfo($webhook_id, $status, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $event_id, $event_type, $from, $to, $contentType);
        return $response;
    }

    /**
     * Operation getWebhookDeliveriesWithHttpInfo
     *
     * List Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event, to answer \&quot;did you send event X?\&quot;. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetAllWebhookDeliveries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getWebhookDeliveriesWithHttpInfo($webhook_id, $status = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, $event_id = null, $event_type = null, $from = null, $to = null, string $contentType = self::contentTypes['getWebhookDeliveries'][0])
    {
        $request = $this->getWebhookDeliveriesRequest($webhook_id, $status, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $event_id, $event_type, $from, $to, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetAllWebhookDeliveries200Response',
                        $request,
                        $response,
                    );
                case 400:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetAllWebhookDeliveries200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetAllWebhookDeliveries200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getWebhookDeliveriesAsync
     *
     * List Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event, to answer \&quot;did you send event X?\&quot;. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhookDeliveriesAsync($webhook_id, $status = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, $event_id = null, $event_type = null, $from = null, $to = null, string $contentType = self::contentTypes['getWebhookDeliveries'][0])
    {
        return $this->getWebhookDeliveriesAsyncWithHttpInfo($webhook_id, $status, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $event_id, $event_type, $from, $to, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getWebhookDeliveriesAsyncWithHttpInfo
     *
     * List Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event, to answer \&quot;did you send event X?\&quot;. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhookDeliveriesAsyncWithHttpInfo($webhook_id, $status = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, $event_id = null, $event_type = null, $from = null, $to = null, string $contentType = self::contentTypes['getWebhookDeliveries'][0])
    {
        $returnType = '\CheckNetworks\Model\GetAllWebhookDeliveries200Response';
        $request = $this->getWebhookDeliveriesRequest($webhook_id, $status, $page, $per_page, $cursor, $sort, $q, $updated_since, $created_from, $created_to, $event_id, $event_type, $from, $to, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getWebhookDeliveries'
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string[]|null $status Return only deliveries in these states, one or several, comma-separated. Case does not matter. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $cursor The &#x60;nextCursor&#x60; of the previous page. Walks the list newest first from where that page ended, so a row added meanwhile is neither repeated nor skipped. A cursor page carries &#x60;perPage&#x60;, &#x60;nextCursor&#x60; and &#x60;results&#x60; only; &#x60;page&#x60; is ignored. A cursor this list did not issue, or one sent with &#x60;sort&#x60;, is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending, in place of newest first. A sorted list has no cursor (&#x60;nextCursor&#x60; is &#x60;null&#x60;). An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $event_id Return only the deliveries of this event, to answer \&quot;did you send event X?\&quot;. (optional)
     * @param  string|null $event_type Return only deliveries of this event type (&#x60;ping&#x60; included). An unknown type is a &#x60;422&#x60;. (optional)
     * @param  string|null $from Return only deliveries made at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. (optional)
     * @param  string|null $to Return only deliveries made at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day, on your &#x60;CN-Time-Zone&#x60; calendar when you send one, UTC&#39;s otherwise. Not before &#x60;from&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDeliveries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getWebhookDeliveriesRequest($webhook_id, $status = null, $page = 1, $per_page = 50, $cursor = null, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, $event_id = null, $event_type = null, $from = null, $to = null, string $contentType = self::contentTypes['getWebhookDeliveries'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling getWebhookDeliveries'
            );
        }


        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling WebhooksApi.getWebhookDeliveries, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getWebhookDeliveries, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getWebhookDeliveries, must be bigger than or equal to 1.');
        }
        


        if ($q !== null && strlen($q) > 100) {
            throw new \InvalidArgumentException('invalid length for "$q" when calling WebhooksApi.getWebhookDeliveries, must be smaller than or equal to 100.');
        }
        








        $resourcePath = '/webhooks/{webhookId}/deliveries';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $status,
            'status', // param base name
            'array', // openApiType
            'form', // style
            false, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $page,
            'page', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $per_page,
            'perPage', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $cursor,
            'cursor', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $sort,
            'sort', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $q,
            'q', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $updated_since,
            'updatedSince', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_from,
            'createdFrom', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_to,
            'createdTo', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $event_id,
            'eventId', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $event_type,
            'eventType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $from,
            'from', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $to,
            'to', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);


        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getWebhookDelivery
     *
     * Get Webhook Delivery
     *
     * @param  string $webhook_id The webhook endpoint id. (required)
     * @param  string $delivery_id The delivery id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDelivery'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetWebhookDelivery200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getWebhookDelivery($webhook_id, $delivery_id, string $contentType = self::contentTypes['getWebhookDelivery'][0])
    {
        list($response) = $this->getWebhookDeliveryWithHttpInfo($webhook_id, $delivery_id, $contentType);
        return $response;
    }

    /**
     * Operation getWebhookDeliveryWithHttpInfo
     *
     * Get Webhook Delivery
     *
     * @param  string $webhook_id The webhook endpoint id. (required)
     * @param  string $delivery_id The delivery id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDelivery'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetWebhookDelivery200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getWebhookDeliveryWithHttpInfo($webhook_id, $delivery_id, string $contentType = self::contentTypes['getWebhookDelivery'][0])
    {
        $request = $this->getWebhookDeliveryRequest($webhook_id, $delivery_id, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetWebhookDelivery200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetWebhookDelivery200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetWebhookDelivery200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getWebhookDeliveryAsync
     *
     * Get Webhook Delivery
     *
     * @param  string $webhook_id The webhook endpoint id. (required)
     * @param  string $delivery_id The delivery id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDelivery'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhookDeliveryAsync($webhook_id, $delivery_id, string $contentType = self::contentTypes['getWebhookDelivery'][0])
    {
        return $this->getWebhookDeliveryAsyncWithHttpInfo($webhook_id, $delivery_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getWebhookDeliveryAsyncWithHttpInfo
     *
     * Get Webhook Delivery
     *
     * @param  string $webhook_id The webhook endpoint id. (required)
     * @param  string $delivery_id The delivery id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDelivery'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhookDeliveryAsyncWithHttpInfo($webhook_id, $delivery_id, string $contentType = self::contentTypes['getWebhookDelivery'][0])
    {
        $returnType = '\CheckNetworks\Model\GetWebhookDelivery200Response';
        $request = $this->getWebhookDeliveryRequest($webhook_id, $delivery_id, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getWebhookDelivery'
     *
     * @param  string $webhook_id The webhook endpoint id. (required)
     * @param  string $delivery_id The delivery id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhookDelivery'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getWebhookDeliveryRequest($webhook_id, $delivery_id, string $contentType = self::contentTypes['getWebhookDelivery'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling getWebhookDelivery'
            );
        }

        // verify the required parameter 'delivery_id' is set
        if ($delivery_id === null || (is_array($delivery_id) && count($delivery_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $delivery_id when calling getWebhookDelivery'
            );
        }


        $resourcePath = '/webhooks/{webhookId}/deliveries/{deliveryId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }
        // path params
        if ($delivery_id !== null) {
            $resourcePath = str_replace(
                '{deliveryId}',
                ObjectSerializer::toPathValue($delivery_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getWebhooks
     *
     * List Webhook Endpoints
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhooks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetWebhooks200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getWebhooks($page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getWebhooks'][0])
    {
        list($response) = $this->getWebhooksWithHttpInfo($page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType);
        return $response;
    }

    /**
     * Operation getWebhooksWithHttpInfo
     *
     * List Webhook Endpoints
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhooks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetWebhooks200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getWebhooksWithHttpInfo($page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getWebhooks'][0])
    {
        $request = $this->getWebhooksRequest($page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetWebhooks200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetWebhooks200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetWebhooks200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getWebhooksAsync
     *
     * List Webhook Endpoints
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhooks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhooksAsync($page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getWebhooks'][0])
    {
        return $this->getWebhooksAsyncWithHttpInfo($page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getWebhooksAsyncWithHttpInfo
     *
     * List Webhook Endpoints
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhooks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getWebhooksAsyncWithHttpInfo($page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getWebhooks'][0])
    {
        $returnType = '\CheckNetworks\Model\GetWebhooks200Response';
        $request = $this->getWebhooksRequest($page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getWebhooks'
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getWebhooks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getWebhooksRequest($page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['getWebhooks'][0])
    {

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling WebhooksApi.getWebhooks, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getWebhooks, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling WebhooksApi.getWebhooks, must be bigger than or equal to 1.');
        }
        

        if ($q !== null && strlen($q) > 100) {
            throw new \InvalidArgumentException('invalid length for "$q" when calling WebhooksApi.getWebhooks, must be smaller than or equal to 100.');
        }
        




        $resourcePath = '/webhooks';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $page,
            'page', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $per_page,
            'perPage', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $sort,
            'sort', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $q,
            'q', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $updated_since,
            'updatedSince', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_from,
            'createdFrom', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $created_to,
            'createdTo', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);




        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation listWebhookEventTypes
     *
     * List Webhook Event Types
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listWebhookEventTypes'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\ListWatchEventTypes200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function listWebhookEventTypes(string $contentType = self::contentTypes['listWebhookEventTypes'][0])
    {
        list($response) = $this->listWebhookEventTypesWithHttpInfo($contentType);
        return $response;
    }

    /**
     * Operation listWebhookEventTypesWithHttpInfo
     *
     * List Webhook Event Types
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listWebhookEventTypes'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\ListWatchEventTypes200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listWebhookEventTypesWithHttpInfo(string $contentType = self::contentTypes['listWebhookEventTypes'][0])
    {
        $request = $this->listWebhookEventTypesRequest($contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\ListWatchEventTypes200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\ListWatchEventTypes200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\ListWatchEventTypes200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation listWebhookEventTypesAsync
     *
     * List Webhook Event Types
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listWebhookEventTypes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listWebhookEventTypesAsync(string $contentType = self::contentTypes['listWebhookEventTypes'][0])
    {
        return $this->listWebhookEventTypesAsyncWithHttpInfo($contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listWebhookEventTypesAsyncWithHttpInfo
     *
     * List Webhook Event Types
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listWebhookEventTypes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listWebhookEventTypesAsyncWithHttpInfo(string $contentType = self::contentTypes['listWebhookEventTypes'][0])
    {
        $returnType = '\CheckNetworks\Model\ListWatchEventTypes200Response';
        $request = $this->listWebhookEventTypesRequest($contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'listWebhookEventTypes'
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listWebhookEventTypes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listWebhookEventTypesRequest(string $contentType = self::contentTypes['listWebhookEventTypes'][0])
    {


        $resourcePath = '/webhooks/event-types';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation patchWebhook
     *
     * Update a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Model\PatchWebhookRequest $patch_webhook_request patch_webhook_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchWebhook'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\PatchWebhook200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function patchWebhook($webhook_id, $patch_webhook_request, $idempotency_key = null, string $contentType = self::contentTypes['patchWebhook'][0])
    {
        list($response) = $this->patchWebhookWithHttpInfo($webhook_id, $patch_webhook_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation patchWebhookWithHttpInfo
     *
     * Update a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Model\PatchWebhookRequest $patch_webhook_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchWebhook'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\PatchWebhook200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function patchWebhookWithHttpInfo($webhook_id, $patch_webhook_request, $idempotency_key = null, string $contentType = self::contentTypes['patchWebhook'][0])
    {
        $request = $this->patchWebhookRequest($webhook_id, $patch_webhook_request, $idempotency_key, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\PatchWebhook200Response',
                        $request,
                        $response,
                    );
                case 400:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\PatchWebhook200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\PatchWebhook200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation patchWebhookAsync
     *
     * Update a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Model\PatchWebhookRequest $patch_webhook_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function patchWebhookAsync($webhook_id, $patch_webhook_request, $idempotency_key = null, string $contentType = self::contentTypes['patchWebhook'][0])
    {
        return $this->patchWebhookAsyncWithHttpInfo($webhook_id, $patch_webhook_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation patchWebhookAsyncWithHttpInfo
     *
     * Update a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Model\PatchWebhookRequest $patch_webhook_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function patchWebhookAsyncWithHttpInfo($webhook_id, $patch_webhook_request, $idempotency_key = null, string $contentType = self::contentTypes['patchWebhook'][0])
    {
        $returnType = '\CheckNetworks\Model\PatchWebhook200Response';
        $request = $this->patchWebhookRequest($webhook_id, $patch_webhook_request, $idempotency_key, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'patchWebhook'
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Model\PatchWebhookRequest $patch_webhook_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchWebhook'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function patchWebhookRequest($webhook_id, $patch_webhook_request, $idempotency_key = null, string $contentType = self::contentTypes['patchWebhook'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling patchWebhook'
            );
        }

        // verify the required parameter 'patch_webhook_request' is set
        if ($patch_webhook_request === null || (is_array($patch_webhook_request) && count($patch_webhook_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $patch_webhook_request when calling patchWebhook'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling WebhooksApi.patchWebhook, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/webhooks/{webhookId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }

        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($patch_webhook_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($patch_webhook_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $patch_webhook_request;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'PATCH',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation postEventDelivery
     *
     * Send an Event to an Endpoint
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  \CheckNetworks\Model\PostEventDeliveryRequest $post_event_delivery_request post_event_delivery_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postEventDelivery'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetWebhookDelivery200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function postEventDelivery($event_id, $post_event_delivery_request, $idempotency_key = null, string $contentType = self::contentTypes['postEventDelivery'][0])
    {
        list($response) = $this->postEventDeliveryWithHttpInfo($event_id, $post_event_delivery_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation postEventDeliveryWithHttpInfo
     *
     * Send an Event to an Endpoint
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  \CheckNetworks\Model\PostEventDeliveryRequest $post_event_delivery_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postEventDelivery'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetWebhookDelivery200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postEventDeliveryWithHttpInfo($event_id, $post_event_delivery_request, $idempotency_key = null, string $contentType = self::contentTypes['postEventDelivery'][0])
    {
        $request = $this->postEventDeliveryRequest($event_id, $post_event_delivery_request, $idempotency_key, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 202:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetWebhookDelivery200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetWebhookDelivery200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetWebhookDelivery200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postEventDeliveryAsync
     *
     * Send an Event to an Endpoint
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  \CheckNetworks\Model\PostEventDeliveryRequest $post_event_delivery_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postEventDelivery'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postEventDeliveryAsync($event_id, $post_event_delivery_request, $idempotency_key = null, string $contentType = self::contentTypes['postEventDelivery'][0])
    {
        return $this->postEventDeliveryAsyncWithHttpInfo($event_id, $post_event_delivery_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postEventDeliveryAsyncWithHttpInfo
     *
     * Send an Event to an Endpoint
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  \CheckNetworks\Model\PostEventDeliveryRequest $post_event_delivery_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postEventDelivery'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postEventDeliveryAsyncWithHttpInfo($event_id, $post_event_delivery_request, $idempotency_key = null, string $contentType = self::contentTypes['postEventDelivery'][0])
    {
        $returnType = '\CheckNetworks\Model\GetWebhookDelivery200Response';
        $request = $this->postEventDeliveryRequest($event_id, $post_event_delivery_request, $idempotency_key, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'postEventDelivery'
     *
     * @param  string $event_id The event&#39;s &#x60;eventId&#x60;, from &#x60;GET /events&#x60; or a delivery. (required)
     * @param  \CheckNetworks\Model\PostEventDeliveryRequest $post_event_delivery_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postEventDelivery'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postEventDeliveryRequest($event_id, $post_event_delivery_request, $idempotency_key = null, string $contentType = self::contentTypes['postEventDelivery'][0])
    {

        // verify the required parameter 'event_id' is set
        if ($event_id === null || (is_array($event_id) && count($event_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $event_id when calling postEventDelivery'
            );
        }

        // verify the required parameter 'post_event_delivery_request' is set
        if ($post_event_delivery_request === null || (is_array($post_event_delivery_request) && count($post_event_delivery_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $post_event_delivery_request when calling postEventDelivery'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling WebhooksApi.postEventDelivery, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/events/{eventId}/deliveries';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }

        // path params
        if ($event_id !== null) {
            $resourcePath = str_replace(
                '{eventId}',
                ObjectSerializer::toPathValue($event_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($post_event_delivery_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($post_event_delivery_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $post_event_delivery_request;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'POST',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation postWebhookDeliveriesReplayDead
     *
     * Replay Dead Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveriesReplayDead'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\PostWebhookDeliveriesReplayDead202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function postWebhookDeliveriesReplayDead($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveriesReplayDead'][0])
    {
        list($response) = $this->postWebhookDeliveriesReplayDeadWithHttpInfo($webhook_id, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation postWebhookDeliveriesReplayDeadWithHttpInfo
     *
     * Replay Dead Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveriesReplayDead'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\PostWebhookDeliveriesReplayDead202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postWebhookDeliveriesReplayDeadWithHttpInfo($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveriesReplayDead'][0])
    {
        $request = $this->postWebhookDeliveriesReplayDeadRequest($webhook_id, $idempotency_key, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 202:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\PostWebhookDeliveriesReplayDead202Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\PostWebhookDeliveriesReplayDead202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\PostWebhookDeliveriesReplayDead202Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postWebhookDeliveriesReplayDeadAsync
     *
     * Replay Dead Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveriesReplayDead'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhookDeliveriesReplayDeadAsync($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveriesReplayDead'][0])
    {
        return $this->postWebhookDeliveriesReplayDeadAsyncWithHttpInfo($webhook_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postWebhookDeliveriesReplayDeadAsyncWithHttpInfo
     *
     * Replay Dead Webhook Deliveries
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveriesReplayDead'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhookDeliveriesReplayDeadAsyncWithHttpInfo($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveriesReplayDead'][0])
    {
        $returnType = '\CheckNetworks\Model\PostWebhookDeliveriesReplayDead202Response';
        $request = $this->postWebhookDeliveriesReplayDeadRequest($webhook_id, $idempotency_key, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'postWebhookDeliveriesReplayDead'
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveriesReplayDead'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postWebhookDeliveriesReplayDeadRequest($webhook_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveriesReplayDead'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling postWebhookDeliveriesReplayDead'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling WebhooksApi.postWebhookDeliveriesReplayDead, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/webhooks/{webhookId}/deliveries/replay-dead';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }

        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'POST',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation postWebhookDeliveryRedeliver
     *
     * Redeliver a Webhook Event
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $delivery_id The delivery&#39;s &#x60;id&#x60;, from &#x60;GET /webhooks/{webhookId}/deliveries&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveryRedeliver'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetWebhookDelivery200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function postWebhookDeliveryRedeliver($webhook_id, $delivery_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveryRedeliver'][0])
    {
        list($response) = $this->postWebhookDeliveryRedeliverWithHttpInfo($webhook_id, $delivery_id, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation postWebhookDeliveryRedeliverWithHttpInfo
     *
     * Redeliver a Webhook Event
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $delivery_id The delivery&#39;s &#x60;id&#x60;, from &#x60;GET /webhooks/{webhookId}/deliveries&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveryRedeliver'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetWebhookDelivery200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postWebhookDeliveryRedeliverWithHttpInfo($webhook_id, $delivery_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveryRedeliver'][0])
    {
        $request = $this->postWebhookDeliveryRedeliverRequest($webhook_id, $delivery_id, $idempotency_key, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 202:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\GetWebhookDelivery200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\GetWebhookDelivery200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetWebhookDelivery200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postWebhookDeliveryRedeliverAsync
     *
     * Redeliver a Webhook Event
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $delivery_id The delivery&#39;s &#x60;id&#x60;, from &#x60;GET /webhooks/{webhookId}/deliveries&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveryRedeliver'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhookDeliveryRedeliverAsync($webhook_id, $delivery_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveryRedeliver'][0])
    {
        return $this->postWebhookDeliveryRedeliverAsyncWithHttpInfo($webhook_id, $delivery_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postWebhookDeliveryRedeliverAsyncWithHttpInfo
     *
     * Redeliver a Webhook Event
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $delivery_id The delivery&#39;s &#x60;id&#x60;, from &#x60;GET /webhooks/{webhookId}/deliveries&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveryRedeliver'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhookDeliveryRedeliverAsyncWithHttpInfo($webhook_id, $delivery_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveryRedeliver'][0])
    {
        $returnType = '\CheckNetworks\Model\GetWebhookDelivery200Response';
        $request = $this->postWebhookDeliveryRedeliverRequest($webhook_id, $delivery_id, $idempotency_key, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'postWebhookDeliveryRedeliver'
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string $delivery_id The delivery&#39;s &#x60;id&#x60;, from &#x60;GET /webhooks/{webhookId}/deliveries&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookDeliveryRedeliver'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postWebhookDeliveryRedeliverRequest($webhook_id, $delivery_id, $idempotency_key = null, string $contentType = self::contentTypes['postWebhookDeliveryRedeliver'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling postWebhookDeliveryRedeliver'
            );
        }

        // verify the required parameter 'delivery_id' is set
        if ($delivery_id === null || (is_array($delivery_id) && count($delivery_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $delivery_id when calling postWebhookDeliveryRedeliver'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling WebhooksApi.postWebhookDeliveryRedeliver, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/webhooks/{webhookId}/deliveries/{deliveryId}/redeliver';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }

        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }
        // path params
        if ($delivery_id !== null) {
            $resourcePath = str_replace(
                '{deliveryId}',
                ObjectSerializer::toPathValue($delivery_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'POST',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation postWebhookTest
     *
     * Test a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\PostWebhookTestRequest|null $post_webhook_test_request post_webhook_test_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookTest'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\PostWebhookTest202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function postWebhookTest($webhook_id, $idempotency_key = null, $post_webhook_test_request = null, string $contentType = self::contentTypes['postWebhookTest'][0])
    {
        list($response) = $this->postWebhookTestWithHttpInfo($webhook_id, $idempotency_key, $post_webhook_test_request, $contentType);
        return $response;
    }

    /**
     * Operation postWebhookTestWithHttpInfo
     *
     * Test a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\PostWebhookTestRequest|null $post_webhook_test_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookTest'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\PostWebhookTest202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postWebhookTestWithHttpInfo($webhook_id, $idempotency_key = null, $post_webhook_test_request = null, string $contentType = self::contentTypes['postWebhookTest'][0])
    {
        $request = $this->postWebhookTestRequest($webhook_id, $idempotency_key, $post_webhook_test_request, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 202:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\PostWebhookTest202Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\PostWebhookTest202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\PostWebhookTest202Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postWebhookTestAsync
     *
     * Test a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\PostWebhookTestRequest|null $post_webhook_test_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookTest'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhookTestAsync($webhook_id, $idempotency_key = null, $post_webhook_test_request = null, string $contentType = self::contentTypes['postWebhookTest'][0])
    {
        return $this->postWebhookTestAsyncWithHttpInfo($webhook_id, $idempotency_key, $post_webhook_test_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postWebhookTestAsyncWithHttpInfo
     *
     * Test a Webhook Endpoint
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\PostWebhookTestRequest|null $post_webhook_test_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookTest'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhookTestAsyncWithHttpInfo($webhook_id, $idempotency_key = null, $post_webhook_test_request = null, string $contentType = self::contentTypes['postWebhookTest'][0])
    {
        $returnType = '\CheckNetworks\Model\PostWebhookTest202Response';
        $request = $this->postWebhookTestRequest($webhook_id, $idempotency_key, $post_webhook_test_request, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'postWebhookTest'
     *
     * @param  string $webhook_id The webhook endpoint&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\PostWebhookTestRequest|null $post_webhook_test_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhookTest'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postWebhookTestRequest($webhook_id, $idempotency_key = null, $post_webhook_test_request = null, string $contentType = self::contentTypes['postWebhookTest'][0])
    {

        // verify the required parameter 'webhook_id' is set
        if ($webhook_id === null || (is_array($webhook_id) && count($webhook_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_id when calling postWebhookTest'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling WebhooksApi.postWebhookTest, must be smaller than or equal to 255.');
        }
        


        $resourcePath = '/webhooks/{webhookId}/test';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }

        // path params
        if ($webhook_id !== null) {
            $resourcePath = str_replace(
                '{webhookId}',
                ObjectSerializer::toPathValue($webhook_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($post_webhook_test_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($post_webhook_test_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $post_webhook_test_request;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'POST',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation postWebhooks
     *
     * Register a Webhook Endpoint
     *
     * @param  \CheckNetworks\Model\PostWebhooksRequest $post_webhooks_request post_webhooks_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhooks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\PostWebhooks201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function postWebhooks($post_webhooks_request, $idempotency_key = null, string $contentType = self::contentTypes['postWebhooks'][0])
    {
        list($response) = $this->postWebhooksWithHttpInfo($post_webhooks_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation postWebhooksWithHttpInfo
     *
     * Register a Webhook Endpoint
     *
     * @param  \CheckNetworks\Model\PostWebhooksRequest $post_webhooks_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhooks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\PostWebhooks201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postWebhooksWithHttpInfo($post_webhooks_request, $idempotency_key = null, string $contentType = self::contentTypes['postWebhooks'][0])
    {
        $request = $this->postWebhooksRequest($post_webhooks_request, $idempotency_key, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 201:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\PostWebhooks201Response',
                        $request,
                        $response,
                    );
                case 400:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Model\Error',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\CheckNetworks\Model\PostWebhooks201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\PostWebhooks201Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postWebhooksAsync
     *
     * Register a Webhook Endpoint
     *
     * @param  \CheckNetworks\Model\PostWebhooksRequest $post_webhooks_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhooks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhooksAsync($post_webhooks_request, $idempotency_key = null, string $contentType = self::contentTypes['postWebhooks'][0])
    {
        return $this->postWebhooksAsyncWithHttpInfo($post_webhooks_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postWebhooksAsyncWithHttpInfo
     *
     * Register a Webhook Endpoint
     *
     * @param  \CheckNetworks\Model\PostWebhooksRequest $post_webhooks_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhooks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postWebhooksAsyncWithHttpInfo($post_webhooks_request, $idempotency_key = null, string $contentType = self::contentTypes['postWebhooks'][0])
    {
        $returnType = '\CheckNetworks\Model\PostWebhooks201Response';
        $request = $this->postWebhooksRequest($post_webhooks_request, $idempotency_key, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'postWebhooks'
     *
     * @param  \CheckNetworks\Model\PostWebhooksRequest $post_webhooks_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postWebhooks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postWebhooksRequest($post_webhooks_request, $idempotency_key = null, string $contentType = self::contentTypes['postWebhooks'][0])
    {

        // verify the required parameter 'post_webhooks_request' is set
        if ($post_webhooks_request === null || (is_array($post_webhooks_request) && count($post_webhooks_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $post_webhooks_request when calling postWebhooks'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling WebhooksApi.postWebhooks, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/webhooks';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;


        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }



        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($post_webhooks_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($post_webhooks_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $post_webhooks_request;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                try {
                    $httpBody = json_encode($formParams, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }
        // this endpoint requires OAuth (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'POST',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        if ($this->config->getCertFile()) {
            $options[RequestOptions::CERT] = $this->config->getCertFile();
        }

        if ($this->config->getKeyFile()) {
            $options[RequestOptions::SSL_KEY] = $this->config->getKeyFile();
        }

        return $options;
    }

    private function handleResponseWithDataType(
        string $dataType,
        RequestInterface $request,
        ResponseInterface $response
    ): array {
        if ($dataType === '\SplFileObject') {
            $content = $response->getBody(); //stream goes to serializer
        } else {
            $content = (string) $response->getBody();
            if ($dataType !== 'string') {
                try {
                    $content = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    throw new ApiException(
                        sprintf(
                            'Error JSON decoding server response (%s)',
                            $request->getUri()
                        ),
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $content
                    );
                }
            }
        }

        return [
            ObjectSerializer::deserialize($content, $dataType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    private function responseWithinRangeCode(
        string $rangeCode,
        int $statusCode
    ): bool {
        $left = (int) ($rangeCode[0].'00');
        $right = (int) ($rangeCode[0].'99');

        return $statusCode >= $left && $statusCode <= $right;
    }
}
