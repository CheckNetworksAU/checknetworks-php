<?php
/**
 * SIPTrunksApi
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
 * SIPTrunksApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class SIPTrunksApi
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
        'addTrunkIp' => [
            'application/json',
        ],
        'cancelTrunkPlanChange' => [
            'application/json',
        ],
        'createTrunk' => [
            'application/json',
        ],
        'deleteTrunk' => [
            'application/json',
        ],
        'deleteTrunkIp' => [
            'application/json',
        ],
        'getAccountAllowedCountries' => [
            'application/json',
        ],
        'getTrunk' => [
            'application/json',
        ],
        'getTrunkRegistration' => [
            'application/json',
        ],
        'getTrunkSourceRestrictions' => [
            'application/json',
        ],
        'issueTrunkCredential' => [
            'application/json',
        ],
        'listTrunkIps' => [
            'application/json',
        ],
        'listTrunks' => [
            'application/json',
        ],
        'postTrunkSpendCapRelease' => [
            'application/json',
        ],
        'putAccountAllowedCountries' => [
            'application/json',
        ],
        'resumeTrunk' => [
            'application/json',
        ],
        'revealTrunkCredential' => [
            'application/json',
        ],
        'rotateTrunkCredential' => [
            'application/json',
        ],
        'setTrunkSourceRestrictions' => [
            'application/json',
        ],
        'suspendTrunk' => [
            'application/json',
        ],
        'updateTrunk' => [
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
     * Operation addTrunkIp
     *
     * Add Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  \CheckNetworks\Model\AddTrunkIpRequest $add_trunk_ip_request add_trunk_ip_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addTrunkIp'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\AddTrunkIp201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function addTrunkIp($username, $add_trunk_ip_request, $idempotency_key = null, string $contentType = self::contentTypes['addTrunkIp'][0])
    {
        list($response) = $this->addTrunkIpWithHttpInfo($username, $add_trunk_ip_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation addTrunkIpWithHttpInfo
     *
     * Add Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  \CheckNetworks\Model\AddTrunkIpRequest $add_trunk_ip_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addTrunkIp'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\AddTrunkIp201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function addTrunkIpWithHttpInfo($username, $add_trunk_ip_request, $idempotency_key = null, string $contentType = self::contentTypes['addTrunkIp'][0])
    {
        $request = $this->addTrunkIpRequest($username, $add_trunk_ip_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\AddTrunkIp201Response',
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
                '\CheckNetworks\Model\AddTrunkIp201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\AddTrunkIp201Response',
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
     * Operation addTrunkIpAsync
     *
     * Add Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  \CheckNetworks\Model\AddTrunkIpRequest $add_trunk_ip_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addTrunkIp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function addTrunkIpAsync($username, $add_trunk_ip_request, $idempotency_key = null, string $contentType = self::contentTypes['addTrunkIp'][0])
    {
        return $this->addTrunkIpAsyncWithHttpInfo($username, $add_trunk_ip_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation addTrunkIpAsyncWithHttpInfo
     *
     * Add Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  \CheckNetworks\Model\AddTrunkIpRequest $add_trunk_ip_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addTrunkIp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function addTrunkIpAsyncWithHttpInfo($username, $add_trunk_ip_request, $idempotency_key = null, string $contentType = self::contentTypes['addTrunkIp'][0])
    {
        $returnType = '\CheckNetworks\Model\AddTrunkIp201Response';
        $request = $this->addTrunkIpRequest($username, $add_trunk_ip_request, $idempotency_key, $contentType);

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
     * Create request for operation 'addTrunkIp'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  \CheckNetworks\Model\AddTrunkIpRequest $add_trunk_ip_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['addTrunkIp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function addTrunkIpRequest($username, $add_trunk_ip_request, $idempotency_key = null, string $contentType = self::contentTypes['addTrunkIp'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling addTrunkIp'
            );
        }

        // verify the required parameter 'add_trunk_ip_request' is set
        if ($add_trunk_ip_request === null || (is_array($add_trunk_ip_request) && count($add_trunk_ip_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $add_trunk_ip_request when calling addTrunkIp'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.addTrunkIp, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/ips';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($add_trunk_ip_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($add_trunk_ip_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $add_trunk_ip_request;
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
     * Operation cancelTrunkPlanChange
     *
     * Cancel Scheduled Plan Change
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelTrunkPlanChange'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function cancelTrunkPlanChange($username, $idempotency_key = null, string $contentType = self::contentTypes['cancelTrunkPlanChange'][0])
    {
        list($response) = $this->cancelTrunkPlanChangeWithHttpInfo($username, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation cancelTrunkPlanChangeWithHttpInfo
     *
     * Cancel Scheduled Plan Change
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelTrunkPlanChange'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function cancelTrunkPlanChangeWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['cancelTrunkPlanChange'][0])
    {
        $request = $this->cancelTrunkPlanChangeRequest($username, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\GetTrunk200Response',
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
                '\CheckNetworks\Model\GetTrunk200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetTrunk200Response',
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
     * Operation cancelTrunkPlanChangeAsync
     *
     * Cancel Scheduled Plan Change
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelTrunkPlanChange'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function cancelTrunkPlanChangeAsync($username, $idempotency_key = null, string $contentType = self::contentTypes['cancelTrunkPlanChange'][0])
    {
        return $this->cancelTrunkPlanChangeAsyncWithHttpInfo($username, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation cancelTrunkPlanChangeAsyncWithHttpInfo
     *
     * Cancel Scheduled Plan Change
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelTrunkPlanChange'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function cancelTrunkPlanChangeAsyncWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['cancelTrunkPlanChange'][0])
    {
        $returnType = '\CheckNetworks\Model\GetTrunk200Response';
        $request = $this->cancelTrunkPlanChangeRequest($username, $idempotency_key, $contentType);

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
     * Create request for operation 'cancelTrunkPlanChange'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['cancelTrunkPlanChange'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function cancelTrunkPlanChangeRequest($username, $idempotency_key = null, string $contentType = self::contentTypes['cancelTrunkPlanChange'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling cancelTrunkPlanChange'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.cancelTrunkPlanChange, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/plan-change';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation createTrunk
     *
     * Create SIP Trunk
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\CreateTrunkRequest $create_trunk_request create_trunk_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\CreateTrunk201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function createTrunk($idempotency_key, $create_trunk_request, string $contentType = self::contentTypes['createTrunk'][0])
    {
        list($response) = $this->createTrunkWithHttpInfo($idempotency_key, $create_trunk_request, $contentType);
        return $response;
    }

    /**
     * Operation createTrunkWithHttpInfo
     *
     * Create SIP Trunk
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\CreateTrunkRequest $create_trunk_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\CreateTrunk201Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function createTrunkWithHttpInfo($idempotency_key, $create_trunk_request, string $contentType = self::contentTypes['createTrunk'][0])
    {
        $request = $this->createTrunkRequest($idempotency_key, $create_trunk_request, $contentType);

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
                        '\CheckNetworks\Model\CreateTrunk201Response',
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
                '\CheckNetworks\Model\CreateTrunk201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\CreateTrunk201Response',
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
     * Operation createTrunkAsync
     *
     * Create SIP Trunk
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\CreateTrunkRequest $create_trunk_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function createTrunkAsync($idempotency_key, $create_trunk_request, string $contentType = self::contentTypes['createTrunk'][0])
    {
        return $this->createTrunkAsyncWithHttpInfo($idempotency_key, $create_trunk_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation createTrunkAsyncWithHttpInfo
     *
     * Create SIP Trunk
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\CreateTrunkRequest $create_trunk_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function createTrunkAsyncWithHttpInfo($idempotency_key, $create_trunk_request, string $contentType = self::contentTypes['createTrunk'][0])
    {
        $returnType = '\CheckNetworks\Model\CreateTrunk201Response';
        $request = $this->createTrunkRequest($idempotency_key, $create_trunk_request, $contentType);

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
     * Create request for operation 'createTrunk'
     *
     * @param  string $idempotency_key **Required.** A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Without it the request answers &#x60;400&#x60;. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (required)
     * @param  \CheckNetworks\Model\CreateTrunkRequest $create_trunk_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function createTrunkRequest($idempotency_key, $create_trunk_request, string $contentType = self::contentTypes['createTrunk'][0])
    {

        // verify the required parameter 'idempotency_key' is set
        if ($idempotency_key === null || (is_array($idempotency_key) && count($idempotency_key) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $idempotency_key when calling createTrunk'
            );
        }
        if (strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.createTrunk, must be smaller than or equal to 255.');
        }
        
        // verify the required parameter 'create_trunk_request' is set
        if ($create_trunk_request === null || (is_array($create_trunk_request) && count($create_trunk_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $create_trunk_request when calling createTrunk'
            );
        }


        $resourcePath = '/trunks';
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
        if (isset($create_trunk_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($create_trunk_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $create_trunk_request;
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
     * Operation deleteTrunk
     *
     * Delete SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  bool|null $acknowledge_early_termination Accept the early termination charge on a trunk still in contract. (optional, default to false)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function deleteTrunk($username, $idempotency_key = null, $acknowledge_early_termination = false, string $contentType = self::contentTypes['deleteTrunk'][0])
    {
        $this->deleteTrunkWithHttpInfo($username, $idempotency_key, $acknowledge_early_termination, $contentType);
    }

    /**
     * Operation deleteTrunkWithHttpInfo
     *
     * Delete SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  bool|null $acknowledge_early_termination Accept the early termination charge on a trunk still in contract. (optional, default to false)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function deleteTrunkWithHttpInfo($username, $idempotency_key = null, $acknowledge_early_termination = false, string $contentType = self::contentTypes['deleteTrunk'][0])
    {
        $request = $this->deleteTrunkRequest($username, $idempotency_key, $acknowledge_early_termination, $contentType);

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
     * Operation deleteTrunkAsync
     *
     * Delete SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  bool|null $acknowledge_early_termination Accept the early termination charge on a trunk still in contract. (optional, default to false)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deleteTrunkAsync($username, $idempotency_key = null, $acknowledge_early_termination = false, string $contentType = self::contentTypes['deleteTrunk'][0])
    {
        return $this->deleteTrunkAsyncWithHttpInfo($username, $idempotency_key, $acknowledge_early_termination, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation deleteTrunkAsyncWithHttpInfo
     *
     * Delete SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  bool|null $acknowledge_early_termination Accept the early termination charge on a trunk still in contract. (optional, default to false)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deleteTrunkAsyncWithHttpInfo($username, $idempotency_key = null, $acknowledge_early_termination = false, string $contentType = self::contentTypes['deleteTrunk'][0])
    {
        $returnType = '';
        $request = $this->deleteTrunkRequest($username, $idempotency_key, $acknowledge_early_termination, $contentType);

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
     * Create request for operation 'deleteTrunk'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  bool|null $acknowledge_early_termination Accept the early termination charge on a trunk still in contract. (optional, default to false)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function deleteTrunkRequest($username, $idempotency_key = null, $acknowledge_early_termination = false, string $contentType = self::contentTypes['deleteTrunk'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling deleteTrunk'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.deleteTrunk, must be smaller than or equal to 255.');
        }
        


        $resourcePath = '/trunks/{username}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $acknowledge_early_termination,
            'acknowledgeEarlyTermination', // param base name
            'boolean', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);

        // header params
        if ($idempotency_key !== null) {
            $headerParams['Idempotency-Key'] = ObjectSerializer::toHeaderValue($idempotency_key);
        }

        // path params
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation deleteTrunkIp
     *
     * Remove Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $ip_id The IP ACL entry&#39;s &#x60;id&#x60;, as the trunk&#39;s IP list gives it. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunkIp'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function deleteTrunkIp($username, $ip_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteTrunkIp'][0])
    {
        $this->deleteTrunkIpWithHttpInfo($username, $ip_id, $idempotency_key, $contentType);
    }

    /**
     * Operation deleteTrunkIpWithHttpInfo
     *
     * Remove Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $ip_id The IP ACL entry&#39;s &#x60;id&#x60;, as the trunk&#39;s IP list gives it. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunkIp'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function deleteTrunkIpWithHttpInfo($username, $ip_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteTrunkIp'][0])
    {
        $request = $this->deleteTrunkIpRequest($username, $ip_id, $idempotency_key, $contentType);

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
     * Operation deleteTrunkIpAsync
     *
     * Remove Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $ip_id The IP ACL entry&#39;s &#x60;id&#x60;, as the trunk&#39;s IP list gives it. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunkIp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deleteTrunkIpAsync($username, $ip_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteTrunkIp'][0])
    {
        return $this->deleteTrunkIpAsyncWithHttpInfo($username, $ip_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation deleteTrunkIpAsyncWithHttpInfo
     *
     * Remove Trunk IP ACL Entry
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $ip_id The IP ACL entry&#39;s &#x60;id&#x60;, as the trunk&#39;s IP list gives it. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunkIp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deleteTrunkIpAsyncWithHttpInfo($username, $ip_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteTrunkIp'][0])
    {
        $returnType = '';
        $request = $this->deleteTrunkIpRequest($username, $ip_id, $idempotency_key, $contentType);

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
     * Create request for operation 'deleteTrunkIp'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $ip_id The IP ACL entry&#39;s &#x60;id&#x60;, as the trunk&#39;s IP list gives it. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deleteTrunkIp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function deleteTrunkIpRequest($username, $ip_id, $idempotency_key = null, string $contentType = self::contentTypes['deleteTrunkIp'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling deleteTrunkIp'
            );
        }

        // verify the required parameter 'ip_id' is set
        if ($ip_id === null || (is_array($ip_id) && count($ip_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $ip_id when calling deleteTrunkIp'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.deleteTrunkIp, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/ips/{ipId}';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
                $resourcePath
            );
        }
        // path params
        if ($ip_id !== null) {
            $resourcePath = str_replace(
                '{ipId}',
                ObjectSerializer::toPathValue($ip_id),
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
     * Operation getAccountAllowedCountries
     *
     * Get Allowed Countries
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetAccountAllowedCountries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getAccountAllowedCountries(string $contentType = self::contentTypes['getAccountAllowedCountries'][0])
    {
        list($response) = $this->getAccountAllowedCountriesWithHttpInfo($contentType);
        return $response;
    }

    /**
     * Operation getAccountAllowedCountriesWithHttpInfo
     *
     * Get Allowed Countries
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetAccountAllowedCountries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getAccountAllowedCountriesWithHttpInfo(string $contentType = self::contentTypes['getAccountAllowedCountries'][0])
    {
        $request = $this->getAccountAllowedCountriesRequest($contentType);

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
                        '\CheckNetworks\Model\GetAccountAllowedCountries200Response',
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
                '\CheckNetworks\Model\GetAccountAllowedCountries200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetAccountAllowedCountries200Response',
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
            }
        

            throw $e;
        }
    }

    /**
     * Operation getAccountAllowedCountriesAsync
     *
     * Get Allowed Countries
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAccountAllowedCountriesAsync(string $contentType = self::contentTypes['getAccountAllowedCountries'][0])
    {
        return $this->getAccountAllowedCountriesAsyncWithHttpInfo($contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getAccountAllowedCountriesAsyncWithHttpInfo
     *
     * Get Allowed Countries
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAccountAllowedCountriesAsyncWithHttpInfo(string $contentType = self::contentTypes['getAccountAllowedCountries'][0])
    {
        $returnType = '\CheckNetworks\Model\GetAccountAllowedCountries200Response';
        $request = $this->getAccountAllowedCountriesRequest($contentType);

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
     * Create request for operation 'getAccountAllowedCountries'
     *
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getAccountAllowedCountriesRequest(string $contentType = self::contentTypes['getAccountAllowedCountries'][0])
    {


        $resourcePath = '/account/allowed-countries';
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
     * Operation getTrunk
     *
     * Get SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getTrunk($username, string $contentType = self::contentTypes['getTrunk'][0])
    {
        list($response) = $this->getTrunkWithHttpInfo($username, $contentType);
        return $response;
    }

    /**
     * Operation getTrunkWithHttpInfo
     *
     * Get SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getTrunkWithHttpInfo($username, string $contentType = self::contentTypes['getTrunk'][0])
    {
        $request = $this->getTrunkRequest($username, $contentType);

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
                        '\CheckNetworks\Model\GetTrunk200Response',
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
                '\CheckNetworks\Model\GetTrunk200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetTrunk200Response',
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
     * Operation getTrunkAsync
     *
     * Get SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getTrunkAsync($username, string $contentType = self::contentTypes['getTrunk'][0])
    {
        return $this->getTrunkAsyncWithHttpInfo($username, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getTrunkAsyncWithHttpInfo
     *
     * Get SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getTrunkAsyncWithHttpInfo($username, string $contentType = self::contentTypes['getTrunk'][0])
    {
        $returnType = '\CheckNetworks\Model\GetTrunk200Response';
        $request = $this->getTrunkRequest($username, $contentType);

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
     * Create request for operation 'getTrunk'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getTrunkRequest($username, string $contentType = self::contentTypes['getTrunk'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling getTrunk'
            );
        }


        $resourcePath = '/trunks/{username}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation getTrunkRegistration
     *
     * Get Trunk Registration Status
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkRegistration'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetTrunkRegistration200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getTrunkRegistration($username, string $contentType = self::contentTypes['getTrunkRegistration'][0])
    {
        list($response) = $this->getTrunkRegistrationWithHttpInfo($username, $contentType);
        return $response;
    }

    /**
     * Operation getTrunkRegistrationWithHttpInfo
     *
     * Get Trunk Registration Status
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkRegistration'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetTrunkRegistration200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getTrunkRegistrationWithHttpInfo($username, string $contentType = self::contentTypes['getTrunkRegistration'][0])
    {
        $request = $this->getTrunkRegistrationRequest($username, $contentType);

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
                        '\CheckNetworks\Model\GetTrunkRegistration200Response',
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
                case 501:
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
                '\CheckNetworks\Model\GetTrunkRegistration200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetTrunkRegistration200Response',
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
                case 501:
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
     * Operation getTrunkRegistrationAsync
     *
     * Get Trunk Registration Status
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkRegistration'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getTrunkRegistrationAsync($username, string $contentType = self::contentTypes['getTrunkRegistration'][0])
    {
        return $this->getTrunkRegistrationAsyncWithHttpInfo($username, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getTrunkRegistrationAsyncWithHttpInfo
     *
     * Get Trunk Registration Status
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkRegistration'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getTrunkRegistrationAsyncWithHttpInfo($username, string $contentType = self::contentTypes['getTrunkRegistration'][0])
    {
        $returnType = '\CheckNetworks\Model\GetTrunkRegistration200Response';
        $request = $this->getTrunkRegistrationRequest($username, $contentType);

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
     * Create request for operation 'getTrunkRegistration'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkRegistration'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getTrunkRegistrationRequest($username, string $contentType = self::contentTypes['getTrunkRegistration'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling getTrunkRegistration'
            );
        }


        $resourcePath = '/trunks/{username}/registration';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation getTrunkSourceRestrictions
     *
     * Get Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetTrunkSourceRestrictions200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function getTrunkSourceRestrictions($username, string $contentType = self::contentTypes['getTrunkSourceRestrictions'][0])
    {
        list($response) = $this->getTrunkSourceRestrictionsWithHttpInfo($username, $contentType);
        return $response;
    }

    /**
     * Operation getTrunkSourceRestrictionsWithHttpInfo
     *
     * Get Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetTrunkSourceRestrictions200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getTrunkSourceRestrictionsWithHttpInfo($username, string $contentType = self::contentTypes['getTrunkSourceRestrictions'][0])
    {
        $request = $this->getTrunkSourceRestrictionsRequest($username, $contentType);

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
                        '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response',
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
                '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response',
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
     * Operation getTrunkSourceRestrictionsAsync
     *
     * Get Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getTrunkSourceRestrictionsAsync($username, string $contentType = self::contentTypes['getTrunkSourceRestrictions'][0])
    {
        return $this->getTrunkSourceRestrictionsAsyncWithHttpInfo($username, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getTrunkSourceRestrictionsAsyncWithHttpInfo
     *
     * Get Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getTrunkSourceRestrictionsAsyncWithHttpInfo($username, string $contentType = self::contentTypes['getTrunkSourceRestrictions'][0])
    {
        $returnType = '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response';
        $request = $this->getTrunkSourceRestrictionsRequest($username, $contentType);

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
     * Create request for operation 'getTrunkSourceRestrictions'
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getTrunkSourceRestrictionsRequest($username, string $contentType = self::contentTypes['getTrunkSourceRestrictions'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling getTrunkSourceRestrictions'
            );
        }


        $resourcePath = '/trunks/{username}/source-restrictions';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation issueTrunkCredential
     *
     * Add Trunk SIP Credential
     *
     * @param  string $username The trunk&#39;s handle, as the portal shows it: a trunk with no credential has no SIP username yet. A SIP username is accepted too, and answers 409, since that trunk already has a credential. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['issueTrunkCredential'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\RevealTrunkCredential200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function issueTrunkCredential($username, $idempotency_key = null, string $contentType = self::contentTypes['issueTrunkCredential'][0])
    {
        list($response) = $this->issueTrunkCredentialWithHttpInfo($username, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation issueTrunkCredentialWithHttpInfo
     *
     * Add Trunk SIP Credential
     *
     * @param  string $username The trunk&#39;s handle, as the portal shows it: a trunk with no credential has no SIP username yet. A SIP username is accepted too, and answers 409, since that trunk already has a credential. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['issueTrunkCredential'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\RevealTrunkCredential200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function issueTrunkCredentialWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['issueTrunkCredential'][0])
    {
        $request = $this->issueTrunkCredentialRequest($username, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\RevealTrunkCredential200Response',
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
                '\CheckNetworks\Model\RevealTrunkCredential200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\RevealTrunkCredential200Response',
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
     * Operation issueTrunkCredentialAsync
     *
     * Add Trunk SIP Credential
     *
     * @param  string $username The trunk&#39;s handle, as the portal shows it: a trunk with no credential has no SIP username yet. A SIP username is accepted too, and answers 409, since that trunk already has a credential. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['issueTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function issueTrunkCredentialAsync($username, $idempotency_key = null, string $contentType = self::contentTypes['issueTrunkCredential'][0])
    {
        return $this->issueTrunkCredentialAsyncWithHttpInfo($username, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation issueTrunkCredentialAsyncWithHttpInfo
     *
     * Add Trunk SIP Credential
     *
     * @param  string $username The trunk&#39;s handle, as the portal shows it: a trunk with no credential has no SIP username yet. A SIP username is accepted too, and answers 409, since that trunk already has a credential. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['issueTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function issueTrunkCredentialAsyncWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['issueTrunkCredential'][0])
    {
        $returnType = '\CheckNetworks\Model\RevealTrunkCredential200Response';
        $request = $this->issueTrunkCredentialRequest($username, $idempotency_key, $contentType);

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
     * Create request for operation 'issueTrunkCredential'
     *
     * @param  string $username The trunk&#39;s handle, as the portal shows it: a trunk with no credential has no SIP username yet. A SIP username is accepted too, and answers 409, since that trunk already has a credential. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['issueTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function issueTrunkCredentialRequest($username, $idempotency_key = null, string $contentType = self::contentTypes['issueTrunkCredential'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling issueTrunkCredential'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.issueTrunkCredential, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/credential';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation listTrunkIps
     *
     * List Trunk IP ACL Entries
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunkIps'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\ListTrunkIps200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function listTrunkIps($username, $page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunkIps'][0])
    {
        list($response) = $this->listTrunkIpsWithHttpInfo($username, $page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType);
        return $response;
    }

    /**
     * Operation listTrunkIpsWithHttpInfo
     *
     * List Trunk IP ACL Entries
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunkIps'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\ListTrunkIps200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listTrunkIpsWithHttpInfo($username, $page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunkIps'][0])
    {
        $request = $this->listTrunkIpsRequest($username, $page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType);

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
                        '\CheckNetworks\Model\ListTrunkIps200Response',
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
                '\CheckNetworks\Model\ListTrunkIps200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\ListTrunkIps200Response',
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
     * Operation listTrunkIpsAsync
     *
     * List Trunk IP ACL Entries
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunkIps'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listTrunkIpsAsync($username, $page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunkIps'][0])
    {
        return $this->listTrunkIpsAsyncWithHttpInfo($username, $page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listTrunkIpsAsyncWithHttpInfo
     *
     * List Trunk IP ACL Entries
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunkIps'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listTrunkIpsAsyncWithHttpInfo($username, $page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunkIps'][0])
    {
        $returnType = '\CheckNetworks\Model\ListTrunkIps200Response';
        $request = $this->listTrunkIpsRequest($username, $page, $per_page, $sort, $q, $updated_since, $created_from, $created_to, $contentType);

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
     * Create request for operation 'listTrunkIps'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunkIps'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listTrunkIpsRequest($username, $page = 1, $per_page = 50, $sort = null, $q = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunkIps'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling listTrunkIps'
            );
        }

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling SIPTrunksApi.listTrunkIps, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling SIPTrunksApi.listTrunkIps, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling SIPTrunksApi.listTrunkIps, must be bigger than or equal to 1.');
        }
        

        if ($q !== null && strlen($q) > 100) {
            throw new \InvalidArgumentException('invalid length for "$q" when calling SIPTrunksApi.listTrunkIps, must be smaller than or equal to 100.');
        }
        




        $resourcePath = '/trunks/{username}/ips';
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


        // path params
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation listTrunks
     *
     * List SIP Trunks
     *
     * @param  string|null $status Only trunks with this &#x60;status&#x60;. (optional)
     * @param  string|null $format Bulk export format. Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams the full result set as a download. (optional)
     * @param  string|null $end_user Only the trunks of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\ListTrunks200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function listTrunks($status = null, $format = null, $end_user = null, $page = 1, $per_page = 50, $sort = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunks'][0])
    {
        list($response) = $this->listTrunksWithHttpInfo($status, $format, $end_user, $page, $per_page, $sort, $q, $external_ref, $updated_since, $created_from, $created_to, $contentType);
        return $response;
    }

    /**
     * Operation listTrunksWithHttpInfo
     *
     * List SIP Trunks
     *
     * @param  string|null $status Only trunks with this &#x60;status&#x60;. (optional)
     * @param  string|null $format Bulk export format. Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams the full result set as a download. (optional)
     * @param  string|null $end_user Only the trunks of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\ListTrunks200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listTrunksWithHttpInfo($status = null, $format = null, $end_user = null, $page = 1, $per_page = 50, $sort = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunks'][0])
    {
        $request = $this->listTrunksRequest($status, $format, $end_user, $page, $per_page, $sort, $q, $external_ref, $updated_since, $created_from, $created_to, $contentType);

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
                        '\CheckNetworks\Model\ListTrunks200Response',
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
                '\CheckNetworks\Model\ListTrunks200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\ListTrunks200Response',
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
     * Operation listTrunksAsync
     *
     * List SIP Trunks
     *
     * @param  string|null $status Only trunks with this &#x60;status&#x60;. (optional)
     * @param  string|null $format Bulk export format. Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams the full result set as a download. (optional)
     * @param  string|null $end_user Only the trunks of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listTrunksAsync($status = null, $format = null, $end_user = null, $page = 1, $per_page = 50, $sort = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunks'][0])
    {
        return $this->listTrunksAsyncWithHttpInfo($status, $format, $end_user, $page, $per_page, $sort, $q, $external_ref, $updated_since, $created_from, $created_to, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listTrunksAsyncWithHttpInfo
     *
     * List SIP Trunks
     *
     * @param  string|null $status Only trunks with this &#x60;status&#x60;. (optional)
     * @param  string|null $format Bulk export format. Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams the full result set as a download. (optional)
     * @param  string|null $end_user Only the trunks of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listTrunksAsyncWithHttpInfo($status = null, $format = null, $end_user = null, $page = 1, $per_page = 50, $sort = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunks'][0])
    {
        $returnType = '\CheckNetworks\Model\ListTrunks200Response';
        $request = $this->listTrunksRequest($status, $format, $end_user, $page, $per_page, $sort, $q, $external_ref, $updated_since, $created_from, $created_to, $contentType);

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
     * Create request for operation 'listTrunks'
     *
     * @param  string|null $status Only trunks with this &#x60;status&#x60;. (optional)
     * @param  string|null $format Bulk export format. Omit for paged JSON; &#x60;csv&#x60; or &#x60;ndjson&#x60; streams the full result set as a download. (optional)
     * @param  string|null $end_user Only the trunks of this end user, by its public number. A number that is not one of your end users is a 422, not an empty list. (optional)
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string|null $sort Order by this field, &#x60;-&#x60; in front for descending. An unknown field is &#x60;422&#x60;. (optional)
     * @param  string|null $q Search: rows whose searchable fields contain this text, ignoring case. Each list&#39;s description names the fields it searches. At most 100 characters; a longer value is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $external_ref Only the object holding this &#x60;externalRef&#x60;, matched exactly (case counts). Up to 64 letters, digits, &#x60;_&#x60;, &#x60;.&#x60; or &#x60;-&#x60;; another value is refused with &#x60;422 - validation_failed&#x60;. A reference no object holds gives an empty list. (optional)
     * @param  string|null $updated_since Only rows changed at or after this time: an ISO 8601 date or date-time, such as &#x60;2026-11-01&#x60; or &#x60;2026-11-01T09:00:00Z&#x60;. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_from Only rows created at or after this time: an ISO 8601 date or date-time. A date alone means the start of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string|null $created_to Only rows created at or before this time: an ISO 8601 date or date-time. A date alone includes the whole of that day (UTC). A value that is not ISO 8601 is refused with &#x60;422 - validation_failed&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listTrunks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listTrunksRequest($status = null, $format = null, $end_user = null, $page = 1, $per_page = 50, $sort = null, $q = null, $external_ref = null, $updated_since = null, $created_from = null, $created_to = null, string $contentType = self::contentTypes['listTrunks'][0])
    {




        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling SIPTrunksApi.listTrunks, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling SIPTrunksApi.listTrunks, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling SIPTrunksApi.listTrunks, must be bigger than or equal to 1.');
        }
        

        if ($q !== null && strlen($q) > 100) {
            throw new \InvalidArgumentException('invalid length for "$q" when calling SIPTrunksApi.listTrunks, must be smaller than or equal to 100.');
        }
        
        if ($external_ref !== null && strlen($external_ref) > 64) {
            throw new \InvalidArgumentException('invalid length for "$external_ref" when calling SIPTrunksApi.listTrunks, must be smaller than or equal to 64.');
        }
        if ($external_ref !== null && strlen($external_ref) < 1) {
            throw new \InvalidArgumentException('invalid length for "$external_ref" when calling SIPTrunksApi.listTrunks, must be bigger than or equal to 1.');
        }
        if ($external_ref !== null && !preg_match("/^[A-Za-z0-9_.\\-]+$/", $external_ref)) {
            throw new \InvalidArgumentException("invalid value for \"external_ref\" when calling SIPTrunksApi.listTrunks, must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
        }
        




        $resourcePath = '/trunks';
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
     * Operation postTrunkSpendCapRelease
     *
     * Release Spend Cap Hold
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postTrunkSpendCapRelease'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\PostTrunkSpendCapRelease200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function postTrunkSpendCapRelease($username, $idempotency_key = null, string $contentType = self::contentTypes['postTrunkSpendCapRelease'][0])
    {
        list($response) = $this->postTrunkSpendCapReleaseWithHttpInfo($username, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation postTrunkSpendCapReleaseWithHttpInfo
     *
     * Release Spend Cap Hold
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postTrunkSpendCapRelease'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\PostTrunkSpendCapRelease200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function postTrunkSpendCapReleaseWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['postTrunkSpendCapRelease'][0])
    {
        $request = $this->postTrunkSpendCapReleaseRequest($username, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\PostTrunkSpendCapRelease200Response',
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
                '\CheckNetworks\Model\PostTrunkSpendCapRelease200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\PostTrunkSpendCapRelease200Response',
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
            }
        

            throw $e;
        }
    }

    /**
     * Operation postTrunkSpendCapReleaseAsync
     *
     * Release Spend Cap Hold
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postTrunkSpendCapRelease'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postTrunkSpendCapReleaseAsync($username, $idempotency_key = null, string $contentType = self::contentTypes['postTrunkSpendCapRelease'][0])
    {
        return $this->postTrunkSpendCapReleaseAsyncWithHttpInfo($username, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation postTrunkSpendCapReleaseAsyncWithHttpInfo
     *
     * Release Spend Cap Hold
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postTrunkSpendCapRelease'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function postTrunkSpendCapReleaseAsyncWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['postTrunkSpendCapRelease'][0])
    {
        $returnType = '\CheckNetworks\Model\PostTrunkSpendCapRelease200Response';
        $request = $this->postTrunkSpendCapReleaseRequest($username, $idempotency_key, $contentType);

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
     * Create request for operation 'postTrunkSpendCapRelease'
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['postTrunkSpendCapRelease'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function postTrunkSpendCapReleaseRequest($username, $idempotency_key = null, string $contentType = self::contentTypes['postTrunkSpendCapRelease'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling postTrunkSpendCapRelease'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.postTrunkSpendCapRelease, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/spend-cap/release';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation putAccountAllowedCountries
     *
     * Set Allowed Countries
     *
     * @param  \CheckNetworks\Model\PutAccountAllowedCountriesRequest $put_account_allowed_countries_request put_account_allowed_countries_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetAccountAllowedCountries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function putAccountAllowedCountries($put_account_allowed_countries_request, $idempotency_key = null, string $contentType = self::contentTypes['putAccountAllowedCountries'][0])
    {
        list($response) = $this->putAccountAllowedCountriesWithHttpInfo($put_account_allowed_countries_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation putAccountAllowedCountriesWithHttpInfo
     *
     * Set Allowed Countries
     *
     * @param  \CheckNetworks\Model\PutAccountAllowedCountriesRequest $put_account_allowed_countries_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetAccountAllowedCountries200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function putAccountAllowedCountriesWithHttpInfo($put_account_allowed_countries_request, $idempotency_key = null, string $contentType = self::contentTypes['putAccountAllowedCountries'][0])
    {
        $request = $this->putAccountAllowedCountriesRequest($put_account_allowed_countries_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\GetAccountAllowedCountries200Response',
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
                '\CheckNetworks\Model\GetAccountAllowedCountries200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetAccountAllowedCountries200Response',
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
     * Operation putAccountAllowedCountriesAsync
     *
     * Set Allowed Countries
     *
     * @param  \CheckNetworks\Model\PutAccountAllowedCountriesRequest $put_account_allowed_countries_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function putAccountAllowedCountriesAsync($put_account_allowed_countries_request, $idempotency_key = null, string $contentType = self::contentTypes['putAccountAllowedCountries'][0])
    {
        return $this->putAccountAllowedCountriesAsyncWithHttpInfo($put_account_allowed_countries_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation putAccountAllowedCountriesAsyncWithHttpInfo
     *
     * Set Allowed Countries
     *
     * @param  \CheckNetworks\Model\PutAccountAllowedCountriesRequest $put_account_allowed_countries_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function putAccountAllowedCountriesAsyncWithHttpInfo($put_account_allowed_countries_request, $idempotency_key = null, string $contentType = self::contentTypes['putAccountAllowedCountries'][0])
    {
        $returnType = '\CheckNetworks\Model\GetAccountAllowedCountries200Response';
        $request = $this->putAccountAllowedCountriesRequest($put_account_allowed_countries_request, $idempotency_key, $contentType);

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
     * Create request for operation 'putAccountAllowedCountries'
     *
     * @param  \CheckNetworks\Model\PutAccountAllowedCountriesRequest $put_account_allowed_countries_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['putAccountAllowedCountries'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function putAccountAllowedCountriesRequest($put_account_allowed_countries_request, $idempotency_key = null, string $contentType = self::contentTypes['putAccountAllowedCountries'][0])
    {

        // verify the required parameter 'put_account_allowed_countries_request' is set
        if ($put_account_allowed_countries_request === null || (is_array($put_account_allowed_countries_request) && count($put_account_allowed_countries_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $put_account_allowed_countries_request when calling putAccountAllowedCountries'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.putAccountAllowedCountries, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/account/allowed-countries';
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
        if (isset($put_account_allowed_countries_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($put_account_allowed_countries_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $put_account_allowed_countries_request;
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
     * Operation resumeTrunk
     *
     * Resume SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['resumeTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function resumeTrunk($username, $idempotency_key = null, string $contentType = self::contentTypes['resumeTrunk'][0])
    {
        list($response) = $this->resumeTrunkWithHttpInfo($username, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation resumeTrunkWithHttpInfo
     *
     * Resume SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['resumeTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function resumeTrunkWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['resumeTrunk'][0])
    {
        $request = $this->resumeTrunkRequest($username, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\GetTrunk200Response',
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
                '\CheckNetworks\Model\GetTrunk200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetTrunk200Response',
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
     * Operation resumeTrunkAsync
     *
     * Resume SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['resumeTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function resumeTrunkAsync($username, $idempotency_key = null, string $contentType = self::contentTypes['resumeTrunk'][0])
    {
        return $this->resumeTrunkAsyncWithHttpInfo($username, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation resumeTrunkAsyncWithHttpInfo
     *
     * Resume SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['resumeTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function resumeTrunkAsyncWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['resumeTrunk'][0])
    {
        $returnType = '\CheckNetworks\Model\GetTrunk200Response';
        $request = $this->resumeTrunkRequest($username, $idempotency_key, $contentType);

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
     * Create request for operation 'resumeTrunk'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['resumeTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function resumeTrunkRequest($username, $idempotency_key = null, string $contentType = self::contentTypes['resumeTrunk'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling resumeTrunk'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.resumeTrunk, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/resume';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation revealTrunkCredential
     *
     * Reveal Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['revealTrunkCredential'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\RevealTrunkCredential200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function revealTrunkCredential($username, string $contentType = self::contentTypes['revealTrunkCredential'][0])
    {
        list($response) = $this->revealTrunkCredentialWithHttpInfo($username, $contentType);
        return $response;
    }

    /**
     * Operation revealTrunkCredentialWithHttpInfo
     *
     * Reveal Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['revealTrunkCredential'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\RevealTrunkCredential200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function revealTrunkCredentialWithHttpInfo($username, string $contentType = self::contentTypes['revealTrunkCredential'][0])
    {
        $request = $this->revealTrunkCredentialRequest($username, $contentType);

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
                        '\CheckNetworks\Model\RevealTrunkCredential200Response',
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
                '\CheckNetworks\Model\RevealTrunkCredential200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\RevealTrunkCredential200Response',
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
     * Operation revealTrunkCredentialAsync
     *
     * Reveal Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['revealTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function revealTrunkCredentialAsync($username, string $contentType = self::contentTypes['revealTrunkCredential'][0])
    {
        return $this->revealTrunkCredentialAsyncWithHttpInfo($username, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation revealTrunkCredentialAsyncWithHttpInfo
     *
     * Reveal Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['revealTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function revealTrunkCredentialAsyncWithHttpInfo($username, string $contentType = self::contentTypes['revealTrunkCredential'][0])
    {
        $returnType = '\CheckNetworks\Model\RevealTrunkCredential200Response';
        $request = $this->revealTrunkCredentialRequest($username, $contentType);

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
     * Create request for operation 'revealTrunkCredential'
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['revealTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function revealTrunkCredentialRequest($username, string $contentType = self::contentTypes['revealTrunkCredential'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling revealTrunkCredential'
            );
        }


        $resourcePath = '/trunks/{username}/credential';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation rotateTrunkCredential
     *
     * Rotate Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['rotateTrunkCredential'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\RevealTrunkCredential200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function rotateTrunkCredential($username, $idempotency_key = null, string $contentType = self::contentTypes['rotateTrunkCredential'][0])
    {
        list($response) = $this->rotateTrunkCredentialWithHttpInfo($username, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation rotateTrunkCredentialWithHttpInfo
     *
     * Rotate Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['rotateTrunkCredential'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\RevealTrunkCredential200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function rotateTrunkCredentialWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['rotateTrunkCredential'][0])
    {
        $request = $this->rotateTrunkCredentialRequest($username, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\RevealTrunkCredential200Response',
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
                '\CheckNetworks\Model\RevealTrunkCredential200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\RevealTrunkCredential200Response',
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
     * Operation rotateTrunkCredentialAsync
     *
     * Rotate Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['rotateTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function rotateTrunkCredentialAsync($username, $idempotency_key = null, string $contentType = self::contentTypes['rotateTrunkCredential'][0])
    {
        return $this->rotateTrunkCredentialAsyncWithHttpInfo($username, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation rotateTrunkCredentialAsyncWithHttpInfo
     *
     * Rotate Trunk SIP Password
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['rotateTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function rotateTrunkCredentialAsyncWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['rotateTrunkCredential'][0])
    {
        $returnType = '\CheckNetworks\Model\RevealTrunkCredential200Response';
        $request = $this->rotateTrunkCredentialRequest($username, $idempotency_key, $contentType);

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
     * Create request for operation 'rotateTrunkCredential'
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['rotateTrunkCredential'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function rotateTrunkCredentialRequest($username, $idempotency_key = null, string $contentType = self::contentTypes['rotateTrunkCredential'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling rotateTrunkCredential'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.rotateTrunkCredential, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/credential/rotate';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation setTrunkSourceRestrictions
     *
     * Set Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  \CheckNetworks\Model\SetTrunkSourceRestrictionsRequest $set_trunk_source_restrictions_request set_trunk_source_restrictions_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetTrunkSourceRestrictions200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function setTrunkSourceRestrictions($username, $set_trunk_source_restrictions_request, $idempotency_key = null, string $contentType = self::contentTypes['setTrunkSourceRestrictions'][0])
    {
        list($response) = $this->setTrunkSourceRestrictionsWithHttpInfo($username, $set_trunk_source_restrictions_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation setTrunkSourceRestrictionsWithHttpInfo
     *
     * Set Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  \CheckNetworks\Model\SetTrunkSourceRestrictionsRequest $set_trunk_source_restrictions_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetTrunkSourceRestrictions200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function setTrunkSourceRestrictionsWithHttpInfo($username, $set_trunk_source_restrictions_request, $idempotency_key = null, string $contentType = self::contentTypes['setTrunkSourceRestrictions'][0])
    {
        $request = $this->setTrunkSourceRestrictionsRequest($username, $set_trunk_source_restrictions_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response',
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
                '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response',
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
     * Operation setTrunkSourceRestrictionsAsync
     *
     * Set Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  \CheckNetworks\Model\SetTrunkSourceRestrictionsRequest $set_trunk_source_restrictions_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function setTrunkSourceRestrictionsAsync($username, $set_trunk_source_restrictions_request, $idempotency_key = null, string $contentType = self::contentTypes['setTrunkSourceRestrictions'][0])
    {
        return $this->setTrunkSourceRestrictionsAsyncWithHttpInfo($username, $set_trunk_source_restrictions_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation setTrunkSourceRestrictionsAsyncWithHttpInfo
     *
     * Set Trunk Source Restrictions
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  \CheckNetworks\Model\SetTrunkSourceRestrictionsRequest $set_trunk_source_restrictions_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function setTrunkSourceRestrictionsAsyncWithHttpInfo($username, $set_trunk_source_restrictions_request, $idempotency_key = null, string $contentType = self::contentTypes['setTrunkSourceRestrictions'][0])
    {
        $returnType = '\CheckNetworks\Model\GetTrunkSourceRestrictions200Response';
        $request = $this->setTrunkSourceRestrictionsRequest($username, $set_trunk_source_restrictions_request, $idempotency_key, $contentType);

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
     * Create request for operation 'setTrunkSourceRestrictions'
     *
     * @param  string $username The trunk&#39;s SIP digest username. (required)
     * @param  \CheckNetworks\Model\SetTrunkSourceRestrictionsRequest $set_trunk_source_restrictions_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['setTrunkSourceRestrictions'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function setTrunkSourceRestrictionsRequest($username, $set_trunk_source_restrictions_request, $idempotency_key = null, string $contentType = self::contentTypes['setTrunkSourceRestrictions'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling setTrunkSourceRestrictions'
            );
        }

        // verify the required parameter 'set_trunk_source_restrictions_request' is set
        if ($set_trunk_source_restrictions_request === null || (is_array($set_trunk_source_restrictions_request) && count($set_trunk_source_restrictions_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $set_trunk_source_restrictions_request when calling setTrunkSourceRestrictions'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.setTrunkSourceRestrictions, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/source-restrictions';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($set_trunk_source_restrictions_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($set_trunk_source_restrictions_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $set_trunk_source_restrictions_request;
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
     * Operation suspendTrunk
     *
     * Suspend SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['suspendTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function suspendTrunk($username, $idempotency_key = null, string $contentType = self::contentTypes['suspendTrunk'][0])
    {
        list($response) = $this->suspendTrunkWithHttpInfo($username, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation suspendTrunkWithHttpInfo
     *
     * Suspend SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['suspendTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\GetTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function suspendTrunkWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['suspendTrunk'][0])
    {
        $request = $this->suspendTrunkRequest($username, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Model\GetTrunk200Response',
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
                '\CheckNetworks\Model\GetTrunk200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\GetTrunk200Response',
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
     * Operation suspendTrunkAsync
     *
     * Suspend SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['suspendTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function suspendTrunkAsync($username, $idempotency_key = null, string $contentType = self::contentTypes['suspendTrunk'][0])
    {
        return $this->suspendTrunkAsyncWithHttpInfo($username, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation suspendTrunkAsyncWithHttpInfo
     *
     * Suspend SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['suspendTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function suspendTrunkAsyncWithHttpInfo($username, $idempotency_key = null, string $contentType = self::contentTypes['suspendTrunk'][0])
    {
        $returnType = '\CheckNetworks\Model\GetTrunk200Response';
        $request = $this->suspendTrunkRequest($username, $idempotency_key, $contentType);

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
     * Create request for operation 'suspendTrunk'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['suspendTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function suspendTrunkRequest($username, $idempotency_key = null, string $contentType = self::contentTypes['suspendTrunk'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling suspendTrunk'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.suspendTrunk, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/trunks/{username}/suspend';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
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
     * Operation updateTrunk
     *
     * Update SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateTrunkRequest|null $update_trunk_request update_trunk_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Model\UpdateTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error
     */
    public function updateTrunk($username, $idempotency_key = null, $update_trunk_request = null, string $contentType = self::contentTypes['updateTrunk'][0])
    {
        list($response) = $this->updateTrunkWithHttpInfo($username, $idempotency_key, $update_trunk_request, $contentType);
        return $response;
    }

    /**
     * Operation updateTrunkWithHttpInfo
     *
     * Update SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateTrunkRequest|null $update_trunk_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateTrunk'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Model\UpdateTrunk200Response|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error|\CheckNetworks\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function updateTrunkWithHttpInfo($username, $idempotency_key = null, $update_trunk_request = null, string $contentType = self::contentTypes['updateTrunk'][0])
    {
        $request = $this->updateTrunkRequest($username, $idempotency_key, $update_trunk_request, $contentType);

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
                        '\CheckNetworks\Model\UpdateTrunk200Response',
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
                '\CheckNetworks\Model\UpdateTrunk200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Model\UpdateTrunk200Response',
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
     * Operation updateTrunkAsync
     *
     * Update SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateTrunkRequest|null $update_trunk_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function updateTrunkAsync($username, $idempotency_key = null, $update_trunk_request = null, string $contentType = self::contentTypes['updateTrunk'][0])
    {
        return $this->updateTrunkAsyncWithHttpInfo($username, $idempotency_key, $update_trunk_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation updateTrunkAsyncWithHttpInfo
     *
     * Update SIP Trunk
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateTrunkRequest|null $update_trunk_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function updateTrunkAsyncWithHttpInfo($username, $idempotency_key = null, $update_trunk_request = null, string $contentType = self::contentTypes['updateTrunk'][0])
    {
        $returnType = '\CheckNetworks\Model\UpdateTrunk200Response';
        $request = $this->updateTrunkRequest($username, $idempotency_key, $update_trunk_request, $contentType);

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
     * Create request for operation 'updateTrunk'
     *
     * @param  string $username The trunk&#39;s &#x60;id&#x60;, or its SIP digest username. A trunk with no SIP credential has only its &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  \CheckNetworks\Model\UpdateTrunkRequest|null $update_trunk_request (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updateTrunk'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function updateTrunkRequest($username, $idempotency_key = null, $update_trunk_request = null, string $contentType = self::contentTypes['updateTrunk'][0])
    {

        // verify the required parameter 'username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $username when calling updateTrunk'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling SIPTrunksApi.updateTrunk, must be smaller than or equal to 255.');
        }
        


        $resourcePath = '/trunks/{username}';
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
        if ($username !== null) {
            $resourcePath = str_replace(
                '{username}',
                ObjectSerializer::toPathValue($username),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($update_trunk_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($update_trunk_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $update_trunk_request;
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
