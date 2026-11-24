<?php
/**
 * DataServicesApi
 * PHP version 8.1
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Check Networks API
 *
 * The Check Networks API lets you run your services from your own systems: qualify and order NBN and Opticomm data services, manage phone numbers, SIP trunks and SMS, raise support tickets, receive webhooks, and read your account and billing.   The reference is published at `https://api.checknetworks.tools/docs`. The OpenAPI document behind it, for generating a client, is at `https://api.checknetworks.tools/docs/openapi.json`.   Endpoints marked **Coming Soon** are published early so you can build against them. Until they go live they answer `501 - Not Implemented`.  # Getting Started  1. **Create an API Client** in the Wholesale Portal, under Company    Settings → Integrations → API Keys, and choose the scopes it may use.    The Client Secret is shown once. 2. **Request an access token** from `POST /oauth/token` with    `grant_type=client_credentials`, your `client_id` and `client_secret`,    and the scopes you need as a space-separated `scope`    (e.g. `scope=data:read tickets:read`). 3. **Call `GET /ping`** with the token to confirm it works and see the    key, account and scopes it carries, the address we saw and which    environment answered. 4. **Send the token on every request**:    `Authorization: Bearer <access token>`. Tokens last one hour; request a    new one when it expires.  The API is for Check Networks customers. If you do not have access to the Wholesale Portal, contact your Account Manager or our Sales Team.  **Build against the sandbox** (`https://api.checknetworks.dev/v2`): a separate copy of the platform, with its own account, keys and test addresses, where nothing reaches a carrier. Orders there move through the carriers' own steps in minutes and fire your webhooks as production does. Ask your Account Manager for one. In production every call runs against your real account, and a carrier write places a real order. A few calls that act on our live network answer `501` in the sandbox.  To test your retries, send `CN-Sandbox-Simulate: 429`, `502` or `503` on any sandbox call: it answers that status in the usual error shape, with `Retry-After: 60` on a 429 or 503, and runs nothing. Production ignores the header.  # Scopes  Each endpoint lists the scope it needs. A token carries only the scopes you asked for **and** your API Client is allowed; any others are dropped without an error. With no `scope` at all, a token carries none and every endpoint refuses it. The `scope` field of the token response is the record of what a token carries, so check it first when you get a `403 - Forbidden`.  Every data endpoint, under `/nbn`, `/opticomm` and `/data` alike, uses the `data:*` scopes.  # Conventions  - Field names are `camelCase`. - Money is a decimal string in Australian dollars (`\"49.95\"`), with a   `currency` field alongside (always `AUD` today). - A single object comes back in `result`, and a list in `results`. - Every list of your own records takes `page` and `perPage` and returns   `total`, `page` and `perPage` with `results`. `perPage` defaults to   **50** and runs from 1 to **200**; a value outside that, or a `page`   that is not a whole number of at least 1, is `422 - Unprocessable   Entity` naming the field, never quietly changed. - Lists take `sort` (a field the list names, `-` in front for   descending), `q` (a case-insensitive search, at most 100 characters,   over the fields the list names), `updatedSince`, `createdFrom` and   `createdTo` (an ISO 8601 date or date-time; a date alone is that day in   UTC, or in your zone when you send `CN-Time-Zone`). A filter such as `status` takes one or several values,   comma-separated, in any case. An unknown sort field or filter value is   `422`. - Lists that grow while you read them (payments, tickets, SMS, webhook   deliveries, call records) also return `nextCursor`, newest first: pass   it back as `cursor` until it is `null`. A cursor page carries `perPage`,   `nextCursor` and `results` only. A sorted list has no cursor. - An end user is named by its number, as `endUser`, everywhere a   response or a request names one; where a response shows its name too,   that is `endUserName`. - `expand` inlines a related record in full where a list or read names   it, such as `expand=endUser` on your services, which adds   `endUserDetail`. - Every time is UTC unless you ask for your own zone: send   `CN-Time-Zone` with an IANA zone name, such as `Australia/Perth`, and   every time in a JSON response comes in that zone with its offset   (`2026-11-22T09:30:00+08:00`), a date alone in a list filter is that day   in that zone, and the response echoes the header. A name that is not a   zone is `422` naming `CN-Time-Zone`. Downloads (CSV, NDJSON, PDF),   webhook deliveries and the carrier-shaped `/nbn` and `/opticomm` bodies   stay as they are. What you set comes back as you set it, whatever it   looks like: `metadata`, `externalRef`, your references   (`orderReference`, `customerReference`, `customerRef`,   `salesReference`, `paymentReference`, `paidReference`) and your own   `name`, `description`, `note` and `notes` text. - A create answers `201 - Created` with the new resource. Every other   success answers `200 - OK`.  **Carrier-shaped endpoints.** Some `/nbn` and `/opticomm` endpoints mirror the carrier's own API, so an integration written for the carrier works here unchanged. These return the carrier's own bodies and status codes, without the `result` wrapper, and are not paginated: the `/nbn` service qualifications (`single_site`, `fibre_uplift`, `enterprise_ethernet`), `/nbn/address/search`, `/nbn/address/geocode`, `/nbn/address/{locId}`, `/nbn/planning/{locId}`, the `/nbn/product_qualification` `service_types` and `bandwidths`, the diagnostic test catalogue and Opticomm `productOrders`. Our account identifiers are removed and links point at this API. The `/nbn` ones also take Kinetix's query parameter names (`loc_id`, `service_ref`, `customer_authority_date`, `location_ref`, `service_type`) as well as ours. Lists of your own records under those paths, such as your NBN inventory, are paginated as usual. Where we add to a carrier's shape, the field or parameter says \"Ours\": the premises `address` and `fibreBuildCategory` on an EE quote, and the search, filters, sort and paging of `GET /nbn/quotes`.  # Errors  Errors use standard HTTP status codes. The body tells you more:  - **`code`** says what went wrong, such as `not_found`,   `key_revoked` or `duplicate_order`. Your code should check this. The   full list is on the `Error` schema. - **`reason`** explains it in words, for a person to read. The wording   can change. - **`messageId`** is this request's id, the same as the `CN-Message-Id`   header. Quote it to Support. - **`docUrl`** links to the code's entry in the   [error reference](https://help.checknetworks.tools/api/errors/),   which says what to do about it. - **`fields`** appears when the request failed validation, and names   each input that was wrong. - **`carrierCode`** appears when nbn or Opticomm refused the request,   and holds their own error code.  | Status | Meaning | |---|---| | `400` | The body could not be parsed, or a required header is missing. On the carrier-shaped `/nbn` and `/opticomm` endpoints, also a bad input, as the carrier answers. | | `401` | The access token is missing, expired or invalid, or its key is past its end date (`key_expired`). | | `403` | Refused: `code` says why, such as `missing_scope`, `key_revoked`, `ip_not_allowed` or `account_suspended`. | | `404` | Not found. We give the same answer for something on another account. | | `405` | The path exists, but not for this method. | | `409` | The resource isn't in a state that allows this right now. | | `422` | An input was wrong (`validation_failed`, with `fields` naming it), or the request was refused on its merits. `reason` says why. | | `429` | Too many requests. See Rate Limits. | | `501` | The endpoint is Coming Soon, or isn't offered in the sandbox. Only the operations that list it can answer it. | | `5xx` | A problem on our side, or with the carrier. Try again shortly. |  Every error, an unknown path included, comes back as this JSON body, whatever your `Accept` header says.  # Idempotency  A write (`POST`, `PUT`, `PATCH` or `DELETE`) can carry an `Idempotency-Key` header so that retrying it never does the work twice, for example after a timeout where you never saw the response. Use a new unique value, such as a UUID, for each operation:  `Idempotency-Key: 1f8e6b1a-9c4d-4a2e-8b3a-2f6d0c9e7a51`  The key is required wherever a repeated request could do real harm, in three groups.  **Calls that reach a carrier**, where a repeat could place a second real order: placing, answering, changing or cancelling an order (`/data/orders`, `/nbn/orders/...`, `/opticomm/productOrders`), submitting a bundle (`POST /bundles/{id}/submit`), ordering a number (`POST /numbers`), reserving, changing or cancelling a technician appointment, the carrier service tests and health reports (and `POST /data/services/{ref}/sessions/refresh`, `POST /data/services/{ref}/diagnostics` and `POST /data/radius/{ref}/session-check`), and Opticomm's `static_ip` GET. These share a limit of **30 per minute per account**, on top of the general rate limit. A replay of a stored answer does not count against it. A batch qualification (`POST /data/sq/batches`) asks the carrier once for each row, so it needs the key too, but is not on that limit.  **Calls that bill or send**, where a repeat could charge you twice or send twice: creating a trunk (`POST /trunks`), ordering a fax plan (`POST /fax/plans`), sending a fax (`POST /fax/documents`), requesting an NBN quote (`POST /nbn/quotes`), sending an SMS (`POST /sms`), ordering colocation (`POST /colocation/orders`), and rotating a webhook secret (`PATCH /webhooks/{webhookId}` with `rotateSecret: true`).  **Writes on our side that must not run twice**: recording an end user's usage (`POST /end-users/{endUser}/usage` and `POST /usage/batch`), which would bill them twice; importing end users (`POST /end-users/imports`), which would import the file twice; rotating your own API key (`POST /account/api-keys/self/rotate`), which would rotate it twice; a bulk change to numbers (`POST /numbers/bulk`), which could release them twice; and hanging up a call (`POST /voice/calls/{callId}/hangup`), which could hang up a second one.  Each of these operations lists the header as required (the webhook secret rotation says so in its description, as it needs the key only with `rotateSecret`). Without a key they all answer `400` with the same body.  Every other write (users, tickets, webhooks, a single number, RADIUS, provisioning and the rest) accepts the key and honours it the same way, but runs without one.  How a key behaves:  | You send | You get | |---|---| | A new key | The request runs, and its response is stored. | | The same key and the same request | The stored response, with `Idempotent-Replayed: true`. Nothing runs again. | | The same key with a different request | `422` `idempotency_key_reused`. | | The same key while its first request is still running | `409` `idempotency_in_progress`, with `Retry-After`. Retry then. | | A key longer than 255 characters | `400`. |  A `5xx` is stored like any other response, so retrying with the same key returns the same `5xx` rather than running the write again. A request that timed out may still have **succeeded**, so we keep the key spent rather than risk a duplicate. Once you know the operation did not happen, send it under a **new** key. If a key keeps answering `409` long after the first call, contact Support: we never release a key on our own, because we can't tell a slow request from one that stopped.  Keys belong to your API Client and are kept for 30 days. A write sent without the header, where it is optional, is not deduplicated.  # Rate Limits  Each API Client may make **120 requests per minute** across the whole API, and your account **600 per minute** across all its API Clients together, so adding clients does not add capacity. Over either, requests answer `429 - Too Many Requests` until the minute passes. If you need more, contact Support: we can raise your account's figure.  Three groups of calls have a lower limit of their own, per account, on top of those. Each of these operations names its limit in its description, as `Limit: ... (carrier write)`, `(webhook send)` or `(domain verify)`:  | Calls | Limit | |---|---| | Carrier-reaching writes (listed under Idempotency) | 30 per minute. A replay of a stored answer does not count. | | Webhook test, redeliver and replay-dead, and sending an event again (`POST /events/{eventId}/deliveries`) | 10 per minute, shared | | Verifying an end-user portal domain | 5 per minute |  `POST /end-users/lookup` allows 20 per minute for each API key, and says so as `Limit: 20 per minute for the key.`  `POST /oauth/token` has its own limit: **30 per minute for each `client_id` from one address**, so systems behind one NAT do not share it, and 120 per minute from one address whatever the `client_id`.  Every response carries the limit that will stop you first, whichever of these it is:  | Header | What it says | |---|---| | `X-RateLimit-Limit` | The calls that limit allows in its minute. | | `X-RateLimit-Remaining` | The calls you have left in it. | | `X-RateLimit-Reset` | When it starts again, as a Unix time. | | `Retry-After` | On a `429` only: the seconds to wait before retrying. |  A `429` always means a rate: wait and retry. A cap on what you hold, such as the 5 open install reservations, answers `409` instead, because waiting does not change it.  # Versioning  The current version is **v2**, at `/v2` (`https://api.checknetworks.tools/v2/account`). Every path also answers without the prefix (`https://api.checknetworks.tools/account`), and those unprefixed paths mean v2 **for good**: a later version gets its own prefix (`/v3`) and never changes what an unprefixed call does.  Every response, errors included, names the version that answered:  ``` API-Version: 2 ```  There is no `/v1` here. v1 is the legacy API at `api.checknetworks.au`, a separate platform being retired over time. If you still use it, talk to us about moving across.  **What counts as a breaking change.** Within v2 we only add: a new endpoint, a new optional parameter, a new field in a response, a new webhook event type or a new error `code`. Build your client to ignore fields it does not know. Removing or renaming a field, endpoint or parameter, changing a field's type or meaning, making an optional input required, or refusing a request we used to accept is breaking, and happens only through the deprecation below or a new version.  # Deprecation  Nothing is deprecated today. When an endpoint or field is to be retired, you get **at least 12 months' notice** before it stops working. We tell you three ways: an entry here and in the changelog saying what to use instead, an email to your account, and three headers on every response from the call:  ``` Deprecation: @1798761599 Sunset: Fri, 31 Dec 2027 23:59:59 GMT Link: <https://api.checknetworks.tools/docs#section/Deprecation>; rel=\"deprecation\"; type=\"text/html\" ```  The `Sunset` is at least 12 months after the `Deprecation`. The one exception is a security fix: when keeping a call would put accounts at risk, we may retire it sooner, and say why.  | Header | What it says | |---|---| | `Deprecation` | When it was deprecated: `@` and a Unix timestamp ([RFC 9745](https://www.rfc-editor.org/rfc/rfc9745.html)). | | `Sunset` | The last moment it answers, as an HTTP date ([RFC 8594](https://www.rfc-editor.org/rfc/rfc8594.html)). After that it answers `404`. | | `Link` | This section, which says what to use instead. |  It keeps working normally until the `Sunset`. We suggest logging a warning whenever a response carries `Deprecation`, and raising an alert when the `Sunset` falls inside your next release cycle. Deprecated operations are also marked `deprecated: true` in this document, so a generated client can flag them.  # Support  Every response carries a **`CN-Message-Id`** header, our identifier for that request. Quote it when you contact Support about a call and we can find the exact request. It is on error responses too. We issue it; you can't set it. For your own handle on a write, use `Idempotency-Key`.  For help with the API, email support@checknetworks.com.au. Our team can answer questions and help you understand the API, but can't write your integration for you.
 *
 * The version of the OpenAPI document: 2.0.0-pre.4
 * Contact: support@checknetworks.com.au
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.26.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace CheckNetworks\Api\Generated\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use CheckNetworks\Api\Generated\ApiException;
use CheckNetworks\Api\Generated\Configuration;
use CheckNetworks\Api\Generated\FormDataProcessor;
use CheckNetworks\Api\Generated\HeaderSelector;
use CheckNetworks\Api\Generated\ObjectSerializer;

/**
 * DataServicesApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class DataServicesApi
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
        'getDataDiagnostic' => [
            'application/json',
        ],
        'getDataHealthReport' => [
            'application/json',
        ],
        'getDataService' => [
            'application/json',
        ],
        'getDataServiceDiagnosticTypes' => [
            'application/json',
        ],
        'getDataServiceDiagnostics' => [
            'application/json',
        ],
        'getDataServiceDisconnectQuote' => [
            'application/json',
        ],
        'getDataServiceHealth' => [
            'application/json',
        ],
        'getDataServiceHealthLatest' => [
            'application/json',
        ],
        'getDataServiceIncidents' => [
            'application/json',
        ],
        'getDataServiceSessions' => [
            'application/json',
        ],
        'getDataServiceUsage' => [
            'application/json',
        ],
        'getDataServices' => [
            'application/json',
        ],
        'getDataTags' => [
            'application/json',
        ],
        'getDataUsage' => [
            'application/json',
        ],
        'patchDataService' => [
            'application/json',
        ],
        'postDataServiceDiagnostics' => [
            'application/json',
        ],
        'postDataServiceHealth' => [
            'application/json',
        ],
        'postDataServiceSessionsRefresh' => [
            'application/json',
        ],
        'postDataServicesAssign' => [
            'application/json',
        ],
        'putDataServiceTags' => [
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
     * Operation getDataDiagnostic
     *
     * Retrieve a Line Test
     *
     * @param  string $id The test&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataDiagnostic'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataDiagnostic200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataDiagnostic($id, string $contentType = self::contentTypes['getDataDiagnostic'][0])
    {
        list($response) = $this->getDataDiagnosticWithHttpInfo($id, $contentType);
        return $response;
    }

    /**
     * Operation getDataDiagnosticWithHttpInfo
     *
     * Retrieve a Line Test
     *
     * @param  string $id The test&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataDiagnostic'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataDiagnostic200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataDiagnosticWithHttpInfo($id, string $contentType = self::contentTypes['getDataDiagnostic'][0])
    {
        $request = $this->getDataDiagnosticRequest($id, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataDiagnostic200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataDiagnostic200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataDiagnostic200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataDiagnosticAsync
     *
     * Retrieve a Line Test
     *
     * @param  string $id The test&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataDiagnostic'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataDiagnosticAsync($id, string $contentType = self::contentTypes['getDataDiagnostic'][0])
    {
        return $this->getDataDiagnosticAsyncWithHttpInfo($id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataDiagnosticAsyncWithHttpInfo
     *
     * Retrieve a Line Test
     *
     * @param  string $id The test&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataDiagnostic'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataDiagnosticAsyncWithHttpInfo($id, string $contentType = self::contentTypes['getDataDiagnostic'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataDiagnostic200Response';
        $request = $this->getDataDiagnosticRequest($id, $contentType);

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
     * Create request for operation 'getDataDiagnostic'
     *
     * @param  string $id The test&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataDiagnostic'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataDiagnosticRequest($id, string $contentType = self::contentTypes['getDataDiagnostic'][0])
    {

        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling getDataDiagnostic'
            );
        }


        $resourcePath = '/data/diagnostics/{id}';
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
     * Operation getDataHealthReport
     *
     * Get a Health Report
     *
     * @param  string $id The report&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataHealthReport'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataHealthReport($id, string $contentType = self::contentTypes['getDataHealthReport'][0])
    {
        list($response) = $this->getDataHealthReportWithHttpInfo($id, $contentType);
        return $response;
    }

    /**
     * Operation getDataHealthReportWithHttpInfo
     *
     * Get a Health Report
     *
     * @param  string $id The report&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataHealthReport'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataHealthReportWithHttpInfo($id, string $contentType = self::contentTypes['getDataHealthReport'][0])
    {
        $request = $this->getDataHealthReportRequest($id, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataHealthReportAsync
     *
     * Get a Health Report
     *
     * @param  string $id The report&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataHealthReport'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataHealthReportAsync($id, string $contentType = self::contentTypes['getDataHealthReport'][0])
    {
        return $this->getDataHealthReportAsyncWithHttpInfo($id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataHealthReportAsyncWithHttpInfo
     *
     * Get a Health Report
     *
     * @param  string $id The report&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataHealthReport'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataHealthReportAsyncWithHttpInfo($id, string $contentType = self::contentTypes['getDataHealthReport'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response';
        $request = $this->getDataHealthReportRequest($id, $contentType);

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
     * Create request for operation 'getDataHealthReport'
     *
     * @param  string $id The report&#39;s id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataHealthReport'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataHealthReportRequest($id, string $contentType = self::contentTypes['getDataHealthReport'][0])
    {

        // verify the required parameter 'id' is set
        if ($id === null || (is_array($id) && count($id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $id when calling getDataHealthReport'
            );
        }


        $resourcePath = '/data/health/{id}';
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
     * Operation getDataService
     *
     * Retrieve a Service
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds the service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataService'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataService($ref, $expand = null, string $contentType = self::contentTypes['getDataService'][0])
    {
        list($response) = $this->getDataServiceWithHttpInfo($ref, $expand, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceWithHttpInfo
     *
     * Retrieve a Service
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds the service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataService'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceWithHttpInfo($ref, $expand = null, string $contentType = self::contentTypes['getDataService'][0])
    {
        $request = $this->getDataServiceRequest($ref, $expand, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataService200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataService200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataService200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceAsync
     *
     * Retrieve a Service
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds the service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceAsync($ref, $expand = null, string $contentType = self::contentTypes['getDataService'][0])
    {
        return $this->getDataServiceAsyncWithHttpInfo($ref, $expand, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceAsyncWithHttpInfo
     *
     * Retrieve a Service
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds the service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceAsyncWithHttpInfo($ref, $expand = null, string $contentType = self::contentTypes['getDataService'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataService200Response';
        $request = $this->getDataServiceRequest($ref, $expand, $contentType);

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
     * Create request for operation 'getDataService'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds the service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceRequest($ref, $expand = null, string $contentType = self::contentTypes['getDataService'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataService'
            );
        }



        $resourcePath = '/data/services/{ref}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $expand,
            'expand', // param base name
            'array', // openApiType
            'form', // style
            false, // explode
            false // required
        ) ?? []);


        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceDiagnosticTypes
     *
     * List the Line Tests a Service Allows
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnosticTypes'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceDiagnosticTypes200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceDiagnosticTypes($ref, string $contentType = self::contentTypes['getDataServiceDiagnosticTypes'][0])
    {
        list($response) = $this->getDataServiceDiagnosticTypesWithHttpInfo($ref, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceDiagnosticTypesWithHttpInfo
     *
     * List the Line Tests a Service Allows
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnosticTypes'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceDiagnosticTypes200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceDiagnosticTypesWithHttpInfo($ref, string $contentType = self::contentTypes['getDataServiceDiagnosticTypes'][0])
    {
        $request = $this->getDataServiceDiagnosticTypesRequest($ref, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnosticTypes200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnosticTypes200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnosticTypes200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceDiagnosticTypesAsync
     *
     * List the Line Tests a Service Allows
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnosticTypes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceDiagnosticTypesAsync($ref, string $contentType = self::contentTypes['getDataServiceDiagnosticTypes'][0])
    {
        return $this->getDataServiceDiagnosticTypesAsyncWithHttpInfo($ref, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceDiagnosticTypesAsyncWithHttpInfo
     *
     * List the Line Tests a Service Allows
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnosticTypes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceDiagnosticTypesAsyncWithHttpInfo($ref, string $contentType = self::contentTypes['getDataServiceDiagnosticTypes'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnosticTypes200Response';
        $request = $this->getDataServiceDiagnosticTypesRequest($ref, $contentType);

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
     * Create request for operation 'getDataServiceDiagnosticTypes'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnosticTypes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceDiagnosticTypesRequest($ref, string $contentType = self::contentTypes['getDataServiceDiagnosticTypes'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceDiagnosticTypes'
            );
        }


        $resourcePath = '/data/services/{ref}/diagnostics/types';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceDiagnostics
     *
     * List a Service&#39;s Line Tests
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $status Only tests in these statuses, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $type Only these tests, by code, comma-separated, in any case. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $result Only tests with these results, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceDiagnostics200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceDiagnostics($ref, $status = null, $type = null, $result = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceDiagnostics'][0])
    {
        list($response) = $this->getDataServiceDiagnosticsWithHttpInfo($ref, $status, $type, $result, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceDiagnosticsWithHttpInfo
     *
     * List a Service&#39;s Line Tests
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $status Only tests in these statuses, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $type Only these tests, by code, comma-separated, in any case. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $result Only tests with these results, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceDiagnostics200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceDiagnosticsWithHttpInfo($ref, $status = null, $type = null, $result = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceDiagnostics'][0])
    {
        $request = $this->getDataServiceDiagnosticsRequest($ref, $status, $type, $result, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnostics200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnostics200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnostics200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceDiagnosticsAsync
     *
     * List a Service&#39;s Line Tests
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $status Only tests in these statuses, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $type Only these tests, by code, comma-separated, in any case. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $result Only tests with these results, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceDiagnosticsAsync($ref, $status = null, $type = null, $result = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceDiagnostics'][0])
    {
        return $this->getDataServiceDiagnosticsAsyncWithHttpInfo($ref, $status, $type, $result, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceDiagnosticsAsyncWithHttpInfo
     *
     * List a Service&#39;s Line Tests
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $status Only tests in these statuses, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $type Only these tests, by code, comma-separated, in any case. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $result Only tests with these results, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceDiagnosticsAsyncWithHttpInfo($ref, $status = null, $type = null, $result = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceDiagnostics'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceDiagnostics200Response';
        $request = $this->getDataServiceDiagnosticsRequest($ref, $status, $type, $result, $page, $per_page, $contentType);

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
     * Create request for operation 'getDataServiceDiagnostics'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string[]|null $status Only tests in these statuses, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $type Only these tests, by code, comma-separated, in any case. Any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $result Only tests with these results, comma-separated. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceDiagnosticsRequest($ref, $status = null, $type = null, $result = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceDiagnostics'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceDiagnostics'
            );
        }




        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling DataServicesApi.getDataServiceDiagnostics, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServiceDiagnostics, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServiceDiagnostics, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/data/services/{ref}/diagnostics';
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
            $type,
            'type', // param base name
            'array', // openApiType
            'form', // style
            false, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $result,
            'result', // param base name
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


        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceDisconnectQuote
     *
     * Price a Disconnect
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \DateTime|null $cease_on The day the service would cease, &#x60;YYYY-MM-DD&#x60;. Any other value, or a day outside the window, is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDisconnectQuote'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceDisconnectQuote200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceDisconnectQuote($ref, $cease_on = null, string $contentType = self::contentTypes['getDataServiceDisconnectQuote'][0])
    {
        list($response) = $this->getDataServiceDisconnectQuoteWithHttpInfo($ref, $cease_on, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceDisconnectQuoteWithHttpInfo
     *
     * Price a Disconnect
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \DateTime|null $cease_on The day the service would cease, &#x60;YYYY-MM-DD&#x60;. Any other value, or a day outside the window, is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDisconnectQuote'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceDisconnectQuote200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceDisconnectQuoteWithHttpInfo($ref, $cease_on = null, string $contentType = self::contentTypes['getDataServiceDisconnectQuote'][0])
    {
        $request = $this->getDataServiceDisconnectQuoteRequest($ref, $cease_on, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceDisconnectQuote200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceDisconnectQuote200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceDisconnectQuote200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceDisconnectQuoteAsync
     *
     * Price a Disconnect
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \DateTime|null $cease_on The day the service would cease, &#x60;YYYY-MM-DD&#x60;. Any other value, or a day outside the window, is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDisconnectQuote'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceDisconnectQuoteAsync($ref, $cease_on = null, string $contentType = self::contentTypes['getDataServiceDisconnectQuote'][0])
    {
        return $this->getDataServiceDisconnectQuoteAsyncWithHttpInfo($ref, $cease_on, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceDisconnectQuoteAsyncWithHttpInfo
     *
     * Price a Disconnect
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \DateTime|null $cease_on The day the service would cease, &#x60;YYYY-MM-DD&#x60;. Any other value, or a day outside the window, is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDisconnectQuote'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceDisconnectQuoteAsyncWithHttpInfo($ref, $cease_on = null, string $contentType = self::contentTypes['getDataServiceDisconnectQuote'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceDisconnectQuote200Response';
        $request = $this->getDataServiceDisconnectQuoteRequest($ref, $cease_on, $contentType);

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
     * Create request for operation 'getDataServiceDisconnectQuote'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \DateTime|null $cease_on The day the service would cease, &#x60;YYYY-MM-DD&#x60;. Any other value, or a day outside the window, is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceDisconnectQuote'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceDisconnectQuoteRequest($ref, $cease_on = null, string $contentType = self::contentTypes['getDataServiceDisconnectQuote'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceDisconnectQuote'
            );
        }



        $resourcePath = '/data/services/{ref}/disconnect-quote';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $cease_on,
            'ceaseOn', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);


        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceHealth
     *
     * List a Service&#39;s Health Reports
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceHealth200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceHealth($ref, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceHealth'][0])
    {
        list($response) = $this->getDataServiceHealthWithHttpInfo($ref, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceHealthWithHttpInfo
     *
     * List a Service&#39;s Health Reports
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceHealth200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceHealthWithHttpInfo($ref, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceHealth'][0])
    {
        $request = $this->getDataServiceHealthRequest($ref, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceHealth200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceHealth200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceHealth200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceHealthAsync
     *
     * List a Service&#39;s Health Reports
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceHealthAsync($ref, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceHealth'][0])
    {
        return $this->getDataServiceHealthAsyncWithHttpInfo($ref, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceHealthAsyncWithHttpInfo
     *
     * List a Service&#39;s Health Reports
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceHealthAsyncWithHttpInfo($ref, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceHealth'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceHealth200Response';
        $request = $this->getDataServiceHealthRequest($ref, $page, $per_page, $contentType);

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
     * Create request for operation 'getDataServiceHealth'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceHealthRequest($ref, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceHealth'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceHealth'
            );
        }

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling DataServicesApi.getDataServiceHealth, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServiceHealth, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServiceHealth, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/data/services/{ref}/health';
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


        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceHealthLatest
     *
     * Get a Service&#39;s Latest Health Report
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealthLatest'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceHealthLatest($ref, string $contentType = self::contentTypes['getDataServiceHealthLatest'][0])
    {
        list($response) = $this->getDataServiceHealthLatestWithHttpInfo($ref, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceHealthLatestWithHttpInfo
     *
     * Get a Service&#39;s Latest Health Report
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealthLatest'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceHealthLatestWithHttpInfo($ref, string $contentType = self::contentTypes['getDataServiceHealthLatest'][0])
    {
        $request = $this->getDataServiceHealthLatestRequest($ref, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceHealthLatestAsync
     *
     * Get a Service&#39;s Latest Health Report
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealthLatest'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceHealthLatestAsync($ref, string $contentType = self::contentTypes['getDataServiceHealthLatest'][0])
    {
        return $this->getDataServiceHealthLatestAsyncWithHttpInfo($ref, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceHealthLatestAsyncWithHttpInfo
     *
     * Get a Service&#39;s Latest Health Report
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealthLatest'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceHealthLatestAsyncWithHttpInfo($ref, string $contentType = self::contentTypes['getDataServiceHealthLatest'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceHealthLatest200Response';
        $request = $this->getDataServiceHealthLatestRequest($ref, $contentType);

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
     * Create request for operation 'getDataServiceHealthLatest'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceHealthLatest'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceHealthLatestRequest($ref, string $contentType = self::contentTypes['getDataServiceHealthLatest'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceHealthLatest'
            );
        }


        $resourcePath = '/data/services/{ref}/health/latest';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceIncidents
     *
     * List a Service&#39;s Outages, Planned Work and Degradations
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $include &#x60;resolved&#x60; adds what was resolved in the last 30 days. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceIncidents'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceIncidents200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceIncidents($ref, $include = null, string $contentType = self::contentTypes['getDataServiceIncidents'][0])
    {
        list($response) = $this->getDataServiceIncidentsWithHttpInfo($ref, $include, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceIncidentsWithHttpInfo
     *
     * List a Service&#39;s Outages, Planned Work and Degradations
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $include &#x60;resolved&#x60; adds what was resolved in the last 30 days. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceIncidents'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceIncidents200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceIncidentsWithHttpInfo($ref, $include = null, string $contentType = self::contentTypes['getDataServiceIncidents'][0])
    {
        $request = $this->getDataServiceIncidentsRequest($ref, $include, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceIncidents200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceIncidents200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceIncidents200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceIncidentsAsync
     *
     * List a Service&#39;s Outages, Planned Work and Degradations
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $include &#x60;resolved&#x60; adds what was resolved in the last 30 days. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceIncidents'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceIncidentsAsync($ref, $include = null, string $contentType = self::contentTypes['getDataServiceIncidents'][0])
    {
        return $this->getDataServiceIncidentsAsyncWithHttpInfo($ref, $include, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceIncidentsAsyncWithHttpInfo
     *
     * List a Service&#39;s Outages, Planned Work and Degradations
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $include &#x60;resolved&#x60; adds what was resolved in the last 30 days. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceIncidents'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceIncidentsAsyncWithHttpInfo($ref, $include = null, string $contentType = self::contentTypes['getDataServiceIncidents'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceIncidents200Response';
        $request = $this->getDataServiceIncidentsRequest($ref, $include, $contentType);

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
     * Create request for operation 'getDataServiceIncidents'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $include &#x60;resolved&#x60; adds what was resolved in the last 30 days. Any other value is &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceIncidents'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceIncidentsRequest($ref, $include = null, string $contentType = self::contentTypes['getDataServiceIncidents'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceIncidents'
            );
        }



        $resourcePath = '/data/services/{ref}/incidents';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $include,
            'include', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);


        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceSessions
     *
     * List a Service&#39;s Sessions
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $from An ISO 8601 date or date-time; a date alone is the start of that day (UTC). At most 90 days before &#x60;to&#x60;; 90 days before &#x60;to&#x60; when absent. Not ISO 8601, or further back, is &#x60;422&#x60;. (optional)
     * @param  string|null $to An ISO 8601 date or date-time; a date alone includes that day. Now when absent. Before &#x60;from&#x60; is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceSessions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceSessions200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceSessions($ref, $from = null, $to = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceSessions'][0])
    {
        list($response) = $this->getDataServiceSessionsWithHttpInfo($ref, $from, $to, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceSessionsWithHttpInfo
     *
     * List a Service&#39;s Sessions
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $from An ISO 8601 date or date-time; a date alone is the start of that day (UTC). At most 90 days before &#x60;to&#x60;; 90 days before &#x60;to&#x60; when absent. Not ISO 8601, or further back, is &#x60;422&#x60;. (optional)
     * @param  string|null $to An ISO 8601 date or date-time; a date alone includes that day. Now when absent. Before &#x60;from&#x60; is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceSessions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceSessions200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceSessionsWithHttpInfo($ref, $from = null, $to = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceSessions'][0])
    {
        $request = $this->getDataServiceSessionsRequest($ref, $from, $to, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceSessions200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceSessions200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceSessions200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceSessionsAsync
     *
     * List a Service&#39;s Sessions
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $from An ISO 8601 date or date-time; a date alone is the start of that day (UTC). At most 90 days before &#x60;to&#x60;; 90 days before &#x60;to&#x60; when absent. Not ISO 8601, or further back, is &#x60;422&#x60;. (optional)
     * @param  string|null $to An ISO 8601 date or date-time; a date alone includes that day. Now when absent. Before &#x60;from&#x60; is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceSessions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceSessionsAsync($ref, $from = null, $to = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceSessions'][0])
    {
        return $this->getDataServiceSessionsAsyncWithHttpInfo($ref, $from, $to, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceSessionsAsyncWithHttpInfo
     *
     * List a Service&#39;s Sessions
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $from An ISO 8601 date or date-time; a date alone is the start of that day (UTC). At most 90 days before &#x60;to&#x60;; 90 days before &#x60;to&#x60; when absent. Not ISO 8601, or further back, is &#x60;422&#x60;. (optional)
     * @param  string|null $to An ISO 8601 date or date-time; a date alone includes that day. Now when absent. Before &#x60;from&#x60; is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceSessions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceSessionsAsyncWithHttpInfo($ref, $from = null, $to = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceSessions'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceSessions200Response';
        $request = $this->getDataServiceSessionsRequest($ref, $from, $to, $page, $per_page, $contentType);

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
     * Create request for operation 'getDataServiceSessions'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $from An ISO 8601 date or date-time; a date alone is the start of that day (UTC). At most 90 days before &#x60;to&#x60;; 90 days before &#x60;to&#x60; when absent. Not ISO 8601, or further back, is &#x60;422&#x60;. (optional)
     * @param  string|null $to An ISO 8601 date or date-time; a date alone includes that day. Now when absent. Before &#x60;from&#x60; is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceSessions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceSessionsRequest($ref, $from = null, $to = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataServiceSessions'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceSessions'
            );
        }



        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling DataServicesApi.getDataServiceSessions, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServiceSessions, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServiceSessions, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/data/services/{ref}/sessions';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

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


        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServiceUsage
     *
     * Retrieve a Service&#39;s Usage
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceUsage'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServiceUsage200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServiceUsage($ref, $period = null, string $contentType = self::contentTypes['getDataServiceUsage'][0])
    {
        list($response) = $this->getDataServiceUsageWithHttpInfo($ref, $period, $contentType);
        return $response;
    }

    /**
     * Operation getDataServiceUsageWithHttpInfo
     *
     * Retrieve a Service&#39;s Usage
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceUsage'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServiceUsage200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServiceUsageWithHttpInfo($ref, $period = null, string $contentType = self::contentTypes['getDataServiceUsage'][0])
    {
        $request = $this->getDataServiceUsageRequest($ref, $period, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceUsage200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServiceUsage200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServiceUsage200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServiceUsageAsync
     *
     * Retrieve a Service&#39;s Usage
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceUsage'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceUsageAsync($ref, $period = null, string $contentType = self::contentTypes['getDataServiceUsage'][0])
    {
        return $this->getDataServiceUsageAsyncWithHttpInfo($ref, $period, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServiceUsageAsyncWithHttpInfo
     *
     * Retrieve a Service&#39;s Usage
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceUsage'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServiceUsageAsyncWithHttpInfo($ref, $period = null, string $contentType = self::contentTypes['getDataServiceUsage'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServiceUsage200Response';
        $request = $this->getDataServiceUsageRequest($ref, $period, $contentType);

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
     * Create request for operation 'getDataServiceUsage'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServiceUsage'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServiceUsageRequest($ref, $period = null, string $contentType = self::contentTypes['getDataServiceUsage'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling getDataServiceUsage'
            );
        }

        if ($period !== null && !preg_match("/^\\d{4}-\\d{2}$/", $period)) {
            throw new \InvalidArgumentException("invalid value for \"period\" when calling DataServicesApi.getDataServiceUsage, must conform to the pattern /^\\d{4}-\\d{2}$/.");
        }
        

        $resourcePath = '/data/services/{ref}/usage';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $period,
            'period', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);


        // path params
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation getDataServices
     *
     * List Your Services
     *
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $status Only services in these statuses, one or several, comma-separated. Case does not matter, so the &#x60;status&#x60; a service shows (&#x60;Live&#x60;) is accepted as it is; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds each service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string[]|null $technology Only services on these access technologies, one or several, comma-separated, as the &#x60;technology&#x60; a service shows. Case does not matter; any other value is &#x60;422&#x60;. A technology none of your services has gives an empty list. (optional)
     * @param  string|null $customer_reference Only the services with this customer reference, matched whole, ignoring case. (optional)
     * @param  string|null $plan Only the services on this plan, by its SKU (the &#x60;plan.sku&#x60; a service shows), ignoring case. (optional)
     * @param  string|null $location_id Only the services at this premises: an NBN location id (&#x60;LOC&#x60; and 12 digits) or an Opticomm one (&#x60;OPC&#x60; and 12 digits). Any other form is &#x60;422&#x60;. (optional)
     * @param  string[]|null $tag Only rows carrying this tag, matched whole, ignoring case. Repeat it (&#x60;tag&#x3D;Site%2012&amp;tag&#x3D;Brisbane&#x60;, or &#x60;tag[]&#x3D;&#x60;) and a row must carry every one. A tag may hold a comma, so values are never split. A blank tag, or one over 255 characters, is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $needs_attention Only the services in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number (the &#x60;endUser&#x60; each service returns). A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServices'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataServices200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataServices($network = null, $status = null, $expand = null, $page = 1, $per_page = 50, $sort = null, $technology = null, $customer_reference = null, $plan = null, $location_id = null, $tag = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, $needs_attention = null, $end_user = null, string $contentType = self::contentTypes['getDataServices'][0])
    {
        list($response) = $this->getDataServicesWithHttpInfo($network, $status, $expand, $page, $per_page, $sort, $technology, $customer_reference, $plan, $location_id, $tag, $q, $external_ref, $updated_since, $created_from, $created_to, $needs_attention, $end_user, $contentType);
        return $response;
    }

    /**
     * Operation getDataServicesWithHttpInfo
     *
     * List Your Services
     *
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $status Only services in these statuses, one or several, comma-separated. Case does not matter, so the &#x60;status&#x60; a service shows (&#x60;Live&#x60;) is accepted as it is; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds each service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string[]|null $technology Only services on these access technologies, one or several, comma-separated, as the &#x60;technology&#x60; a service shows. Case does not matter; any other value is &#x60;422&#x60;. A technology none of your services has gives an empty list. (optional)
     * @param  string|null $customer_reference Only the services with this customer reference, matched whole, ignoring case. (optional)
     * @param  string|null $plan Only the services on this plan, by its SKU (the &#x60;plan.sku&#x60; a service shows), ignoring case. (optional)
     * @param  string|null $location_id Only the services at this premises: an NBN location id (&#x60;LOC&#x60; and 12 digits) or an Opticomm one (&#x60;OPC&#x60; and 12 digits). Any other form is &#x60;422&#x60;. (optional)
     * @param  string[]|null $tag Only rows carrying this tag, matched whole, ignoring case. Repeat it (&#x60;tag&#x3D;Site%2012&amp;tag&#x3D;Brisbane&#x60;, or &#x60;tag[]&#x3D;&#x60;) and a row must carry every one. A tag may hold a comma, so values are never split. A blank tag, or one over 255 characters, is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $needs_attention Only the services in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number (the &#x60;endUser&#x60; each service returns). A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServices'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataServices200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataServicesWithHttpInfo($network = null, $status = null, $expand = null, $page = 1, $per_page = 50, $sort = null, $technology = null, $customer_reference = null, $plan = null, $location_id = null, $tag = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, $needs_attention = null, $end_user = null, string $contentType = self::contentTypes['getDataServices'][0])
    {
        $request = $this->getDataServicesRequest($network, $status, $expand, $page, $per_page, $sort, $technology, $customer_reference, $plan, $location_id, $tag, $q, $external_ref, $updated_since, $created_from, $created_to, $needs_attention, $end_user, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataServices200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataServices200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataServices200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataServicesAsync
     *
     * List Your Services
     *
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $status Only services in these statuses, one or several, comma-separated. Case does not matter, so the &#x60;status&#x60; a service shows (&#x60;Live&#x60;) is accepted as it is; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds each service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string[]|null $technology Only services on these access technologies, one or several, comma-separated, as the &#x60;technology&#x60; a service shows. Case does not matter; any other value is &#x60;422&#x60;. A technology none of your services has gives an empty list. (optional)
     * @param  string|null $customer_reference Only the services with this customer reference, matched whole, ignoring case. (optional)
     * @param  string|null $plan Only the services on this plan, by its SKU (the &#x60;plan.sku&#x60; a service shows), ignoring case. (optional)
     * @param  string|null $location_id Only the services at this premises: an NBN location id (&#x60;LOC&#x60; and 12 digits) or an Opticomm one (&#x60;OPC&#x60; and 12 digits). Any other form is &#x60;422&#x60;. (optional)
     * @param  string[]|null $tag Only rows carrying this tag, matched whole, ignoring case. Repeat it (&#x60;tag&#x3D;Site%2012&amp;tag&#x3D;Brisbane&#x60;, or &#x60;tag[]&#x3D;&#x60;) and a row must carry every one. A tag may hold a comma, so values are never split. A blank tag, or one over 255 characters, is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $needs_attention Only the services in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number (the &#x60;endUser&#x60; each service returns). A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServices'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServicesAsync($network = null, $status = null, $expand = null, $page = 1, $per_page = 50, $sort = null, $technology = null, $customer_reference = null, $plan = null, $location_id = null, $tag = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, $needs_attention = null, $end_user = null, string $contentType = self::contentTypes['getDataServices'][0])
    {
        return $this->getDataServicesAsyncWithHttpInfo($network, $status, $expand, $page, $per_page, $sort, $technology, $customer_reference, $plan, $location_id, $tag, $q, $external_ref, $updated_since, $created_from, $created_to, $needs_attention, $end_user, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataServicesAsyncWithHttpInfo
     *
     * List Your Services
     *
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $status Only services in these statuses, one or several, comma-separated. Case does not matter, so the &#x60;status&#x60; a service shows (&#x60;Live&#x60;) is accepted as it is; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds each service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string[]|null $technology Only services on these access technologies, one or several, comma-separated, as the &#x60;technology&#x60; a service shows. Case does not matter; any other value is &#x60;422&#x60;. A technology none of your services has gives an empty list. (optional)
     * @param  string|null $customer_reference Only the services with this customer reference, matched whole, ignoring case. (optional)
     * @param  string|null $plan Only the services on this plan, by its SKU (the &#x60;plan.sku&#x60; a service shows), ignoring case. (optional)
     * @param  string|null $location_id Only the services at this premises: an NBN location id (&#x60;LOC&#x60; and 12 digits) or an Opticomm one (&#x60;OPC&#x60; and 12 digits). Any other form is &#x60;422&#x60;. (optional)
     * @param  string[]|null $tag Only rows carrying this tag, matched whole, ignoring case. Repeat it (&#x60;tag&#x3D;Site%2012&amp;tag&#x3D;Brisbane&#x60;, or &#x60;tag[]&#x3D;&#x60;) and a row must carry every one. A tag may hold a comma, so values are never split. A blank tag, or one over 255 characters, is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $needs_attention Only the services in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number (the &#x60;endUser&#x60; each service returns). A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServices'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataServicesAsyncWithHttpInfo($network = null, $status = null, $expand = null, $page = 1, $per_page = 50, $sort = null, $technology = null, $customer_reference = null, $plan = null, $location_id = null, $tag = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, $needs_attention = null, $end_user = null, string $contentType = self::contentTypes['getDataServices'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataServices200Response';
        $request = $this->getDataServicesRequest($network, $status, $expand, $page, $per_page, $sort, $technology, $customer_reference, $plan, $location_id, $tag, $q, $external_ref, $updated_since, $created_from, $created_to, $needs_attention, $end_user, $contentType);

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
     * Create request for operation 'getDataServices'
     *
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $status Only services in these statuses, one or several, comma-separated. Case does not matter, so the &#x60;status&#x60; a service shows (&#x60;Live&#x60;) is accepted as it is; any other value is &#x60;422&#x60;. (optional)
     * @param  string[]|null $expand &#x60;endUser&#x60; adds each service&#39;s end user in full, as &#x60;endUserDetail&#x60;. Any other value is &#x60;422&#x60;. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string[]|null $technology Only services on these access technologies, one or several, comma-separated, as the &#x60;technology&#x60; a service shows. Case does not matter; any other value is &#x60;422&#x60;. A technology none of your services has gives an empty list. (optional)
     * @param  string|null $customer_reference Only the services with this customer reference, matched whole, ignoring case. (optional)
     * @param  string|null $plan Only the services on this plan, by its SKU (the &#x60;plan.sku&#x60; a service shows), ignoring case. (optional)
     * @param  string|null $location_id Only the services at this premises: an NBN location id (&#x60;LOC&#x60; and 12 digits) or an Opticomm one (&#x60;OPC&#x60; and 12 digits). Any other form is &#x60;422&#x60;. (optional)
     * @param  string[]|null $tag Only rows carrying this tag, matched whole, ignoring case. Repeat it (&#x60;tag&#x3D;Site%2012&amp;tag&#x3D;Brisbane&#x60;, or &#x60;tag[]&#x3D;&#x60;) and a row must carry every one. A tag may hold a comma, so values are never split. A blank tag, or one over 255 characters, is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $needs_attention Only the services in this needs-attention group, exactly as &#x60;GET /account/summary&#x60; counted them. With it, &#x60;endUser&#x60; (an end user&#39;s number) narrows the group to that end user. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number (the &#x60;endUser&#x60; each service returns). A number that is not one of your end users is a 422, not an empty list. With &#x60;needsAttention&#x60;, it narrows the group to that end user. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataServices'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataServicesRequest($network = null, $status = null, $expand = null, $page = 1, $per_page = 50, $sort = null, $technology = null, $customer_reference = null, $plan = null, $location_id = null, $tag = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, $needs_attention = null, $end_user = null, string $contentType = self::contentTypes['getDataServices'][0])
    {




        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling DataServicesApi.getDataServices, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServices, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataServices, must be bigger than or equal to 1.');
        }
        


        if ($customer_reference !== null && strlen($customer_reference) > 64) {
            throw new \InvalidArgumentException('invalid length for "$customer_reference" when calling DataServicesApi.getDataServices, must be smaller than or equal to 64.');
        }
        
        if ($plan !== null && strlen($plan) > 64) {
            throw new \InvalidArgumentException('invalid length for "$plan" when calling DataServicesApi.getDataServices, must be smaller than or equal to 64.');
        }
        
        if ($location_id !== null && strlen($location_id) > 32) {
            throw new \InvalidArgumentException('invalid length for "$location_id" when calling DataServicesApi.getDataServices, must be smaller than or equal to 32.');
        }
        
        if ($tag !== null && count($tag) > 20) {
            throw new \InvalidArgumentException('invalid value for "$tag" when calling DataServicesApi.getDataServices, number of items must be less than or equal to 20.');
        }
        
        if ($q !== null && strlen($q) > 100) {
            throw new \InvalidArgumentException('invalid length for "$q" when calling DataServicesApi.getDataServices, must be smaller than or equal to 100.');
        }
        
        if ($external_ref !== null && strlen($external_ref) > 64) {
            throw new \InvalidArgumentException('invalid length for "$external_ref" when calling DataServicesApi.getDataServices, must be smaller than or equal to 64.');
        }
        if ($external_ref !== null && strlen($external_ref) < 1) {
            throw new \InvalidArgumentException('invalid length for "$external_ref" when calling DataServicesApi.getDataServices, must be bigger than or equal to 1.');
        }
        if ($external_ref !== null && !preg_match("/^[A-Za-z0-9_.\\-]+$/", $external_ref)) {
            throw new \InvalidArgumentException("invalid value for \"external_ref\" when calling DataServicesApi.getDataServices, must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
        }
        




        if ($end_user !== null && strlen($end_user) > 64) {
            throw new \InvalidArgumentException('invalid length for "$end_user" when calling DataServicesApi.getDataServices, must be smaller than or equal to 64.');
        }
        

        $resourcePath = '/data/services';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $network,
            'network', // param base name
            'array', // openApiType
            'form', // style
            false, // explode
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
            $expand,
            'expand', // param base name
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
            $sort,
            'sort', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $technology,
            'technology', // param base name
            'array', // openApiType
            'form', // style
            false, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $customer_reference,
            'customerReference', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $plan,
            'plan', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $location_id,
            'locationId', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $tag,
            'tag', // param base name
            'array', // openApiType
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
            $external_ref,
            'externalRef', // param base name
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
            $needs_attention,
            'needsAttention', // param base name
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
     * Operation getDataTags
     *
     * List Your Tags
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataTags'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataTags200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataTags($page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataTags'][0])
    {
        list($response) = $this->getDataTagsWithHttpInfo($page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation getDataTagsWithHttpInfo
     *
     * List Your Tags
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataTags'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataTags200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataTagsWithHttpInfo($page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataTags'][0])
    {
        $request = $this->getDataTagsRequest($page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataTags200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataTags200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataTags200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataTagsAsync
     *
     * List Your Tags
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataTags'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataTagsAsync($page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataTags'][0])
    {
        return $this->getDataTagsAsyncWithHttpInfo($page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataTagsAsyncWithHttpInfo
     *
     * List Your Tags
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataTags'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataTagsAsyncWithHttpInfo($page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataTags'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataTags200Response';
        $request = $this->getDataTagsRequest($page, $per_page, $contentType);

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
     * Create request for operation 'getDataTags'
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataTags'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataTagsRequest($page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataTags'][0])
    {

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling DataServicesApi.getDataTags, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataTags, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataTags, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/data/tags';
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
     * Operation getDataUsage
     *
     * List Every Service&#39;s Usage for a Month
     *
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataUsage'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetDataUsage200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getDataUsage($period = null, $network = null, $end_user = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataUsage'][0])
    {
        list($response) = $this->getDataUsageWithHttpInfo($period, $network, $end_user, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation getDataUsageWithHttpInfo
     *
     * List Every Service&#39;s Usage for a Month
     *
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataUsage'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetDataUsage200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getDataUsageWithHttpInfo($period = null, $network = null, $end_user = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataUsage'][0])
    {
        $request = $this->getDataUsageRequest($period, $network, $end_user, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetDataUsage200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\GetDataUsage200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetDataUsage200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getDataUsageAsync
     *
     * List Every Service&#39;s Usage for a Month
     *
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataUsage'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataUsageAsync($period = null, $network = null, $end_user = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataUsage'][0])
    {
        return $this->getDataUsageAsyncWithHttpInfo($period, $network, $end_user, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getDataUsageAsyncWithHttpInfo
     *
     * List Every Service&#39;s Usage for a Month
     *
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataUsage'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getDataUsageAsyncWithHttpInfo($period = null, $network = null, $end_user = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataUsage'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetDataUsage200Response';
        $request = $this->getDataUsageRequest($period, $network, $end_user, $page, $per_page, $contentType);

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
     * Create request for operation 'getDataUsage'
     *
     * @param  string|null $period The month, &#x60;YYYY-MM&#x60;. Defaults to the current month. (optional)
     * @param  string[]|null $network Only services on these networks, one or several, comma-separated. Case does not matter; any other value is &#x60;422&#x60;. (optional)
     * @param  string|null $end_user Only the services of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getDataUsage'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getDataUsageRequest($period = null, $network = null, $end_user = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['getDataUsage'][0])
    {

        if ($period !== null && !preg_match("/^\\d{4}-\\d{2}$/", $period)) {
            throw new \InvalidArgumentException("invalid value for \"period\" when calling DataServicesApi.getDataUsage, must conform to the pattern /^\\d{4}-\\d{2}$/.");
        }
        

        if ($end_user !== null && strlen($end_user) > 64) {
            throw new \InvalidArgumentException('invalid length for "$end_user" when calling DataServicesApi.getDataUsage, must be smaller than or equal to 64.');
        }
        
        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling DataServicesApi.getDataUsage, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataUsage, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling DataServicesApi.getDataUsage, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/data/usage';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $period,
            'period', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $network,
            'network', // param base name
            'array', // openApiType
            'form', // style
            false, // explode
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
     * Operation patchDataService
     *
     * Set a Service&#39;s Own Reference
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchDataServiceRequest $patch_data_service_request patch_data_service_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchDataService'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\PatchDataService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function patchDataService($ref, $patch_data_service_request, $idempotency_key = null, string $contentType = self::contentTypes['patchDataService'][0])
    {
        list($response) = $this->patchDataServiceWithHttpInfo($ref, $patch_data_service_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation patchDataServiceWithHttpInfo
     *
     * Set a Service&#39;s Own Reference
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchDataServiceRequest $patch_data_service_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchDataService'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\PatchDataService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function patchDataServiceWithHttpInfo($ref, $patch_data_service_request, $idempotency_key = null, string $contentType = self::contentTypes['patchDataService'][0])
    {
        $request = $this->patchDataServiceRequest($ref, $patch_data_service_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\PatchDataService200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\PatchDataService200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PatchDataService200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation patchDataServiceAsync
     *
     * Set a Service&#39;s Own Reference
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchDataServiceRequest $patch_data_service_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchDataService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function patchDataServiceAsync($ref, $patch_data_service_request, $idempotency_key = null, string $contentType = self::contentTypes['patchDataService'][0])
    {
        return $this->patchDataServiceAsyncWithHttpInfo($ref, $patch_data_service_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation patchDataServiceAsyncWithHttpInfo
     *
     * Set a Service&#39;s Own Reference
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchDataServiceRequest $patch_data_service_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchDataService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function patchDataServiceAsyncWithHttpInfo($ref, $patch_data_service_request, $idempotency_key = null, string $contentType = self::contentTypes['patchDataService'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\PatchDataService200Response';
        $request = $this->patchDataServiceRequest($ref, $patch_data_service_request, $idempotency_key, $contentType);

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
     * Create request for operation 'patchDataService'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchDataServiceRequest $patch_data_service_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchDataService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function patchDataServiceRequest($ref, $patch_data_service_request, $idempotency_key = null, string $contentType = self::contentTypes['patchDataService'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling patchDataService'
            );
        }

        // verify the required parameter 'patch_data_service_request' is set
        if ($patch_data_service_request === null || (is_array($patch_data_service_request) && count($patch_data_service_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $patch_data_service_request when calling patchDataService'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling DataServicesApi.patchDataService, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/data/services/{ref}';
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
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($patch_data_service_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($patch_data_service_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $patch_data_service_request;
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
     * Operation postDataServiceDiagnostics
     *
     * Run a Line Test on a Service
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServiceDiagnosticsRequest $post_data_service_diagnostics_request post_data_service_diagnostics_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics202Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics409Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function postDataServiceDiagnostics($idempotency_key, $ref, $post_data_service_diagnostics_request, string $contentType = self::contentTypes['postDataServiceDiagnostics'][0])
    {
        list($response) = $this->postDataServiceDiagnosticsWithHttpInfo($idempotency_key, $ref, $post_data_service_diagnostics_request, $contentType);
        return $response;
    }

    /**
     * Operation postDataServiceDiagnosticsWithHttpInfo
     *
     * Run a Line Test on a Service
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServiceDiagnosticsRequest $post_data_service_diagnostics_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics202Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics409Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postDataServiceDiagnosticsWithHttpInfo($idempotency_key, $ref, $post_data_service_diagnostics_request, string $contentType = self::contentTypes['postDataServiceDiagnostics'][0])
    {
        $request = $this->postDataServiceDiagnosticsRequest($idempotency_key, $ref, $post_data_service_diagnostics_request, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics202Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics409Response',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 503:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics202Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics409Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 503:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postDataServiceDiagnosticsAsync
     *
     * Run a Line Test on a Service
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServiceDiagnosticsRequest $post_data_service_diagnostics_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServiceDiagnosticsAsync($idempotency_key, $ref, $post_data_service_diagnostics_request, string $contentType = self::contentTypes['postDataServiceDiagnostics'][0])
    {
        return $this->postDataServiceDiagnosticsAsyncWithHttpInfo($idempotency_key, $ref, $post_data_service_diagnostics_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postDataServiceDiagnosticsAsyncWithHttpInfo
     *
     * Run a Line Test on a Service
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServiceDiagnosticsRequest $post_data_service_diagnostics_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServiceDiagnosticsAsyncWithHttpInfo($idempotency_key, $ref, $post_data_service_diagnostics_request, string $contentType = self::contentTypes['postDataServiceDiagnostics'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\PostDataServiceDiagnostics202Response';
        $request = $this->postDataServiceDiagnosticsRequest($idempotency_key, $ref, $post_data_service_diagnostics_request, $contentType);

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
     * Create request for operation 'postDataServiceDiagnostics'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServiceDiagnosticsRequest $post_data_service_diagnostics_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceDiagnostics'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postDataServiceDiagnosticsRequest($idempotency_key, $ref, $post_data_service_diagnostics_request, string $contentType = self::contentTypes['postDataServiceDiagnostics'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling postDataServiceDiagnostics'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling DataServicesApi.postDataServiceDiagnostics, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling postDataServiceDiagnostics'
            );
        }

        // verify the required parameter 'post_data_service_diagnostics_request' is set
        if ($post_data_service_diagnostics_request === null || (is_array($post_data_service_diagnostics_request) && count($post_data_service_diagnostics_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $post_data_service_diagnostics_request when calling postDataServiceDiagnostics'
            );
        }


        $resourcePath = '/data/services/{ref}/diagnostics';
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
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($post_data_service_diagnostics_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($post_data_service_diagnostics_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $post_data_service_diagnostics_request;
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
     * Operation postDataServiceHealth
     *
     * Request a Service Health Report
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\PostDataServiceHealth202Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\PostDataServiceHealth409Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function postDataServiceHealth($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceHealth'][0])
    {
        list($response) = $this->postDataServiceHealthWithHttpInfo($idempotency_key, $ref, $contentType);
        return $response;
    }

    /**
     * Operation postDataServiceHealthWithHttpInfo
     *
     * Request a Service Health Report
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\PostDataServiceHealth202Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\PostDataServiceHealth409Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postDataServiceHealthWithHttpInfo($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceHealth'][0])
    {
        $request = $this->postDataServiceHealthRequest($idempotency_key, $ref, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceHealth202Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceHealth409Response',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 502:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\PostDataServiceHealth202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceHealth202Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceHealth409Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 502:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postDataServiceHealthAsync
     *
     * Request a Service Health Report
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServiceHealthAsync($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceHealth'][0])
    {
        return $this->postDataServiceHealthAsyncWithHttpInfo($idempotency_key, $ref, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postDataServiceHealthAsyncWithHttpInfo
     *
     * Request a Service Health Report
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServiceHealthAsyncWithHttpInfo($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceHealth'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\PostDataServiceHealth202Response';
        $request = $this->postDataServiceHealthRequest($idempotency_key, $ref, $contentType);

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
     * Create request for operation 'postDataServiceHealth'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC, OAC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceHealth'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postDataServiceHealthRequest($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceHealth'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling postDataServiceHealth'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling DataServicesApi.postDataServiceHealth, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling postDataServiceHealth'
            );
        }


        $resourcePath = '/data/services/{ref}/health';
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
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation postDataServiceSessionsRefresh
     *
     * Refresh a Service&#39;s Sessions
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceSessionsRefresh'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh202Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh409Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function postDataServiceSessionsRefresh($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceSessionsRefresh'][0])
    {
        list($response) = $this->postDataServiceSessionsRefreshWithHttpInfo($idempotency_key, $ref, $contentType);
        return $response;
    }

    /**
     * Operation postDataServiceSessionsRefreshWithHttpInfo
     *
     * Refresh a Service&#39;s Sessions
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceSessionsRefresh'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh202Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh409Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postDataServiceSessionsRefreshWithHttpInfo($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceSessionsRefresh'][0])
    {
        $request = $this->postDataServiceSessionsRefreshRequest($idempotency_key, $ref, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh202Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh409Response',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 502:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh202Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh202Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 409:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh409Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 502:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postDataServiceSessionsRefreshAsync
     *
     * Refresh a Service&#39;s Sessions
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceSessionsRefresh'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServiceSessionsRefreshAsync($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceSessionsRefresh'][0])
    {
        return $this->postDataServiceSessionsRefreshAsyncWithHttpInfo($idempotency_key, $ref, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postDataServiceSessionsRefreshAsyncWithHttpInfo
     *
     * Refresh a Service&#39;s Sessions
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceSessionsRefresh'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServiceSessionsRefreshAsyncWithHttpInfo($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceSessionsRefresh'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\PostDataServiceSessionsRefresh202Response';
        $request = $this->postDataServiceSessionsRefreshRequest($idempotency_key, $ref, $contentType);

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
     * Create request for operation 'postDataServiceSessionsRefresh'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServiceSessionsRefresh'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postDataServiceSessionsRefreshRequest($idempotency_key, $ref, string $contentType = self::contentTypes['postDataServiceSessionsRefresh'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling postDataServiceSessionsRefresh'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling DataServicesApi.postDataServiceSessionsRefresh, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling postDataServiceSessionsRefresh'
            );
        }


        $resourcePath = '/data/services/{ref}/sessions/refresh';
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
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
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
     * Operation postDataServicesAssign
     *
     * Assign Services to an End User
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServicesAssignRequest $post_data_services_assign_request post_data_services_assign_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServicesAssign'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\PostDataServicesAssign200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function postDataServicesAssign($idempotency_key, $post_data_services_assign_request, string $contentType = self::contentTypes['postDataServicesAssign'][0])
    {
        list($response) = $this->postDataServicesAssignWithHttpInfo($idempotency_key, $post_data_services_assign_request, $contentType);
        return $response;
    }

    /**
     * Operation postDataServicesAssignWithHttpInfo
     *
     * Assign Services to an End User
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServicesAssignRequest $post_data_services_assign_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServicesAssign'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\PostDataServicesAssign200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postDataServicesAssignWithHttpInfo($idempotency_key, $post_data_services_assign_request, string $contentType = self::contentTypes['postDataServicesAssign'][0])
    {
        $request = $this->postDataServicesAssignRequest($idempotency_key, $post_data_services_assign_request, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\PostDataServicesAssign200Response',
                        $request,
                        $response,
                    );
                case 400:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\PostDataServicesAssign200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PostDataServicesAssign200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation postDataServicesAssignAsync
     *
     * Assign Services to an End User
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServicesAssignRequest $post_data_services_assign_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServicesAssign'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServicesAssignAsync($idempotency_key, $post_data_services_assign_request, string $contentType = self::contentTypes['postDataServicesAssign'][0])
    {
        return $this->postDataServicesAssignAsyncWithHttpInfo($idempotency_key, $post_data_services_assign_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postDataServicesAssignAsyncWithHttpInfo
     *
     * Assign Services to an End User
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServicesAssignRequest $post_data_services_assign_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServicesAssign'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postDataServicesAssignAsyncWithHttpInfo($idempotency_key, $post_data_services_assign_request, string $contentType = self::contentTypes['postDataServicesAssign'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\PostDataServicesAssign200Response';
        $request = $this->postDataServicesAssignRequest($idempotency_key, $post_data_services_assign_request, $contentType);

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
     * Create request for operation 'postDataServicesAssign'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Api\Generated\Model\PostDataServicesAssignRequest $post_data_services_assign_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postDataServicesAssign'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postDataServicesAssignRequest($idempotency_key, $post_data_services_assign_request, string $contentType = self::contentTypes['postDataServicesAssign'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling postDataServicesAssign'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling DataServicesApi.postDataServicesAssign, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'post_data_services_assign_request' is set
        if ($post_data_services_assign_request === null || (is_array($post_data_services_assign_request) && count($post_data_services_assign_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $post_data_services_assign_request when calling postDataServicesAssign'
            );
        }


        $resourcePath = '/data/services/assign';
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
        if (isset($post_data_services_assign_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($post_data_services_assign_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $post_data_services_assign_request;
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
     * Operation putDataServiceTags
     *
     * Set a Service&#39;s Tags
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PutDataServiceTagsRequest $put_data_service_tags_request put_data_service_tags_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putDataServiceTags'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\PutDataServiceTags200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function putDataServiceTags($ref, $put_data_service_tags_request, $idempotency_key = null, string $contentType = self::contentTypes['putDataServiceTags'][0])
    {
        list($response) = $this->putDataServiceTagsWithHttpInfo($ref, $put_data_service_tags_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation putDataServiceTagsWithHttpInfo
     *
     * Set a Service&#39;s Tags
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PutDataServiceTagsRequest $put_data_service_tags_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putDataServiceTags'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\PutDataServiceTags200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function putDataServiceTagsWithHttpInfo($ref, $put_data_service_tags_request, $idempotency_key = null, string $contentType = self::contentTypes['putDataServiceTags'][0])
    {
        $request = $this->putDataServiceTagsRequest($ref, $put_data_service_tags_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\PutDataServiceTags200Response',
                        $request,
                        $response,
                    );
                case 401:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 403:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 422:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 429:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 500:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
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
                '\CheckNetworks\Api\Generated\Model\PutDataServiceTags200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\PutDataServiceTags200Response',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 422:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 429:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
                case 500:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation putDataServiceTagsAsync
     *
     * Set a Service&#39;s Tags
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PutDataServiceTagsRequest $put_data_service_tags_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putDataServiceTags'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function putDataServiceTagsAsync($ref, $put_data_service_tags_request, $idempotency_key = null, string $contentType = self::contentTypes['putDataServiceTags'][0])
    {
        return $this->putDataServiceTagsAsyncWithHttpInfo($ref, $put_data_service_tags_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation putDataServiceTagsAsyncWithHttpInfo
     *
     * Set a Service&#39;s Tags
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PutDataServiceTagsRequest $put_data_service_tags_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putDataServiceTags'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function putDataServiceTagsAsyncWithHttpInfo($ref, $put_data_service_tags_request, $idempotency_key = null, string $contentType = self::contentTypes['putDataServiceTags'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\PutDataServiceTags200Response';
        $request = $this->putDataServiceTagsRequest($ref, $put_data_service_tags_request, $idempotency_key, $contentType);

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
     * Create request for operation 'putDataServiceTags'
     *
     * @param  string $ref The service&#39;s product reference (PRI, Opticomm product) or service reference (AVC). (required)
     * @param  \CheckNetworks\Api\Generated\Model\PutDataServiceTagsRequest $put_data_service_tags_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putDataServiceTags'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function putDataServiceTagsRequest($ref, $put_data_service_tags_request, $idempotency_key = null, string $contentType = self::contentTypes['putDataServiceTags'][0])
    {

        // verify the required parameter 'ref' is set
        if ($ref === null || (is_array($ref) && count($ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ref when calling putDataServiceTags'
            );
        }

        // verify the required parameter 'put_data_service_tags_request' is set
        if ($put_data_service_tags_request === null || (is_array($put_data_service_tags_request) && count($put_data_service_tags_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $put_data_service_tags_request when calling putDataServiceTags'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling DataServicesApi.putDataServiceTags, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/data/services/{ref}/tags';
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
        if ($ref !== null) {
            $resourcePath = str_replace(
                '{ref}',
                ObjectSerializer::toPathValue($ref),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($put_data_service_tags_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($put_data_service_tags_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $put_data_service_tags_request;
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
            'PUT',
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
