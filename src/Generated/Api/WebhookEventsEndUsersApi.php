<?php
/**
 * WebhookEventsEndUsersApi
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
 * WebhookEventsEndUsersApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class WebhookEventsEndUsersApi
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
        'webhookEndUserCreated' => [
            'application/json',
        ],
        'webhookEndUserCreditNoteIssued' => [
            'application/json',
        ],
        'webhookEndUserDisputeDecided' => [
            'application/json',
        ],
        'webhookEndUserDisputeRaised' => [
            'application/json',
        ],
        'webhookEndUserReactivated' => [
            'application/json',
        ],
        'webhookEndUserSpendThreshold' => [
            'application/json',
        ],
        'webhookEndUserSuspended' => [
            'application/json',
        ],
        'webhookEndUserUpdated' => [
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
     * Operation webhookEndUserCreated
     *
     * End User Created
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserCreated($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserCreated'][0])
    {
        $this->webhookEndUserCreatedWithHttpInfo($webhook_end_user_event, $contentType);
    }

    /**
     * Operation webhookEndUserCreatedWithHttpInfo
     *
     * End User Created
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserCreatedWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserCreated'][0])
    {
        $request = $this->webhookEndUserCreatedRequest($webhook_end_user_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserCreatedAsync
     *
     * End User Created
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserCreatedAsync($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserCreated'][0])
    {
        return $this->webhookEndUserCreatedAsyncWithHttpInfo($webhook_end_user_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserCreatedAsyncWithHttpInfo
     *
     * End User Created
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserCreatedAsyncWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserCreated'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserCreatedRequest($webhook_end_user_event, $contentType);

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
     * Create request for operation 'webhookEndUserCreated'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserCreatedRequest($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserCreated'][0])
    {

        // verify the required parameter 'webhook_end_user_event' is set
        if ($webhook_end_user_event === null || (is_array($webhook_end_user_event) && count($webhook_end_user_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_event when calling webhookEndUserCreated'
            );
        }


        $resourcePath = '/endUserCreated';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_event;
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
     * Operation webhookEndUserCreditNoteIssued
     *
     * End User Credit Note Issued
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserCreditNoteEvent $webhook_end_user_credit_note_event webhook_end_user_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserCreditNoteIssued($webhook_end_user_credit_note_event, string $contentType = self::contentTypes['webhookEndUserCreditNoteIssued'][0])
    {
        $this->webhookEndUserCreditNoteIssuedWithHttpInfo($webhook_end_user_credit_note_event, $contentType);
    }

    /**
     * Operation webhookEndUserCreditNoteIssuedWithHttpInfo
     *
     * End User Credit Note Issued
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserCreditNoteEvent $webhook_end_user_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserCreditNoteIssuedWithHttpInfo($webhook_end_user_credit_note_event, string $contentType = self::contentTypes['webhookEndUserCreditNoteIssued'][0])
    {
        $request = $this->webhookEndUserCreditNoteIssuedRequest($webhook_end_user_credit_note_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserCreditNoteIssuedAsync
     *
     * End User Credit Note Issued
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserCreditNoteEvent $webhook_end_user_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserCreditNoteIssuedAsync($webhook_end_user_credit_note_event, string $contentType = self::contentTypes['webhookEndUserCreditNoteIssued'][0])
    {
        return $this->webhookEndUserCreditNoteIssuedAsyncWithHttpInfo($webhook_end_user_credit_note_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserCreditNoteIssuedAsyncWithHttpInfo
     *
     * End User Credit Note Issued
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserCreditNoteEvent $webhook_end_user_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserCreditNoteIssuedAsyncWithHttpInfo($webhook_end_user_credit_note_event, string $contentType = self::contentTypes['webhookEndUserCreditNoteIssued'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserCreditNoteIssuedRequest($webhook_end_user_credit_note_event, $contentType);

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
     * Create request for operation 'webhookEndUserCreditNoteIssued'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserCreditNoteEvent $webhook_end_user_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserCreditNoteIssuedRequest($webhook_end_user_credit_note_event, string $contentType = self::contentTypes['webhookEndUserCreditNoteIssued'][0])
    {

        // verify the required parameter 'webhook_end_user_credit_note_event' is set
        if ($webhook_end_user_credit_note_event === null || (is_array($webhook_end_user_credit_note_event) && count($webhook_end_user_credit_note_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_credit_note_event when calling webhookEndUserCreditNoteIssued'
            );
        }


        $resourcePath = '/endUserCreditNoteIssued';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_credit_note_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_credit_note_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_credit_note_event;
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
     * Operation webhookEndUserDisputeDecided
     *
     * End User Dispute Decided
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeDecided'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserDisputeDecided($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeDecided'][0])
    {
        $this->webhookEndUserDisputeDecidedWithHttpInfo($webhook_end_user_dispute_event, $contentType);
    }

    /**
     * Operation webhookEndUserDisputeDecidedWithHttpInfo
     *
     * End User Dispute Decided
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeDecided'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserDisputeDecidedWithHttpInfo($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeDecided'][0])
    {
        $request = $this->webhookEndUserDisputeDecidedRequest($webhook_end_user_dispute_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserDisputeDecidedAsync
     *
     * End User Dispute Decided
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeDecided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserDisputeDecidedAsync($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeDecided'][0])
    {
        return $this->webhookEndUserDisputeDecidedAsyncWithHttpInfo($webhook_end_user_dispute_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserDisputeDecidedAsyncWithHttpInfo
     *
     * End User Dispute Decided
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeDecided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserDisputeDecidedAsyncWithHttpInfo($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeDecided'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserDisputeDecidedRequest($webhook_end_user_dispute_event, $contentType);

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
     * Create request for operation 'webhookEndUserDisputeDecided'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeDecided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserDisputeDecidedRequest($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeDecided'][0])
    {

        // verify the required parameter 'webhook_end_user_dispute_event' is set
        if ($webhook_end_user_dispute_event === null || (is_array($webhook_end_user_dispute_event) && count($webhook_end_user_dispute_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_dispute_event when calling webhookEndUserDisputeDecided'
            );
        }


        $resourcePath = '/endUserDisputeDecided';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_dispute_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_dispute_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_dispute_event;
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
     * Operation webhookEndUserDisputeRaised
     *
     * End User Dispute Raised
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeRaised'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserDisputeRaised($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeRaised'][0])
    {
        $this->webhookEndUserDisputeRaisedWithHttpInfo($webhook_end_user_dispute_event, $contentType);
    }

    /**
     * Operation webhookEndUserDisputeRaisedWithHttpInfo
     *
     * End User Dispute Raised
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeRaised'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserDisputeRaisedWithHttpInfo($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeRaised'][0])
    {
        $request = $this->webhookEndUserDisputeRaisedRequest($webhook_end_user_dispute_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserDisputeRaisedAsync
     *
     * End User Dispute Raised
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeRaised'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserDisputeRaisedAsync($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeRaised'][0])
    {
        return $this->webhookEndUserDisputeRaisedAsyncWithHttpInfo($webhook_end_user_dispute_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserDisputeRaisedAsyncWithHttpInfo
     *
     * End User Dispute Raised
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeRaised'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserDisputeRaisedAsyncWithHttpInfo($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeRaised'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserDisputeRaisedRequest($webhook_end_user_dispute_event, $contentType);

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
     * Create request for operation 'webhookEndUserDisputeRaised'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserDisputeEvent $webhook_end_user_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserDisputeRaised'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserDisputeRaisedRequest($webhook_end_user_dispute_event, string $contentType = self::contentTypes['webhookEndUserDisputeRaised'][0])
    {

        // verify the required parameter 'webhook_end_user_dispute_event' is set
        if ($webhook_end_user_dispute_event === null || (is_array($webhook_end_user_dispute_event) && count($webhook_end_user_dispute_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_dispute_event when calling webhookEndUserDisputeRaised'
            );
        }


        $resourcePath = '/endUserDisputeRaised';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_dispute_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_dispute_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_dispute_event;
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
     * Operation webhookEndUserReactivated
     *
     * End User Reactivated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserReactivated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserReactivated($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserReactivated'][0])
    {
        $this->webhookEndUserReactivatedWithHttpInfo($webhook_end_user_event, $contentType);
    }

    /**
     * Operation webhookEndUserReactivatedWithHttpInfo
     *
     * End User Reactivated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserReactivated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserReactivatedWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserReactivated'][0])
    {
        $request = $this->webhookEndUserReactivatedRequest($webhook_end_user_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserReactivatedAsync
     *
     * End User Reactivated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserReactivated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserReactivatedAsync($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserReactivated'][0])
    {
        return $this->webhookEndUserReactivatedAsyncWithHttpInfo($webhook_end_user_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserReactivatedAsyncWithHttpInfo
     *
     * End User Reactivated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserReactivated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserReactivatedAsyncWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserReactivated'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserReactivatedRequest($webhook_end_user_event, $contentType);

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
     * Create request for operation 'webhookEndUserReactivated'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserReactivated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserReactivatedRequest($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserReactivated'][0])
    {

        // verify the required parameter 'webhook_end_user_event' is set
        if ($webhook_end_user_event === null || (is_array($webhook_end_user_event) && count($webhook_end_user_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_event when calling webhookEndUserReactivated'
            );
        }


        $resourcePath = '/endUserReactivated';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_event;
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
     * Operation webhookEndUserSpendThreshold
     *
     * End User Spend Threshold
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserSpendEvent $webhook_end_user_spend_event webhook_end_user_spend_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSpendThreshold'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserSpendThreshold($webhook_end_user_spend_event, string $contentType = self::contentTypes['webhookEndUserSpendThreshold'][0])
    {
        $this->webhookEndUserSpendThresholdWithHttpInfo($webhook_end_user_spend_event, $contentType);
    }

    /**
     * Operation webhookEndUserSpendThresholdWithHttpInfo
     *
     * End User Spend Threshold
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserSpendEvent $webhook_end_user_spend_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSpendThreshold'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserSpendThresholdWithHttpInfo($webhook_end_user_spend_event, string $contentType = self::contentTypes['webhookEndUserSpendThreshold'][0])
    {
        $request = $this->webhookEndUserSpendThresholdRequest($webhook_end_user_spend_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserSpendThresholdAsync
     *
     * End User Spend Threshold
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserSpendEvent $webhook_end_user_spend_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSpendThreshold'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserSpendThresholdAsync($webhook_end_user_spend_event, string $contentType = self::contentTypes['webhookEndUserSpendThreshold'][0])
    {
        return $this->webhookEndUserSpendThresholdAsyncWithHttpInfo($webhook_end_user_spend_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserSpendThresholdAsyncWithHttpInfo
     *
     * End User Spend Threshold
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserSpendEvent $webhook_end_user_spend_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSpendThreshold'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserSpendThresholdAsyncWithHttpInfo($webhook_end_user_spend_event, string $contentType = self::contentTypes['webhookEndUserSpendThreshold'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserSpendThresholdRequest($webhook_end_user_spend_event, $contentType);

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
     * Create request for operation 'webhookEndUserSpendThreshold'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserSpendEvent $webhook_end_user_spend_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSpendThreshold'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserSpendThresholdRequest($webhook_end_user_spend_event, string $contentType = self::contentTypes['webhookEndUserSpendThreshold'][0])
    {

        // verify the required parameter 'webhook_end_user_spend_event' is set
        if ($webhook_end_user_spend_event === null || (is_array($webhook_end_user_spend_event) && count($webhook_end_user_spend_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_spend_event when calling webhookEndUserSpendThreshold'
            );
        }


        $resourcePath = '/endUserSpendThreshold';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_spend_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_spend_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_spend_event;
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
     * Operation webhookEndUserSuspended
     *
     * End User Suspended
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSuspended'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserSuspended($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserSuspended'][0])
    {
        $this->webhookEndUserSuspendedWithHttpInfo($webhook_end_user_event, $contentType);
    }

    /**
     * Operation webhookEndUserSuspendedWithHttpInfo
     *
     * End User Suspended
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSuspended'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserSuspendedWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserSuspended'][0])
    {
        $request = $this->webhookEndUserSuspendedRequest($webhook_end_user_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserSuspendedAsync
     *
     * End User Suspended
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserSuspendedAsync($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserSuspended'][0])
    {
        return $this->webhookEndUserSuspendedAsyncWithHttpInfo($webhook_end_user_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserSuspendedAsyncWithHttpInfo
     *
     * End User Suspended
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserSuspendedAsyncWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserSuspended'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserSuspendedRequest($webhook_end_user_event, $contentType);

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
     * Create request for operation 'webhookEndUserSuspended'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserSuspendedRequest($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserSuspended'][0])
    {

        // verify the required parameter 'webhook_end_user_event' is set
        if ($webhook_end_user_event === null || (is_array($webhook_end_user_event) && count($webhook_end_user_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_event when calling webhookEndUserSuspended'
            );
        }


        $resourcePath = '/endUserSuspended';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_event;
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
     * Operation webhookEndUserUpdated
     *
     * End User Updated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserUpdated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookEndUserUpdated($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserUpdated'][0])
    {
        $this->webhookEndUserUpdatedWithHttpInfo($webhook_end_user_event, $contentType);
    }

    /**
     * Operation webhookEndUserUpdatedWithHttpInfo
     *
     * End User Updated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserUpdated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\Api\Generated\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookEndUserUpdatedWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserUpdated'][0])
    {
        $request = $this->webhookEndUserUpdatedRequest($webhook_end_user_event, $contentType);

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
            }
        

            throw $e;
        }
    }

    /**
     * Operation webhookEndUserUpdatedAsync
     *
     * End User Updated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserUpdated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserUpdatedAsync($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserUpdated'][0])
    {
        return $this->webhookEndUserUpdatedAsyncWithHttpInfo($webhook_end_user_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookEndUserUpdatedAsyncWithHttpInfo
     *
     * End User Updated
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserUpdated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookEndUserUpdatedAsyncWithHttpInfo($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserUpdated'][0])
    {
        $returnType = '';
        $request = $this->webhookEndUserUpdatedRequest($webhook_end_user_event, $contentType);

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
     * Create request for operation 'webhookEndUserUpdated'
     *
     * @param  \CheckNetworks\Api\Generated\Model\WebhookEndUserEvent $webhook_end_user_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookEndUserUpdated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookEndUserUpdatedRequest($webhook_end_user_event, string $contentType = self::contentTypes['webhookEndUserUpdated'][0])
    {

        // verify the required parameter 'webhook_end_user_event' is set
        if ($webhook_end_user_event === null || (is_array($webhook_end_user_event) && count($webhook_end_user_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_end_user_event when calling webhookEndUserUpdated'
            );
        }


        $resourcePath = '/endUserUpdated';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;





        $headers = $this->headerSelector->selectHeaders(
            [],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (isset($webhook_end_user_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_end_user_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_end_user_event;
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
