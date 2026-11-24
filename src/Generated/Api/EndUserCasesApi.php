<?php
/**
 * EndUserCasesApi
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
 * EndUserCasesApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class EndUserCasesApi
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
        'assessEndUserHardship' => [
            'application/json',
        ],
        'closeEndUserDispute' => [
            'application/json',
        ],
        'closeEndUserHardship' => [
            'application/json',
        ],
        'creditEndUserDispute' => [
            'application/json',
        ],
        'getEndUserDispute' => [
            'application/json',
        ],
        'getRetailHardshipOptions' => [
            'application/json',
        ],
        'listEndUserDisputes' => [
            'application/json',
        ],
        'listEndUserHardship' => [
            'application/json',
        ],
        'listEndUserHardshipCases' => [
            'application/json',
        ],
        'openEndUserHardship' => [
            'application/json',
        ],
        'retryEndUserDispute' => [
            'application/json',
        ],
        'reviewEndUserDispute' => [
            'application/json',
        ],
        'setRetailHardshipOptions' => [
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
     * Operation assessEndUserHardship
     *
     * Assess a Hardship Application
     *
     * @param  string $end_user end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\AssessEndUserHardshipRequest $assess_end_user_hardship_request assess_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['assessEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function assessEndUserHardship($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['assessEndUserHardship'][0])
    {
        list($response) = $this->assessEndUserHardshipWithHttpInfo($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation assessEndUserHardshipWithHttpInfo
     *
     * Assess a Hardship Application
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\AssessEndUserHardshipRequest $assess_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['assessEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function assessEndUserHardshipWithHttpInfo($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['assessEndUserHardship'][0])
    {
        $request = $this->assessEndUserHardshipRequest($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
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
                '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
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
     * Operation assessEndUserHardshipAsync
     *
     * Assess a Hardship Application
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\AssessEndUserHardshipRequest $assess_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['assessEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function assessEndUserHardshipAsync($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['assessEndUserHardship'][0])
    {
        return $this->assessEndUserHardshipAsyncWithHttpInfo($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation assessEndUserHardshipAsyncWithHttpInfo
     *
     * Assess a Hardship Application
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\AssessEndUserHardshipRequest $assess_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['assessEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function assessEndUserHardshipAsyncWithHttpInfo($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['assessEndUserHardship'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response';
        $request = $this->assessEndUserHardshipRequest($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key, $contentType);

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
     * Create request for operation 'assessEndUserHardship'
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\AssessEndUserHardshipRequest $assess_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['assessEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function assessEndUserHardshipRequest($end_user, $case_id, $assess_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['assessEndUserHardship'][0])
    {

        // verify the required parameter 'end_user' is set
        if ($end_user === null || (is_array($end_user) && count($end_user) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $end_user when calling assessEndUserHardship'
            );
        }

        // verify the required parameter 'case_id' is set
        if ($case_id === null || (is_array($case_id) && count($case_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $case_id when calling assessEndUserHardship'
            );
        }

        // verify the required parameter 'assess_end_user_hardship_request' is set
        if ($assess_end_user_hardship_request === null || (is_array($assess_end_user_hardship_request) && count($assess_end_user_hardship_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $assess_end_user_hardship_request when calling assessEndUserHardship'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.assessEndUserHardship, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/end-users/{endUser}/hardship/{caseId}/assess';
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
        if ($end_user !== null) {
            $resourcePath = str_replace(
                '{endUser}',
                ObjectSerializer::toPathValue($end_user),
                $resourcePath
            );
        }
        // path params
        if ($case_id !== null) {
            $resourcePath = str_replace(
                '{caseId}',
                ObjectSerializer::toPathValue($case_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($assess_end_user_hardship_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($assess_end_user_hardship_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $assess_end_user_hardship_request;
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
     * Operation closeEndUserDispute
     *
     * Close an End-User Dispute
     *
     * @param  string $dispute_id dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserDisputeRequest $close_end_user_dispute_request close_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function closeEndUserDispute($dispute_id, $close_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserDispute'][0])
    {
        list($response) = $this->closeEndUserDisputeWithHttpInfo($dispute_id, $close_end_user_dispute_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation closeEndUserDisputeWithHttpInfo
     *
     * Close an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserDisputeRequest $close_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function closeEndUserDisputeWithHttpInfo($dispute_id, $close_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserDispute'][0])
    {
        $request = $this->closeEndUserDisputeRequest($dispute_id, $close_end_user_dispute_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
     * Operation closeEndUserDisputeAsync
     *
     * Close an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserDisputeRequest $close_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function closeEndUserDisputeAsync($dispute_id, $close_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserDispute'][0])
    {
        return $this->closeEndUserDisputeAsyncWithHttpInfo($dispute_id, $close_end_user_dispute_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation closeEndUserDisputeAsyncWithHttpInfo
     *
     * Close an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserDisputeRequest $close_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function closeEndUserDisputeAsyncWithHttpInfo($dispute_id, $close_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserDispute'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response';
        $request = $this->closeEndUserDisputeRequest($dispute_id, $close_end_user_dispute_request, $idempotency_key, $contentType);

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
     * Create request for operation 'closeEndUserDispute'
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserDisputeRequest $close_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function closeEndUserDisputeRequest($dispute_id, $close_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserDispute'][0])
    {

        // verify the required parameter 'dispute_id' is set
        if ($dispute_id === null || (is_array($dispute_id) && count($dispute_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $dispute_id when calling closeEndUserDispute'
            );
        }

        // verify the required parameter 'close_end_user_dispute_request' is set
        if ($close_end_user_dispute_request === null || (is_array($close_end_user_dispute_request) && count($close_end_user_dispute_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $close_end_user_dispute_request when calling closeEndUserDispute'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.closeEndUserDispute, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/end-users/disputes/{disputeId}/close';
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
        if ($dispute_id !== null) {
            $resourcePath = str_replace(
                '{disputeId}',
                ObjectSerializer::toPathValue($dispute_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($close_end_user_dispute_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($close_end_user_dispute_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $close_end_user_dispute_request;
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
     * Operation closeEndUserHardship
     *
     * Close a Hardship Case
     *
     * @param  string $end_user end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserHardshipRequest $close_end_user_hardship_request close_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function closeEndUserHardship($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserHardship'][0])
    {
        list($response) = $this->closeEndUserHardshipWithHttpInfo($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation closeEndUserHardshipWithHttpInfo
     *
     * Close a Hardship Case
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserHardshipRequest $close_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function closeEndUserHardshipWithHttpInfo($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserHardship'][0])
    {
        $request = $this->closeEndUserHardshipRequest($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
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
                '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
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
     * Operation closeEndUserHardshipAsync
     *
     * Close a Hardship Case
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserHardshipRequest $close_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function closeEndUserHardshipAsync($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserHardship'][0])
    {
        return $this->closeEndUserHardshipAsyncWithHttpInfo($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation closeEndUserHardshipAsyncWithHttpInfo
     *
     * Close a Hardship Case
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserHardshipRequest $close_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function closeEndUserHardshipAsyncWithHttpInfo($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserHardship'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response';
        $request = $this->closeEndUserHardshipRequest($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key, $contentType);

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
     * Create request for operation 'closeEndUserHardship'
     *
     * @param  string $end_user (required)
     * @param  string $case_id The case&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  \CheckNetworks\Api\Generated\Model\CloseEndUserHardshipRequest $close_end_user_hardship_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['closeEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function closeEndUserHardshipRequest($end_user, $case_id, $close_end_user_hardship_request, $idempotency_key = null, string $contentType = self::contentTypes['closeEndUserHardship'][0])
    {

        // verify the required parameter 'end_user' is set
        if ($end_user === null || (is_array($end_user) && count($end_user) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $end_user when calling closeEndUserHardship'
            );
        }

        // verify the required parameter 'case_id' is set
        if ($case_id === null || (is_array($case_id) && count($case_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $case_id when calling closeEndUserHardship'
            );
        }

        // verify the required parameter 'close_end_user_hardship_request' is set
        if ($close_end_user_hardship_request === null || (is_array($close_end_user_hardship_request) && count($close_end_user_hardship_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $close_end_user_hardship_request when calling closeEndUserHardship'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.closeEndUserHardship, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/end-users/{endUser}/hardship/{caseId}/close';
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
        if ($end_user !== null) {
            $resourcePath = str_replace(
                '{endUser}',
                ObjectSerializer::toPathValue($end_user),
                $resourcePath
            );
        }
        // path params
        if ($case_id !== null) {
            $resourcePath = str_replace(
                '{caseId}',
                ObjectSerializer::toPathValue($case_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($close_end_user_hardship_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($close_end_user_hardship_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $close_end_user_hardship_request;
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
     * Operation creditEndUserDispute
     *
     * Credit and Resolve an End-User Dispute
     *
     * @param  string $dispute_id dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CreditEndUserDisputeRequest $credit_end_user_dispute_request credit_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['creditEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function creditEndUserDispute($dispute_id, $credit_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['creditEndUserDispute'][0])
    {
        list($response) = $this->creditEndUserDisputeWithHttpInfo($dispute_id, $credit_end_user_dispute_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation creditEndUserDisputeWithHttpInfo
     *
     * Credit and Resolve an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CreditEndUserDisputeRequest $credit_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['creditEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function creditEndUserDisputeWithHttpInfo($dispute_id, $credit_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['creditEndUserDispute'][0])
    {
        $request = $this->creditEndUserDisputeRequest($dispute_id, $credit_end_user_dispute_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
     * Operation creditEndUserDisputeAsync
     *
     * Credit and Resolve an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CreditEndUserDisputeRequest $credit_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['creditEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function creditEndUserDisputeAsync($dispute_id, $credit_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['creditEndUserDispute'][0])
    {
        return $this->creditEndUserDisputeAsyncWithHttpInfo($dispute_id, $credit_end_user_dispute_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation creditEndUserDisputeAsyncWithHttpInfo
     *
     * Credit and Resolve an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CreditEndUserDisputeRequest $credit_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['creditEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function creditEndUserDisputeAsyncWithHttpInfo($dispute_id, $credit_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['creditEndUserDispute'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response';
        $request = $this->creditEndUserDisputeRequest($dispute_id, $credit_end_user_dispute_request, $idempotency_key, $contentType);

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
     * Create request for operation 'creditEndUserDispute'
     *
     * @param  string $dispute_id (required)
     * @param  \CheckNetworks\Api\Generated\Model\CreditEndUserDisputeRequest $credit_end_user_dispute_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['creditEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function creditEndUserDisputeRequest($dispute_id, $credit_end_user_dispute_request, $idempotency_key = null, string $contentType = self::contentTypes['creditEndUserDispute'][0])
    {

        // verify the required parameter 'dispute_id' is set
        if ($dispute_id === null || (is_array($dispute_id) && count($dispute_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $dispute_id when calling creditEndUserDispute'
            );
        }

        // verify the required parameter 'credit_end_user_dispute_request' is set
        if ($credit_end_user_dispute_request === null || (is_array($credit_end_user_dispute_request) && count($credit_end_user_dispute_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $credit_end_user_dispute_request when calling creditEndUserDispute'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.creditEndUserDispute, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/end-users/disputes/{disputeId}/credit';
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
        if ($dispute_id !== null) {
            $resourcePath = str_replace(
                '{disputeId}',
                ObjectSerializer::toPathValue($dispute_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($credit_end_user_dispute_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($credit_end_user_dispute_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $credit_end_user_dispute_request;
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
     * Operation getEndUserDispute
     *
     * Get End-User Dispute
     *
     * @param  string $dispute_id dispute_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getEndUserDispute($dispute_id, string $contentType = self::contentTypes['getEndUserDispute'][0])
    {
        list($response) = $this->getEndUserDisputeWithHttpInfo($dispute_id, $contentType);
        return $response;
    }

    /**
     * Operation getEndUserDisputeWithHttpInfo
     *
     * Get End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getEndUserDisputeWithHttpInfo($dispute_id, string $contentType = self::contentTypes['getEndUserDispute'][0])
    {
        $request = $this->getEndUserDisputeRequest($dispute_id, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
     * Operation getEndUserDisputeAsync
     *
     * Get End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getEndUserDisputeAsync($dispute_id, string $contentType = self::contentTypes['getEndUserDispute'][0])
    {
        return $this->getEndUserDisputeAsyncWithHttpInfo($dispute_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getEndUserDisputeAsyncWithHttpInfo
     *
     * Get End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getEndUserDisputeAsyncWithHttpInfo($dispute_id, string $contentType = self::contentTypes['getEndUserDispute'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response';
        $request = $this->getEndUserDisputeRequest($dispute_id, $contentType);

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
     * Create request for operation 'getEndUserDispute'
     *
     * @param  string $dispute_id (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getEndUserDisputeRequest($dispute_id, string $contentType = self::contentTypes['getEndUserDispute'][0])
    {

        // verify the required parameter 'dispute_id' is set
        if ($dispute_id === null || (is_array($dispute_id) && count($dispute_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $dispute_id when calling getEndUserDispute'
            );
        }


        $resourcePath = '/end-users/disputes/{disputeId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($dispute_id !== null) {
            $resourcePath = str_replace(
                '{disputeId}',
                ObjectSerializer::toPathValue($dispute_id),
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
     * Operation getRetailHardshipOptions
     *
     * Get Hardship Options
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getRetailHardshipOptions(string $contentType = self::contentTypes['getRetailHardshipOptions'][0])
    {
        list($response) = $this->getRetailHardshipOptionsWithHttpInfo($contentType);
        return $response;
    }

    /**
     * Operation getRetailHardshipOptionsWithHttpInfo
     *
     * Get Hardship Options
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getRetailHardshipOptionsWithHttpInfo(string $contentType = self::contentTypes['getRetailHardshipOptions'][0])
    {
        $request = $this->getRetailHardshipOptionsRequest($contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response',
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
     * Operation getRetailHardshipOptionsAsync
     *
     * Get Hardship Options
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getRetailHardshipOptionsAsync(string $contentType = self::contentTypes['getRetailHardshipOptions'][0])
    {
        return $this->getRetailHardshipOptionsAsyncWithHttpInfo($contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getRetailHardshipOptionsAsyncWithHttpInfo
     *
     * Get Hardship Options
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getRetailHardshipOptionsAsyncWithHttpInfo(string $contentType = self::contentTypes['getRetailHardshipOptions'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response';
        $request = $this->getRetailHardshipOptionsRequest($contentType);

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
     * Create request for operation 'getRetailHardshipOptions'
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getRetailHardshipOptionsRequest(string $contentType = self::contentTypes['getRetailHardshipOptions'][0])
    {


        $resourcePath = '/retail/hardship-options';
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
     * Operation listEndUserDisputes
     *
     * List End-User Disputes
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status status (optional)
     * @param  int|null $page page (optional)
     * @param  int|null $per_page per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserDisputes'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListEndUserDisputes200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function listEndUserDisputes($end_user = null, $status = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserDisputes'][0])
    {
        list($response) = $this->listEndUserDisputesWithHttpInfo($end_user, $status, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation listEndUserDisputesWithHttpInfo
     *
     * List End-User Disputes
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserDisputes'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListEndUserDisputes200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listEndUserDisputesWithHttpInfo($end_user = null, $status = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserDisputes'][0])
    {
        $request = $this->listEndUserDisputesRequest($end_user, $status, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListEndUserDisputes200Response',
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
                '\CheckNetworks\Api\Generated\Model\ListEndUserDisputes200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListEndUserDisputes200Response',
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
     * Operation listEndUserDisputesAsync
     *
     * List End-User Disputes
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserDisputes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listEndUserDisputesAsync($end_user = null, $status = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserDisputes'][0])
    {
        return $this->listEndUserDisputesAsyncWithHttpInfo($end_user, $status, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listEndUserDisputesAsyncWithHttpInfo
     *
     * List End-User Disputes
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserDisputes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listEndUserDisputesAsyncWithHttpInfo($end_user = null, $status = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserDisputes'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListEndUserDisputes200Response';
        $request = $this->listEndUserDisputesRequest($end_user, $status, $page, $per_page, $contentType);

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
     * Create request for operation 'listEndUserDisputes'
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserDisputes'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listEndUserDisputesRequest($end_user = null, $status = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserDisputes'][0])
    {



        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling EndUserCasesApi.listEndUserDisputes, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling EndUserCasesApi.listEndUserDisputes, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling EndUserCasesApi.listEndUserDisputes, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/end-users/disputes';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

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
     * Operation listEndUserHardship
     *
     * List an End User&#39;s Hardship Cases
     *
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListEndUserHardship200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function listEndUserHardship($end_user, string $contentType = self::contentTypes['listEndUserHardship'][0])
    {
        list($response) = $this->listEndUserHardshipWithHttpInfo($end_user, $contentType);
        return $response;
    }

    /**
     * Operation listEndUserHardshipWithHttpInfo
     *
     * List an End User&#39;s Hardship Cases
     *
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListEndUserHardship200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listEndUserHardshipWithHttpInfo($end_user, string $contentType = self::contentTypes['listEndUserHardship'][0])
    {
        $request = $this->listEndUserHardshipRequest($end_user, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListEndUserHardship200Response',
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
                '\CheckNetworks\Api\Generated\Model\ListEndUserHardship200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListEndUserHardship200Response',
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
     * Operation listEndUserHardshipAsync
     *
     * List an End User&#39;s Hardship Cases
     *
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listEndUserHardshipAsync($end_user, string $contentType = self::contentTypes['listEndUserHardship'][0])
    {
        return $this->listEndUserHardshipAsyncWithHttpInfo($end_user, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listEndUserHardshipAsyncWithHttpInfo
     *
     * List an End User&#39;s Hardship Cases
     *
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listEndUserHardshipAsyncWithHttpInfo($end_user, string $contentType = self::contentTypes['listEndUserHardship'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListEndUserHardship200Response';
        $request = $this->listEndUserHardshipRequest($end_user, $contentType);

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
     * Create request for operation 'listEndUserHardship'
     *
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listEndUserHardshipRequest($end_user, string $contentType = self::contentTypes['listEndUserHardship'][0])
    {

        // verify the required parameter 'end_user' is set
        if ($end_user === null || (is_array($end_user) && count($end_user) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $end_user when calling listEndUserHardship'
            );
        }


        $resourcePath = '/end-users/{endUser}/hardship';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($end_user !== null) {
            $resourcePath = str_replace(
                '{endUser}',
                ObjectSerializer::toPathValue($end_user),
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
     * Operation listEndUserHardshipCases
     *
     * List Hardship Cases
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status status (optional)
     * @param  \DateTime|null $assessment_due_before assessment_due_before (optional)
     * @param  int|null $page page (optional)
     * @param  int|null $per_page per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardshipCases'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListEndUserHardshipCases200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function listEndUserHardshipCases($end_user = null, $status = null, $assessment_due_before = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserHardshipCases'][0])
    {
        list($response) = $this->listEndUserHardshipCasesWithHttpInfo($end_user, $status, $assessment_due_before, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation listEndUserHardshipCasesWithHttpInfo
     *
     * List Hardship Cases
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  \DateTime|null $assessment_due_before (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardshipCases'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListEndUserHardshipCases200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listEndUserHardshipCasesWithHttpInfo($end_user = null, $status = null, $assessment_due_before = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserHardshipCases'][0])
    {
        $request = $this->listEndUserHardshipCasesRequest($end_user, $status, $assessment_due_before, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListEndUserHardshipCases200Response',
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
                '\CheckNetworks\Api\Generated\Model\ListEndUserHardshipCases200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListEndUserHardshipCases200Response',
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
     * Operation listEndUserHardshipCasesAsync
     *
     * List Hardship Cases
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  \DateTime|null $assessment_due_before (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardshipCases'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listEndUserHardshipCasesAsync($end_user = null, $status = null, $assessment_due_before = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserHardshipCases'][0])
    {
        return $this->listEndUserHardshipCasesAsyncWithHttpInfo($end_user, $status, $assessment_due_before, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listEndUserHardshipCasesAsyncWithHttpInfo
     *
     * List Hardship Cases
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  \DateTime|null $assessment_due_before (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardshipCases'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listEndUserHardshipCasesAsyncWithHttpInfo($end_user = null, $status = null, $assessment_due_before = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserHardshipCases'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListEndUserHardshipCases200Response';
        $request = $this->listEndUserHardshipCasesRequest($end_user, $status, $assessment_due_before, $page, $per_page, $contentType);

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
     * Create request for operation 'listEndUserHardshipCases'
     *
     * @param  string|null $end_user Only this end user&#39;s, by their number. An unknown number is a 422. (optional)
     * @param  string|null $status (optional)
     * @param  \DateTime|null $assessment_due_before (optional)
     * @param  int|null $page (optional)
     * @param  int|null $per_page (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listEndUserHardshipCases'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listEndUserHardshipCasesRequest($end_user = null, $status = null, $assessment_due_before = null, $page = null, $per_page = null, string $contentType = self::contentTypes['listEndUserHardshipCases'][0])
    {




        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling EndUserCasesApi.listEndUserHardshipCases, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling EndUserCasesApi.listEndUserHardshipCases, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling EndUserCasesApi.listEndUserHardshipCases, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/end-users/hardship';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

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
            $status,
            'status', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $assessment_due_before,
            'assessmentDueBefore', // param base name
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
     * Operation openEndUserHardship
     *
     * Open a Hardship Case
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  \CheckNetworks\Api\Generated\Model\OpenEndUserHardshipRequest $open_end_user_hardship_request open_end_user_hardship_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['openEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function openEndUserHardship($idempotency_key, $end_user, $open_end_user_hardship_request, string $contentType = self::contentTypes['openEndUserHardship'][0])
    {
        list($response) = $this->openEndUserHardshipWithHttpInfo($idempotency_key, $end_user, $open_end_user_hardship_request, $contentType);
        return $response;
    }

    /**
     * Operation openEndUserHardshipWithHttpInfo
     *
     * Open a Hardship Case
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  \CheckNetworks\Api\Generated\Model\OpenEndUserHardshipRequest $open_end_user_hardship_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['openEndUserHardship'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function openEndUserHardshipWithHttpInfo($idempotency_key, $end_user, $open_end_user_hardship_request, string $contentType = self::contentTypes['openEndUserHardship'][0])
    {
        $request = $this->openEndUserHardshipRequest($idempotency_key, $end_user, $open_end_user_hardship_request, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
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
                case 404:
                    return $this->handleResponseWithDataType(
                        '\CheckNetworks\Api\Generated\Model\Error',
                        $request,
                        $response,
                    );
                case 409:
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
                '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response',
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
     * Operation openEndUserHardshipAsync
     *
     * Open a Hardship Case
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  \CheckNetworks\Api\Generated\Model\OpenEndUserHardshipRequest $open_end_user_hardship_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['openEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function openEndUserHardshipAsync($idempotency_key, $end_user, $open_end_user_hardship_request, string $contentType = self::contentTypes['openEndUserHardship'][0])
    {
        return $this->openEndUserHardshipAsyncWithHttpInfo($idempotency_key, $end_user, $open_end_user_hardship_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation openEndUserHardshipAsyncWithHttpInfo
     *
     * Open a Hardship Case
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  \CheckNetworks\Api\Generated\Model\OpenEndUserHardshipRequest $open_end_user_hardship_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['openEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function openEndUserHardshipAsyncWithHttpInfo($idempotency_key, $end_user, $open_end_user_hardship_request, string $contentType = self::contentTypes['openEndUserHardship'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\OpenEndUserHardship201Response';
        $request = $this->openEndUserHardshipRequest($idempotency_key, $end_user, $open_end_user_hardship_request, $contentType);

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
     * Create request for operation 'openEndUserHardship'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  string $end_user The end user&#39;s number. (required)
     * @param  \CheckNetworks\Api\Generated\Model\OpenEndUserHardshipRequest $open_end_user_hardship_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['openEndUserHardship'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function openEndUserHardshipRequest($idempotency_key, $end_user, $open_end_user_hardship_request, string $contentType = self::contentTypes['openEndUserHardship'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling openEndUserHardship'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.openEndUserHardship, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'end_user' is set
        if ($end_user === null || (is_array($end_user) && count($end_user) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $end_user when calling openEndUserHardship'
            );
        }

        // verify the required parameter 'open_end_user_hardship_request' is set
        if ($open_end_user_hardship_request === null || (is_array($open_end_user_hardship_request) && count($open_end_user_hardship_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $open_end_user_hardship_request when calling openEndUserHardship'
            );
        }


        $resourcePath = '/end-users/{endUser}/hardship';
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
        if ($end_user !== null) {
            $resourcePath = str_replace(
                '{endUser}',
                ObjectSerializer::toPathValue($end_user),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($open_end_user_hardship_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($open_end_user_hardship_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $open_end_user_hardship_request;
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
     * Operation retryEndUserDispute
     *
     * Retry a Failed Dispute Decision
     *
     * @param  string $dispute_id dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['retryEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function retryEndUserDispute($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['retryEndUserDispute'][0])
    {
        list($response) = $this->retryEndUserDisputeWithHttpInfo($dispute_id, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation retryEndUserDisputeWithHttpInfo
     *
     * Retry a Failed Dispute Decision
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['retryEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function retryEndUserDisputeWithHttpInfo($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['retryEndUserDispute'][0])
    {
        $request = $this->retryEndUserDisputeRequest($dispute_id, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
     * Operation retryEndUserDisputeAsync
     *
     * Retry a Failed Dispute Decision
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['retryEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function retryEndUserDisputeAsync($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['retryEndUserDispute'][0])
    {
        return $this->retryEndUserDisputeAsyncWithHttpInfo($dispute_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation retryEndUserDisputeAsyncWithHttpInfo
     *
     * Retry a Failed Dispute Decision
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['retryEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function retryEndUserDisputeAsyncWithHttpInfo($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['retryEndUserDispute'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response';
        $request = $this->retryEndUserDisputeRequest($dispute_id, $idempotency_key, $contentType);

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
     * Create request for operation 'retryEndUserDispute'
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['retryEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function retryEndUserDisputeRequest($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['retryEndUserDispute'][0])
    {

        // verify the required parameter 'dispute_id' is set
        if ($dispute_id === null || (is_array($dispute_id) && count($dispute_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $dispute_id when calling retryEndUserDispute'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.retryEndUserDispute, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/end-users/disputes/{disputeId}/retry';
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
        if ($dispute_id !== null) {
            $resourcePath = str_replace(
                '{disputeId}',
                ObjectSerializer::toPathValue($dispute_id),
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
     * Operation reviewEndUserDispute
     *
     * Start Reviewing an End-User Dispute
     *
     * @param  string $dispute_id dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['reviewEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function reviewEndUserDispute($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['reviewEndUserDispute'][0])
    {
        list($response) = $this->reviewEndUserDisputeWithHttpInfo($dispute_id, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation reviewEndUserDisputeWithHttpInfo
     *
     * Start Reviewing an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['reviewEndUserDispute'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function reviewEndUserDisputeWithHttpInfo($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['reviewEndUserDispute'][0])
    {
        $request = $this->reviewEndUserDisputeRequest($dispute_id, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 202:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response',
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
     * Operation reviewEndUserDisputeAsync
     *
     * Start Reviewing an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['reviewEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function reviewEndUserDisputeAsync($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['reviewEndUserDispute'][0])
    {
        return $this->reviewEndUserDisputeAsyncWithHttpInfo($dispute_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation reviewEndUserDisputeAsyncWithHttpInfo
     *
     * Start Reviewing an End-User Dispute
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['reviewEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function reviewEndUserDisputeAsyncWithHttpInfo($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['reviewEndUserDispute'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetEndUserDispute200Response';
        $request = $this->reviewEndUserDisputeRequest($dispute_id, $idempotency_key, $contentType);

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
     * Create request for operation 'reviewEndUserDispute'
     *
     * @param  string $dispute_id (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['reviewEndUserDispute'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function reviewEndUserDisputeRequest($dispute_id, $idempotency_key = null, string $contentType = self::contentTypes['reviewEndUserDispute'][0])
    {

        // verify the required parameter 'dispute_id' is set
        if ($dispute_id === null || (is_array($dispute_id) && count($dispute_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $dispute_id when calling reviewEndUserDispute'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.reviewEndUserDispute, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/end-users/disputes/{disputeId}/review';
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
        if ($dispute_id !== null) {
            $resourcePath = str_replace(
                '{disputeId}',
                ObjectSerializer::toPathValue($dispute_id),
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
     * Operation setRetailHardshipOptions
     *
     * Set Hardship Options
     *
     * @param  \CheckNetworks\Api\Generated\Model\SetRetailHardshipOptionsRequest $set_retail_hardship_options_request set_retail_hardship_options_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function setRetailHardshipOptions($set_retail_hardship_options_request, $idempotency_key = null, string $contentType = self::contentTypes['setRetailHardshipOptions'][0])
    {
        list($response) = $this->setRetailHardshipOptionsWithHttpInfo($set_retail_hardship_options_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation setRetailHardshipOptionsWithHttpInfo
     *
     * Set Hardship Options
     *
     * @param  \CheckNetworks\Api\Generated\Model\SetRetailHardshipOptionsRequest $set_retail_hardship_options_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function setRetailHardshipOptionsWithHttpInfo($set_retail_hardship_options_request, $idempotency_key = null, string $contentType = self::contentTypes['setRetailHardshipOptions'][0])
    {
        $request = $this->setRetailHardshipOptionsRequest($set_retail_hardship_options_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response',
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
     * Operation setRetailHardshipOptionsAsync
     *
     * Set Hardship Options
     *
     * @param  \CheckNetworks\Api\Generated\Model\SetRetailHardshipOptionsRequest $set_retail_hardship_options_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function setRetailHardshipOptionsAsync($set_retail_hardship_options_request, $idempotency_key = null, string $contentType = self::contentTypes['setRetailHardshipOptions'][0])
    {
        return $this->setRetailHardshipOptionsAsyncWithHttpInfo($set_retail_hardship_options_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation setRetailHardshipOptionsAsyncWithHttpInfo
     *
     * Set Hardship Options
     *
     * @param  \CheckNetworks\Api\Generated\Model\SetRetailHardshipOptionsRequest $set_retail_hardship_options_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function setRetailHardshipOptionsAsyncWithHttpInfo($set_retail_hardship_options_request, $idempotency_key = null, string $contentType = self::contentTypes['setRetailHardshipOptions'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetRetailHardshipOptions200Response';
        $request = $this->setRetailHardshipOptionsRequest($set_retail_hardship_options_request, $idempotency_key, $contentType);

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
     * Create request for operation 'setRetailHardshipOptions'
     *
     * @param  \CheckNetworks\Api\Generated\Model\SetRetailHardshipOptionsRequest $set_retail_hardship_options_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setRetailHardshipOptions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function setRetailHardshipOptionsRequest($set_retail_hardship_options_request, $idempotency_key = null, string $contentType = self::contentTypes['setRetailHardshipOptions'][0])
    {

        // verify the required parameter 'set_retail_hardship_options_request' is set
        if ($set_retail_hardship_options_request === null || (is_array($set_retail_hardship_options_request) && count($set_retail_hardship_options_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $set_retail_hardship_options_request when calling setRetailHardshipOptions'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling EndUserCasesApi.setRetailHardshipOptions, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/retail/hardship-options';
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
        if (isset($set_retail_hardship_options_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($set_retail_hardship_options_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $set_retail_hardship_options_request;
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
