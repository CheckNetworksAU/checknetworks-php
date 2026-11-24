<?php
/**
 * PartnersApi
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
 * PartnersApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class PartnersApi
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
        'createPartnerInvite' => [
            'application/json',
        ],
        'deletePartner' => [
            'application/json',
        ],
        'deletePartnerInvite' => [
            'application/json',
        ],
        'getPartnerCommissionRates' => [
            'application/json',
        ],
        'getPartnerCommissionRun' => [
            'application/json',
        ],
        'listPartnerCommissionClawbacks' => [
            'application/json',
        ],
        'listPartnerCommissionRuns' => [
            'application/json',
        ],
        'listPartnerInvites' => [
            'application/json',
        ],
        'listPartners' => [
            'application/json',
        ],
        'updatePartnerScope' => [
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
     * Operation createPartnerInvite
     *
     * Invite A Partner
     *
     * @param  \CheckNetworks\Api\Generated\Model\CreatePartnerInviteRequest $create_partner_invite_request create_partner_invite_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createPartnerInvite'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\CreatePartnerInvite201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function createPartnerInvite($create_partner_invite_request, $idempotency_key = null, string $contentType = self::contentTypes['createPartnerInvite'][0])
    {
        list($response) = $this->createPartnerInviteWithHttpInfo($create_partner_invite_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation createPartnerInviteWithHttpInfo
     *
     * Invite A Partner
     *
     * @param  \CheckNetworks\Api\Generated\Model\CreatePartnerInviteRequest $create_partner_invite_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createPartnerInvite'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\CreatePartnerInvite201Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function createPartnerInviteWithHttpInfo($create_partner_invite_request, $idempotency_key = null, string $contentType = self::contentTypes['createPartnerInvite'][0])
    {
        $request = $this->createPartnerInviteRequest($create_partner_invite_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\CreatePartnerInvite201Response',
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
                '\CheckNetworks\Api\Generated\Model\CreatePartnerInvite201Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 201:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\CreatePartnerInvite201Response',
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
     * Operation createPartnerInviteAsync
     *
     * Invite A Partner
     *
     * @param  \CheckNetworks\Api\Generated\Model\CreatePartnerInviteRequest $create_partner_invite_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createPartnerInvite'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function createPartnerInviteAsync($create_partner_invite_request, $idempotency_key = null, string $contentType = self::contentTypes['createPartnerInvite'][0])
    {
        return $this->createPartnerInviteAsyncWithHttpInfo($create_partner_invite_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation createPartnerInviteAsyncWithHttpInfo
     *
     * Invite A Partner
     *
     * @param  \CheckNetworks\Api\Generated\Model\CreatePartnerInviteRequest $create_partner_invite_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createPartnerInvite'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function createPartnerInviteAsyncWithHttpInfo($create_partner_invite_request, $idempotency_key = null, string $contentType = self::contentTypes['createPartnerInvite'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\CreatePartnerInvite201Response';
        $request = $this->createPartnerInviteRequest($create_partner_invite_request, $idempotency_key, $contentType);

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
     * Create request for operation 'createPartnerInvite'
     *
     * @param  \CheckNetworks\Api\Generated\Model\CreatePartnerInviteRequest $create_partner_invite_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['createPartnerInvite'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function createPartnerInviteRequest($create_partner_invite_request, $idempotency_key = null, string $contentType = self::contentTypes['createPartnerInvite'][0])
    {

        // verify the required parameter 'create_partner_invite_request' is set
        if ($create_partner_invite_request === null || (is_array($create_partner_invite_request) && count($create_partner_invite_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $create_partner_invite_request when calling createPartnerInvite'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling PartnersApi.createPartnerInvite, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/partners/invites';
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
        if (isset($create_partner_invite_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($create_partner_invite_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $create_partner_invite_request;
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
     * Operation deletePartner
     *
     * Remove A Partner
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartner'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function deletePartner($partner_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartner'][0])
    {
        $this->deletePartnerWithHttpInfo($partner_id, $idempotency_key, $contentType);
    }

    /**
     * Operation deletePartnerWithHttpInfo
     *
     * Remove A Partner
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartner'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function deletePartnerWithHttpInfo($partner_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartner'][0])
    {
        $request = $this->deletePartnerRequest($partner_id, $idempotency_key, $contentType);

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
     * Operation deletePartnerAsync
     *
     * Remove A Partner
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartner'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deletePartnerAsync($partner_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartner'][0])
    {
        return $this->deletePartnerAsyncWithHttpInfo($partner_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation deletePartnerAsyncWithHttpInfo
     *
     * Remove A Partner
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartner'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deletePartnerAsyncWithHttpInfo($partner_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartner'][0])
    {
        $returnType = '';
        $request = $this->deletePartnerRequest($partner_id, $idempotency_key, $contentType);

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
     * Create request for operation 'deletePartner'
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartner'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function deletePartnerRequest($partner_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartner'][0])
    {

        // verify the required parameter 'partner_id' is set
        if ($partner_id === null || (is_array($partner_id) && count($partner_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $partner_id when calling deletePartner'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling PartnersApi.deletePartner, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/partners/{partnerId}';
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
        if ($partner_id !== null) {
            $resourcePath = str_replace(
                '{partnerId}',
                ObjectSerializer::toPathValue($partner_id),
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
     * Operation deletePartnerInvite
     *
     * Withdraw A Partner Invitation
     *
     * @param  string $invite_id The invitation&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartnerInvite'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function deletePartnerInvite($invite_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartnerInvite'][0])
    {
        $this->deletePartnerInviteWithHttpInfo($invite_id, $idempotency_key, $contentType);
    }

    /**
     * Operation deletePartnerInviteWithHttpInfo
     *
     * Withdraw A Partner Invitation
     *
     * @param  string $invite_id The invitation&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartnerInvite'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function deletePartnerInviteWithHttpInfo($invite_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartnerInvite'][0])
    {
        $request = $this->deletePartnerInviteRequest($invite_id, $idempotency_key, $contentType);

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
     * Operation deletePartnerInviteAsync
     *
     * Withdraw A Partner Invitation
     *
     * @param  string $invite_id The invitation&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartnerInvite'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deletePartnerInviteAsync($invite_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartnerInvite'][0])
    {
        return $this->deletePartnerInviteAsyncWithHttpInfo($invite_id, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation deletePartnerInviteAsyncWithHttpInfo
     *
     * Withdraw A Partner Invitation
     *
     * @param  string $invite_id The invitation&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartnerInvite'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function deletePartnerInviteAsyncWithHttpInfo($invite_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartnerInvite'][0])
    {
        $returnType = '';
        $request = $this->deletePartnerInviteRequest($invite_id, $idempotency_key, $contentType);

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
     * Create request for operation 'deletePartnerInvite'
     *
     * @param  string $invite_id The invitation&#39;s &#x60;id&#x60;. (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['deletePartnerInvite'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function deletePartnerInviteRequest($invite_id, $idempotency_key = null, string $contentType = self::contentTypes['deletePartnerInvite'][0])
    {

        // verify the required parameter 'invite_id' is set
        if ($invite_id === null || (is_array($invite_id) && count($invite_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $invite_id when calling deletePartnerInvite'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling PartnersApi.deletePartnerInvite, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/partners/invites/{inviteId}';
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
        if ($invite_id !== null) {
            $resourcePath = str_replace(
                '{inviteId}',
                ObjectSerializer::toPathValue($invite_id),
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
     * Operation getPartnerCommissionRates
     *
     * Get Partner Commission Rates
     *
     * @param  string $partner_id The partner&#39;s user id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRates'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetPartnerCommissionRates200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getPartnerCommissionRates($partner_id, string $contentType = self::contentTypes['getPartnerCommissionRates'][0])
    {
        list($response) = $this->getPartnerCommissionRatesWithHttpInfo($partner_id, $contentType);
        return $response;
    }

    /**
     * Operation getPartnerCommissionRatesWithHttpInfo
     *
     * Get Partner Commission Rates
     *
     * @param  string $partner_id The partner&#39;s user id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRates'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetPartnerCommissionRates200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getPartnerCommissionRatesWithHttpInfo($partner_id, string $contentType = self::contentTypes['getPartnerCommissionRates'][0])
    {
        $request = $this->getPartnerCommissionRatesRequest($partner_id, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRates200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRates200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRates200Response',
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
     * Operation getPartnerCommissionRatesAsync
     *
     * Get Partner Commission Rates
     *
     * @param  string $partner_id The partner&#39;s user id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRates'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPartnerCommissionRatesAsync($partner_id, string $contentType = self::contentTypes['getPartnerCommissionRates'][0])
    {
        return $this->getPartnerCommissionRatesAsyncWithHttpInfo($partner_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getPartnerCommissionRatesAsyncWithHttpInfo
     *
     * Get Partner Commission Rates
     *
     * @param  string $partner_id The partner&#39;s user id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRates'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPartnerCommissionRatesAsyncWithHttpInfo($partner_id, string $contentType = self::contentTypes['getPartnerCommissionRates'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRates200Response';
        $request = $this->getPartnerCommissionRatesRequest($partner_id, $contentType);

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
     * Create request for operation 'getPartnerCommissionRates'
     *
     * @param  string $partner_id The partner&#39;s user id. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRates'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getPartnerCommissionRatesRequest($partner_id, string $contentType = self::contentTypes['getPartnerCommissionRates'][0])
    {

        // verify the required parameter 'partner_id' is set
        if ($partner_id === null || (is_array($partner_id) && count($partner_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $partner_id when calling getPartnerCommissionRates'
            );
        }


        $resourcePath = '/partners/{partnerId}/commission-rates';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($partner_id !== null) {
            $resourcePath = str_replace(
                '{partnerId}',
                ObjectSerializer::toPathValue($partner_id),
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
     * Operation getPartnerCommissionRun
     *
     * Get Commission Run
     *
     * @param  string $run_id The run&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRun'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetPartnerCommissionRun200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getPartnerCommissionRun($run_id, string $contentType = self::contentTypes['getPartnerCommissionRun'][0])
    {
        list($response) = $this->getPartnerCommissionRunWithHttpInfo($run_id, $contentType);
        return $response;
    }

    /**
     * Operation getPartnerCommissionRunWithHttpInfo
     *
     * Get Commission Run
     *
     * @param  string $run_id The run&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRun'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetPartnerCommissionRun200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getPartnerCommissionRunWithHttpInfo($run_id, string $contentType = self::contentTypes['getPartnerCommissionRun'][0])
    {
        $request = $this->getPartnerCommissionRunRequest($run_id, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRun200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRun200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRun200Response',
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
     * Operation getPartnerCommissionRunAsync
     *
     * Get Commission Run
     *
     * @param  string $run_id The run&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRun'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPartnerCommissionRunAsync($run_id, string $contentType = self::contentTypes['getPartnerCommissionRun'][0])
    {
        return $this->getPartnerCommissionRunAsyncWithHttpInfo($run_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getPartnerCommissionRunAsyncWithHttpInfo
     *
     * Get Commission Run
     *
     * @param  string $run_id The run&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRun'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPartnerCommissionRunAsyncWithHttpInfo($run_id, string $contentType = self::contentTypes['getPartnerCommissionRun'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetPartnerCommissionRun200Response';
        $request = $this->getPartnerCommissionRunRequest($run_id, $contentType);

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
     * Create request for operation 'getPartnerCommissionRun'
     *
     * @param  string $run_id The run&#39;s &#x60;id&#x60; (a uuid). (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPartnerCommissionRun'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getPartnerCommissionRunRequest($run_id, string $contentType = self::contentTypes['getPartnerCommissionRun'][0])
    {

        // verify the required parameter 'run_id' is set
        if ($run_id === null || (is_array($run_id) && count($run_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $run_id when calling getPartnerCommissionRun'
            );
        }


        $resourcePath = '/partners/commission-runs/{runId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($run_id !== null) {
            $resourcePath = str_replace(
                '{runId}',
                ObjectSerializer::toPathValue($run_id),
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
     * Operation listPartnerCommissionClawbacks
     *
     * List Outstanding Clawbacks
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionClawbacks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListPartnerCommissionClawbacks200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function listPartnerCommissionClawbacks($partner = null, string $contentType = self::contentTypes['listPartnerCommissionClawbacks'][0])
    {
        list($response) = $this->listPartnerCommissionClawbacksWithHttpInfo($partner, $contentType);
        return $response;
    }

    /**
     * Operation listPartnerCommissionClawbacksWithHttpInfo
     *
     * List Outstanding Clawbacks
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionClawbacks'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListPartnerCommissionClawbacks200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listPartnerCommissionClawbacksWithHttpInfo($partner = null, string $contentType = self::contentTypes['listPartnerCommissionClawbacks'][0])
    {
        $request = $this->listPartnerCommissionClawbacksRequest($partner, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionClawbacks200Response',
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
                '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionClawbacks200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionClawbacks200Response',
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
     * Operation listPartnerCommissionClawbacksAsync
     *
     * List Outstanding Clawbacks
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionClawbacks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnerCommissionClawbacksAsync($partner = null, string $contentType = self::contentTypes['listPartnerCommissionClawbacks'][0])
    {
        return $this->listPartnerCommissionClawbacksAsyncWithHttpInfo($partner, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listPartnerCommissionClawbacksAsyncWithHttpInfo
     *
     * List Outstanding Clawbacks
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionClawbacks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnerCommissionClawbacksAsyncWithHttpInfo($partner = null, string $contentType = self::contentTypes['listPartnerCommissionClawbacks'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionClawbacks200Response';
        $request = $this->listPartnerCommissionClawbacksRequest($partner, $contentType);

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
     * Create request for operation 'listPartnerCommissionClawbacks'
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionClawbacks'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listPartnerCommissionClawbacksRequest($partner = null, string $contentType = self::contentTypes['listPartnerCommissionClawbacks'][0])
    {



        $resourcePath = '/partners/commission-clawbacks';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $partner,
            'partner', // param base name
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
     * Operation listPartnerCommissionRuns
     *
     * List Commission Runs
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string|null $status status (optional)
     * @param  int|null $page page (optional, default to 1)
     * @param  int|null $per_page per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionRuns'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListPartnerCommissionRuns200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function listPartnerCommissionRuns($partner = null, $status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerCommissionRuns'][0])
    {
        list($response) = $this->listPartnerCommissionRunsWithHttpInfo($partner, $status, $page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation listPartnerCommissionRunsWithHttpInfo
     *
     * List Commission Runs
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionRuns'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListPartnerCommissionRuns200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listPartnerCommissionRunsWithHttpInfo($partner = null, $status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerCommissionRuns'][0])
    {
        $request = $this->listPartnerCommissionRunsRequest($partner, $status, $page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionRuns200Response',
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
                '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionRuns200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionRuns200Response',
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
     * Operation listPartnerCommissionRunsAsync
     *
     * List Commission Runs
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionRuns'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnerCommissionRunsAsync($partner = null, $status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerCommissionRuns'][0])
    {
        return $this->listPartnerCommissionRunsAsyncWithHttpInfo($partner, $status, $page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listPartnerCommissionRunsAsyncWithHttpInfo
     *
     * List Commission Runs
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionRuns'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnerCommissionRunsAsyncWithHttpInfo($partner = null, $status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerCommissionRuns'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListPartnerCommissionRuns200Response';
        $request = $this->listPartnerCommissionRunsRequest($partner, $status, $page, $per_page, $contentType);

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
     * Create request for operation 'listPartnerCommissionRuns'
     *
     * @param  string|null $partner One partner&#39;s user id. One that is not your partner answers &#x60;422&#x60;. (optional)
     * @param  string|null $status (optional)
     * @param  int|null $page (optional, default to 1)
     * @param  int|null $per_page (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerCommissionRuns'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listPartnerCommissionRunsRequest($partner = null, $status = null, $page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerCommissionRuns'][0])
    {



        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling PartnersApi.listPartnerCommissionRuns, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling PartnersApi.listPartnerCommissionRuns, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling PartnersApi.listPartnerCommissionRuns, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/partners/commission-runs';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $partner,
            'partner', // param base name
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
     * Operation listPartnerInvites
     *
     * List Partner Invitations
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerInvites'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListPartnerInvites200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function listPartnerInvites($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerInvites'][0])
    {
        list($response) = $this->listPartnerInvitesWithHttpInfo($page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation listPartnerInvitesWithHttpInfo
     *
     * List Partner Invitations
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerInvites'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListPartnerInvites200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listPartnerInvitesWithHttpInfo($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerInvites'][0])
    {
        $request = $this->listPartnerInvitesRequest($page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListPartnerInvites200Response',
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
                '\CheckNetworks\Api\Generated\Model\ListPartnerInvites200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListPartnerInvites200Response',
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
     * Operation listPartnerInvitesAsync
     *
     * List Partner Invitations
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerInvites'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnerInvitesAsync($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerInvites'][0])
    {
        return $this->listPartnerInvitesAsyncWithHttpInfo($page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listPartnerInvitesAsyncWithHttpInfo
     *
     * List Partner Invitations
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerInvites'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnerInvitesAsyncWithHttpInfo($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerInvites'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListPartnerInvites200Response';
        $request = $this->listPartnerInvitesRequest($page, $per_page, $contentType);

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
     * Create request for operation 'listPartnerInvites'
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartnerInvites'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listPartnerInvitesRequest($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartnerInvites'][0])
    {

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling PartnersApi.listPartnerInvites, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling PartnersApi.listPartnerInvites, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling PartnersApi.listPartnerInvites, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/partners/invites';
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
     * Operation listPartners
     *
     * List Partners
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartners'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListPartners200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function listPartners($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartners'][0])
    {
        list($response) = $this->listPartnersWithHttpInfo($page, $per_page, $contentType);
        return $response;
    }

    /**
     * Operation listPartnersWithHttpInfo
     *
     * List Partners
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartners'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListPartners200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function listPartnersWithHttpInfo($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartners'][0])
    {
        $request = $this->listPartnersRequest($page, $per_page, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListPartners200Response',
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
                '\CheckNetworks\Api\Generated\Model\ListPartners200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListPartners200Response',
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
     * Operation listPartnersAsync
     *
     * List Partners
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartners'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnersAsync($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartners'][0])
    {
        return $this->listPartnersAsyncWithHttpInfo($page, $per_page, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation listPartnersAsyncWithHttpInfo
     *
     * List Partners
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartners'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function listPartnersAsyncWithHttpInfo($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartners'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListPartners200Response';
        $request = $this->listPartnersRequest($page, $per_page, $contentType);

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
     * Create request for operation 'listPartners'
     *
     * @param  int|null $page The page to return, from &#x60;1&#x60;. A value that is not a whole number of at least 1 is refused with &#x60;422 - validation_failed&#x60; naming &#x60;page&#x60;, never changed. (optional, default to 1)
     * @param  int|null $per_page Rows per page: a whole number from &#x60;1&#x60; to &#x60;200&#x60;, &#x60;50&#x60; when left out. A value outside that range, or not a whole number, is refused with &#x60;422 - validation_failed&#x60; naming &#x60;perPage&#x60;, never reduced or rounded. (optional, default to 50)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['listPartners'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function listPartnersRequest($page = 1, $per_page = 50, string $contentType = self::contentTypes['listPartners'][0])
    {

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('invalid value for "$page" when calling PartnersApi.listPartners, must be bigger than or equal to 1.');
        }
        
        if ($per_page !== null && $per_page > 200) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling PartnersApi.listPartners, must be smaller than or equal to 200.');
        }
        if ($per_page !== null && $per_page < 1) {
            throw new \InvalidArgumentException('invalid value for "$per_page" when calling PartnersApi.listPartners, must be bigger than or equal to 1.');
        }
        

        $resourcePath = '/partners';
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
     * Operation updatePartnerScope
     *
     * Set A Partner&#39;s Scope
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Api\Generated\Model\UpdatePartnerScopeRequest $update_partner_scope_request update_partner_scope_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updatePartnerScope'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\UpdatePartnerScope200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function updatePartnerScope($partner_id, $update_partner_scope_request, $idempotency_key = null, string $contentType = self::contentTypes['updatePartnerScope'][0])
    {
        list($response) = $this->updatePartnerScopeWithHttpInfo($partner_id, $update_partner_scope_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation updatePartnerScopeWithHttpInfo
     *
     * Set A Partner&#39;s Scope
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Api\Generated\Model\UpdatePartnerScopeRequest $update_partner_scope_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updatePartnerScope'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\UpdatePartnerScope200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function updatePartnerScopeWithHttpInfo($partner_id, $update_partner_scope_request, $idempotency_key = null, string $contentType = self::contentTypes['updatePartnerScope'][0])
    {
        $request = $this->updatePartnerScopeRequest($partner_id, $update_partner_scope_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\UpdatePartnerScope200Response',
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
                '\CheckNetworks\Api\Generated\Model\UpdatePartnerScope200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\UpdatePartnerScope200Response',
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
     * Operation updatePartnerScopeAsync
     *
     * Set A Partner&#39;s Scope
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Api\Generated\Model\UpdatePartnerScopeRequest $update_partner_scope_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updatePartnerScope'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function updatePartnerScopeAsync($partner_id, $update_partner_scope_request, $idempotency_key = null, string $contentType = self::contentTypes['updatePartnerScope'][0])
    {
        return $this->updatePartnerScopeAsyncWithHttpInfo($partner_id, $update_partner_scope_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation updatePartnerScopeAsyncWithHttpInfo
     *
     * Set A Partner&#39;s Scope
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Api\Generated\Model\UpdatePartnerScopeRequest $update_partner_scope_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updatePartnerScope'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function updatePartnerScopeAsyncWithHttpInfo($partner_id, $update_partner_scope_request, $idempotency_key = null, string $contentType = self::contentTypes['updatePartnerScope'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\UpdatePartnerScope200Response';
        $request = $this->updatePartnerScopeRequest($partner_id, $update_partner_scope_request, $idempotency_key, $contentType);

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
     * Create request for operation 'updatePartnerScope'
     *
     * @param  string $partner_id The partner&#39;s &#x60;id&#x60;. (required)
     * @param  \CheckNetworks\Api\Generated\Model\UpdatePartnerScopeRequest $update_partner_scope_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['updatePartnerScope'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function updatePartnerScopeRequest($partner_id, $update_partner_scope_request, $idempotency_key = null, string $contentType = self::contentTypes['updatePartnerScope'][0])
    {

        // verify the required parameter 'partner_id' is set
        if ($partner_id === null || (is_array($partner_id) && count($partner_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $partner_id when calling updatePartnerScope'
            );
        }

        // verify the required parameter 'update_partner_scope_request' is set
        if ($update_partner_scope_request === null || (is_array($update_partner_scope_request) && count($update_partner_scope_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $update_partner_scope_request when calling updatePartnerScope'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling PartnersApi.updatePartnerScope, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/partners/{partnerId}/scope';
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
        if ($partner_id !== null) {
            $resourcePath = str_replace(
                '{partnerId}',
                ObjectSerializer::toPathValue($partner_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($update_partner_scope_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($update_partner_scope_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $update_partner_scope_request;
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
