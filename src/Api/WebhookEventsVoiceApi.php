<?php
/**
 * WebhookEventsVoiceApi
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
 * WebhookEventsVoiceApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class WebhookEventsVoiceApi
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
        'webhookCallerIdStatusChanged' => [
            'application/json',
        ],
        'webhookIpndRecordRejected' => [
            'application/json',
        ],
        'webhookNumberAssigned' => [
            'application/json',
        ],
        'webhookNumberReactivated' => [
            'application/json',
        ],
        'webhookNumberReleased' => [
            'application/json',
        ],
        'webhookNumberSuspended' => [
            'application/json',
        ],
        'webhookNumberUnassigned' => [
            'application/json',
        ],
        'webhookTrunkDown' => [
            'application/json',
        ],
        'webhookTrunkPlanChangeCancelled' => [
            'application/json',
        ],
        'webhookTrunkPlanChangeScheduled' => [
            'application/json',
        ],
        'webhookTrunkPlanChanged' => [
            'application/json',
        ],
        'webhookTrunkUp' => [
            'application/json',
        ],
        'webhookVoiceCallCompleted' => [
            'application/json',
        ],
        'webhookVoiceHoldPlaced' => [
            'application/json',
        ],
        'webhookVoiceHoldReleased' => [
            'application/json',
        ],
        'webhookVoiceRecordingReady' => [
            'application/json',
        ],
        'webhookVoiceVoicemailReceived' => [
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
     * Operation webhookCallerIdStatusChanged
     *
     * Caller ID Authorisation Decided
     *
     * @param  \CheckNetworks\Model\WebhookCallerIdEvent $webhook_caller_id_event webhook_caller_id_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCallerIdStatusChanged'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookCallerIdStatusChanged($webhook_caller_id_event, string $contentType = self::contentTypes['webhookCallerIdStatusChanged'][0])
    {
        $this->webhookCallerIdStatusChangedWithHttpInfo($webhook_caller_id_event, $contentType);
    }

    /**
     * Operation webhookCallerIdStatusChangedWithHttpInfo
     *
     * Caller ID Authorisation Decided
     *
     * @param  \CheckNetworks\Model\WebhookCallerIdEvent $webhook_caller_id_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCallerIdStatusChanged'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookCallerIdStatusChangedWithHttpInfo($webhook_caller_id_event, string $contentType = self::contentTypes['webhookCallerIdStatusChanged'][0])
    {
        $request = $this->webhookCallerIdStatusChangedRequest($webhook_caller_id_event, $contentType);

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
     * Operation webhookCallerIdStatusChangedAsync
     *
     * Caller ID Authorisation Decided
     *
     * @param  \CheckNetworks\Model\WebhookCallerIdEvent $webhook_caller_id_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCallerIdStatusChanged'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookCallerIdStatusChangedAsync($webhook_caller_id_event, string $contentType = self::contentTypes['webhookCallerIdStatusChanged'][0])
    {
        return $this->webhookCallerIdStatusChangedAsyncWithHttpInfo($webhook_caller_id_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookCallerIdStatusChangedAsyncWithHttpInfo
     *
     * Caller ID Authorisation Decided
     *
     * @param  \CheckNetworks\Model\WebhookCallerIdEvent $webhook_caller_id_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCallerIdStatusChanged'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookCallerIdStatusChangedAsyncWithHttpInfo($webhook_caller_id_event, string $contentType = self::contentTypes['webhookCallerIdStatusChanged'][0])
    {
        $returnType = '';
        $request = $this->webhookCallerIdStatusChangedRequest($webhook_caller_id_event, $contentType);

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
     * Create request for operation 'webhookCallerIdStatusChanged'
     *
     * @param  \CheckNetworks\Model\WebhookCallerIdEvent $webhook_caller_id_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCallerIdStatusChanged'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookCallerIdStatusChangedRequest($webhook_caller_id_event, string $contentType = self::contentTypes['webhookCallerIdStatusChanged'][0])
    {

        // verify the required parameter 'webhook_caller_id_event' is set
        if ($webhook_caller_id_event === null || (is_array($webhook_caller_id_event) && count($webhook_caller_id_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_caller_id_event when calling webhookCallerIdStatusChanged'
            );
        }


        $resourcePath = '/callerIdStatusChanged';
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
        if (isset($webhook_caller_id_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_caller_id_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_caller_id_event;
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
     * Operation webhookIpndRecordRejected
     *
     * IPND Record Rejected
     *
     * @param  \CheckNetworks\Model\WebhookIpndRecordRejectedEvent $webhook_ipnd_record_rejected_event webhook_ipnd_record_rejected_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookIpndRecordRejected'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookIpndRecordRejected($webhook_ipnd_record_rejected_event, string $contentType = self::contentTypes['webhookIpndRecordRejected'][0])
    {
        $this->webhookIpndRecordRejectedWithHttpInfo($webhook_ipnd_record_rejected_event, $contentType);
    }

    /**
     * Operation webhookIpndRecordRejectedWithHttpInfo
     *
     * IPND Record Rejected
     *
     * @param  \CheckNetworks\Model\WebhookIpndRecordRejectedEvent $webhook_ipnd_record_rejected_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookIpndRecordRejected'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookIpndRecordRejectedWithHttpInfo($webhook_ipnd_record_rejected_event, string $contentType = self::contentTypes['webhookIpndRecordRejected'][0])
    {
        $request = $this->webhookIpndRecordRejectedRequest($webhook_ipnd_record_rejected_event, $contentType);

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
     * Operation webhookIpndRecordRejectedAsync
     *
     * IPND Record Rejected
     *
     * @param  \CheckNetworks\Model\WebhookIpndRecordRejectedEvent $webhook_ipnd_record_rejected_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookIpndRecordRejected'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookIpndRecordRejectedAsync($webhook_ipnd_record_rejected_event, string $contentType = self::contentTypes['webhookIpndRecordRejected'][0])
    {
        return $this->webhookIpndRecordRejectedAsyncWithHttpInfo($webhook_ipnd_record_rejected_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookIpndRecordRejectedAsyncWithHttpInfo
     *
     * IPND Record Rejected
     *
     * @param  \CheckNetworks\Model\WebhookIpndRecordRejectedEvent $webhook_ipnd_record_rejected_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookIpndRecordRejected'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookIpndRecordRejectedAsyncWithHttpInfo($webhook_ipnd_record_rejected_event, string $contentType = self::contentTypes['webhookIpndRecordRejected'][0])
    {
        $returnType = '';
        $request = $this->webhookIpndRecordRejectedRequest($webhook_ipnd_record_rejected_event, $contentType);

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
     * Create request for operation 'webhookIpndRecordRejected'
     *
     * @param  \CheckNetworks\Model\WebhookIpndRecordRejectedEvent $webhook_ipnd_record_rejected_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookIpndRecordRejected'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookIpndRecordRejectedRequest($webhook_ipnd_record_rejected_event, string $contentType = self::contentTypes['webhookIpndRecordRejected'][0])
    {

        // verify the required parameter 'webhook_ipnd_record_rejected_event' is set
        if ($webhook_ipnd_record_rejected_event === null || (is_array($webhook_ipnd_record_rejected_event) && count($webhook_ipnd_record_rejected_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_ipnd_record_rejected_event when calling webhookIpndRecordRejected'
            );
        }


        $resourcePath = '/ipndRecordRejected';
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
        if (isset($webhook_ipnd_record_rejected_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_ipnd_record_rejected_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_ipnd_record_rejected_event;
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
     * Operation webhookNumberAssigned
     *
     * Number Assigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberAssigned'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookNumberAssigned($webhook_number_event, string $contentType = self::contentTypes['webhookNumberAssigned'][0])
    {
        $this->webhookNumberAssignedWithHttpInfo($webhook_number_event, $contentType);
    }

    /**
     * Operation webhookNumberAssignedWithHttpInfo
     *
     * Number Assigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberAssigned'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookNumberAssignedWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberAssigned'][0])
    {
        $request = $this->webhookNumberAssignedRequest($webhook_number_event, $contentType);

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
     * Operation webhookNumberAssignedAsync
     *
     * Number Assigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberAssigned'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberAssignedAsync($webhook_number_event, string $contentType = self::contentTypes['webhookNumberAssigned'][0])
    {
        return $this->webhookNumberAssignedAsyncWithHttpInfo($webhook_number_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookNumberAssignedAsyncWithHttpInfo
     *
     * Number Assigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberAssigned'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberAssignedAsyncWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberAssigned'][0])
    {
        $returnType = '';
        $request = $this->webhookNumberAssignedRequest($webhook_number_event, $contentType);

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
     * Create request for operation 'webhookNumberAssigned'
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberAssigned'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookNumberAssignedRequest($webhook_number_event, string $contentType = self::contentTypes['webhookNumberAssigned'][0])
    {

        // verify the required parameter 'webhook_number_event' is set
        if ($webhook_number_event === null || (is_array($webhook_number_event) && count($webhook_number_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_number_event when calling webhookNumberAssigned'
            );
        }


        $resourcePath = '/numberAssigned';
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
        if (isset($webhook_number_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_number_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_number_event;
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
     * Operation webhookNumberReactivated
     *
     * Number Reactivated
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReactivated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookNumberReactivated($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReactivated'][0])
    {
        $this->webhookNumberReactivatedWithHttpInfo($webhook_number_event, $contentType);
    }

    /**
     * Operation webhookNumberReactivatedWithHttpInfo
     *
     * Number Reactivated
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReactivated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookNumberReactivatedWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReactivated'][0])
    {
        $request = $this->webhookNumberReactivatedRequest($webhook_number_event, $contentType);

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
     * Operation webhookNumberReactivatedAsync
     *
     * Number Reactivated
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReactivated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberReactivatedAsync($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReactivated'][0])
    {
        return $this->webhookNumberReactivatedAsyncWithHttpInfo($webhook_number_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookNumberReactivatedAsyncWithHttpInfo
     *
     * Number Reactivated
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReactivated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberReactivatedAsyncWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReactivated'][0])
    {
        $returnType = '';
        $request = $this->webhookNumberReactivatedRequest($webhook_number_event, $contentType);

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
     * Create request for operation 'webhookNumberReactivated'
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReactivated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookNumberReactivatedRequest($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReactivated'][0])
    {

        // verify the required parameter 'webhook_number_event' is set
        if ($webhook_number_event === null || (is_array($webhook_number_event) && count($webhook_number_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_number_event when calling webhookNumberReactivated'
            );
        }


        $resourcePath = '/numberReactivated';
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
        if (isset($webhook_number_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_number_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_number_event;
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
     * Operation webhookNumberReleased
     *
     * Number Released
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReleased'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookNumberReleased($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReleased'][0])
    {
        $this->webhookNumberReleasedWithHttpInfo($webhook_number_event, $contentType);
    }

    /**
     * Operation webhookNumberReleasedWithHttpInfo
     *
     * Number Released
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReleased'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookNumberReleasedWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReleased'][0])
    {
        $request = $this->webhookNumberReleasedRequest($webhook_number_event, $contentType);

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
     * Operation webhookNumberReleasedAsync
     *
     * Number Released
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReleased'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberReleasedAsync($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReleased'][0])
    {
        return $this->webhookNumberReleasedAsyncWithHttpInfo($webhook_number_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookNumberReleasedAsyncWithHttpInfo
     *
     * Number Released
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReleased'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberReleasedAsyncWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReleased'][0])
    {
        $returnType = '';
        $request = $this->webhookNumberReleasedRequest($webhook_number_event, $contentType);

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
     * Create request for operation 'webhookNumberReleased'
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberReleased'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookNumberReleasedRequest($webhook_number_event, string $contentType = self::contentTypes['webhookNumberReleased'][0])
    {

        // verify the required parameter 'webhook_number_event' is set
        if ($webhook_number_event === null || (is_array($webhook_number_event) && count($webhook_number_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_number_event when calling webhookNumberReleased'
            );
        }


        $resourcePath = '/numberReleased';
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
        if (isset($webhook_number_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_number_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_number_event;
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
     * Operation webhookNumberSuspended
     *
     * Number Suspended
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberSuspended'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookNumberSuspended($webhook_number_event, string $contentType = self::contentTypes['webhookNumberSuspended'][0])
    {
        $this->webhookNumberSuspendedWithHttpInfo($webhook_number_event, $contentType);
    }

    /**
     * Operation webhookNumberSuspendedWithHttpInfo
     *
     * Number Suspended
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberSuspended'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookNumberSuspendedWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberSuspended'][0])
    {
        $request = $this->webhookNumberSuspendedRequest($webhook_number_event, $contentType);

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
     * Operation webhookNumberSuspendedAsync
     *
     * Number Suspended
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberSuspendedAsync($webhook_number_event, string $contentType = self::contentTypes['webhookNumberSuspended'][0])
    {
        return $this->webhookNumberSuspendedAsyncWithHttpInfo($webhook_number_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookNumberSuspendedAsyncWithHttpInfo
     *
     * Number Suspended
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberSuspendedAsyncWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberSuspended'][0])
    {
        $returnType = '';
        $request = $this->webhookNumberSuspendedRequest($webhook_number_event, $contentType);

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
     * Create request for operation 'webhookNumberSuspended'
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookNumberSuspendedRequest($webhook_number_event, string $contentType = self::contentTypes['webhookNumberSuspended'][0])
    {

        // verify the required parameter 'webhook_number_event' is set
        if ($webhook_number_event === null || (is_array($webhook_number_event) && count($webhook_number_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_number_event when calling webhookNumberSuspended'
            );
        }


        $resourcePath = '/numberSuspended';
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
        if (isset($webhook_number_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_number_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_number_event;
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
     * Operation webhookNumberUnassigned
     *
     * Number Unassigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberUnassigned'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookNumberUnassigned($webhook_number_event, string $contentType = self::contentTypes['webhookNumberUnassigned'][0])
    {
        $this->webhookNumberUnassignedWithHttpInfo($webhook_number_event, $contentType);
    }

    /**
     * Operation webhookNumberUnassignedWithHttpInfo
     *
     * Number Unassigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberUnassigned'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookNumberUnassignedWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberUnassigned'][0])
    {
        $request = $this->webhookNumberUnassignedRequest($webhook_number_event, $contentType);

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
     * Operation webhookNumberUnassignedAsync
     *
     * Number Unassigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberUnassigned'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberUnassignedAsync($webhook_number_event, string $contentType = self::contentTypes['webhookNumberUnassigned'][0])
    {
        return $this->webhookNumberUnassignedAsyncWithHttpInfo($webhook_number_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookNumberUnassignedAsyncWithHttpInfo
     *
     * Number Unassigned
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberUnassigned'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookNumberUnassignedAsyncWithHttpInfo($webhook_number_event, string $contentType = self::contentTypes['webhookNumberUnassigned'][0])
    {
        $returnType = '';
        $request = $this->webhookNumberUnassignedRequest($webhook_number_event, $contentType);

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
     * Create request for operation 'webhookNumberUnassigned'
     *
     * @param  \CheckNetworks\Model\WebhookNumberEvent $webhook_number_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookNumberUnassigned'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookNumberUnassignedRequest($webhook_number_event, string $contentType = self::contentTypes['webhookNumberUnassigned'][0])
    {

        // verify the required parameter 'webhook_number_event' is set
        if ($webhook_number_event === null || (is_array($webhook_number_event) && count($webhook_number_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_number_event when calling webhookNumberUnassigned'
            );
        }


        $resourcePath = '/numberUnassigned';
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
        if (isset($webhook_number_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_number_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_number_event;
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
     * Operation webhookTrunkDown
     *
     * Trunk Down
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkDown'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookTrunkDown($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkDown'][0])
    {
        $this->webhookTrunkDownWithHttpInfo($webhook_trunk_event, $contentType);
    }

    /**
     * Operation webhookTrunkDownWithHttpInfo
     *
     * Trunk Down
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkDown'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookTrunkDownWithHttpInfo($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkDown'][0])
    {
        $request = $this->webhookTrunkDownRequest($webhook_trunk_event, $contentType);

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
     * Operation webhookTrunkDownAsync
     *
     * Trunk Down
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkDown'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkDownAsync($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkDown'][0])
    {
        return $this->webhookTrunkDownAsyncWithHttpInfo($webhook_trunk_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookTrunkDownAsyncWithHttpInfo
     *
     * Trunk Down
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkDown'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkDownAsyncWithHttpInfo($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkDown'][0])
    {
        $returnType = '';
        $request = $this->webhookTrunkDownRequest($webhook_trunk_event, $contentType);

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
     * Create request for operation 'webhookTrunkDown'
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkDown'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookTrunkDownRequest($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkDown'][0])
    {

        // verify the required parameter 'webhook_trunk_event' is set
        if ($webhook_trunk_event === null || (is_array($webhook_trunk_event) && count($webhook_trunk_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_trunk_event when calling webhookTrunkDown'
            );
        }


        $resourcePath = '/trunkDown';
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
        if (isset($webhook_trunk_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_trunk_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_trunk_event;
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
     * Operation webhookTrunkPlanChangeCancelled
     *
     * Trunk Plan Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeCancelled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookTrunkPlanChangeCancelled($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeCancelled'][0])
    {
        $this->webhookTrunkPlanChangeCancelledWithHttpInfo($webhook_trunk_plan_change_event, $contentType);
    }

    /**
     * Operation webhookTrunkPlanChangeCancelledWithHttpInfo
     *
     * Trunk Plan Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeCancelled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookTrunkPlanChangeCancelledWithHttpInfo($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeCancelled'][0])
    {
        $request = $this->webhookTrunkPlanChangeCancelledRequest($webhook_trunk_plan_change_event, $contentType);

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
     * Operation webhookTrunkPlanChangeCancelledAsync
     *
     * Trunk Plan Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeCancelled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkPlanChangeCancelledAsync($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeCancelled'][0])
    {
        return $this->webhookTrunkPlanChangeCancelledAsyncWithHttpInfo($webhook_trunk_plan_change_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookTrunkPlanChangeCancelledAsyncWithHttpInfo
     *
     * Trunk Plan Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeCancelled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkPlanChangeCancelledAsyncWithHttpInfo($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeCancelled'][0])
    {
        $returnType = '';
        $request = $this->webhookTrunkPlanChangeCancelledRequest($webhook_trunk_plan_change_event, $contentType);

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
     * Create request for operation 'webhookTrunkPlanChangeCancelled'
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeCancelled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookTrunkPlanChangeCancelledRequest($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeCancelled'][0])
    {

        // verify the required parameter 'webhook_trunk_plan_change_event' is set
        if ($webhook_trunk_plan_change_event === null || (is_array($webhook_trunk_plan_change_event) && count($webhook_trunk_plan_change_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_trunk_plan_change_event when calling webhookTrunkPlanChangeCancelled'
            );
        }


        $resourcePath = '/trunkPlanChangeCancelled';
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
        if (isset($webhook_trunk_plan_change_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_trunk_plan_change_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_trunk_plan_change_event;
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
     * Operation webhookTrunkPlanChangeScheduled
     *
     * Trunk Plan Change Scheduled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeScheduled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookTrunkPlanChangeScheduled($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeScheduled'][0])
    {
        $this->webhookTrunkPlanChangeScheduledWithHttpInfo($webhook_trunk_plan_change_event, $contentType);
    }

    /**
     * Operation webhookTrunkPlanChangeScheduledWithHttpInfo
     *
     * Trunk Plan Change Scheduled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeScheduled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookTrunkPlanChangeScheduledWithHttpInfo($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeScheduled'][0])
    {
        $request = $this->webhookTrunkPlanChangeScheduledRequest($webhook_trunk_plan_change_event, $contentType);

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
     * Operation webhookTrunkPlanChangeScheduledAsync
     *
     * Trunk Plan Change Scheduled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeScheduled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkPlanChangeScheduledAsync($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeScheduled'][0])
    {
        return $this->webhookTrunkPlanChangeScheduledAsyncWithHttpInfo($webhook_trunk_plan_change_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookTrunkPlanChangeScheduledAsyncWithHttpInfo
     *
     * Trunk Plan Change Scheduled
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeScheduled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkPlanChangeScheduledAsyncWithHttpInfo($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeScheduled'][0])
    {
        $returnType = '';
        $request = $this->webhookTrunkPlanChangeScheduledRequest($webhook_trunk_plan_change_event, $contentType);

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
     * Create request for operation 'webhookTrunkPlanChangeScheduled'
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChangeScheduled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookTrunkPlanChangeScheduledRequest($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChangeScheduled'][0])
    {

        // verify the required parameter 'webhook_trunk_plan_change_event' is set
        if ($webhook_trunk_plan_change_event === null || (is_array($webhook_trunk_plan_change_event) && count($webhook_trunk_plan_change_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_trunk_plan_change_event when calling webhookTrunkPlanChangeScheduled'
            );
        }


        $resourcePath = '/trunkPlanChangeScheduled';
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
        if (isset($webhook_trunk_plan_change_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_trunk_plan_change_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_trunk_plan_change_event;
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
     * Operation webhookTrunkPlanChanged
     *
     * Trunk Plan Changed
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChanged'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookTrunkPlanChanged($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChanged'][0])
    {
        $this->webhookTrunkPlanChangedWithHttpInfo($webhook_trunk_plan_change_event, $contentType);
    }

    /**
     * Operation webhookTrunkPlanChangedWithHttpInfo
     *
     * Trunk Plan Changed
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChanged'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookTrunkPlanChangedWithHttpInfo($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChanged'][0])
    {
        $request = $this->webhookTrunkPlanChangedRequest($webhook_trunk_plan_change_event, $contentType);

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
     * Operation webhookTrunkPlanChangedAsync
     *
     * Trunk Plan Changed
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChanged'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkPlanChangedAsync($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChanged'][0])
    {
        return $this->webhookTrunkPlanChangedAsyncWithHttpInfo($webhook_trunk_plan_change_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookTrunkPlanChangedAsyncWithHttpInfo
     *
     * Trunk Plan Changed
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChanged'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkPlanChangedAsyncWithHttpInfo($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChanged'][0])
    {
        $returnType = '';
        $request = $this->webhookTrunkPlanChangedRequest($webhook_trunk_plan_change_event, $contentType);

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
     * Create request for operation 'webhookTrunkPlanChanged'
     *
     * @param  \CheckNetworks\Model\WebhookTrunkPlanChangeEvent $webhook_trunk_plan_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkPlanChanged'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookTrunkPlanChangedRequest($webhook_trunk_plan_change_event, string $contentType = self::contentTypes['webhookTrunkPlanChanged'][0])
    {

        // verify the required parameter 'webhook_trunk_plan_change_event' is set
        if ($webhook_trunk_plan_change_event === null || (is_array($webhook_trunk_plan_change_event) && count($webhook_trunk_plan_change_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_trunk_plan_change_event when calling webhookTrunkPlanChanged'
            );
        }


        $resourcePath = '/trunkPlanChanged';
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
        if (isset($webhook_trunk_plan_change_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_trunk_plan_change_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_trunk_plan_change_event;
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
     * Operation webhookTrunkUp
     *
     * Trunk Back Up
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkUp'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookTrunkUp($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkUp'][0])
    {
        $this->webhookTrunkUpWithHttpInfo($webhook_trunk_event, $contentType);
    }

    /**
     * Operation webhookTrunkUpWithHttpInfo
     *
     * Trunk Back Up
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkUp'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookTrunkUpWithHttpInfo($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkUp'][0])
    {
        $request = $this->webhookTrunkUpRequest($webhook_trunk_event, $contentType);

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
     * Operation webhookTrunkUpAsync
     *
     * Trunk Back Up
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkUp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkUpAsync($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkUp'][0])
    {
        return $this->webhookTrunkUpAsyncWithHttpInfo($webhook_trunk_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookTrunkUpAsyncWithHttpInfo
     *
     * Trunk Back Up
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkUp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookTrunkUpAsyncWithHttpInfo($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkUp'][0])
    {
        $returnType = '';
        $request = $this->webhookTrunkUpRequest($webhook_trunk_event, $contentType);

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
     * Create request for operation 'webhookTrunkUp'
     *
     * @param  \CheckNetworks\Model\WebhookTrunkEvent $webhook_trunk_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookTrunkUp'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookTrunkUpRequest($webhook_trunk_event, string $contentType = self::contentTypes['webhookTrunkUp'][0])
    {

        // verify the required parameter 'webhook_trunk_event' is set
        if ($webhook_trunk_event === null || (is_array($webhook_trunk_event) && count($webhook_trunk_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_trunk_event when calling webhookTrunkUp'
            );
        }


        $resourcePath = '/trunkUp';
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
        if (isset($webhook_trunk_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_trunk_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_trunk_event;
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
     * Operation webhookVoiceCallCompleted
     *
     * Voice Call Completed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceCallEvent $webhook_voice_call_event webhook_voice_call_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceCallCompleted'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookVoiceCallCompleted($webhook_voice_call_event, string $contentType = self::contentTypes['webhookVoiceCallCompleted'][0])
    {
        $this->webhookVoiceCallCompletedWithHttpInfo($webhook_voice_call_event, $contentType);
    }

    /**
     * Operation webhookVoiceCallCompletedWithHttpInfo
     *
     * Voice Call Completed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceCallEvent $webhook_voice_call_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceCallCompleted'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookVoiceCallCompletedWithHttpInfo($webhook_voice_call_event, string $contentType = self::contentTypes['webhookVoiceCallCompleted'][0])
    {
        $request = $this->webhookVoiceCallCompletedRequest($webhook_voice_call_event, $contentType);

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
     * Operation webhookVoiceCallCompletedAsync
     *
     * Voice Call Completed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceCallEvent $webhook_voice_call_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceCallCompleted'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceCallCompletedAsync($webhook_voice_call_event, string $contentType = self::contentTypes['webhookVoiceCallCompleted'][0])
    {
        return $this->webhookVoiceCallCompletedAsyncWithHttpInfo($webhook_voice_call_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookVoiceCallCompletedAsyncWithHttpInfo
     *
     * Voice Call Completed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceCallEvent $webhook_voice_call_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceCallCompleted'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceCallCompletedAsyncWithHttpInfo($webhook_voice_call_event, string $contentType = self::contentTypes['webhookVoiceCallCompleted'][0])
    {
        $returnType = '';
        $request = $this->webhookVoiceCallCompletedRequest($webhook_voice_call_event, $contentType);

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
     * Create request for operation 'webhookVoiceCallCompleted'
     *
     * @param  \CheckNetworks\Model\WebhookVoiceCallEvent $webhook_voice_call_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceCallCompleted'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookVoiceCallCompletedRequest($webhook_voice_call_event, string $contentType = self::contentTypes['webhookVoiceCallCompleted'][0])
    {

        // verify the required parameter 'webhook_voice_call_event' is set
        if ($webhook_voice_call_event === null || (is_array($webhook_voice_call_event) && count($webhook_voice_call_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_voice_call_event when calling webhookVoiceCallCompleted'
            );
        }


        $resourcePath = '/voiceCallCompleted';
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
        if (isset($webhook_voice_call_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_voice_call_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_voice_call_event;
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
     * Operation webhookVoiceHoldPlaced
     *
     * Voice Hold Placed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldPlaced'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookVoiceHoldPlaced($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldPlaced'][0])
    {
        $this->webhookVoiceHoldPlacedWithHttpInfo($webhook_voice_hold_event, $contentType);
    }

    /**
     * Operation webhookVoiceHoldPlacedWithHttpInfo
     *
     * Voice Hold Placed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldPlaced'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookVoiceHoldPlacedWithHttpInfo($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldPlaced'][0])
    {
        $request = $this->webhookVoiceHoldPlacedRequest($webhook_voice_hold_event, $contentType);

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
     * Operation webhookVoiceHoldPlacedAsync
     *
     * Voice Hold Placed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldPlaced'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceHoldPlacedAsync($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldPlaced'][0])
    {
        return $this->webhookVoiceHoldPlacedAsyncWithHttpInfo($webhook_voice_hold_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookVoiceHoldPlacedAsyncWithHttpInfo
     *
     * Voice Hold Placed
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldPlaced'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceHoldPlacedAsyncWithHttpInfo($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldPlaced'][0])
    {
        $returnType = '';
        $request = $this->webhookVoiceHoldPlacedRequest($webhook_voice_hold_event, $contentType);

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
     * Create request for operation 'webhookVoiceHoldPlaced'
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldPlaced'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookVoiceHoldPlacedRequest($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldPlaced'][0])
    {

        // verify the required parameter 'webhook_voice_hold_event' is set
        if ($webhook_voice_hold_event === null || (is_array($webhook_voice_hold_event) && count($webhook_voice_hold_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_voice_hold_event when calling webhookVoiceHoldPlaced'
            );
        }


        $resourcePath = '/voiceHoldPlaced';
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
        if (isset($webhook_voice_hold_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_voice_hold_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_voice_hold_event;
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
     * Operation webhookVoiceHoldReleased
     *
     * Voice Hold Released
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldReleased'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookVoiceHoldReleased($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldReleased'][0])
    {
        $this->webhookVoiceHoldReleasedWithHttpInfo($webhook_voice_hold_event, $contentType);
    }

    /**
     * Operation webhookVoiceHoldReleasedWithHttpInfo
     *
     * Voice Hold Released
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldReleased'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookVoiceHoldReleasedWithHttpInfo($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldReleased'][0])
    {
        $request = $this->webhookVoiceHoldReleasedRequest($webhook_voice_hold_event, $contentType);

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
     * Operation webhookVoiceHoldReleasedAsync
     *
     * Voice Hold Released
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldReleased'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceHoldReleasedAsync($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldReleased'][0])
    {
        return $this->webhookVoiceHoldReleasedAsyncWithHttpInfo($webhook_voice_hold_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookVoiceHoldReleasedAsyncWithHttpInfo
     *
     * Voice Hold Released
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldReleased'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceHoldReleasedAsyncWithHttpInfo($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldReleased'][0])
    {
        $returnType = '';
        $request = $this->webhookVoiceHoldReleasedRequest($webhook_voice_hold_event, $contentType);

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
     * Create request for operation 'webhookVoiceHoldReleased'
     *
     * @param  \CheckNetworks\Model\WebhookVoiceHoldEvent $webhook_voice_hold_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceHoldReleased'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookVoiceHoldReleasedRequest($webhook_voice_hold_event, string $contentType = self::contentTypes['webhookVoiceHoldReleased'][0])
    {

        // verify the required parameter 'webhook_voice_hold_event' is set
        if ($webhook_voice_hold_event === null || (is_array($webhook_voice_hold_event) && count($webhook_voice_hold_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_voice_hold_event when calling webhookVoiceHoldReleased'
            );
        }


        $resourcePath = '/voiceHoldReleased';
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
        if (isset($webhook_voice_hold_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_voice_hold_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_voice_hold_event;
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
     * Operation webhookVoiceRecordingReady
     *
     * Voice Recording Ready
     *
     * @param  \CheckNetworks\Model\WebhookVoiceRecordingEvent $webhook_voice_recording_event webhook_voice_recording_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceRecordingReady'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookVoiceRecordingReady($webhook_voice_recording_event, string $contentType = self::contentTypes['webhookVoiceRecordingReady'][0])
    {
        $this->webhookVoiceRecordingReadyWithHttpInfo($webhook_voice_recording_event, $contentType);
    }

    /**
     * Operation webhookVoiceRecordingReadyWithHttpInfo
     *
     * Voice Recording Ready
     *
     * @param  \CheckNetworks\Model\WebhookVoiceRecordingEvent $webhook_voice_recording_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceRecordingReady'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookVoiceRecordingReadyWithHttpInfo($webhook_voice_recording_event, string $contentType = self::contentTypes['webhookVoiceRecordingReady'][0])
    {
        $request = $this->webhookVoiceRecordingReadyRequest($webhook_voice_recording_event, $contentType);

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
     * Operation webhookVoiceRecordingReadyAsync
     *
     * Voice Recording Ready
     *
     * @param  \CheckNetworks\Model\WebhookVoiceRecordingEvent $webhook_voice_recording_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceRecordingReady'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceRecordingReadyAsync($webhook_voice_recording_event, string $contentType = self::contentTypes['webhookVoiceRecordingReady'][0])
    {
        return $this->webhookVoiceRecordingReadyAsyncWithHttpInfo($webhook_voice_recording_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookVoiceRecordingReadyAsyncWithHttpInfo
     *
     * Voice Recording Ready
     *
     * @param  \CheckNetworks\Model\WebhookVoiceRecordingEvent $webhook_voice_recording_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceRecordingReady'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceRecordingReadyAsyncWithHttpInfo($webhook_voice_recording_event, string $contentType = self::contentTypes['webhookVoiceRecordingReady'][0])
    {
        $returnType = '';
        $request = $this->webhookVoiceRecordingReadyRequest($webhook_voice_recording_event, $contentType);

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
     * Create request for operation 'webhookVoiceRecordingReady'
     *
     * @param  \CheckNetworks\Model\WebhookVoiceRecordingEvent $webhook_voice_recording_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceRecordingReady'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookVoiceRecordingReadyRequest($webhook_voice_recording_event, string $contentType = self::contentTypes['webhookVoiceRecordingReady'][0])
    {

        // verify the required parameter 'webhook_voice_recording_event' is set
        if ($webhook_voice_recording_event === null || (is_array($webhook_voice_recording_event) && count($webhook_voice_recording_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_voice_recording_event when calling webhookVoiceRecordingReady'
            );
        }


        $resourcePath = '/voiceRecordingReady';
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
        if (isset($webhook_voice_recording_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_voice_recording_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_voice_recording_event;
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
     * Operation webhookVoiceVoicemailReceived
     *
     * Voicemail Received
     *
     * @param  \CheckNetworks\Model\WebhookVoicemailEvent $webhook_voicemail_event webhook_voicemail_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceVoicemailReceived'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookVoiceVoicemailReceived($webhook_voicemail_event, string $contentType = self::contentTypes['webhookVoiceVoicemailReceived'][0])
    {
        $this->webhookVoiceVoicemailReceivedWithHttpInfo($webhook_voicemail_event, $contentType);
    }

    /**
     * Operation webhookVoiceVoicemailReceivedWithHttpInfo
     *
     * Voicemail Received
     *
     * @param  \CheckNetworks\Model\WebhookVoicemailEvent $webhook_voicemail_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceVoicemailReceived'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookVoiceVoicemailReceivedWithHttpInfo($webhook_voicemail_event, string $contentType = self::contentTypes['webhookVoiceVoicemailReceived'][0])
    {
        $request = $this->webhookVoiceVoicemailReceivedRequest($webhook_voicemail_event, $contentType);

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
     * Operation webhookVoiceVoicemailReceivedAsync
     *
     * Voicemail Received
     *
     * @param  \CheckNetworks\Model\WebhookVoicemailEvent $webhook_voicemail_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceVoicemailReceived'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceVoicemailReceivedAsync($webhook_voicemail_event, string $contentType = self::contentTypes['webhookVoiceVoicemailReceived'][0])
    {
        return $this->webhookVoiceVoicemailReceivedAsyncWithHttpInfo($webhook_voicemail_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookVoiceVoicemailReceivedAsyncWithHttpInfo
     *
     * Voicemail Received
     *
     * @param  \CheckNetworks\Model\WebhookVoicemailEvent $webhook_voicemail_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceVoicemailReceived'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookVoiceVoicemailReceivedAsyncWithHttpInfo($webhook_voicemail_event, string $contentType = self::contentTypes['webhookVoiceVoicemailReceived'][0])
    {
        $returnType = '';
        $request = $this->webhookVoiceVoicemailReceivedRequest($webhook_voicemail_event, $contentType);

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
     * Create request for operation 'webhookVoiceVoicemailReceived'
     *
     * @param  \CheckNetworks\Model\WebhookVoicemailEvent $webhook_voicemail_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookVoiceVoicemailReceived'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookVoiceVoicemailReceivedRequest($webhook_voicemail_event, string $contentType = self::contentTypes['webhookVoiceVoicemailReceived'][0])
    {

        // verify the required parameter 'webhook_voicemail_event' is set
        if ($webhook_voicemail_event === null || (is_array($webhook_voicemail_event) && count($webhook_voicemail_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_voicemail_event when calling webhookVoiceVoicemailReceived'
            );
        }


        $resourcePath = '/voicemailReceived';
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
        if (isset($webhook_voicemail_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_voicemail_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_voicemail_event;
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
