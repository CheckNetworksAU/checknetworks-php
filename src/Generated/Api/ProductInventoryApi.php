<?php
/**
 * ProductInventoryApi
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
 * ProductInventoryApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class ProductInventoryApi
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
        'getActiveProducts' => [
            'application/json',
        ],
        'getInProgressProducts' => [
            'application/json',
        ],
        'getProduct' => [
            'application/json',
        ],
        'getProductViaService' => [
            'application/json',
        ],
        'getProducts' => [
            'application/json',
        ],
        'getRecentlyDisconnectedProducts' => [
            'application/json',
        ],
        'getRelatedOrders' => [
            'application/json',
        ],
        'patchProduct' => [
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
     * Operation getActiveProducts
     *
     * List the &#39;Active&#39; Product Instances
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getActiveProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getActiveProducts($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getActiveProducts'][0])
    {
        list($response) = $this->getActiveProductsWithHttpInfo($product_type, $tech_type, $contentType);
        return $response;
    }

    /**
     * Operation getActiveProductsWithHttpInfo
     *
     * List the &#39;Active&#39; Product Instances
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getActiveProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getActiveProductsWithHttpInfo($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getActiveProducts'][0])
    {
        $request = $this->getActiveProductsRequest($product_type, $tech_type, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
                '\CheckNetworks\Api\Generated\Model\ListProducts',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
     * Operation getActiveProductsAsync
     *
     * List the &#39;Active&#39; Product Instances
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getActiveProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getActiveProductsAsync($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getActiveProducts'][0])
    {
        return $this->getActiveProductsAsyncWithHttpInfo($product_type, $tech_type, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getActiveProductsAsyncWithHttpInfo
     *
     * List the &#39;Active&#39; Product Instances
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getActiveProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getActiveProductsAsyncWithHttpInfo($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getActiveProducts'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListProducts';
        $request = $this->getActiveProductsRequest($product_type, $tech_type, $contentType);

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
     * Create request for operation 'getActiveProducts'
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getActiveProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getActiveProductsRequest($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getActiveProducts'][0])
    {




        $resourcePath = '/nbn/products/active';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $product_type,
            'productType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $tech_type,
            'techType', // param base name
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
     * Operation getInProgressProducts
     *
     * List Product Instances still being provisioned (always empty)
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getInProgressProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getInProgressProducts($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getInProgressProducts'][0])
    {
        list($response) = $this->getInProgressProductsWithHttpInfo($product_type, $tech_type, $contentType);
        return $response;
    }

    /**
     * Operation getInProgressProductsWithHttpInfo
     *
     * List Product Instances still being provisioned (always empty)
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getInProgressProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getInProgressProductsWithHttpInfo($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getInProgressProducts'][0])
    {
        $request = $this->getInProgressProductsRequest($product_type, $tech_type, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
                '\CheckNetworks\Api\Generated\Model\ListProducts',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
     * Operation getInProgressProductsAsync
     *
     * List Product Instances still being provisioned (always empty)
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getInProgressProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getInProgressProductsAsync($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getInProgressProducts'][0])
    {
        return $this->getInProgressProductsAsyncWithHttpInfo($product_type, $tech_type, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getInProgressProductsAsyncWithHttpInfo
     *
     * List Product Instances still being provisioned (always empty)
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getInProgressProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getInProgressProductsAsyncWithHttpInfo($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getInProgressProducts'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListProducts';
        $request = $this->getInProgressProductsRequest($product_type, $tech_type, $contentType);

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
     * Create request for operation 'getInProgressProducts'
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getInProgressProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getInProgressProductsRequest($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getInProgressProducts'][0])
    {




        $resourcePath = '/nbn/products/in_progress';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $product_type,
            'productType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $tech_type,
            'techType', // param base name
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
     * Operation getProduct
     *
     * Retrieve the details of a nbn™ Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProduct'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetProductViaService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getProduct($product_id, string $contentType = self::contentTypes['getProduct'][0])
    {
        list($response) = $this->getProductWithHttpInfo($product_id, $contentType);
        return $response;
    }

    /**
     * Operation getProductWithHttpInfo
     *
     * Retrieve the details of a nbn™ Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProduct'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetProductViaService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getProductWithHttpInfo($product_id, string $contentType = self::contentTypes['getProduct'][0])
    {
        $request = $this->getProductRequest($product_id, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
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
     * Operation getProductAsync
     *
     * Retrieve the details of a nbn™ Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProduct'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getProductAsync($product_id, string $contentType = self::contentTypes['getProduct'][0])
    {
        return $this->getProductAsyncWithHttpInfo($product_id, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getProductAsyncWithHttpInfo
     *
     * Retrieve the details of a nbn™ Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProduct'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getProductAsyncWithHttpInfo($product_id, string $contentType = self::contentTypes['getProduct'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response';
        $request = $this->getProductRequest($product_id, $contentType);

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
     * Create request for operation 'getProduct'
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProduct'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getProductRequest($product_id, string $contentType = self::contentTypes['getProduct'][0])
    {

        // verify the required parameter 'product_id' is set
        if ($product_id === null || (is_array($product_id) && count($product_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $product_id when calling getProduct'
            );
        }


        $resourcePath = '/nbn/products/{productId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($product_id !== null) {
            $resourcePath = str_replace(
                '{productId}',
                ObjectSerializer::toPathValue($product_id),
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
     * Operation getProductViaService
     *
     * Retrieve Product Instance details via the Service Ref
     *
     * @param  string $service_ref The Service Reference (AVC #) of the Product Instance. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProductViaService'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetProductViaService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getProductViaService($service_ref, string $contentType = self::contentTypes['getProductViaService'][0])
    {
        list($response) = $this->getProductViaServiceWithHttpInfo($service_ref, $contentType);
        return $response;
    }

    /**
     * Operation getProductViaServiceWithHttpInfo
     *
     * Retrieve Product Instance details via the Service Ref
     *
     * @param  string $service_ref The Service Reference (AVC #) of the Product Instance. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProductViaService'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetProductViaService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getProductViaServiceWithHttpInfo($service_ref, string $contentType = self::contentTypes['getProductViaService'][0])
    {
        $request = $this->getProductViaServiceRequest($service_ref, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
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
     * Operation getProductViaServiceAsync
     *
     * Retrieve Product Instance details via the Service Ref
     *
     * @param  string $service_ref The Service Reference (AVC #) of the Product Instance. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProductViaService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getProductViaServiceAsync($service_ref, string $contentType = self::contentTypes['getProductViaService'][0])
    {
        return $this->getProductViaServiceAsyncWithHttpInfo($service_ref, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getProductViaServiceAsyncWithHttpInfo
     *
     * Retrieve Product Instance details via the Service Ref
     *
     * @param  string $service_ref The Service Reference (AVC #) of the Product Instance. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProductViaService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getProductViaServiceAsyncWithHttpInfo($service_ref, string $contentType = self::contentTypes['getProductViaService'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response';
        $request = $this->getProductViaServiceRequest($service_ref, $contentType);

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
     * Create request for operation 'getProductViaService'
     *
     * @param  string $service_ref The Service Reference (AVC #) of the Product Instance. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProductViaService'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getProductViaServiceRequest($service_ref, string $contentType = self::contentTypes['getProductViaService'][0])
    {

        // verify the required parameter 'service_ref' is set
        if ($service_ref === null || (is_array($service_ref) && count($service_ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $service_ref when calling getProductViaService'
            );
        }


        $resourcePath = '/nbn/products/service/{serviceRef}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($service_ref !== null) {
            $resourcePath = str_replace(
                '{serviceRef}',
                ObjectSerializer::toPathValue($service_ref),
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
     * Operation getProducts
     *
     * List Product Instances
     *
     * @param  string|null $status (Optional) Filter results to this Status. Defaults to \&quot;Active\&quot;. A product is only ever Active or Deleted; any other value, such as an order state (New, InProgress, Held, Pending), is &#x60;400&#x60;. (optional, default to 'Active')
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string|null $customer_ref (Optional) Filter results to this Customer Ref. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getProducts($status = 'Active', $product_type = null, $tech_type = null, $customer_ref = null, string $contentType = self::contentTypes['getProducts'][0])
    {
        list($response) = $this->getProductsWithHttpInfo($status, $product_type, $tech_type, $customer_ref, $contentType);
        return $response;
    }

    /**
     * Operation getProductsWithHttpInfo
     *
     * List Product Instances
     *
     * @param  string|null $status (Optional) Filter results to this Status. Defaults to \&quot;Active\&quot;. A product is only ever Active or Deleted; any other value, such as an order state (New, InProgress, Held, Pending), is &#x60;400&#x60;. (optional, default to 'Active')
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string|null $customer_ref (Optional) Filter results to this Customer Ref. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getProductsWithHttpInfo($status = 'Active', $product_type = null, $tech_type = null, $customer_ref = null, string $contentType = self::contentTypes['getProducts'][0])
    {
        $request = $this->getProductsRequest($status, $product_type, $tech_type, $customer_ref, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
                '\CheckNetworks\Api\Generated\Model\ListProducts',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
     * Operation getProductsAsync
     *
     * List Product Instances
     *
     * @param  string|null $status (Optional) Filter results to this Status. Defaults to \&quot;Active\&quot;. A product is only ever Active or Deleted; any other value, such as an order state (New, InProgress, Held, Pending), is &#x60;400&#x60;. (optional, default to 'Active')
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string|null $customer_ref (Optional) Filter results to this Customer Ref. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getProductsAsync($status = 'Active', $product_type = null, $tech_type = null, $customer_ref = null, string $contentType = self::contentTypes['getProducts'][0])
    {
        return $this->getProductsAsyncWithHttpInfo($status, $product_type, $tech_type, $customer_ref, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getProductsAsyncWithHttpInfo
     *
     * List Product Instances
     *
     * @param  string|null $status (Optional) Filter results to this Status. Defaults to \&quot;Active\&quot;. A product is only ever Active or Deleted; any other value, such as an order state (New, InProgress, Held, Pending), is &#x60;400&#x60;. (optional, default to 'Active')
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string|null $customer_ref (Optional) Filter results to this Customer Ref. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getProductsAsyncWithHttpInfo($status = 'Active', $product_type = null, $tech_type = null, $customer_ref = null, string $contentType = self::contentTypes['getProducts'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListProducts';
        $request = $this->getProductsRequest($status, $product_type, $tech_type, $customer_ref, $contentType);

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
     * Create request for operation 'getProducts'
     *
     * @param  string|null $status (Optional) Filter results to this Status. Defaults to \&quot;Active\&quot;. A product is only ever Active or Deleted; any other value, such as an order state (New, InProgress, Held, Pending), is &#x60;400&#x60;. (optional, default to 'Active')
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string|null $customer_ref (Optional) Filter results to this Customer Ref. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getProductsRequest($status = 'Active', $product_type = null, $tech_type = null, $customer_ref = null, string $contentType = self::contentTypes['getProducts'][0])
    {






        $resourcePath = '/nbn/products';
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
            $product_type,
            'productType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $tech_type,
            'techType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $customer_ref,
            'customerRef', // param base name
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
     * Operation getRecentlyDisconnectedProducts
     *
     * List Product Instances disconnected in the past seven days
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRecentlyDisconnectedProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getRecentlyDisconnectedProducts($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getRecentlyDisconnectedProducts'][0])
    {
        list($response) = $this->getRecentlyDisconnectedProductsWithHttpInfo($product_type, $tech_type, $contentType);
        return $response;
    }

    /**
     * Operation getRecentlyDisconnectedProductsWithHttpInfo
     *
     * List Product Instances disconnected in the past seven days
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRecentlyDisconnectedProducts'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\ListProducts|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getRecentlyDisconnectedProductsWithHttpInfo($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getRecentlyDisconnectedProducts'][0])
    {
        $request = $this->getRecentlyDisconnectedProductsRequest($product_type, $tech_type, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
                '\CheckNetworks\Api\Generated\Model\ListProducts',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\ListProducts',
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
     * Operation getRecentlyDisconnectedProductsAsync
     *
     * List Product Instances disconnected in the past seven days
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRecentlyDisconnectedProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getRecentlyDisconnectedProductsAsync($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getRecentlyDisconnectedProducts'][0])
    {
        return $this->getRecentlyDisconnectedProductsAsyncWithHttpInfo($product_type, $tech_type, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getRecentlyDisconnectedProductsAsyncWithHttpInfo
     *
     * List Product Instances disconnected in the past seven days
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRecentlyDisconnectedProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getRecentlyDisconnectedProductsAsyncWithHttpInfo($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getRecentlyDisconnectedProducts'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\ListProducts';
        $request = $this->getRecentlyDisconnectedProductsRequest($product_type, $tech_type, $contentType);

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
     * Create request for operation 'getRecentlyDisconnectedProducts'
     *
     * @param  string|null $product_type (Optional) Only the products of this traffic class, as each product&#39;s own &#x60;productType&#x60; shows it: &#x60;EE&#x60; for Enterprise Ethernet, &#x60;TC4&#x60; for every other product. Any other value is &#x60;400&#x60;: &#x60;TC1&#x60; and &#x60;TC2&#x60;, which no product reports, and a product family, which is filtered with &#x60;techType&#x60;. (optional)
     * @param  string|null $tech_type (Optional) Only the products of this product family, as each product&#39;s &#x60;technology&#x60; shows it: &#x60;NFAS&#x60; (fibre), &#x60;NCAS&#x60; (copper), &#x60;NWAS&#x60; (fixed wireless), &#x60;NHAS&#x60; (HFC), &#x60;NSAS&#x60; (satellite) or &#x60;EEAS&#x60; (Enterprise Ethernet). Any other value is &#x60;400&#x60;. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRecentlyDisconnectedProducts'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getRecentlyDisconnectedProductsRequest($product_type = null, $tech_type = null, string $contentType = self::contentTypes['getRecentlyDisconnectedProducts'][0])
    {




        $resourcePath = '/nbn/products/recently_disconnected';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $product_type,
            'productType', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $tech_type,
            'techType', // param base name
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
     * Operation getRelatedOrders
     *
     * Retrieve the orders related to this Product Instance
     *
     * @param  string $product_ref The Product Instance or Product Service reference to query. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRelatedOrders'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetRelatedOrders200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function getRelatedOrders($product_ref, string $contentType = self::contentTypes['getRelatedOrders'][0])
    {
        list($response) = $this->getRelatedOrdersWithHttpInfo($product_ref, $contentType);
        return $response;
    }

    /**
     * Operation getRelatedOrdersWithHttpInfo
     *
     * Retrieve the orders related to this Product Instance
     *
     * @param  string $product_ref The Product Instance or Product Service reference to query. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRelatedOrders'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetRelatedOrders200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function getRelatedOrdersWithHttpInfo($product_ref, string $contentType = self::contentTypes['getRelatedOrders'][0])
    {
        $request = $this->getRelatedOrdersRequest($product_ref, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetRelatedOrders200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetRelatedOrders200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetRelatedOrders200Response',
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
     * Operation getRelatedOrdersAsync
     *
     * Retrieve the orders related to this Product Instance
     *
     * @param  string $product_ref The Product Instance or Product Service reference to query. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRelatedOrders'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getRelatedOrdersAsync($product_ref, string $contentType = self::contentTypes['getRelatedOrders'][0])
    {
        return $this->getRelatedOrdersAsyncWithHttpInfo($product_ref, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getRelatedOrdersAsyncWithHttpInfo
     *
     * Retrieve the orders related to this Product Instance
     *
     * @param  string $product_ref The Product Instance or Product Service reference to query. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRelatedOrders'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getRelatedOrdersAsyncWithHttpInfo($product_ref, string $contentType = self::contentTypes['getRelatedOrders'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetRelatedOrders200Response';
        $request = $this->getRelatedOrdersRequest($product_ref, $contentType);

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
     * Create request for operation 'getRelatedOrders'
     *
     * @param  string $product_ref The Product Instance or Product Service reference to query. (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getRelatedOrders'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getRelatedOrdersRequest($product_ref, string $contentType = self::contentTypes['getRelatedOrders'][0])
    {

        // verify the required parameter 'product_ref' is set
        if ($product_ref === null || (is_array($product_ref) && count($product_ref) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $product_ref when calling getRelatedOrders'
            );
        }


        $resourcePath = '/nbn/products/related/{productRef}/orders';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($product_ref !== null) {
            $resourcePath = str_replace(
                '{productRef}',
                ObjectSerializer::toPathValue($product_ref),
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
     * Operation patchProduct
     *
     * Update the administrative details of the Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchProductRequest $patch_product_request patch_product_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchProduct'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \CheckNetworks\Api\Generated\Model\GetProductViaService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error
     */
    public function patchProduct($product_id, $patch_product_request, $idempotency_key = null, string $contentType = self::contentTypes['patchProduct'][0])
    {
        list($response) = $this->patchProductWithHttpInfo($product_id, $patch_product_request, $idempotency_key, $contentType);
        return $response;
    }

    /**
     * Operation patchProductWithHttpInfo
     *
     * Update the administrative details of the Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchProductRequest $patch_product_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchProduct'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \CheckNetworks\Api\Generated\Model\GetProductViaService200Response|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error|\CheckNetworks\Api\Generated\Model\Error, HTTP status code, HTTP response headers (array of strings)
     */
    public function patchProductWithHttpInfo($product_id, $patch_product_request, $idempotency_key = null, string $contentType = self::contentTypes['patchProduct'][0])
    {
        $request = $this->patchProductRequest($product_id, $patch_product_request, $idempotency_key, $contentType);

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
                        '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
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
                '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response',
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
     * Operation patchProductAsync
     *
     * Update the administrative details of the Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchProductRequest $patch_product_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchProduct'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function patchProductAsync($product_id, $patch_product_request, $idempotency_key = null, string $contentType = self::contentTypes['patchProduct'][0])
    {
        return $this->patchProductAsyncWithHttpInfo($product_id, $patch_product_request, $idempotency_key, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation patchProductAsyncWithHttpInfo
     *
     * Update the administrative details of the Product Instance
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchProductRequest $patch_product_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchProduct'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function patchProductAsyncWithHttpInfo($product_id, $patch_product_request, $idempotency_key = null, string $contentType = self::contentTypes['patchProduct'][0])
    {
        $returnType = '\CheckNetworks\Api\Generated\Model\GetProductViaService200Response';
        $request = $this->patchProductRequest($product_id, $patch_product_request, $idempotency_key, $contentType);

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
     * Create request for operation 'patchProduct'
     *
     * @param  string $product_id The NBN Product Instance ID. For Example: \&quot;PRI000000000000\&quot; (required)
     * @param  \CheckNetworks\Api\Generated\Model\PatchProductRequest $patch_product_request (required)
     * @param  string|null $idempotency_key Optional. A unique value for this operation, such as a UUID, so that retrying it never does the work twice. Sending the same key and the same request again returns the stored response with &#x60;Idempotent-Replayed: true&#x60;. See Idempotency, above. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['patchProduct'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function patchProductRequest($product_id, $patch_product_request, $idempotency_key = null, string $contentType = self::contentTypes['patchProduct'][0])
    {

        // verify the required parameter 'product_id' is set
        if ($product_id === null || (is_array($product_id) && count($product_id) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $product_id when calling patchProduct'
            );
        }

        // verify the required parameter 'patch_product_request' is set
        if ($patch_product_request === null || (is_array($patch_product_request) && count($patch_product_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $patch_product_request when calling patchProduct'
            );
        }

        if ($idempotency_key !== null && strlen($idempotency_key) > 255) {
            throw new \InvalidArgumentException('invalid length for "$idempotency_key" when calling ProductInventoryApi.patchProduct, must be smaller than or equal to 255.');
        }
        

        $resourcePath = '/nbn/products/{productId}';
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
        if ($product_id !== null) {
            $resourcePath = str_replace(
                '{productId}',
                ObjectSerializer::toPathValue($product_id),
                $resourcePath
            );
        }


        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($patch_product_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($patch_product_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $patch_product_request;
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
