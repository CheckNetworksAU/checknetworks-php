<?php
/**
 * WebhookEventsAccountApi
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
 * WebhookEventsAccountApi Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class WebhookEventsAccountApi
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
        'webhookAccountReinstated' => [
            'application/json',
        ],
        'webhookAccountSuspended' => [
            'application/json',
        ],
        'webhookBookedChangeApplied' => [
            'application/json',
        ],
        'webhookBookedChangeCancelled' => [
            'application/json',
        ],
        'webhookBookedChangeFailed' => [
            'application/json',
        ],
        'webhookBookedChangeRescheduled' => [
            'application/json',
        ],
        'webhookCreditNoteIssued' => [
            'application/json',
        ],
        'webhookDisputeDecided' => [
            'application/json',
        ],
        'webhookDisputeRaised' => [
            'application/json',
        ],
        'webhookExportFailed' => [
            'application/json',
        ],
        'webhookExportReady' => [
            'application/json',
        ],
        'webhookInvoiceIssued' => [
            'application/json',
        ],
        'webhookInvoiceOverdue' => [
            'application/json',
        ],
        'webhookInvoicePaid' => [
            'application/json',
        ],
        'webhookInvoiceVoided' => [
            'application/json',
        ],
        'webhookPaymentFailed' => [
            'application/json',
        ],
        'webhookPaymentMethodExpiring' => [
            'application/json',
        ],
        'webhookPaymentReceived' => [
            'application/json',
        ],
        'webhookPaymentReversed' => [
            'application/json',
        ],
        'webhookPaymentSucceeded' => [
            'application/json',
        ],
        'webhookPriceRiseNoticed' => [
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
     * Operation webhookAccountReinstated
     *
     * Account Reinstated
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountReinstated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookAccountReinstated($webhook_account_event, string $contentType = self::contentTypes['webhookAccountReinstated'][0])
    {
        $this->webhookAccountReinstatedWithHttpInfo($webhook_account_event, $contentType);
    }

    /**
     * Operation webhookAccountReinstatedWithHttpInfo
     *
     * Account Reinstated
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountReinstated'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookAccountReinstatedWithHttpInfo($webhook_account_event, string $contentType = self::contentTypes['webhookAccountReinstated'][0])
    {
        $request = $this->webhookAccountReinstatedRequest($webhook_account_event, $contentType);

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
     * Operation webhookAccountReinstatedAsync
     *
     * Account Reinstated
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountReinstated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookAccountReinstatedAsync($webhook_account_event, string $contentType = self::contentTypes['webhookAccountReinstated'][0])
    {
        return $this->webhookAccountReinstatedAsyncWithHttpInfo($webhook_account_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookAccountReinstatedAsyncWithHttpInfo
     *
     * Account Reinstated
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountReinstated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookAccountReinstatedAsyncWithHttpInfo($webhook_account_event, string $contentType = self::contentTypes['webhookAccountReinstated'][0])
    {
        $returnType = '';
        $request = $this->webhookAccountReinstatedRequest($webhook_account_event, $contentType);

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
     * Create request for operation 'webhookAccountReinstated'
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountReinstated'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookAccountReinstatedRequest($webhook_account_event, string $contentType = self::contentTypes['webhookAccountReinstated'][0])
    {

        // verify the required parameter 'webhook_account_event' is set
        if ($webhook_account_event === null || (is_array($webhook_account_event) && count($webhook_account_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_account_event when calling webhookAccountReinstated'
            );
        }


        $resourcePath = '/accountReinstated';
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
        if (isset($webhook_account_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_account_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_account_event;
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
     * Operation webhookAccountSuspended
     *
     * Account Suspended
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountSuspended'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookAccountSuspended($webhook_account_event, string $contentType = self::contentTypes['webhookAccountSuspended'][0])
    {
        $this->webhookAccountSuspendedWithHttpInfo($webhook_account_event, $contentType);
    }

    /**
     * Operation webhookAccountSuspendedWithHttpInfo
     *
     * Account Suspended
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountSuspended'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookAccountSuspendedWithHttpInfo($webhook_account_event, string $contentType = self::contentTypes['webhookAccountSuspended'][0])
    {
        $request = $this->webhookAccountSuspendedRequest($webhook_account_event, $contentType);

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
     * Operation webhookAccountSuspendedAsync
     *
     * Account Suspended
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookAccountSuspendedAsync($webhook_account_event, string $contentType = self::contentTypes['webhookAccountSuspended'][0])
    {
        return $this->webhookAccountSuspendedAsyncWithHttpInfo($webhook_account_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookAccountSuspendedAsyncWithHttpInfo
     *
     * Account Suspended
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookAccountSuspendedAsyncWithHttpInfo($webhook_account_event, string $contentType = self::contentTypes['webhookAccountSuspended'][0])
    {
        $returnType = '';
        $request = $this->webhookAccountSuspendedRequest($webhook_account_event, $contentType);

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
     * Create request for operation 'webhookAccountSuspended'
     *
     * @param  \CheckNetworks\Model\WebhookAccountEvent $webhook_account_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookAccountSuspended'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookAccountSuspendedRequest($webhook_account_event, string $contentType = self::contentTypes['webhookAccountSuspended'][0])
    {

        // verify the required parameter 'webhook_account_event' is set
        if ($webhook_account_event === null || (is_array($webhook_account_event) && count($webhook_account_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_account_event when calling webhookAccountSuspended'
            );
        }


        $resourcePath = '/accountSuspended';
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
        if (isset($webhook_account_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_account_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_account_event;
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
     * Operation webhookBookedChangeApplied
     *
     * Booked Change Applied
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeApplied'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookBookedChangeApplied($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeApplied'][0])
    {
        $this->webhookBookedChangeAppliedWithHttpInfo($webhook_booked_change_event, $contentType);
    }

    /**
     * Operation webhookBookedChangeAppliedWithHttpInfo
     *
     * Booked Change Applied
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeApplied'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookBookedChangeAppliedWithHttpInfo($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeApplied'][0])
    {
        $request = $this->webhookBookedChangeAppliedRequest($webhook_booked_change_event, $contentType);

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
     * Operation webhookBookedChangeAppliedAsync
     *
     * Booked Change Applied
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeApplied'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeAppliedAsync($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeApplied'][0])
    {
        return $this->webhookBookedChangeAppliedAsyncWithHttpInfo($webhook_booked_change_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookBookedChangeAppliedAsyncWithHttpInfo
     *
     * Booked Change Applied
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeApplied'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeAppliedAsyncWithHttpInfo($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeApplied'][0])
    {
        $returnType = '';
        $request = $this->webhookBookedChangeAppliedRequest($webhook_booked_change_event, $contentType);

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
     * Create request for operation 'webhookBookedChangeApplied'
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeApplied'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookBookedChangeAppliedRequest($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeApplied'][0])
    {

        // verify the required parameter 'webhook_booked_change_event' is set
        if ($webhook_booked_change_event === null || (is_array($webhook_booked_change_event) && count($webhook_booked_change_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_booked_change_event when calling webhookBookedChangeApplied'
            );
        }


        $resourcePath = '/bookedChangeApplied';
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
        if (isset($webhook_booked_change_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_booked_change_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_booked_change_event;
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
     * Operation webhookBookedChangeCancelled
     *
     * Booked Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeCancelled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookBookedChangeCancelled($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeCancelled'][0])
    {
        $this->webhookBookedChangeCancelledWithHttpInfo($webhook_booked_change_event, $contentType);
    }

    /**
     * Operation webhookBookedChangeCancelledWithHttpInfo
     *
     * Booked Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeCancelled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookBookedChangeCancelledWithHttpInfo($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeCancelled'][0])
    {
        $request = $this->webhookBookedChangeCancelledRequest($webhook_booked_change_event, $contentType);

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
     * Operation webhookBookedChangeCancelledAsync
     *
     * Booked Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeCancelled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeCancelledAsync($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeCancelled'][0])
    {
        return $this->webhookBookedChangeCancelledAsyncWithHttpInfo($webhook_booked_change_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookBookedChangeCancelledAsyncWithHttpInfo
     *
     * Booked Change Cancelled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeCancelled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeCancelledAsyncWithHttpInfo($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeCancelled'][0])
    {
        $returnType = '';
        $request = $this->webhookBookedChangeCancelledRequest($webhook_booked_change_event, $contentType);

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
     * Create request for operation 'webhookBookedChangeCancelled'
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeEvent $webhook_booked_change_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeCancelled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookBookedChangeCancelledRequest($webhook_booked_change_event, string $contentType = self::contentTypes['webhookBookedChangeCancelled'][0])
    {

        // verify the required parameter 'webhook_booked_change_event' is set
        if ($webhook_booked_change_event === null || (is_array($webhook_booked_change_event) && count($webhook_booked_change_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_booked_change_event when calling webhookBookedChangeCancelled'
            );
        }


        $resourcePath = '/bookedChangeCancelled';
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
        if (isset($webhook_booked_change_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_booked_change_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_booked_change_event;
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
     * Operation webhookBookedChangeFailed
     *
     * Booked Change Failed
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeFailedEvent $webhook_booked_change_failed_event webhook_booked_change_failed_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeFailed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookBookedChangeFailed($webhook_booked_change_failed_event, string $contentType = self::contentTypes['webhookBookedChangeFailed'][0])
    {
        $this->webhookBookedChangeFailedWithHttpInfo($webhook_booked_change_failed_event, $contentType);
    }

    /**
     * Operation webhookBookedChangeFailedWithHttpInfo
     *
     * Booked Change Failed
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeFailedEvent $webhook_booked_change_failed_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeFailed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookBookedChangeFailedWithHttpInfo($webhook_booked_change_failed_event, string $contentType = self::contentTypes['webhookBookedChangeFailed'][0])
    {
        $request = $this->webhookBookedChangeFailedRequest($webhook_booked_change_failed_event, $contentType);

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
     * Operation webhookBookedChangeFailedAsync
     *
     * Booked Change Failed
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeFailedEvent $webhook_booked_change_failed_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeFailedAsync($webhook_booked_change_failed_event, string $contentType = self::contentTypes['webhookBookedChangeFailed'][0])
    {
        return $this->webhookBookedChangeFailedAsyncWithHttpInfo($webhook_booked_change_failed_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookBookedChangeFailedAsyncWithHttpInfo
     *
     * Booked Change Failed
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeFailedEvent $webhook_booked_change_failed_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeFailedAsyncWithHttpInfo($webhook_booked_change_failed_event, string $contentType = self::contentTypes['webhookBookedChangeFailed'][0])
    {
        $returnType = '';
        $request = $this->webhookBookedChangeFailedRequest($webhook_booked_change_failed_event, $contentType);

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
     * Create request for operation 'webhookBookedChangeFailed'
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeFailedEvent $webhook_booked_change_failed_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookBookedChangeFailedRequest($webhook_booked_change_failed_event, string $contentType = self::contentTypes['webhookBookedChangeFailed'][0])
    {

        // verify the required parameter 'webhook_booked_change_failed_event' is set
        if ($webhook_booked_change_failed_event === null || (is_array($webhook_booked_change_failed_event) && count($webhook_booked_change_failed_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_booked_change_failed_event when calling webhookBookedChangeFailed'
            );
        }


        $resourcePath = '/bookedChangeFailed';
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
        if (isset($webhook_booked_change_failed_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_booked_change_failed_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_booked_change_failed_event;
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
     * Operation webhookBookedChangeRescheduled
     *
     * Booked Change Rescheduled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeRescheduledEvent $webhook_booked_change_rescheduled_event webhook_booked_change_rescheduled_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeRescheduled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookBookedChangeRescheduled($webhook_booked_change_rescheduled_event, string $contentType = self::contentTypes['webhookBookedChangeRescheduled'][0])
    {
        $this->webhookBookedChangeRescheduledWithHttpInfo($webhook_booked_change_rescheduled_event, $contentType);
    }

    /**
     * Operation webhookBookedChangeRescheduledWithHttpInfo
     *
     * Booked Change Rescheduled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeRescheduledEvent $webhook_booked_change_rescheduled_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeRescheduled'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookBookedChangeRescheduledWithHttpInfo($webhook_booked_change_rescheduled_event, string $contentType = self::contentTypes['webhookBookedChangeRescheduled'][0])
    {
        $request = $this->webhookBookedChangeRescheduledRequest($webhook_booked_change_rescheduled_event, $contentType);

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
     * Operation webhookBookedChangeRescheduledAsync
     *
     * Booked Change Rescheduled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeRescheduledEvent $webhook_booked_change_rescheduled_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeRescheduled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeRescheduledAsync($webhook_booked_change_rescheduled_event, string $contentType = self::contentTypes['webhookBookedChangeRescheduled'][0])
    {
        return $this->webhookBookedChangeRescheduledAsyncWithHttpInfo($webhook_booked_change_rescheduled_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookBookedChangeRescheduledAsyncWithHttpInfo
     *
     * Booked Change Rescheduled
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeRescheduledEvent $webhook_booked_change_rescheduled_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeRescheduled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookBookedChangeRescheduledAsyncWithHttpInfo($webhook_booked_change_rescheduled_event, string $contentType = self::contentTypes['webhookBookedChangeRescheduled'][0])
    {
        $returnType = '';
        $request = $this->webhookBookedChangeRescheduledRequest($webhook_booked_change_rescheduled_event, $contentType);

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
     * Create request for operation 'webhookBookedChangeRescheduled'
     *
     * @param  \CheckNetworks\Model\WebhookBookedChangeRescheduledEvent $webhook_booked_change_rescheduled_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookBookedChangeRescheduled'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookBookedChangeRescheduledRequest($webhook_booked_change_rescheduled_event, string $contentType = self::contentTypes['webhookBookedChangeRescheduled'][0])
    {

        // verify the required parameter 'webhook_booked_change_rescheduled_event' is set
        if ($webhook_booked_change_rescheduled_event === null || (is_array($webhook_booked_change_rescheduled_event) && count($webhook_booked_change_rescheduled_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_booked_change_rescheduled_event when calling webhookBookedChangeRescheduled'
            );
        }


        $resourcePath = '/bookedChangeRescheduled';
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
        if (isset($webhook_booked_change_rescheduled_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_booked_change_rescheduled_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_booked_change_rescheduled_event;
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
     * Operation webhookCreditNoteIssued
     *
     * Credit Note Issued
     *
     * @param  \CheckNetworks\Model\WebhookCreditNoteEvent $webhook_credit_note_event webhook_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookCreditNoteIssued($webhook_credit_note_event, string $contentType = self::contentTypes['webhookCreditNoteIssued'][0])
    {
        $this->webhookCreditNoteIssuedWithHttpInfo($webhook_credit_note_event, $contentType);
    }

    /**
     * Operation webhookCreditNoteIssuedWithHttpInfo
     *
     * Credit Note Issued
     *
     * @param  \CheckNetworks\Model\WebhookCreditNoteEvent $webhook_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookCreditNoteIssuedWithHttpInfo($webhook_credit_note_event, string $contentType = self::contentTypes['webhookCreditNoteIssued'][0])
    {
        $request = $this->webhookCreditNoteIssuedRequest($webhook_credit_note_event, $contentType);

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
     * Operation webhookCreditNoteIssuedAsync
     *
     * Credit Note Issued
     *
     * @param  \CheckNetworks\Model\WebhookCreditNoteEvent $webhook_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookCreditNoteIssuedAsync($webhook_credit_note_event, string $contentType = self::contentTypes['webhookCreditNoteIssued'][0])
    {
        return $this->webhookCreditNoteIssuedAsyncWithHttpInfo($webhook_credit_note_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookCreditNoteIssuedAsyncWithHttpInfo
     *
     * Credit Note Issued
     *
     * @param  \CheckNetworks\Model\WebhookCreditNoteEvent $webhook_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookCreditNoteIssuedAsyncWithHttpInfo($webhook_credit_note_event, string $contentType = self::contentTypes['webhookCreditNoteIssued'][0])
    {
        $returnType = '';
        $request = $this->webhookCreditNoteIssuedRequest($webhook_credit_note_event, $contentType);

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
     * Create request for operation 'webhookCreditNoteIssued'
     *
     * @param  \CheckNetworks\Model\WebhookCreditNoteEvent $webhook_credit_note_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookCreditNoteIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookCreditNoteIssuedRequest($webhook_credit_note_event, string $contentType = self::contentTypes['webhookCreditNoteIssued'][0])
    {

        // verify the required parameter 'webhook_credit_note_event' is set
        if ($webhook_credit_note_event === null || (is_array($webhook_credit_note_event) && count($webhook_credit_note_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_credit_note_event when calling webhookCreditNoteIssued'
            );
        }


        $resourcePath = '/creditNoteIssued';
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
        if (isset($webhook_credit_note_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_credit_note_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_credit_note_event;
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
     * Operation webhookDisputeDecided
     *
     * Dispute Decided
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeDecided'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookDisputeDecided($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeDecided'][0])
    {
        $this->webhookDisputeDecidedWithHttpInfo($webhook_dispute_event, $contentType);
    }

    /**
     * Operation webhookDisputeDecidedWithHttpInfo
     *
     * Dispute Decided
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeDecided'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookDisputeDecidedWithHttpInfo($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeDecided'][0])
    {
        $request = $this->webhookDisputeDecidedRequest($webhook_dispute_event, $contentType);

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
     * Operation webhookDisputeDecidedAsync
     *
     * Dispute Decided
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeDecided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookDisputeDecidedAsync($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeDecided'][0])
    {
        return $this->webhookDisputeDecidedAsyncWithHttpInfo($webhook_dispute_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookDisputeDecidedAsyncWithHttpInfo
     *
     * Dispute Decided
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeDecided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookDisputeDecidedAsyncWithHttpInfo($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeDecided'][0])
    {
        $returnType = '';
        $request = $this->webhookDisputeDecidedRequest($webhook_dispute_event, $contentType);

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
     * Create request for operation 'webhookDisputeDecided'
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeDecided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookDisputeDecidedRequest($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeDecided'][0])
    {

        // verify the required parameter 'webhook_dispute_event' is set
        if ($webhook_dispute_event === null || (is_array($webhook_dispute_event) && count($webhook_dispute_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_dispute_event when calling webhookDisputeDecided'
            );
        }


        $resourcePath = '/disputeDecided';
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
        if (isset($webhook_dispute_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_dispute_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_dispute_event;
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
     * Operation webhookDisputeRaised
     *
     * Dispute Raised
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeRaised'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookDisputeRaised($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeRaised'][0])
    {
        $this->webhookDisputeRaisedWithHttpInfo($webhook_dispute_event, $contentType);
    }

    /**
     * Operation webhookDisputeRaisedWithHttpInfo
     *
     * Dispute Raised
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeRaised'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookDisputeRaisedWithHttpInfo($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeRaised'][0])
    {
        $request = $this->webhookDisputeRaisedRequest($webhook_dispute_event, $contentType);

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
     * Operation webhookDisputeRaisedAsync
     *
     * Dispute Raised
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeRaised'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookDisputeRaisedAsync($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeRaised'][0])
    {
        return $this->webhookDisputeRaisedAsyncWithHttpInfo($webhook_dispute_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookDisputeRaisedAsyncWithHttpInfo
     *
     * Dispute Raised
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeRaised'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookDisputeRaisedAsyncWithHttpInfo($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeRaised'][0])
    {
        $returnType = '';
        $request = $this->webhookDisputeRaisedRequest($webhook_dispute_event, $contentType);

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
     * Create request for operation 'webhookDisputeRaised'
     *
     * @param  \CheckNetworks\Model\WebhookDisputeEvent $webhook_dispute_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookDisputeRaised'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookDisputeRaisedRequest($webhook_dispute_event, string $contentType = self::contentTypes['webhookDisputeRaised'][0])
    {

        // verify the required parameter 'webhook_dispute_event' is set
        if ($webhook_dispute_event === null || (is_array($webhook_dispute_event) && count($webhook_dispute_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_dispute_event when calling webhookDisputeRaised'
            );
        }


        $resourcePath = '/disputeRaised';
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
        if (isset($webhook_dispute_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_dispute_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_dispute_event;
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
     * Operation webhookExportFailed
     *
     * Export Failed
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportFailed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookExportFailed($webhook_export_event, string $contentType = self::contentTypes['webhookExportFailed'][0])
    {
        $this->webhookExportFailedWithHttpInfo($webhook_export_event, $contentType);
    }

    /**
     * Operation webhookExportFailedWithHttpInfo
     *
     * Export Failed
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportFailed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookExportFailedWithHttpInfo($webhook_export_event, string $contentType = self::contentTypes['webhookExportFailed'][0])
    {
        $request = $this->webhookExportFailedRequest($webhook_export_event, $contentType);

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
     * Operation webhookExportFailedAsync
     *
     * Export Failed
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookExportFailedAsync($webhook_export_event, string $contentType = self::contentTypes['webhookExportFailed'][0])
    {
        return $this->webhookExportFailedAsyncWithHttpInfo($webhook_export_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookExportFailedAsyncWithHttpInfo
     *
     * Export Failed
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookExportFailedAsyncWithHttpInfo($webhook_export_event, string $contentType = self::contentTypes['webhookExportFailed'][0])
    {
        $returnType = '';
        $request = $this->webhookExportFailedRequest($webhook_export_event, $contentType);

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
     * Create request for operation 'webhookExportFailed'
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookExportFailedRequest($webhook_export_event, string $contentType = self::contentTypes['webhookExportFailed'][0])
    {

        // verify the required parameter 'webhook_export_event' is set
        if ($webhook_export_event === null || (is_array($webhook_export_event) && count($webhook_export_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_export_event when calling webhookExportFailed'
            );
        }


        $resourcePath = '/exportFailed';
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
        if (isset($webhook_export_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_export_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_export_event;
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
     * Operation webhookExportReady
     *
     * Export Ready
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportReady'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookExportReady($webhook_export_event, string $contentType = self::contentTypes['webhookExportReady'][0])
    {
        $this->webhookExportReadyWithHttpInfo($webhook_export_event, $contentType);
    }

    /**
     * Operation webhookExportReadyWithHttpInfo
     *
     * Export Ready
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportReady'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookExportReadyWithHttpInfo($webhook_export_event, string $contentType = self::contentTypes['webhookExportReady'][0])
    {
        $request = $this->webhookExportReadyRequest($webhook_export_event, $contentType);

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
     * Operation webhookExportReadyAsync
     *
     * Export Ready
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportReady'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookExportReadyAsync($webhook_export_event, string $contentType = self::contentTypes['webhookExportReady'][0])
    {
        return $this->webhookExportReadyAsyncWithHttpInfo($webhook_export_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookExportReadyAsyncWithHttpInfo
     *
     * Export Ready
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportReady'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookExportReadyAsyncWithHttpInfo($webhook_export_event, string $contentType = self::contentTypes['webhookExportReady'][0])
    {
        $returnType = '';
        $request = $this->webhookExportReadyRequest($webhook_export_event, $contentType);

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
     * Create request for operation 'webhookExportReady'
     *
     * @param  \CheckNetworks\Model\WebhookExportEvent $webhook_export_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookExportReady'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookExportReadyRequest($webhook_export_event, string $contentType = self::contentTypes['webhookExportReady'][0])
    {

        // verify the required parameter 'webhook_export_event' is set
        if ($webhook_export_event === null || (is_array($webhook_export_event) && count($webhook_export_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_export_event when calling webhookExportReady'
            );
        }


        $resourcePath = '/exportReady';
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
        if (isset($webhook_export_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_export_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_export_event;
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
     * Operation webhookInvoiceIssued
     *
     * Invoice Issued
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceIssued'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookInvoiceIssued($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceIssued'][0])
    {
        $this->webhookInvoiceIssuedWithHttpInfo($webhook_invoice_event, $contentType);
    }

    /**
     * Operation webhookInvoiceIssuedWithHttpInfo
     *
     * Invoice Issued
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceIssued'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookInvoiceIssuedWithHttpInfo($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceIssued'][0])
    {
        $request = $this->webhookInvoiceIssuedRequest($webhook_invoice_event, $contentType);

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
     * Operation webhookInvoiceIssuedAsync
     *
     * Invoice Issued
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoiceIssuedAsync($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceIssued'][0])
    {
        return $this->webhookInvoiceIssuedAsyncWithHttpInfo($webhook_invoice_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookInvoiceIssuedAsyncWithHttpInfo
     *
     * Invoice Issued
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoiceIssuedAsyncWithHttpInfo($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceIssued'][0])
    {
        $returnType = '';
        $request = $this->webhookInvoiceIssuedRequest($webhook_invoice_event, $contentType);

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
     * Create request for operation 'webhookInvoiceIssued'
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceIssued'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookInvoiceIssuedRequest($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceIssued'][0])
    {

        // verify the required parameter 'webhook_invoice_event' is set
        if ($webhook_invoice_event === null || (is_array($webhook_invoice_event) && count($webhook_invoice_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_invoice_event when calling webhookInvoiceIssued'
            );
        }


        $resourcePath = '/invoiceIssued';
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
        if (isset($webhook_invoice_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_invoice_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_invoice_event;
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
     * Operation webhookInvoiceOverdue
     *
     * Invoice Overdue
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceOverdueEvent $webhook_invoice_overdue_event webhook_invoice_overdue_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceOverdue'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookInvoiceOverdue($webhook_invoice_overdue_event, string $contentType = self::contentTypes['webhookInvoiceOverdue'][0])
    {
        $this->webhookInvoiceOverdueWithHttpInfo($webhook_invoice_overdue_event, $contentType);
    }

    /**
     * Operation webhookInvoiceOverdueWithHttpInfo
     *
     * Invoice Overdue
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceOverdueEvent $webhook_invoice_overdue_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceOverdue'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookInvoiceOverdueWithHttpInfo($webhook_invoice_overdue_event, string $contentType = self::contentTypes['webhookInvoiceOverdue'][0])
    {
        $request = $this->webhookInvoiceOverdueRequest($webhook_invoice_overdue_event, $contentType);

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
     * Operation webhookInvoiceOverdueAsync
     *
     * Invoice Overdue
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceOverdueEvent $webhook_invoice_overdue_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceOverdue'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoiceOverdueAsync($webhook_invoice_overdue_event, string $contentType = self::contentTypes['webhookInvoiceOverdue'][0])
    {
        return $this->webhookInvoiceOverdueAsyncWithHttpInfo($webhook_invoice_overdue_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookInvoiceOverdueAsyncWithHttpInfo
     *
     * Invoice Overdue
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceOverdueEvent $webhook_invoice_overdue_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceOverdue'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoiceOverdueAsyncWithHttpInfo($webhook_invoice_overdue_event, string $contentType = self::contentTypes['webhookInvoiceOverdue'][0])
    {
        $returnType = '';
        $request = $this->webhookInvoiceOverdueRequest($webhook_invoice_overdue_event, $contentType);

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
     * Create request for operation 'webhookInvoiceOverdue'
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceOverdueEvent $webhook_invoice_overdue_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceOverdue'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookInvoiceOverdueRequest($webhook_invoice_overdue_event, string $contentType = self::contentTypes['webhookInvoiceOverdue'][0])
    {

        // verify the required parameter 'webhook_invoice_overdue_event' is set
        if ($webhook_invoice_overdue_event === null || (is_array($webhook_invoice_overdue_event) && count($webhook_invoice_overdue_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_invoice_overdue_event when calling webhookInvoiceOverdue'
            );
        }


        $resourcePath = '/invoiceOverdue';
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
        if (isset($webhook_invoice_overdue_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_invoice_overdue_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_invoice_overdue_event;
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
     * Operation webhookInvoicePaid
     *
     * Invoice Paid
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoicePaid'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookInvoicePaid($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoicePaid'][0])
    {
        $this->webhookInvoicePaidWithHttpInfo($webhook_invoice_event, $contentType);
    }

    /**
     * Operation webhookInvoicePaidWithHttpInfo
     *
     * Invoice Paid
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoicePaid'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookInvoicePaidWithHttpInfo($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoicePaid'][0])
    {
        $request = $this->webhookInvoicePaidRequest($webhook_invoice_event, $contentType);

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
     * Operation webhookInvoicePaidAsync
     *
     * Invoice Paid
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoicePaid'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoicePaidAsync($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoicePaid'][0])
    {
        return $this->webhookInvoicePaidAsyncWithHttpInfo($webhook_invoice_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookInvoicePaidAsyncWithHttpInfo
     *
     * Invoice Paid
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoicePaid'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoicePaidAsyncWithHttpInfo($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoicePaid'][0])
    {
        $returnType = '';
        $request = $this->webhookInvoicePaidRequest($webhook_invoice_event, $contentType);

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
     * Create request for operation 'webhookInvoicePaid'
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoicePaid'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookInvoicePaidRequest($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoicePaid'][0])
    {

        // verify the required parameter 'webhook_invoice_event' is set
        if ($webhook_invoice_event === null || (is_array($webhook_invoice_event) && count($webhook_invoice_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_invoice_event when calling webhookInvoicePaid'
            );
        }


        $resourcePath = '/invoicePaid';
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
        if (isset($webhook_invoice_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_invoice_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_invoice_event;
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
     * Operation webhookInvoiceVoided
     *
     * Invoice Voided
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceVoided'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookInvoiceVoided($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceVoided'][0])
    {
        $this->webhookInvoiceVoidedWithHttpInfo($webhook_invoice_event, $contentType);
    }

    /**
     * Operation webhookInvoiceVoidedWithHttpInfo
     *
     * Invoice Voided
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceVoided'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookInvoiceVoidedWithHttpInfo($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceVoided'][0])
    {
        $request = $this->webhookInvoiceVoidedRequest($webhook_invoice_event, $contentType);

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
     * Operation webhookInvoiceVoidedAsync
     *
     * Invoice Voided
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceVoided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoiceVoidedAsync($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceVoided'][0])
    {
        return $this->webhookInvoiceVoidedAsyncWithHttpInfo($webhook_invoice_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookInvoiceVoidedAsyncWithHttpInfo
     *
     * Invoice Voided
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceVoided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookInvoiceVoidedAsyncWithHttpInfo($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceVoided'][0])
    {
        $returnType = '';
        $request = $this->webhookInvoiceVoidedRequest($webhook_invoice_event, $contentType);

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
     * Create request for operation 'webhookInvoiceVoided'
     *
     * @param  \CheckNetworks\Model\WebhookInvoiceEvent $webhook_invoice_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookInvoiceVoided'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookInvoiceVoidedRequest($webhook_invoice_event, string $contentType = self::contentTypes['webhookInvoiceVoided'][0])
    {

        // verify the required parameter 'webhook_invoice_event' is set
        if ($webhook_invoice_event === null || (is_array($webhook_invoice_event) && count($webhook_invoice_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_invoice_event when calling webhookInvoiceVoided'
            );
        }


        $resourcePath = '/invoiceVoided';
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
        if (isset($webhook_invoice_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_invoice_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_invoice_event;
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
     * Operation webhookPaymentFailed
     *
     * Payment Failed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentFailed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookPaymentFailed($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentFailed'][0])
    {
        $this->webhookPaymentFailedWithHttpInfo($webhook_payment_event, $contentType);
    }

    /**
     * Operation webhookPaymentFailedWithHttpInfo
     *
     * Payment Failed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentFailed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookPaymentFailedWithHttpInfo($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentFailed'][0])
    {
        $request = $this->webhookPaymentFailedRequest($webhook_payment_event, $contentType);

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
     * Operation webhookPaymentFailedAsync
     *
     * Payment Failed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentFailedAsync($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentFailed'][0])
    {
        return $this->webhookPaymentFailedAsyncWithHttpInfo($webhook_payment_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookPaymentFailedAsyncWithHttpInfo
     *
     * Payment Failed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentFailedAsyncWithHttpInfo($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentFailed'][0])
    {
        $returnType = '';
        $request = $this->webhookPaymentFailedRequest($webhook_payment_event, $contentType);

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
     * Create request for operation 'webhookPaymentFailed'
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentFailed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookPaymentFailedRequest($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentFailed'][0])
    {

        // verify the required parameter 'webhook_payment_event' is set
        if ($webhook_payment_event === null || (is_array($webhook_payment_event) && count($webhook_payment_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_payment_event when calling webhookPaymentFailed'
            );
        }


        $resourcePath = '/paymentFailed';
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
        if (isset($webhook_payment_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_payment_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_payment_event;
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
     * Operation webhookPaymentMethodExpiring
     *
     * Card Expiring
     *
     * @param  \CheckNetworks\Model\WebhookPaymentMethodEvent $webhook_payment_method_event webhook_payment_method_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentMethodExpiring'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookPaymentMethodExpiring($webhook_payment_method_event, string $contentType = self::contentTypes['webhookPaymentMethodExpiring'][0])
    {
        $this->webhookPaymentMethodExpiringWithHttpInfo($webhook_payment_method_event, $contentType);
    }

    /**
     * Operation webhookPaymentMethodExpiringWithHttpInfo
     *
     * Card Expiring
     *
     * @param  \CheckNetworks\Model\WebhookPaymentMethodEvent $webhook_payment_method_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentMethodExpiring'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookPaymentMethodExpiringWithHttpInfo($webhook_payment_method_event, string $contentType = self::contentTypes['webhookPaymentMethodExpiring'][0])
    {
        $request = $this->webhookPaymentMethodExpiringRequest($webhook_payment_method_event, $contentType);

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
     * Operation webhookPaymentMethodExpiringAsync
     *
     * Card Expiring
     *
     * @param  \CheckNetworks\Model\WebhookPaymentMethodEvent $webhook_payment_method_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentMethodExpiring'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentMethodExpiringAsync($webhook_payment_method_event, string $contentType = self::contentTypes['webhookPaymentMethodExpiring'][0])
    {
        return $this->webhookPaymentMethodExpiringAsyncWithHttpInfo($webhook_payment_method_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookPaymentMethodExpiringAsyncWithHttpInfo
     *
     * Card Expiring
     *
     * @param  \CheckNetworks\Model\WebhookPaymentMethodEvent $webhook_payment_method_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentMethodExpiring'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentMethodExpiringAsyncWithHttpInfo($webhook_payment_method_event, string $contentType = self::contentTypes['webhookPaymentMethodExpiring'][0])
    {
        $returnType = '';
        $request = $this->webhookPaymentMethodExpiringRequest($webhook_payment_method_event, $contentType);

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
     * Create request for operation 'webhookPaymentMethodExpiring'
     *
     * @param  \CheckNetworks\Model\WebhookPaymentMethodEvent $webhook_payment_method_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentMethodExpiring'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookPaymentMethodExpiringRequest($webhook_payment_method_event, string $contentType = self::contentTypes['webhookPaymentMethodExpiring'][0])
    {

        // verify the required parameter 'webhook_payment_method_event' is set
        if ($webhook_payment_method_event === null || (is_array($webhook_payment_method_event) && count($webhook_payment_method_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_payment_method_event when calling webhookPaymentMethodExpiring'
            );
        }


        $resourcePath = '/paymentMethodExpiring';
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
        if (isset($webhook_payment_method_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_payment_method_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_payment_method_event;
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
     * Operation webhookPaymentReceived
     *
     * Payment Received
     *
     * @param  \CheckNetworks\Model\WebhookPaymentReceivedRequest $webhook_payment_received_request webhook_payment_received_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReceived'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookPaymentReceived($webhook_payment_received_request, string $contentType = self::contentTypes['webhookPaymentReceived'][0])
    {
        $this->webhookPaymentReceivedWithHttpInfo($webhook_payment_received_request, $contentType);
    }

    /**
     * Operation webhookPaymentReceivedWithHttpInfo
     *
     * Payment Received
     *
     * @param  \CheckNetworks\Model\WebhookPaymentReceivedRequest $webhook_payment_received_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReceived'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookPaymentReceivedWithHttpInfo($webhook_payment_received_request, string $contentType = self::contentTypes['webhookPaymentReceived'][0])
    {
        $request = $this->webhookPaymentReceivedRequest($webhook_payment_received_request, $contentType);

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
     * Operation webhookPaymentReceivedAsync
     *
     * Payment Received
     *
     * @param  \CheckNetworks\Model\WebhookPaymentReceivedRequest $webhook_payment_received_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReceived'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentReceivedAsync($webhook_payment_received_request, string $contentType = self::contentTypes['webhookPaymentReceived'][0])
    {
        return $this->webhookPaymentReceivedAsyncWithHttpInfo($webhook_payment_received_request, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookPaymentReceivedAsyncWithHttpInfo
     *
     * Payment Received
     *
     * @param  \CheckNetworks\Model\WebhookPaymentReceivedRequest $webhook_payment_received_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReceived'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentReceivedAsyncWithHttpInfo($webhook_payment_received_request, string $contentType = self::contentTypes['webhookPaymentReceived'][0])
    {
        $returnType = '';
        $request = $this->webhookPaymentReceivedRequest($webhook_payment_received_request, $contentType);

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
     * Create request for operation 'webhookPaymentReceived'
     *
     * @param  \CheckNetworks\Model\WebhookPaymentReceivedRequest $webhook_payment_received_request (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReceived'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookPaymentReceivedRequest($webhook_payment_received_request, string $contentType = self::contentTypes['webhookPaymentReceived'][0])
    {

        // verify the required parameter 'webhook_payment_received_request' is set
        if ($webhook_payment_received_request === null || (is_array($webhook_payment_received_request) && count($webhook_payment_received_request) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_payment_received_request when calling webhookPaymentReceived'
            );
        }


        $resourcePath = '/paymentReceived';
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
        if (isset($webhook_payment_received_request)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_payment_received_request), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_payment_received_request;
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
     * Operation webhookPaymentReversed
     *
     * Payment Reversed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReversed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookPaymentReversed($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentReversed'][0])
    {
        $this->webhookPaymentReversedWithHttpInfo($webhook_payment_event, $contentType);
    }

    /**
     * Operation webhookPaymentReversedWithHttpInfo
     *
     * Payment Reversed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReversed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookPaymentReversedWithHttpInfo($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentReversed'][0])
    {
        $request = $this->webhookPaymentReversedRequest($webhook_payment_event, $contentType);

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
     * Operation webhookPaymentReversedAsync
     *
     * Payment Reversed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReversed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentReversedAsync($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentReversed'][0])
    {
        return $this->webhookPaymentReversedAsyncWithHttpInfo($webhook_payment_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookPaymentReversedAsyncWithHttpInfo
     *
     * Payment Reversed
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReversed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentReversedAsyncWithHttpInfo($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentReversed'][0])
    {
        $returnType = '';
        $request = $this->webhookPaymentReversedRequest($webhook_payment_event, $contentType);

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
     * Create request for operation 'webhookPaymentReversed'
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentReversed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookPaymentReversedRequest($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentReversed'][0])
    {

        // verify the required parameter 'webhook_payment_event' is set
        if ($webhook_payment_event === null || (is_array($webhook_payment_event) && count($webhook_payment_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_payment_event when calling webhookPaymentReversed'
            );
        }


        $resourcePath = '/paymentReversed';
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
        if (isset($webhook_payment_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_payment_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_payment_event;
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
     * Operation webhookPaymentSucceeded
     *
     * Payment Succeeded
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentSucceeded'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookPaymentSucceeded($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentSucceeded'][0])
    {
        $this->webhookPaymentSucceededWithHttpInfo($webhook_payment_event, $contentType);
    }

    /**
     * Operation webhookPaymentSucceededWithHttpInfo
     *
     * Payment Succeeded
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentSucceeded'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookPaymentSucceededWithHttpInfo($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentSucceeded'][0])
    {
        $request = $this->webhookPaymentSucceededRequest($webhook_payment_event, $contentType);

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
     * Operation webhookPaymentSucceededAsync
     *
     * Payment Succeeded
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentSucceeded'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentSucceededAsync($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentSucceeded'][0])
    {
        return $this->webhookPaymentSucceededAsyncWithHttpInfo($webhook_payment_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookPaymentSucceededAsyncWithHttpInfo
     *
     * Payment Succeeded
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentSucceeded'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPaymentSucceededAsyncWithHttpInfo($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentSucceeded'][0])
    {
        $returnType = '';
        $request = $this->webhookPaymentSucceededRequest($webhook_payment_event, $contentType);

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
     * Create request for operation 'webhookPaymentSucceeded'
     *
     * @param  \CheckNetworks\Model\WebhookPaymentEvent $webhook_payment_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPaymentSucceeded'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookPaymentSucceededRequest($webhook_payment_event, string $contentType = self::contentTypes['webhookPaymentSucceeded'][0])
    {

        // verify the required parameter 'webhook_payment_event' is set
        if ($webhook_payment_event === null || (is_array($webhook_payment_event) && count($webhook_payment_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_payment_event when calling webhookPaymentSucceeded'
            );
        }


        $resourcePath = '/paymentSucceeded';
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
        if (isset($webhook_payment_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_payment_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_payment_event;
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
     * Operation webhookPriceRiseNoticed
     *
     * Price Rise Notice
     *
     * @param  \CheckNetworks\Model\WebhookPriceRiseEvent $webhook_price_rise_event webhook_price_rise_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPriceRiseNoticed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return void
     */
    public function webhookPriceRiseNoticed($webhook_price_rise_event, string $contentType = self::contentTypes['webhookPriceRiseNoticed'][0])
    {
        $this->webhookPriceRiseNoticedWithHttpInfo($webhook_price_rise_event, $contentType);
    }

    /**
     * Operation webhookPriceRiseNoticedWithHttpInfo
     *
     * Price Rise Notice
     *
     * @param  \CheckNetworks\Model\WebhookPriceRiseEvent $webhook_price_rise_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPriceRiseNoticed'] to see the possible values for this operation
     *
     * @throws \CheckNetworks\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of null, HTTP status code, HTTP response headers (array of strings)
     */
    public function webhookPriceRiseNoticedWithHttpInfo($webhook_price_rise_event, string $contentType = self::contentTypes['webhookPriceRiseNoticed'][0])
    {
        $request = $this->webhookPriceRiseNoticedRequest($webhook_price_rise_event, $contentType);

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
     * Operation webhookPriceRiseNoticedAsync
     *
     * Price Rise Notice
     *
     * @param  \CheckNetworks\Model\WebhookPriceRiseEvent $webhook_price_rise_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPriceRiseNoticed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPriceRiseNoticedAsync($webhook_price_rise_event, string $contentType = self::contentTypes['webhookPriceRiseNoticed'][0])
    {
        return $this->webhookPriceRiseNoticedAsyncWithHttpInfo($webhook_price_rise_event, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation webhookPriceRiseNoticedAsyncWithHttpInfo
     *
     * Price Rise Notice
     *
     * @param  \CheckNetworks\Model\WebhookPriceRiseEvent $webhook_price_rise_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPriceRiseNoticed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function webhookPriceRiseNoticedAsyncWithHttpInfo($webhook_price_rise_event, string $contentType = self::contentTypes['webhookPriceRiseNoticed'][0])
    {
        $returnType = '';
        $request = $this->webhookPriceRiseNoticedRequest($webhook_price_rise_event, $contentType);

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
     * Create request for operation 'webhookPriceRiseNoticed'
     *
     * @param  \CheckNetworks\Model\WebhookPriceRiseEvent $webhook_price_rise_event (required)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['webhookPriceRiseNoticed'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function webhookPriceRiseNoticedRequest($webhook_price_rise_event, string $contentType = self::contentTypes['webhookPriceRiseNoticed'][0])
    {

        // verify the required parameter 'webhook_price_rise_event' is set
        if ($webhook_price_rise_event === null || (is_array($webhook_price_rise_event) && count($webhook_price_rise_event) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webhook_price_rise_event when calling webhookPriceRiseNoticed'
            );
        }


        $resourcePath = '/priceRiseNoticed';
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
        if (isset($webhook_price_rise_event)) {
            if (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the body
                try {
                    $httpBody = json_encode(ObjectSerializer::sanitizeForSerialization($webhook_price_rise_event), JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \InvalidArgumentException('json_encode error: ' . $e->getMessage(), 0, $e);
                }
            } else {
                $httpBody = $webhook_price_rise_event;
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
