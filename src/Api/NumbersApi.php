<?php
/**
 * NumbersApi
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
 * NumbersApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class NumbersApi
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
        'bulkNumbers' => [
            'application/json',
        ],
        'cancelNumber' => [
            'application/json',
        ],
        'findNumberingAreas' => [
            'application/json',
        ],
        'getBulkNumbers' => [
            'application/json',
        ],
        'getNumber' => [
            'application/json',
        ],
        'getNumberOrder' => [
            'application/json',
        ],
        'holdNumber' => [
            'application/json',
        ],
        'listNumberOrders' => [
            'application/json',
        ],
        'listNumbers' => [
            'application/json',
        ],
        'lookupNumbers' => [
            'application/json',
        ],
        'orderNumber' => [
            'application/json',
        ],
        'searchAvailableNumbers' => [
            'application/json',
        ],
        'updateNumber' => [
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
     * Operation bulkNumbers
     *
     * Change Many Numbers
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\BulkNumbersRequest $bulk_numbers_request bulk_numbers_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['bulkNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\BulkNumbers202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function bulkNumbers($idempotency_key, $bulk_numbers_request, string $contentType = self::contentTypes['bulkNumbers'][0])
    {
        list($response) = $this->bulkNumbersWithHttpInfo($idempotency_key, $bulk_numbers_request, $contentType);
        return $response;
    }

    /**
     * Operation bulkNumbersWithHttpInfo
     *
     * Change Many Numbers
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\BulkNumbersRequest $bulk_numbers_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['bulkNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\BulkNumbers202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function bulkNumbersWithHttpInfo($idempotency_key, $bulk_numbers_request, string $contentType = self::contentTypes['bulkNumbers'][0])
    {
        $request = $this->bulkNumbersRequest($idempotency_key, $bulk_numbers_request, $contentType);

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
                        '\CheckNetworks\Model\BulkNumbers202Response',
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
                '\CheckNetworks\Model\BulkNumbers202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\BulkNumbers202Response',
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
     * Operation bulkNumbersAsync
     *
     * Change Many Numbers
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\BulkNumbersRequest $bulk_numbers_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['bulkNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function bulkNumbersAsync($idempotency_key, $bulk_numbers_request, string $contentType = self::contentTypes['bulkNumbers'][0])
    {
        return $this->bulkNumbersAsyncWithHttpInfo($idempotency_key, $bulk_numbers_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation bulkNumbersAsyncWithHttpInfo
     *
     * Change Many Numbers
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\BulkNumbersRequest $bulk_numbers_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['bulkNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function bulkNumbersAsyncWithHttpInfo($idempotency_key, $bulk_numbers_request, string $contentType = self::contentTypes['bulkNumbers'][0])
    {
        $returnType = '\CheckNetworks\Model\BulkNumbers202Response';
        $request = $this->bulkNumbersRequest($idempotency_key, $bulk_numbers_request, $contentType);

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
     * Create request for operation 'bulkNumbers'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\BulkNumbersRequest $bulk_numbers_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['bulkNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function bulkNumbersRequest($idempotency_key, $bulk_numbers_request, string $contentType = self::contentTypes['bulkNumbers'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling bulkNumbers'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling NumbersApi.bulkNumbers, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'bulk_numbers_request' is set
        if ($bulk_numbers_request === null || (is_array($bulk_numbers_request) && count($bulk_numbers_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $bulk_numbers_request when calling bulkNumbers'
            );
        }


        $resourcePath = '/numbers/bulk';
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
        if (isset($bulk_numbers_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($bulk_numbers_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $bulk_numbers_request;
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
     * Operation cancelNumber
     *
     * Cancel a Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\CancelNumberRequest|null $cancel_number_request cancel_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\CancelNumber202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function cancelNumber($number, $idempotency_key = null, $cancel_number_request = null, string $contentType = self::contentTypes['cancelNumber'][0])
    {
        list($response) = $this->cancelNumberWithHttpInfo($number, $idempotency_key, $cancel_number_request, $contentType);
        return $response;
    }

    /**
     * Operation cancelNumberWithHttpInfo
     *
     * Cancel a Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\CancelNumberRequest|null $cancel_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\CancelNumber202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function cancelNumberWithHttpInfo($number, $idempotency_key = null, $cancel_number_request = null, string $contentType = self::contentTypes['cancelNumber'][0])
    {
        $request = $this->cancelNumberRequest($number, $idempotency_key, $cancel_number_request, $contentType);

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
                        '\CheckNetworks\Model\CancelNumber202Response',
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
                '\CheckNetworks\Model\CancelNumber202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\CancelNumber202Response',
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
     * Operation cancelNumberAsync
     *
     * Cancel a Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\CancelNumberRequest|null $cancel_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function cancelNumberAsync($number, $idempotency_key = null, $cancel_number_request = null, string $contentType = self::contentTypes['cancelNumber'][0])
    {
        return $this->cancelNumberAsyncWithHttpInfo($number, $idempotency_key, $cancel_number_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation cancelNumberAsyncWithHttpInfo
     *
     * Cancel a Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\CancelNumberRequest|null $cancel_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function cancelNumberAsyncWithHttpInfo($number, $idempotency_key = null, $cancel_number_request = null, string $contentType = self::contentTypes['cancelNumber'][0])
    {
        $returnType = '\CheckNetworks\Model\CancelNumber202Response';
        $request = $this->cancelNumberRequest($number, $idempotency_key, $cancel_number_request, $contentType);

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
     * Create request for operation 'cancelNumber'
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\CancelNumberRequest|null $cancel_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function cancelNumberRequest($number, $idempotency_key = null, $cancel_number_request = null, string $contentType = self::contentTypes['cancelNumber'][0])
    {

        // verify the required parameter 'number' is set
        if ($number === null || (is_array($number) && count($number) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $number when calling cancelNumber'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling NumbersApi.cancelNumber, must be smaller than or equal to 255.');
        }
        


        $resourcePath = '/numbers/{number}';
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
        if ($number !== null) {
            $resourcePath = str_replace(
                '{number}',
                ObjectSerializer::toPathValue($number),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($cancel_number_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($cancel_number_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $cancel_number_request;
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
            'DELETE',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation findNumberingAreas
     *
     * Find the Numbering Area for an Address
     *
     * @param  string $address The &#x60;fullAddress&#x60; of a &#x60;GET /addresses/search&#x60; result. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['findNumberingAreas'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\FindNumberingAreas200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function findNumberingAreas($address, string $contentType = self::contentTypes['findNumberingAreas'][0])
    {
        list($response) = $this->findNumberingAreasWithHttpInfo($address, $contentType);
        return $response;
    }

    /**
     * Operation findNumberingAreasWithHttpInfo
     *
     * Find the Numbering Area for an Address
     *
     * @param  string $address The &#x60;fullAddress&#x60; of a &#x60;GET /addresses/search&#x60; result. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['findNumberingAreas'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\FindNumberingAreas200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function findNumberingAreasWithHttpInfo($address, string $contentType = self::contentTypes['findNumberingAreas'][0])
    {
        $request = $this->findNumberingAreasRequest($address, $contentType);

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
                        '\CheckNetworks\Model\FindNumberingAreas200Response',
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
                '\CheckNetworks\Model\FindNumberingAreas200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\FindNumberingAreas200Response',
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
     * Operation findNumberingAreasAsync
     *
     * Find the Numbering Area for an Address
     *
     * @param  string $address The &#x60;fullAddress&#x60; of a &#x60;GET /addresses/search&#x60; result. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['findNumberingAreas'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function findNumberingAreasAsync($address, string $contentType = self::contentTypes['findNumberingAreas'][0])
    {
        return $this->findNumberingAreasAsyncWithHttpInfo($address, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation findNumberingAreasAsyncWithHttpInfo
     *
     * Find the Numbering Area for an Address
     *
     * @param  string $address The &#x60;fullAddress&#x60; of a &#x60;GET /addresses/search&#x60; result. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['findNumberingAreas'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function findNumberingAreasAsyncWithHttpInfo($address, string $contentType = self::contentTypes['findNumberingAreas'][0])
    {
        $returnType = '\CheckNetworks\Model\FindNumberingAreas200Response';
        $request = $this->findNumberingAreasRequest($address, $contentType);

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
     * Create request for operation 'findNumberingAreas'
     *
     * @param  string $address The &#x60;fullAddress&#x60; of a &#x60;GET /addresses/search&#x60; result. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['findNumberingAreas'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function findNumberingAreasRequest($address, string $contentType = self::contentTypes['findNumberingAreas'][0])
    {

        // verify the required parameter 'address' is set
        if ($address === null || (is_array($address) && count($address) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $address when calling findNumberingAreas'
            );
        }
        if (strlen($address) > 255) {
            throw new \InvalidArgumentException('invalid length for "$address" when calling NumbersApi.findNumberingAreas, must be smaller than or equal to 255.');
        }
        if (strlen($address) < 4) {
            throw new \InvalidArgumentException('invalid length for "$address" when calling NumbersApi.findNumberingAreas, must be bigger than or equal to 4.');
        }
        

        $resourcePath = '/numbers/areas';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $address,
            'address', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            true // required
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
     * Operation getBulkNumbers
     *
     * Get a Bulk Number Change
     *
     * @param  string $job_id The &#x60;jobId&#x60; from &#x60;POST /numbers/bulk&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getBulkNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\BulkNumbers202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getBulkNumbers($job_id, string $contentType = self::contentTypes['getBulkNumbers'][0])
    {
        list($response) = $this->getBulkNumbersWithHttpInfo($job_id, $contentType);
        return $response;
    }

    /**
     * Operation getBulkNumbersWithHttpInfo
     *
     * Get a Bulk Number Change
     *
     * @param  string $job_id The &#x60;jobId&#x60; from &#x60;POST /numbers/bulk&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getBulkNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\BulkNumbers202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getBulkNumbersWithHttpInfo($job_id, string $contentType = self::contentTypes['getBulkNumbers'][0])
    {
        $request = $this->getBulkNumbersRequest($job_id, $contentType);

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
                        '\CheckNetworks\Model\BulkNumbers202Response',
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
                '\CheckNetworks\Model\BulkNumbers202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\BulkNumbers202Response',
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
     * Operation getBulkNumbersAsync
     *
     * Get a Bulk Number Change
     *
     * @param  string $job_id The &#x60;jobId&#x60; from &#x60;POST /numbers/bulk&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getBulkNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getBulkNumbersAsync($job_id, string $contentType = self::contentTypes['getBulkNumbers'][0])
    {
        return $this->getBulkNumbersAsyncWithHttpInfo($job_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getBulkNumbersAsyncWithHttpInfo
     *
     * Get a Bulk Number Change
     *
     * @param  string $job_id The &#x60;jobId&#x60; from &#x60;POST /numbers/bulk&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getBulkNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getBulkNumbersAsyncWithHttpInfo($job_id, string $contentType = self::contentTypes['getBulkNumbers'][0])
    {
        $returnType = '\CheckNetworks\Model\BulkNumbers202Response';
        $request = $this->getBulkNumbersRequest($job_id, $contentType);

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
     * Create request for operation 'getBulkNumbers'
     *
     * @param  string $job_id The &#x60;jobId&#x60; from &#x60;POST /numbers/bulk&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getBulkNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getBulkNumbersRequest($job_id, string $contentType = self::contentTypes['getBulkNumbers'][0])
    {

        // verify the required parameter 'job_id' is set
        if ($job_id === null || (is_array($job_id) && count($job_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $job_id when calling getBulkNumbers'
            );
        }


        $resourcePath = '/numbers/bulk/{jobId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($job_id !== null) {
            $resourcePath = str_replace(
                '{jobId}',
                ObjectSerializer::toPathValue($job_id),
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
     * Operation getNumber
     *
     * Get Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetNumber200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getNumber($number, string $contentType = self::contentTypes['getNumber'][0])
    {
        list($response) = $this->getNumberWithHttpInfo($number, $contentType);
        return $response;
    }

    /**
     * Operation getNumberWithHttpInfo
     *
     * Get Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetNumber200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getNumberWithHttpInfo($number, string $contentType = self::contentTypes['getNumber'][0])
    {
        $request = $this->getNumberRequest($number, $contentType);

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
                        '\CheckNetworks\Model\GetNumber200Response',
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
                '\CheckNetworks\Model\GetNumber200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetNumber200Response',
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
     * Operation getNumberAsync
     *
     * Get Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getNumberAsync($number, string $contentType = self::contentTypes['getNumber'][0])
    {
        return $this->getNumberAsyncWithHttpInfo($number, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getNumberAsyncWithHttpInfo
     *
     * Get Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getNumberAsyncWithHttpInfo($number, string $contentType = self::contentTypes['getNumber'][0])
    {
        $returnType = '\CheckNetworks\Model\GetNumber200Response';
        $request = $this->getNumberRequest($number, $contentType);

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
     * Create request for operation 'getNumber'
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getNumberRequest($number, string $contentType = self::contentTypes['getNumber'][0])
    {

        // verify the required parameter 'number' is set
        if ($number === null || (is_array($number) && count($number) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $number when calling getNumber'
            );
        }


        $resourcePath = '/numbers/{number}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($number !== null) {
            $resourcePath = str_replace(
                '{number}',
                ObjectSerializer::toPathValue($number),
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
     * Operation getNumberOrder
     *
     * Retrieve a Number Order
     *
     * @param  string $id id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumberOrder'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\OrderNumber202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getNumberOrder($id, string $contentType = self::contentTypes['getNumberOrder'][0])
    {
        list($response) = $this->getNumberOrderWithHttpInfo($id, $contentType);
        return $response;
    }

    /**
     * Operation getNumberOrderWithHttpInfo
     *
     * Retrieve a Number Order
     *
     * @param  string $id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumberOrder'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\OrderNumber202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getNumberOrderWithHttpInfo($id, string $contentType = self::contentTypes['getNumberOrder'][0])
    {
        $request = $this->getNumberOrderRequest($id, $contentType);

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
                        '\CheckNetworks\Model\OrderNumber202Response',
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
                '\CheckNetworks\Model\OrderNumber202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\OrderNumber202Response',
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
            }
        

            throw $e;
        }
    }

    /**
     * Operation getNumberOrderAsync
     *
     * Retrieve a Number Order
     *
     * @param  string $id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumberOrder'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getNumberOrderAsync($id, string $contentType = self::contentTypes['getNumberOrder'][0])
    {
        return $this->getNumberOrderAsyncWithHttpInfo($id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getNumberOrderAsyncWithHttpInfo
     *
     * Retrieve a Number Order
     *
     * @param  string $id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumberOrder'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getNumberOrderAsyncWithHttpInfo($id, string $contentType = self::contentTypes['getNumberOrder'][0])
    {
        $returnType = '\CheckNetworks\Model\OrderNumber202Response';
        $request = $this->getNumberOrderRequest($id, $contentType);

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
     * Create request for operation 'getNumberOrder'
     *
     * @param  string $id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getNumberOrder'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getNumberOrderRequest($id, string $contentType = self::contentTypes['getNumberOrder'][0])
    {

        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling getNumberOrder'
            );
        }


        $resourcePath = '/numbers/orders/{id}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($id !== null) {
            $resourcePath = str_replace(
                '{id}',
                ObjectSerializer::toPathValue($id),
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
     * Operation holdNumber
     *
     * Hold a Number
     *
     * @param  \CheckNetworks\Model\HoldNumberRequest $hold_number_request hold_number_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['holdNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\HoldNumber201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function holdNumber($hold_number_request, $idempotency_key = null, string $contentType = self::contentTypes['holdNumber'][0])
    {
        list($response) = $this->holdNumberWithHttpInfo($hold_number_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation holdNumberWithHttpInfo
     *
     * Hold a Number
     *
     * @param  \CheckNetworks\Model\HoldNumberRequest $hold_number_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['holdNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\HoldNumber201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function holdNumberWithHttpInfo($hold_number_request, $idempotency_key = null, string $contentType = self::contentTypes['holdNumber'][0])
    {
        $request = $this->holdNumberRequest($hold_number_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\HoldNumber201Response',
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
                '\CheckNetworks\Model\HoldNumber201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\HoldNumber201Response',
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
            }
        

            throw $e;
        }
    }

    /**
     * Operation holdNumberAsync
     *
     * Hold a Number
     *
     * @param  \CheckNetworks\Model\HoldNumberRequest $hold_number_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['holdNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function holdNumberAsync($hold_number_request, $idempotency_key = null, string $contentType = self::contentTypes['holdNumber'][0])
    {
        return $this->holdNumberAsyncWithHttpInfo($hold_number_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation holdNumberAsyncWithHttpInfo
     *
     * Hold a Number
     *
     * @param  \CheckNetworks\Model\HoldNumberRequest $hold_number_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['holdNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function holdNumberAsyncWithHttpInfo($hold_number_request, $idempotency_key = null, string $contentType = self::contentTypes['holdNumber'][0])
    {
        $returnType = '\CheckNetworks\Model\HoldNumber201Response';
        $request = $this->holdNumberRequest($hold_number_request, $idempotency_key, $contentType);

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
     * Create request for operation 'holdNumber'
     *
     * @param  \CheckNetworks\Model\HoldNumberRequest $hold_number_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['holdNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function holdNumberRequest($hold_number_request, $idempotency_key = null, string $contentType = self::contentTypes['holdNumber'][0])
    {

        // verify the required parameter 'hold_number_request' is set
        if ($hold_number_request === null || (is_array($hold_number_request) && count($hold_number_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $hold_number_request when calling holdNumber'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling NumbersApi.holdNumber, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/numbers/holds';
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
        if (isset($hold_number_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($hold_number_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $hold_number_request;
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
     * Operation listNumberOrders
     *
     * List Number Orders
     *
     * @param  string|null $status status (optional)
     * @param  int|null $page page (optional, default to 1)
     * @param  int|null $per_page per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumberOrders'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\ListNumberOrders200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function listNumberOrders($status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listNumberOrders'][0])
    {
        list($response) = $this->listNumberOrdersWithHttpInfo($status, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation listNumberOrdersWithHttpInfo
     *
     * List Number Orders
     *
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumberOrders'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\ListNumberOrders200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listNumberOrdersWithHttpInfo($status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listNumberOrders'][0])
    {
        $request = $this->listNumberOrdersRequest($status, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Model\ListNumberOrders200Response',
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
                '\CheckNetworks\Model\ListNumberOrders200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\ListNumberOrders200Response',
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
            }
        

            throw $e;
        }
    }

    /**
     * Operation listNumberOrdersAsync
     *
     * List Number Orders
     *
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumberOrders'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listNumberOrdersAsync($status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listNumberOrders'][0])
    {
        return $this->listNumberOrdersAsyncWithHttpInfo($status, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listNumberOrdersAsyncWithHttpInfo
     *
     * List Number Orders
     *
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumberOrders'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listNumberOrdersAsyncWithHttpInfo($status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listNumberOrders'][0])
    {
        $returnType = '\CheckNetworks\Model\ListNumberOrders200Response';
        $request = $this->listNumberOrdersRequest($status, $page, $per_page, $contentType);

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
     * Create request for operation 'listNumberOrders'
     *
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumberOrders'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listNumberOrdersRequest($status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listNumberOrders'][0])
    {


        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling NumbersApi.listNumberOrders, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling NumbersApi.listNumberOrders, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling NumbersApi.listNumberOrders, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/numbers/orders';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $status,
            'status', // param base name
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
     * Operation listNumbers
     *
     * List Phone Numbers
     *
     * @param  string|null $q Part of a number or of its description. A national number matches too: &#x60;07&#x60; finds numbers stored as &#x60;617&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $type The number type. (optional)
     * @param  string|null $szu The standard zone unit (SZU) of the number&#39;s block, by name. (optional)
     * @param  string|null $status status (optional)
     * @param  string|null $trunk Only the numbers on this trunk, by its &#x60;sipUsername&#x60;. A trunk that is not yours is a 422. (optional)
     * @param  string|null $route_type Only the numbers whose first route is of this type. (optional)
     * @param  string|null $assigned &#x60;false&#x60; for the numbers on no trunk; &#x60;true&#x60; for those on one. (optional)
     * @param  string|null $has_emergency_address &#x60;false&#x60; for the numbers with no emergency address linked; &#x60;true&#x60; for those with one. (optional)
     * @param  string|null $format Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams every match. (optional)
     * @param  string|null $end_user Only the numbers this end user holds, by its public number: those tagged to it, and untagged ones on its trunks. A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string|null $needs_attention Only the numbers in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\ListNumbers200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function listNumbers($q = null, $external_ref = null, $type = null, $szu = null, $status = null, $trunk = null, $route_type = null, $assigned = null, $has_emergency_address = null, $format = null, $end_user = null, $needs_attention = null, $page = 1, $per_page = 50, $sort = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listNumbers'][0])
    {
        list($response) = $this->listNumbersWithHttpInfo($q, $external_ref, $type, $szu, $status, $trunk, $route_type, $assigned, $has_emergency_address, $format, $end_user, $needs_attention, $page, $per_page, $sort, $updated_since, $created_from, $created_to, $contentType);
        return $response;
    }

    /**
     * Operation listNumbersWithHttpInfo
     *
     * List Phone Numbers
     *
     * @param  string|null $q Part of a number or of its description. A national number matches too: &#x60;07&#x60; finds numbers stored as &#x60;617&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $type The number type. (optional)
     * @param  string|null $szu The standard zone unit (SZU) of the number&#39;s block, by name. (optional)
     * @param  string|null $status (optional)
     * @param  string|null $trunk Only the numbers on this trunk, by its &#x60;sipUsername&#x60;. A trunk that is not yours is a 422. (optional)
     * @param  string|null $route_type Only the numbers whose first route is of this type. (optional)
     * @param  string|null $assigned &#x60;false&#x60; for the numbers on no trunk; &#x60;true&#x60; for those on one. (optional)
     * @param  string|null $has_emergency_address &#x60;false&#x60; for the numbers with no emergency address linked; &#x60;true&#x60; for those with one. (optional)
     * @param  string|null $format Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams every match. (optional)
     * @param  string|null $end_user Only the numbers this end user holds, by its public number: those tagged to it, and untagged ones on its trunks. A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string|null $needs_attention Only the numbers in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\ListNumbers200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listNumbersWithHttpInfo($q = null, $external_ref = null, $type = null, $szu = null, $status = null, $trunk = null, $route_type = null, $assigned = null, $has_emergency_address = null, $format = null, $end_user = null, $needs_attention = null, $page = 1, $per_page = 50, $sort = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listNumbers'][0])
    {
        $request = $this->listNumbersRequest($q, $external_ref, $type, $szu, $status, $trunk, $route_type, $assigned, $has_emergency_address, $format, $end_user, $needs_attention, $page, $per_page, $sort, $updated_since, $created_from, $created_to, $contentType);

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
                        '\CheckNetworks\Model\ListNumbers200Response',
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
                '\CheckNetworks\Model\ListNumbers200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\ListNumbers200Response',
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
     * Operation listNumbersAsync
     *
     * List Phone Numbers
     *
     * @param  string|null $q Part of a number or of its description. A national number matches too: &#x60;07&#x60; finds numbers stored as &#x60;617&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $type The number type. (optional)
     * @param  string|null $szu The standard zone unit (SZU) of the number&#39;s block, by name. (optional)
     * @param  string|null $status (optional)
     * @param  string|null $trunk Only the numbers on this trunk, by its &#x60;sipUsername&#x60;. A trunk that is not yours is a 422. (optional)
     * @param  string|null $route_type Only the numbers whose first route is of this type. (optional)
     * @param  string|null $assigned &#x60;false&#x60; for the numbers on no trunk; &#x60;true&#x60; for those on one. (optional)
     * @param  string|null $has_emergency_address &#x60;false&#x60; for the numbers with no emergency address linked; &#x60;true&#x60; for those with one. (optional)
     * @param  string|null $format Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams every match. (optional)
     * @param  string|null $end_user Only the numbers this end user holds, by its public number: those tagged to it, and untagged ones on its trunks. A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string|null $needs_attention Only the numbers in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listNumbersAsync($q = null, $external_ref = null, $type = null, $szu = null, $status = null, $trunk = null, $route_type = null, $assigned = null, $has_emergency_address = null, $format = null, $end_user = null, $needs_attention = null, $page = 1, $per_page = 50, $sort = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listNumbers'][0])
    {
        return $this->listNumbersAsyncWithHttpInfo($q, $external_ref, $type, $szu, $status, $trunk, $route_type, $assigned, $has_emergency_address, $format, $end_user, $needs_attention, $page, $per_page, $sort, $updated_since, $created_from, $created_to, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listNumbersAsyncWithHttpInfo
     *
     * List Phone Numbers
     *
     * @param  string|null $q Part of a number or of its description. A national number matches too: &#x60;07&#x60; finds numbers stored as &#x60;617&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $type The number type. (optional)
     * @param  string|null $szu The standard zone unit (SZU) of the number&#39;s block, by name. (optional)
     * @param  string|null $status (optional)
     * @param  string|null $trunk Only the numbers on this trunk, by its &#x60;sipUsername&#x60;. A trunk that is not yours is a 422. (optional)
     * @param  string|null $route_type Only the numbers whose first route is of this type. (optional)
     * @param  string|null $assigned &#x60;false&#x60; for the numbers on no trunk; &#x60;true&#x60; for those on one. (optional)
     * @param  string|null $has_emergency_address &#x60;false&#x60; for the numbers with no emergency address linked; &#x60;true&#x60; for those with one. (optional)
     * @param  string|null $format Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams every match. (optional)
     * @param  string|null $end_user Only the numbers this end user holds, by its public number: those tagged to it, and untagged ones on its trunks. A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string|null $needs_attention Only the numbers in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listNumbersAsyncWithHttpInfo($q = null, $external_ref = null, $type = null, $szu = null, $status = null, $trunk = null, $route_type = null, $assigned = null, $has_emergency_address = null, $format = null, $end_user = null, $needs_attention = null, $page = 1, $per_page = 50, $sort = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listNumbers'][0])
    {
        $returnType = '\CheckNetworks\Model\ListNumbers200Response';
        $request = $this->listNumbersRequest($q, $external_ref, $type, $szu, $status, $trunk, $route_type, $assigned, $has_emergency_address, $format, $end_user, $needs_attention, $page, $per_page, $sort, $updated_since, $created_from, $created_to, $contentType);

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
     * Create request for operation 'listNumbers'
     *
     * @param  string|null $q Part of a number or of its description. A national number matches too: &#x60;07&#x60; finds numbers stored as &#x60;617&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $type The number type. (optional)
     * @param  string|null $szu The standard zone unit (SZU) of the number&#39;s block, by name. (optional)
     * @param  string|null $status (optional)
     * @param  string|null $trunk Only the numbers on this trunk, by its &#x60;sipUsername&#x60;. A trunk that is not yours is a 422. (optional)
     * @param  string|null $route_type Only the numbers whose first route is of this type. (optional)
     * @param  string|null $assigned &#x60;false&#x60; for the numbers on no trunk; &#x60;true&#x60; for those on one. (optional)
     * @param  string|null $has_emergency_address &#x60;false&#x60; for the numbers with no emergency address linked; &#x60;true&#x60; for those with one. (optional)
     * @param  string|null $format Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams every match. (optional)
     * @param  string|null $end_user Only the numbers this end user holds, by its public number: those tagged to it, and untagged ones on its trunks. A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string|null $needs_attention Only the numbers in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listNumbersRequest($q = null, $external_ref = null, $type = null, $szu = null, $status = null, $trunk = null, $route_type = null, $assigned = null, $has_emergency_address = null, $format = null, $end_user = null, $needs_attention = null, $page = 1, $per_page = 50, $sort = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listNumbers'][0])
    {

        if ($q !== null && strlen($q) > 100) {
            throw new \InvalidArgumentException('invalid length for "$q" when calling NumbersApi.listNumbers, must be smaller than or equal to 100.');
        }
        
        if ($external_ref !== null && strlen($external_ref) > 64) {
            throw new \InvalidArgumentException('invalid length for "$external_ref" when calling NumbersApi.listNumbers, must be smaller than or equal to 64.');
        }
        if ($external_ref !== null && strlen($external_ref) < 1) {
            throw new \InvalidArgumentException('invalid length for "$external_ref" when calling NumbersApi.listNumbers, must be bigger than or equal to 1.');
        }
        if ($external_ref !== null && !preg_match("/^[A-Za-z0-9_.\\-]+$/", $external_ref)) {
            throw new \InvalidArgumentException("invalid value for \"external_ref\" when calling NumbersApi.listNumbers, must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
        }
        








        if ($end_user !== null && strlen($end_user) > 64) {
            throw new \InvalidArgumentException('invalid length for "$end_user" when calling NumbersApi.listNumbers, must be smaller than or equal to 64.');
        }
        

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling NumbersApi.listNumbers, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling NumbersApi.listNumbers, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling NumbersApi.listNumbers, must be bigger than or equal to 1.');
        }
        





        $resourcePath = '/numbers';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

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
            $external_ref,
            'externalRef', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
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
            $szu,
            'szu', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $status,
            'status', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $trunk,
            'trunk', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $route_type,
            'routeType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $assigned,
            'assigned', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $has_emergency_address,
            'hasEmergencyAddress', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $format,
            'format', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $end_user,
            'endUser', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $needs_attention,
            'needsAttention', // param base name
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
            $sort,
            'sort', // param base name
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
            ['application/json', 'text/csv', 'application/x-ndjson', ],
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
     * Operation lookupNumbers
     *
     * Look Up Numbers
     *
     * @param  \CheckNetworks\Model\LookupNumbersRequest $lookup_numbers_request lookup_numbers_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['lookupNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\LookupNumbers200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function lookupNumbers($lookup_numbers_request, $idempotency_key = null, string $contentType = self::contentTypes['lookupNumbers'][0])
    {
        list($response) = $this->lookupNumbersWithHttpInfo($lookup_numbers_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation lookupNumbersWithHttpInfo
     *
     * Look Up Numbers
     *
     * @param  \CheckNetworks\Model\LookupNumbersRequest $lookup_numbers_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['lookupNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\LookupNumbers200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function lookupNumbersWithHttpInfo($lookup_numbers_request, $idempotency_key = null, string $contentType = self::contentTypes['lookupNumbers'][0])
    {
        $request = $this->lookupNumbersRequest($lookup_numbers_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\LookupNumbers200Response',
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
                '\CheckNetworks\Model\LookupNumbers200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\LookupNumbers200Response',
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
     * Operation lookupNumbersAsync
     *
     * Look Up Numbers
     *
     * @param  \CheckNetworks\Model\LookupNumbersRequest $lookup_numbers_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['lookupNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function lookupNumbersAsync($lookup_numbers_request, $idempotency_key = null, string $contentType = self::contentTypes['lookupNumbers'][0])
    {
        return $this->lookupNumbersAsyncWithHttpInfo($lookup_numbers_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation lookupNumbersAsyncWithHttpInfo
     *
     * Look Up Numbers
     *
     * @param  \CheckNetworks\Model\LookupNumbersRequest $lookup_numbers_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['lookupNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function lookupNumbersAsyncWithHttpInfo($lookup_numbers_request, $idempotency_key = null, string $contentType = self::contentTypes['lookupNumbers'][0])
    {
        $returnType = '\CheckNetworks\Model\LookupNumbers200Response';
        $request = $this->lookupNumbersRequest($lookup_numbers_request, $idempotency_key, $contentType);

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
     * Create request for operation 'lookupNumbers'
     *
     * @param  \CheckNetworks\Model\LookupNumbersRequest $lookup_numbers_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['lookupNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function lookupNumbersRequest($lookup_numbers_request, $idempotency_key = null, string $contentType = self::contentTypes['lookupNumbers'][0])
    {

        // verify the required parameter 'lookup_numbers_request' is set
        if ($lookup_numbers_request === null || (is_array($lookup_numbers_request) && count($lookup_numbers_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $lookup_numbers_request when calling lookupNumbers'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling NumbersApi.lookupNumbers, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/numbers/lookup';
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
        if (isset($lookup_numbers_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($lookup_numbers_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $lookup_numbers_request;
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
     * Operation orderNumber
     *
     * Order a Number
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\OrderNumberRequest $order_number_request order_number_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['orderNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\OrderNumber202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function orderNumber($idempotency_key, $order_number_request, string $contentType = self::contentTypes['orderNumber'][0])
    {
        list($response) = $this->orderNumberWithHttpInfo($idempotency_key, $order_number_request, $contentType);
        return $response;
    }

    /**
     * Operation orderNumberWithHttpInfo
     *
     * Order a Number
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\OrderNumberRequest $order_number_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['orderNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\OrderNumber202Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function orderNumberWithHttpInfo($idempotency_key, $order_number_request, string $contentType = self::contentTypes['orderNumber'][0])
    {
        $request = $this->orderNumberRequest($idempotency_key, $order_number_request, $contentType);

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
                        '\CheckNetworks\Model\OrderNumber202Response',
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
                '\CheckNetworks\Model\OrderNumber202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\OrderNumber202Response',
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
     * Operation orderNumberAsync
     *
     * Order a Number
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\OrderNumberRequest $order_number_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['orderNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function orderNumberAsync($idempotency_key, $order_number_request, string $contentType = self::contentTypes['orderNumber'][0])
    {
        return $this->orderNumberAsyncWithHttpInfo($idempotency_key, $order_number_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation orderNumberAsyncWithHttpInfo
     *
     * Order a Number
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\OrderNumberRequest $order_number_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['orderNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function orderNumberAsyncWithHttpInfo($idempotency_key, $order_number_request, string $contentType = self::contentTypes['orderNumber'][0])
    {
        $returnType = '\CheckNetworks\Model\OrderNumber202Response';
        $request = $this->orderNumberRequest($idempotency_key, $order_number_request, $contentType);

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
     * Create request for operation 'orderNumber'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\OrderNumberRequest $order_number_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['orderNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function orderNumberRequest($idempotency_key, $order_number_request, string $contentType = self::contentTypes['orderNumber'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling orderNumber'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling NumbersApi.orderNumber, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'order_number_request' is set
        if ($order_number_request === null || (is_array($order_number_request) && count($order_number_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $order_number_request when calling orderNumber'
            );
        }


        $resourcePath = '/numbers';
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
        if (isset($order_number_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($order_number_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $order_number_request;
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
     * Operation searchAvailableNumbers
     *
     * Search Available Numbers
     *
     * @param  string $type type (required)
     * @param  string|null $szu The numbering area, required for &#x60;fixed&#x60; and &#x60;nz_geographic&#x60;; from &#x60;GET /numbers/areas&#x60;. (optional)
     * @param  int|null $block_size &#x60;1&#x60;, or &#x60;10&#x60; or &#x60;100&#x60; consecutive numbers for a geographic type. (optional, default to 1)
     * @param  string|null $bundle The bundle you will order into: only numbers we hold are offered. A bundle that has been submitted answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['searchAvailableNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\SearchAvailableNumbers200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function searchAvailableNumbers($type, $szu = null, $block_size = 1, $bundle = null, string $contentType = self::contentTypes['searchAvailableNumbers'][0])
    {
        list($response) = $this->searchAvailableNumbersWithHttpInfo($type, $szu, $block_size, $bundle, $contentType);
        return $response;
    }

    /**
     * Operation searchAvailableNumbersWithHttpInfo
     *
     * Search Available Numbers
     *
     * @param  string $type (required)
     * @param  string|null $szu The numbering area, required for &#x60;fixed&#x60; and &#x60;nz_geographic&#x60;; from &#x60;GET /numbers/areas&#x60;. (optional)
     * @param  int|null $block_size &#x60;1&#x60;, or &#x60;10&#x60; or &#x60;100&#x60; consecutive numbers for a geographic type. (optional, default to 1)
     * @param  string|null $bundle The bundle you will order into: only numbers we hold are offered. A bundle that has been submitted answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['searchAvailableNumbers'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\SearchAvailableNumbers200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function searchAvailableNumbersWithHttpInfo($type, $szu = null, $block_size = 1, $bundle = null, string $contentType = self::contentTypes['searchAvailableNumbers'][0])
    {
        $request = $this->searchAvailableNumbersRequest($type, $szu, $block_size, $bundle, $contentType);

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
                        '\CheckNetworks\Model\SearchAvailableNumbers200Response',
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
                '\CheckNetworks\Model\SearchAvailableNumbers200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\SearchAvailableNumbers200Response',
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
     * Operation searchAvailableNumbersAsync
     *
     * Search Available Numbers
     *
     * @param  string $type (required)
     * @param  string|null $szu The numbering area, required for &#x60;fixed&#x60; and &#x60;nz_geographic&#x60;; from &#x60;GET /numbers/areas&#x60;. (optional)
     * @param  int|null $block_size &#x60;1&#x60;, or &#x60;10&#x60; or &#x60;100&#x60; consecutive numbers for a geographic type. (optional, default to 1)
     * @param  string|null $bundle The bundle you will order into: only numbers we hold are offered. A bundle that has been submitted answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['searchAvailableNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function searchAvailableNumbersAsync($type, $szu = null, $block_size = 1, $bundle = null, string $contentType = self::contentTypes['searchAvailableNumbers'][0])
    {
        return $this->searchAvailableNumbersAsyncWithHttpInfo($type, $szu, $block_size, $bundle, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation searchAvailableNumbersAsyncWithHttpInfo
     *
     * Search Available Numbers
     *
     * @param  string $type (required)
     * @param  string|null $szu The numbering area, required for &#x60;fixed&#x60; and &#x60;nz_geographic&#x60;; from &#x60;GET /numbers/areas&#x60;. (optional)
     * @param  int|null $block_size &#x60;1&#x60;, or &#x60;10&#x60; or &#x60;100&#x60; consecutive numbers for a geographic type. (optional, default to 1)
     * @param  string|null $bundle The bundle you will order into: only numbers we hold are offered. A bundle that has been submitted answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['searchAvailableNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function searchAvailableNumbersAsyncWithHttpInfo($type, $szu = null, $block_size = 1, $bundle = null, string $contentType = self::contentTypes['searchAvailableNumbers'][0])
    {
        $returnType = '\CheckNetworks\Model\SearchAvailableNumbers200Response';
        $request = $this->searchAvailableNumbersRequest($type, $szu, $block_size, $bundle, $contentType);

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
     * Create request for operation 'searchAvailableNumbers'
     *
     * @param  string $type (required)
     * @param  string|null $szu The numbering area, required for &#x60;fixed&#x60; and &#x60;nz_geographic&#x60;; from &#x60;GET /numbers/areas&#x60;. (optional)
     * @param  int|null $block_size &#x60;1&#x60;, or &#x60;10&#x60; or &#x60;100&#x60; consecutive numbers for a geographic type. (optional, default to 1)
     * @param  string|null $bundle The bundle you will order into: only numbers we hold are offered. A bundle that has been submitted answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['searchAvailableNumbers'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function searchAvailableNumbersRequest($type, $szu = null, $block_size = 1, $bundle = null, string $contentType = self::contentTypes['searchAvailableNumbers'][0])
    {

        // verify the required parameter 'type' is set
        if ($type === null || (is_array($type) && count($type) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $type when calling searchAvailableNumbers'
            );
        }

        if ($szu !== null && strlen($szu) > 120) {
            throw new \InvalidArgumentException('invalid length for "$szu" when calling NumbersApi.searchAvailableNumbers, must be smaller than or equal to 120.');
        }
        



        $resourcePath = '/numbers/available';
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
            true // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $szu,
            'szu', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $block_size,
            'blockSize', // param base name
            'integer', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $bundle,
            'bundle', // param base name
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
     * Operation updateNumber
     *
     * Update Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateNumberRequest|null $update_number_request update_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\UpdateNumber200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function updateNumber($number, $idempotency_key = null, $update_number_request = null, string $contentType = self::contentTypes['updateNumber'][0])
    {
        list($response) = $this->updateNumberWithHttpInfo($number, $idempotency_key, $update_number_request, $contentType);
        return $response;
    }

    /**
     * Operation updateNumberWithHttpInfo
     *
     * Update Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateNumberRequest|null $update_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateNumber'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\UpdateNumber200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function updateNumberWithHttpInfo($number, $idempotency_key = null, $update_number_request = null, string $contentType = self::contentTypes['updateNumber'][0])
    {
        $request = $this->updateNumberRequest($number, $idempotency_key, $update_number_request, $contentType);

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
                        '\CheckNetworks\Model\UpdateNumber200Response',
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
                '\CheckNetworks\Model\UpdateNumber200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\UpdateNumber200Response',
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
     * Operation updateNumberAsync
     *
     * Update Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateNumberRequest|null $update_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function updateNumberAsync($number, $idempotency_key = null, $update_number_request = null, string $contentType = self::contentTypes['updateNumber'][0])
    {
        return $this->updateNumberAsyncWithHttpInfo($number, $idempotency_key, $update_number_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation updateNumberAsyncWithHttpInfo
     *
     * Update Phone Number
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateNumberRequest|null $update_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function updateNumberAsyncWithHttpInfo($number, $idempotency_key = null, $update_number_request = null, string $contentType = self::contentTypes['updateNumber'][0])
    {
        $returnType = '\CheckNetworks\Model\UpdateNumber200Response';
        $request = $this->updateNumberRequest($number, $idempotency_key, $update_number_request, $contentType);

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
     * Create request for operation 'updateNumber'
     *
     * @param  string $number The E.164 phone number (e.g. &#x60;61299999999&#x60;). (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateNumberRequest|null $update_number_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateNumber'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function updateNumberRequest($number, $idempotency_key = null, $update_number_request = null, string $contentType = self::contentTypes['updateNumber'][0])
    {

        // verify the required parameter 'number' is set
        if ($number === null || (is_array($number) && count($number) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $number when calling updateNumber'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling NumbersApi.updateNumber, must be smaller than or equal to 255.');
        }
        


        $resourcePath = '/numbers/{number}';
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
        if ($number !== null) {
            $resourcePath = str_replace(
                '{number}',
                ObjectSerializer::toPathValue($number),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($update_number_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($update_number_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $update_number_request;
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
