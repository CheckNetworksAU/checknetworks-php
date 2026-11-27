<?php
/**
 * Quote
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
 * Quote Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class Quote implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'Quote';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'external_id' => 'string',
        'category' => 'string',
        'description' => 'string',
        'quote_date' => '\DateTime',
        'poi_name' => 'string',
        'zone' => 'string',
        'region' => 'string',
        'slr' => 'string',
        'estimated_delivery_time' => 'string',
        'status' => 'string',
        'sub_status' => 'string',
        'product_offering' => '\CheckNetworks\Model\ProductOfferingReference',
        'place' => '\CheckNetworks\Model\PlaceReference',
        'address' => 'string',
        'fibre_build_category' => 'string',
        'valid_for' => '\CheckNetworks\Model\DatePeriod',
        'quote_specification' => '\CheckNetworks\Model\QuoteSpecificationReference',
        'quote_item' => '\CheckNetworks\Model\QuoteItemReference[]',
        'quote_service_item' => '\CheckNetworks\Model\QuoteItemReference[]',
        'quote_resource_item' => '\CheckNetworks\Model\QuoteItemReference[]',
        'event_note' => '\CheckNetworks\Model\EventNoteReference',
        'quote_approval' => '\CheckNetworks\Model\QuoteApproval'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'id' => null,
        'external_id' => null,
        'category' => null,
        'description' => null,
        'quote_date' => 'date-time',
        'poi_name' => null,
        'zone' => null,
        'region' => null,
        'slr' => null,
        'estimated_delivery_time' => null,
        'status' => null,
        'sub_status' => null,
        'product_offering' => null,
        'place' => null,
        'address' => null,
        'fibre_build_category' => null,
        'valid_for' => null,
        'quote_specification' => null,
        'quote_item' => null,
        'quote_service_item' => null,
        'quote_resource_item' => null,
        'event_note' => null,
        'quote_approval' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'external_id' => false,
        'category' => false,
        'description' => false,
        'quote_date' => false,
        'poi_name' => false,
        'zone' => false,
        'region' => false,
        'slr' => false,
        'estimated_delivery_time' => false,
        'status' => false,
        'sub_status' => false,
        'product_offering' => false,
        'place' => false,
        'address' => false,
        'fibre_build_category' => false,
        'valid_for' => false,
        'quote_specification' => false,
        'quote_item' => false,
        'quote_service_item' => false,
        'quote_resource_item' => false,
        'event_note' => false,
        'quote_approval' => false
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
        'external_id' => 'externalId',
        'category' => 'category',
        'description' => 'description',
        'quote_date' => 'quoteDate',
        'poi_name' => 'poiName',
        'zone' => 'zone',
        'region' => 'region',
        'slr' => 'slr',
        'estimated_delivery_time' => 'estimatedDeliveryTime',
        'status' => 'status',
        'sub_status' => 'subStatus',
        'product_offering' => 'productOffering',
        'place' => 'place',
        'address' => 'address',
        'fibre_build_category' => 'fibreBuildCategory',
        'valid_for' => 'validFor',
        'quote_specification' => 'quoteSpecification',
        'quote_item' => 'quoteItem',
        'quote_service_item' => 'quoteServiceItem',
        'quote_resource_item' => 'quoteResourceItem',
        'event_note' => 'eventNote',
        'quote_approval' => 'quoteApproval'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'external_id' => 'setExternalId',
        'category' => 'setCategory',
        'description' => 'setDescription',
        'quote_date' => 'setQuoteDate',
        'poi_name' => 'setPoiName',
        'zone' => 'setZone',
        'region' => 'setRegion',
        'slr' => 'setSlr',
        'estimated_delivery_time' => 'setEstimatedDeliveryTime',
        'status' => 'setStatus',
        'sub_status' => 'setSubStatus',
        'product_offering' => 'setProductOffering',
        'place' => 'setPlace',
        'address' => 'setAddress',
        'fibre_build_category' => 'setFibreBuildCategory',
        'valid_for' => 'setValidFor',
        'quote_specification' => 'setQuoteSpecification',
        'quote_item' => 'setQuoteItem',
        'quote_service_item' => 'setQuoteServiceItem',
        'quote_resource_item' => 'setQuoteResourceItem',
        'event_note' => 'setEventNote',
        'quote_approval' => 'setQuoteApproval'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'external_id' => 'getExternalId',
        'category' => 'getCategory',
        'description' => 'getDescription',
        'quote_date' => 'getQuoteDate',
        'poi_name' => 'getPoiName',
        'zone' => 'getZone',
        'region' => 'getRegion',
        'slr' => 'getSlr',
        'estimated_delivery_time' => 'getEstimatedDeliveryTime',
        'status' => 'getStatus',
        'sub_status' => 'getSubStatus',
        'product_offering' => 'getProductOffering',
        'place' => 'getPlace',
        'address' => 'getAddress',
        'fibre_build_category' => 'getFibreBuildCategory',
        'valid_for' => 'getValidFor',
        'quote_specification' => 'getQuoteSpecification',
        'quote_item' => 'getQuoteItem',
        'quote_service_item' => 'getQuoteServiceItem',
        'quote_resource_item' => 'getQuoteResourceItem',
        'event_note' => 'getEventNote',
        'quote_approval' => 'getQuoteApproval'
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

    public const CATEGORY_CONNECT = 'Connect';
    public const ZONE_CBD = 'CBD';
    public const ZONE_ZONE_1 = 'Zone 1';
    public const ZONE_ZONE_2 = 'Zone 2';
    public const ZONE_ZONE_3 = 'Zone 3';
    public const REGION_REGION_1 = 'Region 1';
    public const REGION_REGION_2 = 'Region 2';
    public const REGION_REGION_3 = 'Region 3';
    public const REGION_REGION_4 = 'Region 4';
    public const SLR_ISOLATED = 'Isolated';
    public const SLR_MAJOR_RURAL = 'Major Rural';
    public const SLR_METROPOLITAN = 'Metropolitan';
    public const SLR_MINOR_RURAL = 'Minor Rural';
    public const SLR_REGIONAL_CENTRE = 'Regional Centre';
    public const SLR_REMOTE = 'Remote';
    public const SLR_URBAN = 'Urban';
    public const STATUS_ACKNOWLEDGED = 'Acknowledged';
    public const STATUS_IN_PROGRESS = 'InProgress';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_COMPLETE = 'Complete';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_EXPIRED = 'Expired';
    public const SUB_STATUS_PENDING = 'Pending';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getCategoryAllowableValues()
    {
        return [
            self::CATEGORY_CONNECT,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getZoneAllowableValues()
    {
        return [
            self::ZONE_CBD,
            self::ZONE_ZONE_1,
            self::ZONE_ZONE_2,
            self::ZONE_ZONE_3,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getRegionAllowableValues()
    {
        return [
            self::REGION_REGION_1,
            self::REGION_REGION_2,
            self::REGION_REGION_3,
            self::REGION_REGION_4,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getSlrAllowableValues()
    {
        return [
            self::SLR_ISOLATED,
            self::SLR_MAJOR_RURAL,
            self::SLR_METROPOLITAN,
            self::SLR_MINOR_RURAL,
            self::SLR_REGIONAL_CENTRE,
            self::SLR_REMOTE,
            self::SLR_URBAN,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_ACKNOWLEDGED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETE,
            self::STATUS_CLOSED,
            self::STATUS_EXPIRED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getSubStatusAllowableValues()
    {
        return [
            self::SUB_STATUS_PENDING,
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
        $this->setIfExists('external_id', $data ?? [], null);
        $this->setIfExists('category', $data ?? [], null);
        $this->setIfExists('description', $data ?? [], null);
        $this->setIfExists('quote_date', $data ?? [], null);
        $this->setIfExists('poi_name', $data ?? [], null);
        $this->setIfExists('zone', $data ?? [], null);
        $this->setIfExists('region', $data ?? [], null);
        $this->setIfExists('slr', $data ?? [], null);
        $this->setIfExists('estimated_delivery_time', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('sub_status', $data ?? [], null);
        $this->setIfExists('product_offering', $data ?? [], null);
        $this->setIfExists('place', $data ?? [], null);
        $this->setIfExists('address', $data ?? [], null);
        $this->setIfExists('fibre_build_category', $data ?? [], null);
        $this->setIfExists('valid_for', $data ?? [], null);
        $this->setIfExists('quote_specification', $data ?? [], null);
        $this->setIfExists('quote_item', $data ?? [], null);
        $this->setIfExists('quote_service_item', $data ?? [], null);
        $this->setIfExists('quote_resource_item', $data ?? [], null);
        $this->setIfExists('event_note', $data ?? [], null);
        $this->setIfExists('quote_approval', $data ?? [], null);
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

        $allowedValues = $this->getCategoryAllowableValues();
        if (!is_null($this->container['category']) && !in_array($this->container['category'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'category', must be one of '%s'",
                $this->container['category'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getZoneAllowableValues();
        if (!is_null($this->container['zone']) && !in_array($this->container['zone'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'zone', must be one of '%s'",
                $this->container['zone'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getRegionAllowableValues();
        if (!is_null($this->container['region']) && !in_array($this->container['region'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'region', must be one of '%s'",
                $this->container['region'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getSlrAllowableValues();
        if (!is_null($this->container['slr']) && !in_array($this->container['slr'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'slr', must be one of '%s'",
                $this->container['slr'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getStatusAllowableValues();
        if (!is_null($this->container['status']) && !in_array($this->container['status'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'status', must be one of '%s'",
                $this->container['status'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getSubStatusAllowableValues();
        if (!is_null($this->container['sub_status']) && !in_array($this->container['sub_status'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'sub_status', must be one of '%s'",
                $this->container['sub_status'],
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
     * @param string|null $id A unique identifier for quote
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
     * Gets external_id
     *
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->container['external_id'];
    }

    /**
     * Sets external_id
     *
     * @param string|null $external_id Reference provided by the RSP
     *
     * @return self
     */
    public function setExternalId($external_id)
    {
        if (is_null($external_id)) {
            throw new \InvalidArgumentException('non-nullable external_id cannot be null');
        }
        $this->container['external_id'] = $external_id;

        return $this;
    }

    /**
     * Gets category
     *
     * @return string|null
     */
    public function getCategory()
    {
        return $this->container['category'];
    }

    /**
     * Sets category
     *
     * @param string|null $category Type of quote being requested.
     *
     * @return self
     */
    public function setCategory($category)
    {
        if (is_null($category)) {
            throw new \InvalidArgumentException('non-nullable category cannot be null');
        }
        $allowedValues = $this->getCategoryAllowableValues();
        if (!in_array($category, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'category', must be one of '%s'",
                    $category,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['category'] = $category;

        return $this;
    }

    /**
     * Gets description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->container['description'];
    }

    /**
     * Sets description
     *
     * @param string|null $description A description of the requested quote
     *
     * @return self
     */
    public function setDescription($description)
    {
        if (is_null($description)) {
            throw new \InvalidArgumentException('non-nullable description cannot be null');
        }
        $this->container['description'] = $description;

        return $this;
    }

    /**
     * Gets quote_date
     *
     * @return \DateTime|null
     */
    public function getQuoteDate()
    {
        return $this->container['quote_date'];
    }

    /**
     * Sets quote_date
     *
     * @param \DateTime|null $quote_date Date on which quote was requested.
     *
     * @return self
     */
    public function setQuoteDate($quote_date)
    {
        if (is_null($quote_date)) {
            throw new \InvalidArgumentException('non-nullable quote_date cannot be null');
        }
        $this->container['quote_date'] = $quote_date;

        return $this;
    }

    /**
     * Gets poi_name
     *
     * @return string|null
     */
    public function getPoiName()
    {
        return $this->container['poi_name'];
    }

    /**
     * Sets poi_name
     *
     * @param string|null $poi_name The name of the nbn POI for this connection: its code, then its name, as the carrier writes it. The carrier's own capitalisation is kept, so the name may arrive in capitals (\"4TNS - TOWNSVILLE\"); compare the code.
     *
     * @return self
     */
    public function setPoiName($poi_name)
    {
        if (is_null($poi_name)) {
            throw new \InvalidArgumentException('non-nullable poi_name cannot be null');
        }
        $this->container['poi_name'] = $poi_name;

        return $this;
    }

    /**
     * Gets zone
     *
     * @return string|null
     */
    public function getZone()
    {
        return $this->container['zone'];
    }

    /**
     * Sets zone
     *
     * @param string|null $zone The nbn EE Zone for the Address in this Quote
     *
     * @return self
     */
    public function setZone($zone)
    {
        if (is_null($zone)) {
            throw new \InvalidArgumentException('non-nullable zone cannot be null');
        }
        $allowedValues = $this->getZoneAllowableValues();
        if (!in_array($zone, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'zone', must be one of '%s'",
                    $zone,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['zone'] = $zone;

        return $this;
    }

    /**
     * Gets region
     *
     * @return string|null
     */
    public function getRegion()
    {
        return $this->container['region'];
    }

    /**
     * Sets region
     *
     * @param string|null $region The nbn EE Region for the Address in this Quote
     *
     * @return self
     */
    public function setRegion($region)
    {
        if (is_null($region)) {
            throw new \InvalidArgumentException('non-nullable region cannot be null');
        }
        $allowedValues = $this->getRegionAllowableValues();
        if (!in_array($region, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'region', must be one of '%s'",
                    $region,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['region'] = $region;

        return $this;
    }

    /**
     * Gets slr
     *
     * @return string|null
     */
    public function getSlr()
    {
        return $this->container['slr'];
    }

    /**
     * Sets slr
     *
     * @param string|null $slr nbn Service Level Region (SLR) for issue response times
     *
     * @return self
     */
    public function setSlr($slr)
    {
        if (is_null($slr)) {
            throw new \InvalidArgumentException('non-nullable slr cannot be null');
        }
        $allowedValues = $this->getSlrAllowableValues();
        if (!in_array($slr, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'slr', must be one of '%s'",
                    $slr,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['slr'] = $slr;

        return $this;
    }

    /**
     * Gets estimated_delivery_time
     *
     * @return string|null
     */
    public function getEstimatedDeliveryTime()
    {
        return $this->container['estimated_delivery_time'];
    }

    /**
     * Sets estimated_delivery_time
     *
     * @param string|null $estimated_delivery_time Number of days (after order acceptance) when service will be activated
     *
     * @return self
     */
    public function setEstimatedDeliveryTime($estimated_delivery_time)
    {
        if (is_null($estimated_delivery_time)) {
            throw new \InvalidArgumentException('non-nullable estimated_delivery_time cannot be null');
        }
        $this->container['estimated_delivery_time'] = $estimated_delivery_time;

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
     * @param string|null $status Status of the Quote being requested. `Complete` is the one priced status. `Rejected`, `Cancelled`, `Closed` and `Expired` end the quote without a price, and a new quote can be requested for the location. A status the carrier sends outside this list is published as `InProgress`.
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
     * Gets sub_status
     *
     * @return string|null
     */
    public function getSubStatus()
    {
        return $this->container['sub_status'];
    }

    /**
     * Sets sub_status
     *
     * @param string|null $sub_status Present only while an `InProgress` quote is held waiting on you (\"Action Required\"): the carrier's reason is in `eventNote`. Contact support to give the carrier what it needs. The `quote.actionRequired` webhook is sent when it is set.
     *
     * @return self
     */
    public function setSubStatus($sub_status)
    {
        if (is_null($sub_status)) {
            throw new \InvalidArgumentException('non-nullable sub_status cannot be null');
        }
        $allowedValues = $this->getSubStatusAllowableValues();
        if (!in_array($sub_status, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'sub_status', must be one of '%s'",
                    $sub_status,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['sub_status'] = $sub_status;

        return $this;
    }

    /**
     * Gets product_offering
     *
     * @return \CheckNetworks\Model\ProductOfferingReference|null
     */
    public function getProductOffering()
    {
        return $this->container['product_offering'];
    }

    /**
     * Sets product_offering
     *
     * @param \CheckNetworks\Model\ProductOfferingReference|null $product_offering product_offering
     *
     * @return self
     */
    public function setProductOffering($product_offering)
    {
        if (is_null($product_offering)) {
            throw new \InvalidArgumentException('non-nullable product_offering cannot be null');
        }
        $this->container['product_offering'] = $product_offering;

        return $this;
    }

    /**
     * Gets place
     *
     * @return \CheckNetworks\Model\PlaceReference|null
     */
    public function getPlace()
    {
        return $this->container['place'];
    }

    /**
     * Sets place
     *
     * @param \CheckNetworks\Model\PlaceReference|null $place place
     *
     * @return self
     */
    public function setPlace($place)
    {
        if (is_null($place)) {
            throw new \InvalidArgumentException('non-nullable place cannot be null');
        }
        $this->container['place'] = $place;

        return $this;
    }

    /**
     * Gets address
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->container['address'];
    }

    /**
     * Sets address
     *
     * @param string|null $address Ours, not the carrier's: the premises address for `place.id`, as the portal shows it. Absent when we hold no address for the location.
     *
     * @return self
     */
    public function setAddress($address)
    {
        if (is_null($address)) {
            throw new \InvalidArgumentException('non-nullable address cannot be null');
        }
        $this->container['address'] = $address;

        return $this;
    }

    /**
     * Gets fibre_build_category
     *
     * @return string|null
     */
    public function getFibreBuildCategory()
    {
        return $this->container['fibre_build_category'];
    }

    /**
     * Sets fibre_build_category
     *
     * @param string|null $fibre_build_category Ours: the fibre build category the carrier gave for the premises, as the portal shows it. Absent until the carrier gives one.
     *
     * @return self
     */
    public function setFibreBuildCategory($fibre_build_category)
    {
        if (is_null($fibre_build_category)) {
            throw new \InvalidArgumentException('non-nullable fibre_build_category cannot be null');
        }
        $this->container['fibre_build_category'] = $fibre_build_category;

        return $this;
    }

    /**
     * Gets valid_for
     *
     * @return \CheckNetworks\Model\DatePeriod|null
     */
    public function getValidFor()
    {
        return $this->container['valid_for'];
    }

    /**
     * Sets valid_for
     *
     * @param \CheckNetworks\Model\DatePeriod|null $valid_for valid_for
     *
     * @return self
     */
    public function setValidFor($valid_for)
    {
        if (is_null($valid_for)) {
            throw new \InvalidArgumentException('non-nullable valid_for cannot be null');
        }
        $this->container['valid_for'] = $valid_for;

        return $this;
    }

    /**
     * Gets quote_specification
     *
     * @return \CheckNetworks\Model\QuoteSpecificationReference|null
     */
    public function getQuoteSpecification()
    {
        return $this->container['quote_specification'];
    }

    /**
     * Sets quote_specification
     *
     * @param \CheckNetworks\Model\QuoteSpecificationReference|null $quote_specification quote_specification
     *
     * @return self
     */
    public function setQuoteSpecification($quote_specification)
    {
        if (is_null($quote_specification)) {
            throw new \InvalidArgumentException('non-nullable quote_specification cannot be null');
        }
        $this->container['quote_specification'] = $quote_specification;

        return $this;
    }

    /**
     * Gets quote_item
     *
     * @return \CheckNetworks\Model\QuoteItemReference[]|null
     */
    public function getQuoteItem()
    {
        return $this->container['quote_item'];
    }

    /**
     * Sets quote_item
     *
     * @param \CheckNetworks\Model\QuoteItemReference[]|null $quote_item quote_item
     *
     * @return self
     */
    public function setQuoteItem($quote_item)
    {
        if (is_null($quote_item)) {
            throw new \InvalidArgumentException('non-nullable quote_item cannot be null');
        }
        $this->container['quote_item'] = $quote_item;

        return $this;
    }

    /**
     * Gets quote_service_item
     *
     * @return \CheckNetworks\Model\QuoteItemReference[]|null
     */
    public function getQuoteServiceItem()
    {
        return $this->container['quote_service_item'];
    }

    /**
     * Sets quote_service_item
     *
     * @param \CheckNetworks\Model\QuoteItemReference[]|null $quote_service_item quote_service_item
     *
     * @return self
     */
    public function setQuoteServiceItem($quote_service_item)
    {
        if (is_null($quote_service_item)) {
            throw new \InvalidArgumentException('non-nullable quote_service_item cannot be null');
        }
        $this->container['quote_service_item'] = $quote_service_item;

        return $this;
    }

    /**
     * Gets quote_resource_item
     *
     * @return \CheckNetworks\Model\QuoteItemReference[]|null
     */
    public function getQuoteResourceItem()
    {
        return $this->container['quote_resource_item'];
    }

    /**
     * Sets quote_resource_item
     *
     * @param \CheckNetworks\Model\QuoteItemReference[]|null $quote_resource_item quote_resource_item
     *
     * @return self
     */
    public function setQuoteResourceItem($quote_resource_item)
    {
        if (is_null($quote_resource_item)) {
            throw new \InvalidArgumentException('non-nullable quote_resource_item cannot be null');
        }
        $this->container['quote_resource_item'] = $quote_resource_item;

        return $this;
    }

    /**
     * Gets event_note
     *
     * @return \CheckNetworks\Model\EventNoteReference|null
     */
    public function getEventNote()
    {
        return $this->container['event_note'];
    }

    /**
     * Sets event_note
     *
     * @param \CheckNetworks\Model\EventNoteReference|null $event_note event_note
     *
     * @return self
     */
    public function setEventNote($event_note)
    {
        if (is_null($event_note)) {
            throw new \InvalidArgumentException('non-nullable event_note cannot be null');
        }
        $this->container['event_note'] = $event_note;

        return $this;
    }

    /**
     * Gets quote_approval
     *
     * @return \CheckNetworks\Model\QuoteApproval|null
     */
    public function getQuoteApproval()
    {
        return $this->container['quote_approval'];
    }

    /**
     * Sets quote_approval
     *
     * @param \CheckNetworks\Model\QuoteApproval|null $quote_approval quote_approval
     *
     * @return self
     */
    public function setQuoteApproval($quote_approval)
    {
        if (is_null($quote_approval)) {
            throw new \InvalidArgumentException('non-nullable quote_approval cannot be null');
        }
        $this->container['quote_approval'] = $quote_approval;

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


