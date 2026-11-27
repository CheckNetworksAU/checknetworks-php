<?php
/**
 * AddressApi
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
 * AddressApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class AddressApi
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
        'getGeocode' => [
            'application/json',
        ],
        'getSearch' => [
            'application/json',
        ],
        'getServices' => [
            'application/json',
        ],
        'getSingleAddress' => [
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
     * Operation getGeocode
     *
     * Request nbn™ Location IDs (LOC #) for a Latitude and Longitude
     *
     * @param  float $latitude Geographic latitude coordinate of the address. (required)
     * @param  float $longitude Geographic longitude coordinate of the address. (required)
     * @param  int|null $limit Limit the amount of locations returned. Leave empty to return all available results. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getGeocode'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GeographicAddress[]|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getGeocode($latitude, $longitude, $limit = null, string $contentType = self::contentTypes['getGeocode'][0])
    {
        list($response) = $this->getGeocodeWithHttpInfo($latitude, $longitude, $limit, $contentType);
        return $response;
    }

    /**
     * Operation getGeocodeWithHttpInfo
     *
     * Request nbn™ Location IDs (LOC #) for a Latitude and Longitude
     *
     * @param  float $latitude Geographic latitude coordinate of the address. (required)
     * @param  float $longitude Geographic longitude coordinate of the address. (required)
     * @param  int|null $limit Limit the amount of locations returned. Leave empty to return all available results. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getGeocode'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GeographicAddress[]|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getGeocodeWithHttpInfo($latitude, $longitude, $limit = null, string $contentType = self::contentTypes['getGeocode'][0])
    {
        $request = $this->getGeocodeRequest($latitude, $longitude, $limit, $contentType);

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
                        '\CheckNetworks\Model\GeographicAddress[]',
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
                case 502:
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
                '\CheckNetworks\Model\GeographicAddress[]',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GeographicAddress[]',
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
                case 502:
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
     * Operation getGeocodeAsync
     *
     * Request nbn™ Location IDs (LOC #) for a Latitude and Longitude
     *
     * @param  float $latitude Geographic latitude coordinate of the address. (required)
     * @param  float $longitude Geographic longitude coordinate of the address. (required)
     * @param  int|null $limit Limit the amount of locations returned. Leave empty to return all available results. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getGeocode'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getGeocodeAsync($latitude, $longitude, $limit = null, string $contentType = self::contentTypes['getGeocode'][0])
    {
        return $this->getGeocodeAsyncWithHttpInfo($latitude, $longitude, $limit, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getGeocodeAsyncWithHttpInfo
     *
     * Request nbn™ Location IDs (LOC #) for a Latitude and Longitude
     *
     * @param  float $latitude Geographic latitude coordinate of the address. (required)
     * @param  float $longitude Geographic longitude coordinate of the address. (required)
     * @param  int|null $limit Limit the amount of locations returned. Leave empty to return all available results. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getGeocode'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getGeocodeAsyncWithHttpInfo($latitude, $longitude, $limit = null, string $contentType = self::contentTypes['getGeocode'][0])
    {
        $returnType = '\CheckNetworks\Model\GeographicAddress[]';
        $request = $this->getGeocodeRequest($latitude, $longitude, $limit, $contentType);

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
     * Create request for operation 'getGeocode'
     *
     * @param  float $latitude Geographic latitude coordinate of the address. (required)
     * @param  float $longitude Geographic longitude coordinate of the address. (required)
     * @param  int|null $limit Limit the amount of locations returned. Leave empty to return all available results. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getGeocode'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getGeocodeRequest($latitude, $longitude, $limit = null, string $contentType = self::contentTypes['getGeocode'][0])
    {

        // verify the required parameter 'latitude' is set
        if ($latitude === null || (is_array($latitude) && count($latitude) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $latitude when calling getGeocode'
            );
        }

        // verify the required parameter 'longitude' is set
        if ($longitude === null || (is_array($longitude) && count($longitude) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $longitude when calling getGeocode'
            );
        }



        $resourcePath = '/nbn/address/geocode';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $latitude,
            'latitude', // param base name
            'number', // openApiType
            'form', // style
            true, // explode
            true // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $longitude,
            'longitude', // param base name
            'number', // openApiType
            'form', // style
            true, // explode
            true // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $limit,
            'limit', // param base name
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
     * Operation getSearch
     *
     * Request the nbn™ Location ID (LOC #) for an address
     *
     * @param  string|null $secondary_complex_name Name associated with a building or area within a complex site. This data element may represent the name given to an entire building within an address site that has its own separate address. (optional)
     * @param  string|null $unit_type_code Sub dwelling unit type code. Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina. (optional)
     * @param  string|null $unit_number Sub-dwelling unit number. Within a complex, a number used to distinguish an address found within a building / sub-complex or marina. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $level_number Within a complex, the number of the floor or level of a multi-storey building/ sub-complex. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $complex_road_number1 Within a complex, identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. (optional)
     * @param  string|null $complex_road_number2 Within a complex, identifies the last number for a ranged address in the road or thoroughfare. (optional)
     * @param  string|null $complex_road_name Within a complex, the name of the road /thoroughfare of the address. Within a Road name it is possible to find what appears to be a Road type (eg. The Boulevard). It is also possible to have a null Road type. (optional)
     * @param  string|null $complex_road_type_code Within a complex, an abbreviation used to distinguish the type of road / thoroughfare of the address. (optional)
     * @param  string|null $complex_road_suffix_code Within a complex, an abbreviation representing the suffix of the road/ thoroughfare of the address. (optional)
     * @param  string|null $address_site_name Synonyms: Building property name, Primary Complex name  The official place name or culturally accepted common usage name for an address site, including the name of a building, homestead, building complex, agricultural property, park or unbounded address site. This data element represents the name given to an entire building or address site (optional)
     * @param  string|null $lot_number The Lot number is a reference number allocated to a property for subdivision administration purposes prior to road numbering.  Usage: Where an official Road number has not been assigned, this field may be used for postal purposes as well as the physical identification of an address site. (optional)
     * @param  string|null $plan_number The deposited plan number allocated to a property for subdivision administration purposes prior to road numbering in addition to the Lot number. (optional)
     * @param  string|null $road_number1 Identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. (optional)
     * @param  string|null $road_number2 Identifies the last number for a ranged address in the road or thoroughfare. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. This data element is used for ranged street numbers otherwise it is left blank. (optional)
     * @param  string|null $road_name The name of the road/thoroughfare applicable to the address site or complex. Only the road name should be stored in this data element. (optional)
     * @param  string|null $road_type_code The abbreviation code used to distinguish the type of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $road_suffix_code The abbreviation code used to represent the suffix of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $locality_name The name of the locality/suburb of the address. (optional)
     * @param  string|null $postcode The Australian numeric descriptor for a postal delivery area, aligned with locality, suburb or place  *Mandatory if stateTerritoryCode not provided (optional)
     * @param  string|null $state_territory_code The State or Territory code of the address.  *Mandatory if postcode not provided (optional)
     * @param  string|null $gnaf Geocoded National Address File (GNAF) ID (optional)
     * @param  float|null $latitude Geographic latitude coordinate of the address. (optional)
     * @param  float|null $longitude Geographic longitude coordinate of the address. (optional)
     * @param  string|null $full_text Try and parse an entire address string. (optional)
     * @param  bool|null $adjust_full_text [beta] (Optional) Have Check Networks internally adjust the &#x60;.fullText&#x60; value when querying nbn&amp;trade; to obtain better search results. The adjustments will change over time and may include:  - Removing the Country  - Inserting the missing State when PostCode is included  - Reordering State and Postcode. e.g., \&quot;2000 NSW\&quot; &gt;&gt; \&quot;NSW 2000\&quot;  - Changing Road Types to their codes. e.g. \&quot;Street\&quot; &gt;&gt; \&quot;ST\&quot;  - Adding sub-premise prefix. e.g. \&quot;2 12 Some St\&quot; &gt;&gt; \&quot;UNIT 2 12 Some St\&quot;  - Stripping excess spaces. e.g. \&quot;12 - 24 Some St\&quot; &gt;&gt; \&quot;12-24 Some St\&quot; (optional)
     * @param  bool|null $requery_full_text [beta] (Optional) Do an internal requery to nbn&amp;trade; when there are no results to an address search. For a full text query, this will attempt to split the &#x60;fullText&#x60; value into its component parts for use in an Advanced Search. For an advanced query, this will attempt to construct a full text string from the address components for use in a full text search. This will only occur when the initial query to nbn&amp;trade; returns no results. (optional)
     * @param  bool|null $rank_results [beta] (Optional) Attach a rank to each of the query results. This rank will be a number between 0 and 100, indicating how well the result matches your initial search. A higher rank represents a better match. (optional)
     * @param  bool|null $fuzzy Enable Fuzzy searching for more, but less accurate, search results. (optional)
     * @param  int|null $limit Limit the amount of results returned, from 1 to 500. A value outside that range is brought into it. Defaults to 50 when not supplied. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSearch'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetSearch200ResponseInner[]|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getSearch($secondary_complex_name = null, $unit_type_code = null, $unit_number = null, $level_number = null, $complex_road_number1 = null, $complex_road_number2 = null, $complex_road_name = null, $complex_road_type_code = null, $complex_road_suffix_code = null, $address_site_name = null, $lot_number = null, $plan_number = null, $road_number1 = null, $road_number2 = null, $road_name = null, $road_type_code = null, $road_suffix_code = null, $locality_name = null, $postcode = null, $state_territory_code = null, $gnaf = null, $latitude = null, $longitude = null, $full_text = null, $adjust_full_text = null, $requery_full_text = null, $rank_results = null, $fuzzy = null, $limit = 50, string $contentType = self::contentTypes['getSearch'][0])
    {
        list($response) = $this->getSearchWithHttpInfo($secondary_complex_name, $unit_type_code, $unit_number, $level_number, $complex_road_number1, $complex_road_number2, $complex_road_name, $complex_road_type_code, $complex_road_suffix_code, $address_site_name, $lot_number, $plan_number, $road_number1, $road_number2, $road_name, $road_type_code, $road_suffix_code, $locality_name, $postcode, $state_territory_code, $gnaf, $latitude, $longitude, $full_text, $adjust_full_text, $requery_full_text, $rank_results, $fuzzy, $limit, $contentType);
        return $response;
    }

    /**
     * Operation getSearchWithHttpInfo
     *
     * Request the nbn™ Location ID (LOC #) for an address
     *
     * @param  string|null $secondary_complex_name Name associated with a building or area within a complex site. This data element may represent the name given to an entire building within an address site that has its own separate address. (optional)
     * @param  string|null $unit_type_code Sub dwelling unit type code. Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina. (optional)
     * @param  string|null $unit_number Sub-dwelling unit number. Within a complex, a number used to distinguish an address found within a building / sub-complex or marina. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $level_number Within a complex, the number of the floor or level of a multi-storey building/ sub-complex. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $complex_road_number1 Within a complex, identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. (optional)
     * @param  string|null $complex_road_number2 Within a complex, identifies the last number for a ranged address in the road or thoroughfare. (optional)
     * @param  string|null $complex_road_name Within a complex, the name of the road /thoroughfare of the address. Within a Road name it is possible to find what appears to be a Road type (eg. The Boulevard). It is also possible to have a null Road type. (optional)
     * @param  string|null $complex_road_type_code Within a complex, an abbreviation used to distinguish the type of road / thoroughfare of the address. (optional)
     * @param  string|null $complex_road_suffix_code Within a complex, an abbreviation representing the suffix of the road/ thoroughfare of the address. (optional)
     * @param  string|null $address_site_name Synonyms: Building property name, Primary Complex name  The official place name or culturally accepted common usage name for an address site, including the name of a building, homestead, building complex, agricultural property, park or unbounded address site. This data element represents the name given to an entire building or address site (optional)
     * @param  string|null $lot_number The Lot number is a reference number allocated to a property for subdivision administration purposes prior to road numbering.  Usage: Where an official Road number has not been assigned, this field may be used for postal purposes as well as the physical identification of an address site. (optional)
     * @param  string|null $plan_number The deposited plan number allocated to a property for subdivision administration purposes prior to road numbering in addition to the Lot number. (optional)
     * @param  string|null $road_number1 Identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. (optional)
     * @param  string|null $road_number2 Identifies the last number for a ranged address in the road or thoroughfare. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. This data element is used for ranged street numbers otherwise it is left blank. (optional)
     * @param  string|null $road_name The name of the road/thoroughfare applicable to the address site or complex. Only the road name should be stored in this data element. (optional)
     * @param  string|null $road_type_code The abbreviation code used to distinguish the type of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $road_suffix_code The abbreviation code used to represent the suffix of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $locality_name The name of the locality/suburb of the address. (optional)
     * @param  string|null $postcode The Australian numeric descriptor for a postal delivery area, aligned with locality, suburb or place  *Mandatory if stateTerritoryCode not provided (optional)
     * @param  string|null $state_territory_code The State or Territory code of the address.  *Mandatory if postcode not provided (optional)
     * @param  string|null $gnaf Geocoded National Address File (GNAF) ID (optional)
     * @param  float|null $latitude Geographic latitude coordinate of the address. (optional)
     * @param  float|null $longitude Geographic longitude coordinate of the address. (optional)
     * @param  string|null $full_text Try and parse an entire address string. (optional)
     * @param  bool|null $adjust_full_text [beta] (Optional) Have Check Networks internally adjust the &#x60;.fullText&#x60; value when querying nbn&amp;trade; to obtain better search results. The adjustments will change over time and may include:  - Removing the Country  - Inserting the missing State when PostCode is included  - Reordering State and Postcode. e.g., \&quot;2000 NSW\&quot; &gt;&gt; \&quot;NSW 2000\&quot;  - Changing Road Types to their codes. e.g. \&quot;Street\&quot; &gt;&gt; \&quot;ST\&quot;  - Adding sub-premise prefix. e.g. \&quot;2 12 Some St\&quot; &gt;&gt; \&quot;UNIT 2 12 Some St\&quot;  - Stripping excess spaces. e.g. \&quot;12 - 24 Some St\&quot; &gt;&gt; \&quot;12-24 Some St\&quot; (optional)
     * @param  bool|null $requery_full_text [beta] (Optional) Do an internal requery to nbn&amp;trade; when there are no results to an address search. For a full text query, this will attempt to split the &#x60;fullText&#x60; value into its component parts for use in an Advanced Search. For an advanced query, this will attempt to construct a full text string from the address components for use in a full text search. This will only occur when the initial query to nbn&amp;trade; returns no results. (optional)
     * @param  bool|null $rank_results [beta] (Optional) Attach a rank to each of the query results. This rank will be a number between 0 and 100, indicating how well the result matches your initial search. A higher rank represents a better match. (optional)
     * @param  bool|null $fuzzy Enable Fuzzy searching for more, but less accurate, search results. (optional)
     * @param  int|null $limit Limit the amount of results returned, from 1 to 500. A value outside that range is brought into it. Defaults to 50 when not supplied. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSearch'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetSearch200ResponseInner[]|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getSearchWithHttpInfo($secondary_complex_name = null, $unit_type_code = null, $unit_number = null, $level_number = null, $complex_road_number1 = null, $complex_road_number2 = null, $complex_road_name = null, $complex_road_type_code = null, $complex_road_suffix_code = null, $address_site_name = null, $lot_number = null, $plan_number = null, $road_number1 = null, $road_number2 = null, $road_name = null, $road_type_code = null, $road_suffix_code = null, $locality_name = null, $postcode = null, $state_territory_code = null, $gnaf = null, $latitude = null, $longitude = null, $full_text = null, $adjust_full_text = null, $requery_full_text = null, $rank_results = null, $fuzzy = null, $limit = 50, string $contentType = self::contentTypes['getSearch'][0])
    {
        $request = $this->getSearchRequest($secondary_complex_name, $unit_type_code, $unit_number, $level_number, $complex_road_number1, $complex_road_number2, $complex_road_name, $complex_road_type_code, $complex_road_suffix_code, $address_site_name, $lot_number, $plan_number, $road_number1, $road_number2, $road_name, $road_type_code, $road_suffix_code, $locality_name, $postcode, $state_territory_code, $gnaf, $latitude, $longitude, $full_text, $adjust_full_text, $requery_full_text, $rank_results, $fuzzy, $limit, $contentType);

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
                        '\CheckNetworks\Model\GetSearch200ResponseInner[]',
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
                case 502:
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
                '\CheckNetworks\Model\GetSearch200ResponseInner[]',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetSearch200ResponseInner[]',
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
                case 502:
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
     * Operation getSearchAsync
     *
     * Request the nbn™ Location ID (LOC #) for an address
     *
     * @param  string|null $secondary_complex_name Name associated with a building or area within a complex site. This data element may represent the name given to an entire building within an address site that has its own separate address. (optional)
     * @param  string|null $unit_type_code Sub dwelling unit type code. Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina. (optional)
     * @param  string|null $unit_number Sub-dwelling unit number. Within a complex, a number used to distinguish an address found within a building / sub-complex or marina. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $level_number Within a complex, the number of the floor or level of a multi-storey building/ sub-complex. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $complex_road_number1 Within a complex, identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. (optional)
     * @param  string|null $complex_road_number2 Within a complex, identifies the last number for a ranged address in the road or thoroughfare. (optional)
     * @param  string|null $complex_road_name Within a complex, the name of the road /thoroughfare of the address. Within a Road name it is possible to find what appears to be a Road type (eg. The Boulevard). It is also possible to have a null Road type. (optional)
     * @param  string|null $complex_road_type_code Within a complex, an abbreviation used to distinguish the type of road / thoroughfare of the address. (optional)
     * @param  string|null $complex_road_suffix_code Within a complex, an abbreviation representing the suffix of the road/ thoroughfare of the address. (optional)
     * @param  string|null $address_site_name Synonyms: Building property name, Primary Complex name  The official place name or culturally accepted common usage name for an address site, including the name of a building, homestead, building complex, agricultural property, park or unbounded address site. This data element represents the name given to an entire building or address site (optional)
     * @param  string|null $lot_number The Lot number is a reference number allocated to a property for subdivision administration purposes prior to road numbering.  Usage: Where an official Road number has not been assigned, this field may be used for postal purposes as well as the physical identification of an address site. (optional)
     * @param  string|null $plan_number The deposited plan number allocated to a property for subdivision administration purposes prior to road numbering in addition to the Lot number. (optional)
     * @param  string|null $road_number1 Identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. (optional)
     * @param  string|null $road_number2 Identifies the last number for a ranged address in the road or thoroughfare. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. This data element is used for ranged street numbers otherwise it is left blank. (optional)
     * @param  string|null $road_name The name of the road/thoroughfare applicable to the address site or complex. Only the road name should be stored in this data element. (optional)
     * @param  string|null $road_type_code The abbreviation code used to distinguish the type of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $road_suffix_code The abbreviation code used to represent the suffix of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $locality_name The name of the locality/suburb of the address. (optional)
     * @param  string|null $postcode The Australian numeric descriptor for a postal delivery area, aligned with locality, suburb or place  *Mandatory if stateTerritoryCode not provided (optional)
     * @param  string|null $state_territory_code The State or Territory code of the address.  *Mandatory if postcode not provided (optional)
     * @param  string|null $gnaf Geocoded National Address File (GNAF) ID (optional)
     * @param  float|null $latitude Geographic latitude coordinate of the address. (optional)
     * @param  float|null $longitude Geographic longitude coordinate of the address. (optional)
     * @param  string|null $full_text Try and parse an entire address string. (optional)
     * @param  bool|null $adjust_full_text [beta] (Optional) Have Check Networks internally adjust the &#x60;.fullText&#x60; value when querying nbn&amp;trade; to obtain better search results. The adjustments will change over time and may include:  - Removing the Country  - Inserting the missing State when PostCode is included  - Reordering State and Postcode. e.g., \&quot;2000 NSW\&quot; &gt;&gt; \&quot;NSW 2000\&quot;  - Changing Road Types to their codes. e.g. \&quot;Street\&quot; &gt;&gt; \&quot;ST\&quot;  - Adding sub-premise prefix. e.g. \&quot;2 12 Some St\&quot; &gt;&gt; \&quot;UNIT 2 12 Some St\&quot;  - Stripping excess spaces. e.g. \&quot;12 - 24 Some St\&quot; &gt;&gt; \&quot;12-24 Some St\&quot; (optional)
     * @param  bool|null $requery_full_text [beta] (Optional) Do an internal requery to nbn&amp;trade; when there are no results to an address search. For a full text query, this will attempt to split the &#x60;fullText&#x60; value into its component parts for use in an Advanced Search. For an advanced query, this will attempt to construct a full text string from the address components for use in a full text search. This will only occur when the initial query to nbn&amp;trade; returns no results. (optional)
     * @param  bool|null $rank_results [beta] (Optional) Attach a rank to each of the query results. This rank will be a number between 0 and 100, indicating how well the result matches your initial search. A higher rank represents a better match. (optional)
     * @param  bool|null $fuzzy Enable Fuzzy searching for more, but less accurate, search results. (optional)
     * @param  int|null $limit Limit the amount of results returned, from 1 to 500. A value outside that range is brought into it. Defaults to 50 when not supplied. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSearch'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getSearchAsync($secondary_complex_name = null, $unit_type_code = null, $unit_number = null, $level_number = null, $complex_road_number1 = null, $complex_road_number2 = null, $complex_road_name = null, $complex_road_type_code = null, $complex_road_suffix_code = null, $address_site_name = null, $lot_number = null, $plan_number = null, $road_number1 = null, $road_number2 = null, $road_name = null, $road_type_code = null, $road_suffix_code = null, $locality_name = null, $postcode = null, $state_territory_code = null, $gnaf = null, $latitude = null, $longitude = null, $full_text = null, $adjust_full_text = null, $requery_full_text = null, $rank_results = null, $fuzzy = null, $limit = 50, string $contentType = self::contentTypes['getSearch'][0])
    {
        return $this->getSearchAsyncWithHttpInfo($secondary_complex_name, $unit_type_code, $unit_number, $level_number, $complex_road_number1, $complex_road_number2, $complex_road_name, $complex_road_type_code, $complex_road_suffix_code, $address_site_name, $lot_number, $plan_number, $road_number1, $road_number2, $road_name, $road_type_code, $road_suffix_code, $locality_name, $postcode, $state_territory_code, $gnaf, $latitude, $longitude, $full_text, $adjust_full_text, $requery_full_text, $rank_results, $fuzzy, $limit, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getSearchAsyncWithHttpInfo
     *
     * Request the nbn™ Location ID (LOC #) for an address
     *
     * @param  string|null $secondary_complex_name Name associated with a building or area within a complex site. This data element may represent the name given to an entire building within an address site that has its own separate address. (optional)
     * @param  string|null $unit_type_code Sub dwelling unit type code. Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina. (optional)
     * @param  string|null $unit_number Sub-dwelling unit number. Within a complex, a number used to distinguish an address found within a building / sub-complex or marina. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $level_number Within a complex, the number of the floor or level of a multi-storey building/ sub-complex. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $complex_road_number1 Within a complex, identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. (optional)
     * @param  string|null $complex_road_number2 Within a complex, identifies the last number for a ranged address in the road or thoroughfare. (optional)
     * @param  string|null $complex_road_name Within a complex, the name of the road /thoroughfare of the address. Within a Road name it is possible to find what appears to be a Road type (eg. The Boulevard). It is also possible to have a null Road type. (optional)
     * @param  string|null $complex_road_type_code Within a complex, an abbreviation used to distinguish the type of road / thoroughfare of the address. (optional)
     * @param  string|null $complex_road_suffix_code Within a complex, an abbreviation representing the suffix of the road/ thoroughfare of the address. (optional)
     * @param  string|null $address_site_name Synonyms: Building property name, Primary Complex name  The official place name or culturally accepted common usage name for an address site, including the name of a building, homestead, building complex, agricultural property, park or unbounded address site. This data element represents the name given to an entire building or address site (optional)
     * @param  string|null $lot_number The Lot number is a reference number allocated to a property for subdivision administration purposes prior to road numbering.  Usage: Where an official Road number has not been assigned, this field may be used for postal purposes as well as the physical identification of an address site. (optional)
     * @param  string|null $plan_number The deposited plan number allocated to a property for subdivision administration purposes prior to road numbering in addition to the Lot number. (optional)
     * @param  string|null $road_number1 Identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. (optional)
     * @param  string|null $road_number2 Identifies the last number for a ranged address in the road or thoroughfare. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. This data element is used for ranged street numbers otherwise it is left blank. (optional)
     * @param  string|null $road_name The name of the road/thoroughfare applicable to the address site or complex. Only the road name should be stored in this data element. (optional)
     * @param  string|null $road_type_code The abbreviation code used to distinguish the type of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $road_suffix_code The abbreviation code used to represent the suffix of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $locality_name The name of the locality/suburb of the address. (optional)
     * @param  string|null $postcode The Australian numeric descriptor for a postal delivery area, aligned with locality, suburb or place  *Mandatory if stateTerritoryCode not provided (optional)
     * @param  string|null $state_territory_code The State or Territory code of the address.  *Mandatory if postcode not provided (optional)
     * @param  string|null $gnaf Geocoded National Address File (GNAF) ID (optional)
     * @param  float|null $latitude Geographic latitude coordinate of the address. (optional)
     * @param  float|null $longitude Geographic longitude coordinate of the address. (optional)
     * @param  string|null $full_text Try and parse an entire address string. (optional)
     * @param  bool|null $adjust_full_text [beta] (Optional) Have Check Networks internally adjust the &#x60;.fullText&#x60; value when querying nbn&amp;trade; to obtain better search results. The adjustments will change over time and may include:  - Removing the Country  - Inserting the missing State when PostCode is included  - Reordering State and Postcode. e.g., \&quot;2000 NSW\&quot; &gt;&gt; \&quot;NSW 2000\&quot;  - Changing Road Types to their codes. e.g. \&quot;Street\&quot; &gt;&gt; \&quot;ST\&quot;  - Adding sub-premise prefix. e.g. \&quot;2 12 Some St\&quot; &gt;&gt; \&quot;UNIT 2 12 Some St\&quot;  - Stripping excess spaces. e.g. \&quot;12 - 24 Some St\&quot; &gt;&gt; \&quot;12-24 Some St\&quot; (optional)
     * @param  bool|null $requery_full_text [beta] (Optional) Do an internal requery to nbn&amp;trade; when there are no results to an address search. For a full text query, this will attempt to split the &#x60;fullText&#x60; value into its component parts for use in an Advanced Search. For an advanced query, this will attempt to construct a full text string from the address components for use in a full text search. This will only occur when the initial query to nbn&amp;trade; returns no results. (optional)
     * @param  bool|null $rank_results [beta] (Optional) Attach a rank to each of the query results. This rank will be a number between 0 and 100, indicating how well the result matches your initial search. A higher rank represents a better match. (optional)
     * @param  bool|null $fuzzy Enable Fuzzy searching for more, but less accurate, search results. (optional)
     * @param  int|null $limit Limit the amount of results returned, from 1 to 500. A value outside that range is brought into it. Defaults to 50 when not supplied. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSearch'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getSearchAsyncWithHttpInfo($secondary_complex_name = null, $unit_type_code = null, $unit_number = null, $level_number = null, $complex_road_number1 = null, $complex_road_number2 = null, $complex_road_name = null, $complex_road_type_code = null, $complex_road_suffix_code = null, $address_site_name = null, $lot_number = null, $plan_number = null, $road_number1 = null, $road_number2 = null, $road_name = null, $road_type_code = null, $road_suffix_code = null, $locality_name = null, $postcode = null, $state_territory_code = null, $gnaf = null, $latitude = null, $longitude = null, $full_text = null, $adjust_full_text = null, $requery_full_text = null, $rank_results = null, $fuzzy = null, $limit = 50, string $contentType = self::contentTypes['getSearch'][0])
    {
        $returnType = '\CheckNetworks\Model\GetSearch200ResponseInner[]';
        $request = $this->getSearchRequest($secondary_complex_name, $unit_type_code, $unit_number, $level_number, $complex_road_number1, $complex_road_number2, $complex_road_name, $complex_road_type_code, $complex_road_suffix_code, $address_site_name, $lot_number, $plan_number, $road_number1, $road_number2, $road_name, $road_type_code, $road_suffix_code, $locality_name, $postcode, $state_territory_code, $gnaf, $latitude, $longitude, $full_text, $adjust_full_text, $requery_full_text, $rank_results, $fuzzy, $limit, $contentType);

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
     * Create request for operation 'getSearch'
     *
     * @param  string|null $secondary_complex_name Name associated with a building or area within a complex site. This data element may represent the name given to an entire building within an address site that has its own separate address. (optional)
     * @param  string|null $unit_type_code Sub dwelling unit type code. Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina. (optional)
     * @param  string|null $unit_number Sub-dwelling unit number. Within a complex, a number used to distinguish an address found within a building / sub-complex or marina. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $level_number Within a complex, the number of the floor or level of a multi-storey building/ sub-complex. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix. (optional)
     * @param  string|null $complex_road_number1 Within a complex, identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. (optional)
     * @param  string|null $complex_road_number2 Within a complex, identifies the last number for a ranged address in the road or thoroughfare. (optional)
     * @param  string|null $complex_road_name Within a complex, the name of the road /thoroughfare of the address. Within a Road name it is possible to find what appears to be a Road type (eg. The Boulevard). It is also possible to have a null Road type. (optional)
     * @param  string|null $complex_road_type_code Within a complex, an abbreviation used to distinguish the type of road / thoroughfare of the address. (optional)
     * @param  string|null $complex_road_suffix_code Within a complex, an abbreviation representing the suffix of the road/ thoroughfare of the address. (optional)
     * @param  string|null $address_site_name Synonyms: Building property name, Primary Complex name  The official place name or culturally accepted common usage name for an address site, including the name of a building, homestead, building complex, agricultural property, park or unbounded address site. This data element represents the name given to an entire building or address site (optional)
     * @param  string|null $lot_number The Lot number is a reference number allocated to a property for subdivision administration purposes prior to road numbering.  Usage: Where an official Road number has not been assigned, this field may be used for postal purposes as well as the physical identification of an address site. (optional)
     * @param  string|null $plan_number The deposited plan number allocated to a property for subdivision administration purposes prior to road numbering in addition to the Lot number. (optional)
     * @param  string|null $road_number1 Identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. (optional)
     * @param  string|null $road_number2 Identifies the last number for a ranged address in the road or thoroughfare. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. This data element is used for ranged street numbers otherwise it is left blank. (optional)
     * @param  string|null $road_name The name of the road/thoroughfare applicable to the address site or complex. Only the road name should be stored in this data element. (optional)
     * @param  string|null $road_type_code The abbreviation code used to distinguish the type of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $road_suffix_code The abbreviation code used to represent the suffix of road/thoroughfare applicable to the address site/complex. (optional)
     * @param  string|null $locality_name The name of the locality/suburb of the address. (optional)
     * @param  string|null $postcode The Australian numeric descriptor for a postal delivery area, aligned with locality, suburb or place  *Mandatory if stateTerritoryCode not provided (optional)
     * @param  string|null $state_territory_code The State or Territory code of the address.  *Mandatory if postcode not provided (optional)
     * @param  string|null $gnaf Geocoded National Address File (GNAF) ID (optional)
     * @param  float|null $latitude Geographic latitude coordinate of the address. (optional)
     * @param  float|null $longitude Geographic longitude coordinate of the address. (optional)
     * @param  string|null $full_text Try and parse an entire address string. (optional)
     * @param  bool|null $adjust_full_text [beta] (Optional) Have Check Networks internally adjust the &#x60;.fullText&#x60; value when querying nbn&amp;trade; to obtain better search results. The adjustments will change over time and may include:  - Removing the Country  - Inserting the missing State when PostCode is included  - Reordering State and Postcode. e.g., \&quot;2000 NSW\&quot; &gt;&gt; \&quot;NSW 2000\&quot;  - Changing Road Types to their codes. e.g. \&quot;Street\&quot; &gt;&gt; \&quot;ST\&quot;  - Adding sub-premise prefix. e.g. \&quot;2 12 Some St\&quot; &gt;&gt; \&quot;UNIT 2 12 Some St\&quot;  - Stripping excess spaces. e.g. \&quot;12 - 24 Some St\&quot; &gt;&gt; \&quot;12-24 Some St\&quot; (optional)
     * @param  bool|null $requery_full_text [beta] (Optional) Do an internal requery to nbn&amp;trade; when there are no results to an address search. For a full text query, this will attempt to split the &#x60;fullText&#x60; value into its component parts for use in an Advanced Search. For an advanced query, this will attempt to construct a full text string from the address components for use in a full text search. This will only occur when the initial query to nbn&amp;trade; returns no results. (optional)
     * @param  bool|null $rank_results [beta] (Optional) Attach a rank to each of the query results. This rank will be a number between 0 and 100, indicating how well the result matches your initial search. A higher rank represents a better match. (optional)
     * @param  bool|null $fuzzy Enable Fuzzy searching for more, but less accurate, search results. (optional)
     * @param  int|null $limit Limit the amount of results returned, from 1 to 500. A value outside that range is brought into it. Defaults to 50 when not supplied. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSearch'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getSearchRequest($secondary_complex_name = null, $unit_type_code = null, $unit_number = null, $level_number = null, $complex_road_number1 = null, $complex_road_number2 = null, $complex_road_name = null, $complex_road_type_code = null, $complex_road_suffix_code = null, $address_site_name = null, $lot_number = null, $plan_number = null, $road_number1 = null, $road_number2 = null, $road_name = null, $road_type_code = null, $road_suffix_code = null, $locality_name = null, $postcode = null, $state_territory_code = null, $gnaf = null, $latitude = null, $longitude = null, $full_text = null, $adjust_full_text = null, $requery_full_text = null, $rank_results = null, $fuzzy = null, $limit = 50, string $contentType = self::contentTypes['getSearch'][0])
    {





























        if ($limit !== null && $limit > 500) {
            throw new \InvalidArgumentException('invalid value for "$limit" when calling AddressApi.getSearch, must be smaller than or equal to 500.');
        }
        if ($limit !== null && $limit < 1) {
            throw new \InvalidArgumentException('invalid value for "$limit" when calling AddressApi.getSearch, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/nbn/address/search';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $secondary_complex_name,
            'secondaryComplexName', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $unit_type_code,
            'unitTypeCode', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $unit_number,
            'unitNumber', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $level_number,
            'levelNumber', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $complex_road_number1,
            'complexRoadNumber1', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $complex_road_number2,
            'complexRoadNumber2', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $complex_road_name,
            'complexRoadName', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $complex_road_type_code,
            'complexRoadTypeCode', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $complex_road_suffix_code,
            'complexRoadSuffixCode', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $address_site_name,
            'addressSiteName', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $lot_number,
            'lotNumber', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $plan_number,
            'planNumber', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $road_number1,
            'roadNumber1', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $road_number2,
            'roadNumber2', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $road_name,
            'roadName', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $road_type_code,
            'roadTypeCode', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $road_suffix_code,
            'roadSuffixCode', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $locality_name,
            'localityName', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $postcode,
            'postcode', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $state_territory_code,
            'stateTerritoryCode', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $gnaf,
            'gnaf', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $latitude,
            'latitude', // param base name
            'number', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $longitude,
            'longitude', // param base name
            'number', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $full_text,
            'fullText', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $adjust_full_text,
            'adjustFullText', // param base name
            'boolean', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $requery_full_text,
            'requeryFullText', // param base name
            'boolean', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $rank_results,
            'rankResults', // param base name
            'boolean', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $fuzzy,
            'fuzzy', // param base name
            'boolean', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $limit,
            'limit', // param base name
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
     * Operation getServices
     *
     * List the known services that you have active at this location
     *
     * @param  string $loc_id loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getServices'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetServices200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getServices($loc_id, string $contentType = self::contentTypes['getServices'][0])
    {
        list($response) = $this->getServicesWithHttpInfo($loc_id, $contentType);
        return $response;
    }

    /**
     * Operation getServicesWithHttpInfo
     *
     * List the known services that you have active at this location
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getServices'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetServices200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getServicesWithHttpInfo($loc_id, string $contentType = self::contentTypes['getServices'][0])
    {
        $request = $this->getServicesRequest($loc_id, $contentType);

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
                        '\CheckNetworks\Model\GetServices200Response',
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
                '\CheckNetworks\Model\GetServices200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetServices200Response',
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
     * Operation getServicesAsync
     *
     * List the known services that you have active at this location
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getServices'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getServicesAsync($loc_id, string $contentType = self::contentTypes['getServices'][0])
    {
        return $this->getServicesAsyncWithHttpInfo($loc_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getServicesAsyncWithHttpInfo
     *
     * List the known services that you have active at this location
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getServices'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getServicesAsyncWithHttpInfo($loc_id, string $contentType = self::contentTypes['getServices'][0])
    {
        $returnType = '\CheckNetworks\Model\GetServices200Response';
        $request = $this->getServicesRequest($loc_id, $contentType);

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
     * Create request for operation 'getServices'
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getServices'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getServicesRequest($loc_id, string $contentType = self::contentTypes['getServices'][0])
    {

        // verify the required parameter 'loc_id' is set
        if ($loc_id === null || (is_array($loc_id) && count($loc_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $loc_id when calling getServices'
            );
        }


        $resourcePath = '/nbn/address/{locId}/services';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($loc_id !== null) {
            $resourcePath = str_replace(
                '{locId}',
                ObjectSerializer::toPathValue($loc_id),
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
     * Operation getSingleAddress
     *
     * Get the address details for a nbn™ Location ID (LOC #)
     *
     * @param  string $loc_id loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSingleAddress'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GeographicAddress|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getSingleAddress($loc_id, string $contentType = self::contentTypes['getSingleAddress'][0])
    {
        list($response) = $this->getSingleAddressWithHttpInfo($loc_id, $contentType);
        return $response;
    }

    /**
     * Operation getSingleAddressWithHttpInfo
     *
     * Get the address details for a nbn™ Location ID (LOC #)
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSingleAddress'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GeographicAddress|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getSingleAddressWithHttpInfo($loc_id, string $contentType = self::contentTypes['getSingleAddress'][0])
    {
        $request = $this->getSingleAddressRequest($loc_id, $contentType);

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
                        '\CheckNetworks\Model\GeographicAddress',
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
                case 502:
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
                '\CheckNetworks\Model\GeographicAddress',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GeographicAddress',
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
                case 502:
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
     * Operation getSingleAddressAsync
     *
     * Get the address details for a nbn™ Location ID (LOC #)
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSingleAddress'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getSingleAddressAsync($loc_id, string $contentType = self::contentTypes['getSingleAddress'][0])
    {
        return $this->getSingleAddressAsyncWithHttpInfo($loc_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getSingleAddressAsyncWithHttpInfo
     *
     * Get the address details for a nbn™ Location ID (LOC #)
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSingleAddress'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getSingleAddressAsyncWithHttpInfo($loc_id, string $contentType = self::contentTypes['getSingleAddress'][0])
    {
        $returnType = '\CheckNetworks\Model\GeographicAddress';
        $request = $this->getSingleAddressRequest($loc_id, $contentType);

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
     * Create request for operation 'getSingleAddress'
     *
     * @param  string $loc_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getSingleAddress'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getSingleAddressRequest($loc_id, string $contentType = self::contentTypes['getSingleAddress'][0])
    {

        // verify the required parameter 'loc_id' is set
        if ($loc_id === null || (is_array($loc_id) && count($loc_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $loc_id when calling getSingleAddress'
            );
        }


        $resourcePath = '/nbn/address/{locId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($loc_id !== null) {
            $resourcePath = str_replace(
                '{locId}',
                ObjectSerializer::toPathValue($loc_id),
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
