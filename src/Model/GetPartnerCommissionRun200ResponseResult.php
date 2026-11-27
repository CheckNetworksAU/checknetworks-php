<?php
/**
 * GetPartnerCommissionRun200ResponseResult
 *
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

namespace CheckNetworks\Model;

use \ArrayAccess;
use \CheckNetworks\ObjectSerializer;

/**
 * GetPartnerCommissionRun200ResponseResult Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class GetPartnerCommissionRun200ResponseResult implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'get_partner_commission_run_200_response_result';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'partner_id' => 'string',
        'partner_name' => 'string',
        'month' => 'string',
        'period_start' => '\DateTime',
        'period_end' => '\DateTime',
        'status' => 'string',
        'basis' => 'string',
        'revenue_ex_gst' => 'string',
        'revenue_gst' => 'string',
        'revenue_inc_gst' => 'string',
        'commission_ex_gst' => 'string',
        'commission_gst' => 'string',
        'commission_inc_gst' => 'string',
        'clawback_ex_gst' => 'string',
        'clawback_gst' => 'string',
        'clawback_inc_gst' => 'string',
        'payable_ex_gst' => 'string',
        'payable_gst' => 'string',
        'payable_inc_gst' => 'string',
        'currency' => 'string',
        'finalised_at' => '\DateTime',
        'paid_on' => '\DateTime',
        'paid_reference' => 'string',
        'gst_shown' => 'bool',
        'breakdown' => '\CheckNetworks\Model\GetPartnerCommissionRun200ResponseResultAllOfBreakdownInner[]',
        'clawbacks_taken' => '\CheckNetworks\Model\PartnerCommissionClawback[]'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'id' => 'uuid',
        'partner_id' => null,
        'partner_name' => null,
        'month' => null,
        'period_start' => 'date',
        'period_end' => 'date',
        'status' => null,
        'basis' => null,
        'revenue_ex_gst' => null,
        'revenue_gst' => null,
        'revenue_inc_gst' => null,
        'commission_ex_gst' => null,
        'commission_gst' => null,
        'commission_inc_gst' => null,
        'clawback_ex_gst' => null,
        'clawback_gst' => null,
        'clawback_inc_gst' => null,
        'payable_ex_gst' => null,
        'payable_gst' => null,
        'payable_inc_gst' => null,
        'currency' => null,
        'finalised_at' => 'date-time',
        'paid_on' => 'date',
        'paid_reference' => null,
        'gst_shown' => null,
        'breakdown' => null,
        'clawbacks_taken' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'partner_id' => true,
        'partner_name' => true,
        'month' => true,
        'period_start' => true,
        'period_end' => true,
        'status' => false,
        'basis' => true,
        'revenue_ex_gst' => false,
        'revenue_gst' => false,
        'revenue_inc_gst' => false,
        'commission_ex_gst' => false,
        'commission_gst' => false,
        'commission_inc_gst' => false,
        'clawback_ex_gst' => false,
        'clawback_gst' => false,
        'clawback_inc_gst' => false,
        'payable_ex_gst' => false,
        'payable_gst' => false,
        'payable_inc_gst' => false,
        'currency' => false,
        'finalised_at' => true,
        'paid_on' => true,
        'paid_reference' => true,
        'gst_shown' => false,
        'breakdown' => false,
        'clawbacks_taken' => false
    ];

    /**
     * If a nullable field gets set to null, insert it here
     *
     * @var boolean[]
     */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'id' => 'id',
        'partner_id' => 'partnerId',
        'partner_name' => 'partnerName',
        'month' => 'month',
        'period_start' => 'periodStart',
        'period_end' => 'periodEnd',
        'status' => 'status',
        'basis' => 'basis',
        'revenue_ex_gst' => 'revenueExGst',
        'revenue_gst' => 'revenueGst',
        'revenue_inc_gst' => 'revenueIncGst',
        'commission_ex_gst' => 'commissionExGst',
        'commission_gst' => 'commissionGst',
        'commission_inc_gst' => 'commissionIncGst',
        'clawback_ex_gst' => 'clawbackExGst',
        'clawback_gst' => 'clawbackGst',
        'clawback_inc_gst' => 'clawbackIncGst',
        'payable_ex_gst' => 'payableExGst',
        'payable_gst' => 'payableGst',
        'payable_inc_gst' => 'payableIncGst',
        'currency' => 'currency',
        'finalised_at' => 'finalisedAt',
        'paid_on' => 'paidOn',
        'paid_reference' => 'paidReference',
        'gst_shown' => 'gstShown',
        'breakdown' => 'breakdown',
        'clawbacks_taken' => 'clawbacksTaken'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'partner_id' => 'setPartnerId',
        'partner_name' => 'setPartnerName',
        'month' => 'setMonth',
        'period_start' => 'setPeriodStart',
        'period_end' => 'setPeriodEnd',
        'status' => 'setStatus',
        'basis' => 'setBasis',
        'revenue_ex_gst' => 'setRevenueExGst',
        'revenue_gst' => 'setRevenueGst',
        'revenue_inc_gst' => 'setRevenueIncGst',
        'commission_ex_gst' => 'setCommissionExGst',
        'commission_gst' => 'setCommissionGst',
        'commission_inc_gst' => 'setCommissionIncGst',
        'clawback_ex_gst' => 'setClawbackExGst',
        'clawback_gst' => 'setClawbackGst',
        'clawback_inc_gst' => 'setClawbackIncGst',
        'payable_ex_gst' => 'setPayableExGst',
        'payable_gst' => 'setPayableGst',
        'payable_inc_gst' => 'setPayableIncGst',
        'currency' => 'setCurrency',
        'finalised_at' => 'setFinalisedAt',
        'paid_on' => 'setPaidOn',
        'paid_reference' => 'setPaidReference',
        'gst_shown' => 'setGstShown',
        'breakdown' => 'setBreakdown',
        'clawbacks_taken' => 'setClawbacksTaken'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'partner_id' => 'getPartnerId',
        'partner_name' => 'getPartnerName',
        'month' => 'getMonth',
        'period_start' => 'getPeriodStart',
        'period_end' => 'getPeriodEnd',
        'status' => 'getStatus',
        'basis' => 'getBasis',
        'revenue_ex_gst' => 'getRevenueExGst',
        'revenue_gst' => 'getRevenueGst',
        'revenue_inc_gst' => 'getRevenueIncGst',
        'commission_ex_gst' => 'getCommissionExGst',
        'commission_gst' => 'getCommissionGst',
        'commission_inc_gst' => 'getCommissionIncGst',
        'clawback_ex_gst' => 'getClawbackExGst',
        'clawback_gst' => 'getClawbackGst',
        'clawback_inc_gst' => 'getClawbackIncGst',
        'payable_ex_gst' => 'getPayableExGst',
        'payable_gst' => 'getPayableGst',
        'payable_inc_gst' => 'getPayableIncGst',
        'currency' => 'getCurrency',
        'finalised_at' => 'getFinalisedAt',
        'paid_on' => 'getPaidOn',
        'paid_reference' => 'getPaidReference',
        'gst_shown' => 'getGstShown',
        'breakdown' => 'getBreakdown',
        'clawbacks_taken' => 'getClawbacksTaken'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALISED = 'finalised';
    public const STATUS_PAID = 'paid';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_FINALISED,
            self::STATUS_PAID,
        ];
    }

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[]|null $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(?array $data = null)
    {
        $this->setIfExists('id', $data ?? [], null);
        $this->setIfExists('partner_id', $data ?? [], null);
        $this->setIfExists('partner_name', $data ?? [], null);
        $this->setIfExists('month', $data ?? [], null);
        $this->setIfExists('period_start', $data ?? [], null);
        $this->setIfExists('period_end', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('basis', $data ?? [], null);
        $this->setIfExists('revenue_ex_gst', $data ?? [], null);
        $this->setIfExists('revenue_gst', $data ?? [], null);
        $this->setIfExists('revenue_inc_gst', $data ?? [], null);
        $this->setIfExists('commission_ex_gst', $data ?? [], null);
        $this->setIfExists('commission_gst', $data ?? [], null);
        $this->setIfExists('commission_inc_gst', $data ?? [], null);
        $this->setIfExists('clawback_ex_gst', $data ?? [], null);
        $this->setIfExists('clawback_gst', $data ?? [], null);
        $this->setIfExists('clawback_inc_gst', $data ?? [], null);
        $this->setIfExists('payable_ex_gst', $data ?? [], null);
        $this->setIfExists('payable_gst', $data ?? [], null);
        $this->setIfExists('payable_inc_gst', $data ?? [], null);
        $this->setIfExists('currency', $data ?? [], null);
        $this->setIfExists('finalised_at', $data ?? [], null);
        $this->setIfExists('paid_on', $data ?? [], null);
        $this->setIfExists('paid_reference', $data ?? [], null);
        $this->setIfExists('gst_shown', $data ?? [], null);
        $this->setIfExists('breakdown', $data ?? [], null);
        $this->setIfExists('clawbacks_taken', $data ?? [], null);
    }

    /**
     * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
     * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
     * $this->openAPINullablesSetToNull array
     *
     * @param string $variableName
     * @param array  $fields
     * @param mixed  $defaultValue
     */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        $allowedValues = $this->getStatusAllowableValues();
        if (!is_null($this->container['status']) && !in_array($this->container['status'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'status', must be one of '%s'",
                $this->container['status'],
                implode("', '", $allowedValues)
            );
        }

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->container['id'];
    }

    /**
     * Sets id
     *
     * @param string|null $id The run's public id, for `GET /partners/commission-runs/{runId}`.
     *
     * @return self
     */
    public function setId($id)
    {
        if (is_null($id)) {
            throw new \InvalidArgumentException('non-nullable id cannot be null');
        }
        $this->container['id'] = $id;

        return $this;
    }

    /**
     * Gets partner_id
     *
     * @return string|null
     */
    public function getPartnerId()
    {
        return $this->container['partner_id'];
    }

    /**
     * Sets partner_id
     *
     * @param string|null $partner_id The partner's user id, as on GET /account/users.
     *
     * @return self
     */
    public function setPartnerId($partner_id)
    {
        if (is_null($partner_id)) {
            array_push($this->openAPINullablesSetToNull, 'partner_id');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('partner_id', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['partner_id'] = $partner_id;

        return $this;
    }

    /**
     * Gets partner_name
     *
     * @return string|null
     */
    public function getPartnerName()
    {
        return $this->container['partner_name'];
    }

    /**
     * Sets partner_name
     *
     * @param string|null $partner_name partner_name
     *
     * @return self
     */
    public function setPartnerName($partner_name)
    {
        if (is_null($partner_name)) {
            array_push($this->openAPINullablesSetToNull, 'partner_name');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('partner_name', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['partner_name'] = $partner_name;

        return $this;
    }

    /**
     * Gets month
     *
     * @return string|null
     */
    public function getMonth()
    {
        return $this->container['month'];
    }

    /**
     * Sets month
     *
     * @param string|null $month month
     *
     * @return self
     */
    public function setMonth($month)
    {
        if (is_null($month)) {
            array_push($this->openAPINullablesSetToNull, 'month');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('month', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['month'] = $month;

        return $this;
    }

    /**
     * Gets period_start
     *
     * @return \DateTime|null
     */
    public function getPeriodStart()
    {
        return $this->container['period_start'];
    }

    /**
     * Sets period_start
     *
     * @param \DateTime|null $period_start period_start
     *
     * @return self
     */
    public function setPeriodStart($period_start)
    {
        if (is_null($period_start)) {
            array_push($this->openAPINullablesSetToNull, 'period_start');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('period_start', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['period_start'] = $period_start;

        return $this;
    }

    /**
     * Gets period_end
     *
     * @return \DateTime|null
     */
    public function getPeriodEnd()
    {
        return $this->container['period_end'];
    }

    /**
     * Sets period_end
     *
     * @param \DateTime|null $period_end period_end
     *
     * @return self
     */
    public function setPeriodEnd($period_end)
    {
        if (is_null($period_end)) {
            array_push($this->openAPINullablesSetToNull, 'period_end');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('period_end', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['period_end'] = $period_end;

        return $this;
    }

    /**
     * Gets status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->container['status'];
    }

    /**
     * Sets status
     *
     * @param string|null $status status
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (is_null($status)) {
            throw new \InvalidArgumentException('non-nullable status cannot be null');
        }
        $allowedValues = $this->getStatusAllowableValues();
        if (!in_array($status, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'status', must be one of '%s'",
                    $status,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['status'] = $status;

        return $this;
    }

    /**
     * Gets basis
     *
     * @return string|null
     */
    public function getBasis()
    {
        return $this->container['basis'];
    }

    /**
     * Sets basis
     *
     * @param string|null $basis What the rate was applied to.
     *
     * @return self
     */
    public function setBasis($basis)
    {
        if (is_null($basis)) {
            array_push($this->openAPINullablesSetToNull, 'basis');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('basis', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['basis'] = $basis;

        return $this;
    }

    /**
     * Gets revenue_ex_gst
     *
     * @return string|null
     */
    public function getRevenueExGst()
    {
        return $this->container['revenue_ex_gst'];
    }

    /**
     * Sets revenue_ex_gst
     *
     * @param string|null $revenue_ex_gst revenue_ex_gst
     *
     * @return self
     */
    public function setRevenueExGst($revenue_ex_gst)
    {
        if (is_null($revenue_ex_gst)) {
            throw new \InvalidArgumentException('non-nullable revenue_ex_gst cannot be null');
        }
        $this->container['revenue_ex_gst'] = $revenue_ex_gst;

        return $this;
    }

    /**
     * Gets revenue_gst
     *
     * @return string|null
     */
    public function getRevenueGst()
    {
        return $this->container['revenue_gst'];
    }

    /**
     * Sets revenue_gst
     *
     * @param string|null $revenue_gst revenue_gst
     *
     * @return self
     */
    public function setRevenueGst($revenue_gst)
    {
        if (is_null($revenue_gst)) {
            throw new \InvalidArgumentException('non-nullable revenue_gst cannot be null');
        }
        $this->container['revenue_gst'] = $revenue_gst;

        return $this;
    }

    /**
     * Gets revenue_inc_gst
     *
     * @return string|null
     */
    public function getRevenueIncGst()
    {
        return $this->container['revenue_inc_gst'];
    }

    /**
     * Sets revenue_inc_gst
     *
     * @param string|null $revenue_inc_gst revenue_inc_gst
     *
     * @return self
     */
    public function setRevenueIncGst($revenue_inc_gst)
    {
        if (is_null($revenue_inc_gst)) {
            throw new \InvalidArgumentException('non-nullable revenue_inc_gst cannot be null');
        }
        $this->container['revenue_inc_gst'] = $revenue_inc_gst;

        return $this;
    }

    /**
     * Gets commission_ex_gst
     *
     * @return string|null
     */
    public function getCommissionExGst()
    {
        return $this->container['commission_ex_gst'];
    }

    /**
     * Sets commission_ex_gst
     *
     * @param string|null $commission_ex_gst commission_ex_gst
     *
     * @return self
     */
    public function setCommissionExGst($commission_ex_gst)
    {
        if (is_null($commission_ex_gst)) {
            throw new \InvalidArgumentException('non-nullable commission_ex_gst cannot be null');
        }
        $this->container['commission_ex_gst'] = $commission_ex_gst;

        return $this;
    }

    /**
     * Gets commission_gst
     *
     * @return string|null
     */
    public function getCommissionGst()
    {
        return $this->container['commission_gst'];
    }

    /**
     * Sets commission_gst
     *
     * @param string|null $commission_gst commission_gst
     *
     * @return self
     */
    public function setCommissionGst($commission_gst)
    {
        if (is_null($commission_gst)) {
            throw new \InvalidArgumentException('non-nullable commission_gst cannot be null');
        }
        $this->container['commission_gst'] = $commission_gst;

        return $this;
    }

    /**
     * Gets commission_inc_gst
     *
     * @return string|null
     */
    public function getCommissionIncGst()
    {
        return $this->container['commission_inc_gst'];
    }

    /**
     * Sets commission_inc_gst
     *
     * @param string|null $commission_inc_gst commission_inc_gst
     *
     * @return self
     */
    public function setCommissionIncGst($commission_inc_gst)
    {
        if (is_null($commission_inc_gst)) {
            throw new \InvalidArgumentException('non-nullable commission_inc_gst cannot be null');
        }
        $this->container['commission_inc_gst'] = $commission_inc_gst;

        return $this;
    }

    /**
     * Gets clawback_ex_gst
     *
     * @return string|null
     */
    public function getClawbackExGst()
    {
        return $this->container['clawback_ex_gst'];
    }

    /**
     * Sets clawback_ex_gst
     *
     * @param string|null $clawback_ex_gst Clawbacks taken off this run.
     *
     * @return self
     */
    public function setClawbackExGst($clawback_ex_gst)
    {
        if (is_null($clawback_ex_gst)) {
            throw new \InvalidArgumentException('non-nullable clawback_ex_gst cannot be null');
        }
        $this->container['clawback_ex_gst'] = $clawback_ex_gst;

        return $this;
    }

    /**
     * Gets clawback_gst
     *
     * @return string|null
     */
    public function getClawbackGst()
    {
        return $this->container['clawback_gst'];
    }

    /**
     * Sets clawback_gst
     *
     * @param string|null $clawback_gst clawback_gst
     *
     * @return self
     */
    public function setClawbackGst($clawback_gst)
    {
        if (is_null($clawback_gst)) {
            throw new \InvalidArgumentException('non-nullable clawback_gst cannot be null');
        }
        $this->container['clawback_gst'] = $clawback_gst;

        return $this;
    }

    /**
     * Gets clawback_inc_gst
     *
     * @return string|null
     */
    public function getClawbackIncGst()
    {
        return $this->container['clawback_inc_gst'];
    }

    /**
     * Sets clawback_inc_gst
     *
     * @param string|null $clawback_inc_gst clawback_inc_gst
     *
     * @return self
     */
    public function setClawbackIncGst($clawback_inc_gst)
    {
        if (is_null($clawback_inc_gst)) {
            throw new \InvalidArgumentException('non-nullable clawback_inc_gst cannot be null');
        }
        $this->container['clawback_inc_gst'] = $clawback_inc_gst;

        return $this;
    }

    /**
     * Gets payable_ex_gst
     *
     * @return string|null
     */
    public function getPayableExGst()
    {
        return $this->container['payable_ex_gst'];
    }

    /**
     * Sets payable_ex_gst
     *
     * @param string|null $payable_ex_gst Commission less clawbacks, what the partner is paid.
     *
     * @return self
     */
    public function setPayableExGst($payable_ex_gst)
    {
        if (is_null($payable_ex_gst)) {
            throw new \InvalidArgumentException('non-nullable payable_ex_gst cannot be null');
        }
        $this->container['payable_ex_gst'] = $payable_ex_gst;

        return $this;
    }

    /**
     * Gets payable_gst
     *
     * @return string|null
     */
    public function getPayableGst()
    {
        return $this->container['payable_gst'];
    }

    /**
     * Sets payable_gst
     *
     * @param string|null $payable_gst payable_gst
     *
     * @return self
     */
    public function setPayableGst($payable_gst)
    {
        if (is_null($payable_gst)) {
            throw new \InvalidArgumentException('non-nullable payable_gst cannot be null');
        }
        $this->container['payable_gst'] = $payable_gst;

        return $this;
    }

    /**
     * Gets payable_inc_gst
     *
     * @return string|null
     */
    public function getPayableIncGst()
    {
        return $this->container['payable_inc_gst'];
    }

    /**
     * Sets payable_inc_gst
     *
     * @param string|null $payable_inc_gst payable_inc_gst
     *
     * @return self
     */
    public function setPayableIncGst($payable_inc_gst)
    {
        if (is_null($payable_inc_gst)) {
            throw new \InvalidArgumentException('non-nullable payable_inc_gst cannot be null');
        }
        $this->container['payable_inc_gst'] = $payable_inc_gst;

        return $this;
    }

    /**
     * Gets currency
     *
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->container['currency'];
    }

    /**
     * Sets currency
     *
     * @param string|null $currency currency
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        if (is_null($currency)) {
            throw new \InvalidArgumentException('non-nullable currency cannot be null');
        }
        $this->container['currency'] = $currency;

        return $this;
    }

    /**
     * Gets finalised_at
     *
     * @return \DateTime|null
     */
    public function getFinalisedAt()
    {
        return $this->container['finalised_at'];
    }

    /**
     * Sets finalised_at
     *
     * @param \DateTime|null $finalised_at finalised_at
     *
     * @return self
     */
    public function setFinalisedAt($finalised_at)
    {
        if (is_null($finalised_at)) {
            array_push($this->openAPINullablesSetToNull, 'finalised_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('finalised_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['finalised_at'] = $finalised_at;

        return $this;
    }

    /**
     * Gets paid_on
     *
     * @return \DateTime|null
     */
    public function getPaidOn()
    {
        return $this->container['paid_on'];
    }

    /**
     * Sets paid_on
     *
     * @param \DateTime|null $paid_on paid_on
     *
     * @return self
     */
    public function setPaidOn($paid_on)
    {
        if (is_null($paid_on)) {
            array_push($this->openAPINullablesSetToNull, 'paid_on');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('paid_on', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['paid_on'] = $paid_on;

        return $this;
    }

    /**
     * Gets paid_reference
     *
     * @return string|null
     */
    public function getPaidReference()
    {
        return $this->container['paid_reference'];
    }

    /**
     * Sets paid_reference
     *
     * @param string|null $paid_reference paid_reference
     *
     * @return self
     */
    public function setPaidReference($paid_reference)
    {
        if (is_null($paid_reference)) {
            array_push($this->openAPINullablesSetToNull, 'paid_reference');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('paid_reference', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['paid_reference'] = $paid_reference;

        return $this;
    }

    /**
     * Gets gst_shown
     *
     * @return bool|null
     */
    public function getGstShown()
    {
        return $this->container['gst_shown'];
    }

    /**
     * Sets gst_shown
     *
     * @param bool|null $gst_shown gst_shown
     *
     * @return self
     */
    public function setGstShown($gst_shown)
    {
        if (is_null($gst_shown)) {
            throw new \InvalidArgumentException('non-nullable gst_shown cannot be null');
        }
        $this->container['gst_shown'] = $gst_shown;

        return $this;
    }

    /**
     * Gets breakdown
     *
     * @return \CheckNetworks\Model\GetPartnerCommissionRun200ResponseResultAllOfBreakdownInner[]|null
     */
    public function getBreakdown()
    {
        return $this->container['breakdown'];
    }

    /**
     * Sets breakdown
     *
     * @param \CheckNetworks\Model\GetPartnerCommissionRun200ResponseResultAllOfBreakdownInner[]|null $breakdown breakdown
     *
     * @return self
     */
    public function setBreakdown($breakdown)
    {
        if (is_null($breakdown)) {
            throw new \InvalidArgumentException('non-nullable breakdown cannot be null');
        }
        $this->container['breakdown'] = $breakdown;

        return $this;
    }

    /**
     * Gets clawbacks_taken
     *
     * @return \CheckNetworks\Model\PartnerCommissionClawback[]|null
     */
    public function getClawbacksTaken()
    {
        return $this->container['clawbacks_taken'];
    }

    /**
     * Sets clawbacks_taken
     *
     * @param \CheckNetworks\Model\PartnerCommissionClawback[]|null $clawbacks_taken clawbacks_taken
     *
     * @return self
     */
    public function setClawbacksTaken($clawbacks_taken)
    {
        if (is_null($clawbacks_taken)) {
            throw new \InvalidArgumentException('non-nullable clawbacks_taken cannot be null');
        }
        $this->container['clawbacks_taken'] = $clawbacks_taken;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer|string $offset Offset
     *
     * @return boolean
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer|string $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer|string $offset Offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


