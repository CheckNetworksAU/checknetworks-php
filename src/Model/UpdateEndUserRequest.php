<?php
/**
 * UpdateEndUserRequest
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
 * UpdateEndUserRequest Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class UpdateEndUserRequest implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'update_end_user_request';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'name' => 'string',
        'business_name' => 'string',
        'trading_name' => 'string',
        'abn' => 'string',
        'acn' => 'string',
        'external_ref' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'alt_contact_name' => 'string',
        'alt_contact_phone' => 'string',
        'billing_email' => 'string',
        'invoice_delivery' => 'string',
        'usage_notification_method' => 'string',
        'payment_terms_days' => 'int',
        'invoice_format' => 'string',
        'credit_limit' => '\CheckNetworks\Model\EndUserDetailsCreditLimit',
        'spend_limit' => '\CheckNetworks\Model\EndUserDetailsSpendLimit',
        'notes' => 'string',
        'type' => 'string',
        'timezone' => 'string'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'name' => null,
        'business_name' => null,
        'trading_name' => null,
        'abn' => null,
        'acn' => null,
        'external_ref' => null,
        'email' => 'email',
        'phone' => null,
        'alt_contact_name' => null,
        'alt_contact_phone' => null,
        'billing_email' => 'email',
        'invoice_delivery' => null,
        'usage_notification_method' => null,
        'payment_terms_days' => null,
        'invoice_format' => null,
        'credit_limit' => null,
        'spend_limit' => null,
        'notes' => null,
        'type' => null,
        'timezone' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'name' => false,
        'business_name' => true,
        'trading_name' => true,
        'abn' => true,
        'acn' => true,
        'external_ref' => true,
        'email' => true,
        'phone' => true,
        'alt_contact_name' => true,
        'alt_contact_phone' => true,
        'billing_email' => true,
        'invoice_delivery' => true,
        'usage_notification_method' => true,
        'payment_terms_days' => true,
        'invoice_format' => true,
        'credit_limit' => true,
        'spend_limit' => true,
        'notes' => true,
        'type' => false,
        'timezone' => true
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
        'name' => 'name',
        'business_name' => 'businessName',
        'trading_name' => 'tradingName',
        'abn' => 'abn',
        'acn' => 'acn',
        'external_ref' => 'externalRef',
        'email' => 'email',
        'phone' => 'phone',
        'alt_contact_name' => 'altContactName',
        'alt_contact_phone' => 'altContactPhone',
        'billing_email' => 'billingEmail',
        'invoice_delivery' => 'invoiceDelivery',
        'usage_notification_method' => 'usageNotificationMethod',
        'payment_terms_days' => 'paymentTermsDays',
        'invoice_format' => 'invoiceFormat',
        'credit_limit' => 'creditLimit',
        'spend_limit' => 'spendLimit',
        'notes' => 'notes',
        'type' => 'type',
        'timezone' => 'timezone'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'name' => 'setName',
        'business_name' => 'setBusinessName',
        'trading_name' => 'setTradingName',
        'abn' => 'setAbn',
        'acn' => 'setAcn',
        'external_ref' => 'setExternalRef',
        'email' => 'setEmail',
        'phone' => 'setPhone',
        'alt_contact_name' => 'setAltContactName',
        'alt_contact_phone' => 'setAltContactPhone',
        'billing_email' => 'setBillingEmail',
        'invoice_delivery' => 'setInvoiceDelivery',
        'usage_notification_method' => 'setUsageNotificationMethod',
        'payment_terms_days' => 'setPaymentTermsDays',
        'invoice_format' => 'setInvoiceFormat',
        'credit_limit' => 'setCreditLimit',
        'spend_limit' => 'setSpendLimit',
        'notes' => 'setNotes',
        'type' => 'setType',
        'timezone' => 'setTimezone'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'name' => 'getName',
        'business_name' => 'getBusinessName',
        'trading_name' => 'getTradingName',
        'abn' => 'getAbn',
        'acn' => 'getAcn',
        'external_ref' => 'getExternalRef',
        'email' => 'getEmail',
        'phone' => 'getPhone',
        'alt_contact_name' => 'getAltContactName',
        'alt_contact_phone' => 'getAltContactPhone',
        'billing_email' => 'getBillingEmail',
        'invoice_delivery' => 'getInvoiceDelivery',
        'usage_notification_method' => 'getUsageNotificationMethod',
        'payment_terms_days' => 'getPaymentTermsDays',
        'invoice_format' => 'getInvoiceFormat',
        'credit_limit' => 'getCreditLimit',
        'spend_limit' => 'getSpendLimit',
        'notes' => 'getNotes',
        'type' => 'getType',
        'timezone' => 'getTimezone'
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

    public const INVOICE_DELIVERY_EMAIL = 'email';
    public const INVOICE_DELIVERY_POST = 'post';
    public const INVOICE_DELIVERY_NONE = 'none';
    public const USAGE_NOTIFICATION_METHOD_EMAIL = 'email';
    public const USAGE_NOTIFICATION_METHOD_SMS = 'sms';
    public const INVOICE_FORMAT_SUMMARY = 'summary';
    public const INVOICE_FORMAT_DETAILED = 'detailed';
    public const TYPE_RESIDENTIAL = 'residential';
    public const TYPE_BUSINESS = 'business';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getInvoiceDeliveryAllowableValues()
    {
        return [
            self::INVOICE_DELIVERY_EMAIL,
            self::INVOICE_DELIVERY_POST,
            self::INVOICE_DELIVERY_NONE,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getUsageNotificationMethodAllowableValues()
    {
        return [
            self::USAGE_NOTIFICATION_METHOD_EMAIL,
            self::USAGE_NOTIFICATION_METHOD_SMS,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getInvoiceFormatAllowableValues()
    {
        return [
            self::INVOICE_FORMAT_SUMMARY,
            self::INVOICE_FORMAT_DETAILED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getTypeAllowableValues()
    {
        return [
            self::TYPE_RESIDENTIAL,
            self::TYPE_BUSINESS,
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
        $this->setIfExists('name', $data ?? [], null);
        $this->setIfExists('business_name', $data ?? [], null);
        $this->setIfExists('trading_name', $data ?? [], null);
        $this->setIfExists('abn', $data ?? [], null);
        $this->setIfExists('acn', $data ?? [], null);
        $this->setIfExists('external_ref', $data ?? [], null);
        $this->setIfExists('email', $data ?? [], null);
        $this->setIfExists('phone', $data ?? [], null);
        $this->setIfExists('alt_contact_name', $data ?? [], null);
        $this->setIfExists('alt_contact_phone', $data ?? [], null);
        $this->setIfExists('billing_email', $data ?? [], null);
        $this->setIfExists('invoice_delivery', $data ?? [], null);
        $this->setIfExists('usage_notification_method', $data ?? [], null);
        $this->setIfExists('payment_terms_days', $data ?? [], null);
        $this->setIfExists('invoice_format', $data ?? [], null);
        $this->setIfExists('credit_limit', $data ?? [], null);
        $this->setIfExists('spend_limit', $data ?? [], null);
        $this->setIfExists('notes', $data ?? [], null);
        $this->setIfExists('type', $data ?? [], null);
        $this->setIfExists('timezone', $data ?? [], null);
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

        if (!is_null($this->container['name']) && (mb_strlen($this->container['name']) > 255)) {
            $invalidProperties[] = "invalid value for 'name', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['business_name']) && (mb_strlen($this->container['business_name']) > 255)) {
            $invalidProperties[] = "invalid value for 'business_name', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['trading_name']) && (mb_strlen($this->container['trading_name']) > 255)) {
            $invalidProperties[] = "invalid value for 'trading_name', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['acn']) && !preg_match("/^\\d{9}$/", $this->container['acn'])) {
            $invalidProperties[] = "invalid value for 'acn', must be conform to the pattern /^\\d{9}$/.";
        }

        if (!is_null($this->container['external_ref']) && (mb_strlen($this->container['external_ref']) > 64)) {
            $invalidProperties[] = "invalid value for 'external_ref', the character length must be smaller than or equal to 64.";
        }

        if (!is_null($this->container['email']) && (mb_strlen($this->container['email']) > 255)) {
            $invalidProperties[] = "invalid value for 'email', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['phone']) && (mb_strlen($this->container['phone']) > 32)) {
            $invalidProperties[] = "invalid value for 'phone', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['alt_contact_name']) && (mb_strlen($this->container['alt_contact_name']) > 255)) {
            $invalidProperties[] = "invalid value for 'alt_contact_name', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['alt_contact_phone']) && (mb_strlen($this->container['alt_contact_phone']) > 32)) {
            $invalidProperties[] = "invalid value for 'alt_contact_phone', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['billing_email']) && (mb_strlen($this->container['billing_email']) > 255)) {
            $invalidProperties[] = "invalid value for 'billing_email', the character length must be smaller than or equal to 255.";
        }

        $allowedValues = $this->getInvoiceDeliveryAllowableValues();
        if (!is_null($this->container['invoice_delivery']) && !in_array($this->container['invoice_delivery'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'invoice_delivery', must be one of '%s'",
                $this->container['invoice_delivery'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getUsageNotificationMethodAllowableValues();
        if (!is_null($this->container['usage_notification_method']) && !in_array($this->container['usage_notification_method'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'usage_notification_method', must be one of '%s'",
                $this->container['usage_notification_method'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['payment_terms_days']) && ($this->container['payment_terms_days'] > 90)) {
            $invalidProperties[] = "invalid value for 'payment_terms_days', must be smaller than or equal to 90.";
        }

        if (!is_null($this->container['payment_terms_days']) && ($this->container['payment_terms_days'] < 0)) {
            $invalidProperties[] = "invalid value for 'payment_terms_days', must be bigger than or equal to 0.";
        }

        $allowedValues = $this->getInvoiceFormatAllowableValues();
        if (!is_null($this->container['invoice_format']) && !in_array($this->container['invoice_format'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'invoice_format', must be one of '%s'",
                $this->container['invoice_format'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['notes']) && (mb_strlen($this->container['notes']) > 5000)) {
            $invalidProperties[] = "invalid value for 'notes', the character length must be smaller than or equal to 5000.";
        }

        $allowedValues = $this->getTypeAllowableValues();
        if (!is_null($this->container['type']) && !in_array($this->container['type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'type', must be one of '%s'",
                $this->container['type'],
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
     * Gets name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->container['name'];
    }

    /**
     * Sets name
     *
     * @param string|null $name name
     *
     * @return self
     */
    public function setName($name)
    {
        if (is_null($name)) {
            throw new \InvalidArgumentException('non-nullable name cannot be null');
        }
        if ((mb_strlen($name) > 255)) {
            throw new \InvalidArgumentException('invalid length for $name when calling UpdateEndUserRequest., must be smaller than or equal to 255.');
        }

        $this->container['name'] = $name;

        return $this;
    }

    /**
     * Gets business_name
     *
     * @return string|null
     */
    public function getBusinessName()
    {
        return $this->container['business_name'];
    }

    /**
     * Sets business_name
     *
     * @param string|null $business_name The legal business name. Required for a business end user; ignored for a residential one.
     *
     * @return self
     */
    public function setBusinessName($business_name)
    {
        if (is_null($business_name)) {
            array_push($this->openAPINullablesSetToNull, 'business_name');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('business_name', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($business_name) && (mb_strlen($business_name) > 255)) {
            throw new \InvalidArgumentException('invalid length for $business_name when calling UpdateEndUserRequest., must be smaller than or equal to 255.');
        }

        $this->container['business_name'] = $business_name;

        return $this;
    }

    /**
     * Gets trading_name
     *
     * @return string|null
     */
    public function getTradingName()
    {
        return $this->container['trading_name'];
    }

    /**
     * Sets trading_name
     *
     * @param string|null $trading_name Kept for a business end user only.
     *
     * @return self
     */
    public function setTradingName($trading_name)
    {
        if (is_null($trading_name)) {
            array_push($this->openAPINullablesSetToNull, 'trading_name');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('trading_name', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($trading_name) && (mb_strlen($trading_name) > 255)) {
            throw new \InvalidArgumentException('invalid length for $trading_name when calling UpdateEndUserRequest., must be smaller than or equal to 255.');
        }

        $this->container['trading_name'] = $trading_name;

        return $this;
    }

    /**
     * Gets abn
     *
     * @return string|null
     */
    public function getAbn()
    {
        return $this->container['abn'];
    }

    /**
     * Sets abn
     *
     * @param string|null $abn An ABN, checked with the ATO check digit. Spaces are allowed. Kept for a business end user only.
     *
     * @return self
     */
    public function setAbn($abn)
    {
        if (is_null($abn)) {
            array_push($this->openAPINullablesSetToNull, 'abn');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('abn', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['abn'] = $abn;

        return $this;
    }

    /**
     * Gets acn
     *
     * @return string|null
     */
    public function getAcn()
    {
        return $this->container['acn'];
    }

    /**
     * Sets acn
     *
     * @param string|null $acn Kept for a business end user only.
     *
     * @return self
     */
    public function setAcn($acn)
    {
        if (is_null($acn)) {
            array_push($this->openAPINullablesSetToNull, 'acn');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('acn', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }

        if (!is_null($acn) && (!preg_match("/^\\d{9}$/", ObjectSerializer::toString($acn)))) {
            throw new \InvalidArgumentException("invalid value for \$acn when calling UpdateEndUserRequest., must conform to the pattern /^\\d{9}$/.");
        }

        $this->container['acn'] = $acn;

        return $this;
    }

    /**
     * Gets external_ref
     *
     * @return string|null
     */
    public function getExternalRef()
    {
        return $this->container['external_ref'];
    }

    /**
     * Sets external_ref
     *
     * @param string|null $external_ref Your own reference for the end user, such as its id in your CRM.
     *
     * @return self
     */
    public function setExternalRef($external_ref)
    {
        if (is_null($external_ref)) {
            array_push($this->openAPINullablesSetToNull, 'external_ref');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('external_ref', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($external_ref) && (mb_strlen($external_ref) > 64)) {
            throw new \InvalidArgumentException('invalid length for $external_ref when calling UpdateEndUserRequest., must be smaller than or equal to 64.');
        }

        $this->container['external_ref'] = $external_ref;

        return $this;
    }

    /**
     * Gets email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->container['email'];
    }

    /**
     * Sets email
     *
     * @param string|null $email email
     *
     * @return self
     */
    public function setEmail($email)
    {
        if (is_null($email)) {
            array_push($this->openAPINullablesSetToNull, 'email');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('email', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($email) && (mb_strlen($email) > 255)) {
            throw new \InvalidArgumentException('invalid length for $email when calling UpdateEndUserRequest., must be smaller than or equal to 255.');
        }

        $this->container['email'] = $email;

        return $this;
    }

    /**
     * Gets phone
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->container['phone'];
    }

    /**
     * Sets phone
     *
     * @param string|null $phone phone
     *
     * @return self
     */
    public function setPhone($phone)
    {
        if (is_null($phone)) {
            array_push($this->openAPINullablesSetToNull, 'phone');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('phone', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($phone) && (mb_strlen($phone) > 32)) {
            throw new \InvalidArgumentException('invalid length for $phone when calling UpdateEndUserRequest., must be smaller than or equal to 32.');
        }

        $this->container['phone'] = $phone;

        return $this;
    }

    /**
     * Gets alt_contact_name
     *
     * @return string|null
     */
    public function getAltContactName()
    {
        return $this->container['alt_contact_name'];
    }

    /**
     * Sets alt_contact_name
     *
     * @param string|null $alt_contact_name alt_contact_name
     *
     * @return self
     */
    public function setAltContactName($alt_contact_name)
    {
        if (is_null($alt_contact_name)) {
            array_push($this->openAPINullablesSetToNull, 'alt_contact_name');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('alt_contact_name', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($alt_contact_name) && (mb_strlen($alt_contact_name) > 255)) {
            throw new \InvalidArgumentException('invalid length for $alt_contact_name when calling UpdateEndUserRequest., must be smaller than or equal to 255.');
        }

        $this->container['alt_contact_name'] = $alt_contact_name;

        return $this;
    }

    /**
     * Gets alt_contact_phone
     *
     * @return string|null
     */
    public function getAltContactPhone()
    {
        return $this->container['alt_contact_phone'];
    }

    /**
     * Sets alt_contact_phone
     *
     * @param string|null $alt_contact_phone alt_contact_phone
     *
     * @return self
     */
    public function setAltContactPhone($alt_contact_phone)
    {
        if (is_null($alt_contact_phone)) {
            array_push($this->openAPINullablesSetToNull, 'alt_contact_phone');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('alt_contact_phone', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($alt_contact_phone) && (mb_strlen($alt_contact_phone) > 32)) {
            throw new \InvalidArgumentException('invalid length for $alt_contact_phone when calling UpdateEndUserRequest., must be smaller than or equal to 32.');
        }

        $this->container['alt_contact_phone'] = $alt_contact_phone;

        return $this;
    }

    /**
     * Gets billing_email
     *
     * @return string|null
     */
    public function getBillingEmail()
    {
        return $this->container['billing_email'];
    }

    /**
     * Sets billing_email
     *
     * @param string|null $billing_email billing_email
     *
     * @return self
     */
    public function setBillingEmail($billing_email)
    {
        if (is_null($billing_email)) {
            array_push($this->openAPINullablesSetToNull, 'billing_email');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('billing_email', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($billing_email) && (mb_strlen($billing_email) > 255)) {
            throw new \InvalidArgumentException('invalid length for $billing_email when calling UpdateEndUserRequest., must be smaller than or equal to 255.');
        }

        $this->container['billing_email'] = $billing_email;

        return $this;
    }

    /**
     * Gets invoice_delivery
     *
     * @return string|null
     */
    public function getInvoiceDelivery()
    {
        return $this->container['invoice_delivery'];
    }

    /**
     * Sets invoice_delivery
     *
     * @param string|null $invoice_delivery How their bills are sent. On a create, left out or null takes your default.
     *
     * @return self
     */
    public function setInvoiceDelivery($invoice_delivery)
    {
        if (is_null($invoice_delivery)) {
            array_push($this->openAPINullablesSetToNull, 'invoice_delivery');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('invoice_delivery', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getInvoiceDeliveryAllowableValues();
        if (!is_null($invoice_delivery) && !in_array($invoice_delivery, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'invoice_delivery', must be one of '%s'",
                    $invoice_delivery,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['invoice_delivery'] = $invoice_delivery;

        return $this;
    }

    /**
     * Gets usage_notification_method
     *
     * @return string|null
     */
    public function getUsageNotificationMethod()
    {
        return $this->container['usage_notification_method'];
    }

    /**
     * Sets usage_notification_method
     *
     * @param string|null $usage_notification_method How they are told about their data usage. On a create, left out or null is email.
     *
     * @return self
     */
    public function setUsageNotificationMethod($usage_notification_method)
    {
        if (is_null($usage_notification_method)) {
            array_push($this->openAPINullablesSetToNull, 'usage_notification_method');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('usage_notification_method', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getUsageNotificationMethodAllowableValues();
        if (!is_null($usage_notification_method) && !in_array($usage_notification_method, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'usage_notification_method', must be one of '%s'",
                    $usage_notification_method,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['usage_notification_method'] = $usage_notification_method;

        return $this;
    }

    /**
     * Gets payment_terms_days
     *
     * @return int|null
     */
    public function getPaymentTermsDays()
    {
        return $this->container['payment_terms_days'];
    }

    /**
     * Sets payment_terms_days
     *
     * @param int|null $payment_terms_days Days to pay their bills. Null uses your default.
     *
     * @return self
     */
    public function setPaymentTermsDays($payment_terms_days)
    {
        if (is_null($payment_terms_days)) {
            array_push($this->openAPINullablesSetToNull, 'payment_terms_days');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('payment_terms_days', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($payment_terms_days) && ($payment_terms_days > 90)) {
            throw new \InvalidArgumentException('invalid value for $payment_terms_days when calling UpdateEndUserRequest., must be smaller than or equal to 90.');
        }
        if (!is_null($payment_terms_days) && ($payment_terms_days < 0)) {
            throw new \InvalidArgumentException('invalid value for $payment_terms_days when calling UpdateEndUserRequest., must be bigger than or equal to 0.');
        }

        $this->container['payment_terms_days'] = $payment_terms_days;

        return $this;
    }

    /**
     * Gets invoice_format
     *
     * @return string|null
     */
    public function getInvoiceFormat()
    {
        return $this->container['invoice_format'];
    }

    /**
     * Sets invoice_format
     *
     * @param string|null $invoice_format Null uses your default.
     *
     * @return self
     */
    public function setInvoiceFormat($invoice_format)
    {
        if (is_null($invoice_format)) {
            array_push($this->openAPINullablesSetToNull, 'invoice_format');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('invoice_format', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getInvoiceFormatAllowableValues();
        if (!is_null($invoice_format) && !in_array($invoice_format, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'invoice_format', must be one of '%s'",
                    $invoice_format,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['invoice_format'] = $invoice_format;

        return $this;
    }

    /**
     * Gets credit_limit
     *
     * @return \CheckNetworks\Model\EndUserDetailsCreditLimit|null
     */
    public function getCreditLimit()
    {
        return $this->container['credit_limit'];
    }

    /**
     * Sets credit_limit
     *
     * @param \CheckNetworks\Model\EndUserDetailsCreditLimit|null $credit_limit credit_limit
     *
     * @return self
     */
    public function setCreditLimit($credit_limit)
    {
        if (is_null($credit_limit)) {
            array_push($this->openAPINullablesSetToNull, 'credit_limit');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('credit_limit', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['credit_limit'] = $credit_limit;

        return $this;
    }

    /**
     * Gets spend_limit
     *
     * @return \CheckNetworks\Model\EndUserDetailsSpendLimit|null
     */
    public function getSpendLimit()
    {
        return $this->container['spend_limit'];
    }

    /**
     * Sets spend_limit
     *
     * @param \CheckNetworks\Model\EndUserDetailsSpendLimit|null $spend_limit spend_limit
     *
     * @return self
     */
    public function setSpendLimit($spend_limit)
    {
        if (is_null($spend_limit)) {
            array_push($this->openAPINullablesSetToNull, 'spend_limit');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('spend_limit', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['spend_limit'] = $spend_limit;

        return $this;
    }

    /**
     * Gets notes
     *
     * @return string|null
     */
    public function getNotes()
    {
        return $this->container['notes'];
    }

    /**
     * Sets notes
     *
     * @param string|null $notes notes
     *
     * @return self
     */
    public function setNotes($notes)
    {
        if (is_null($notes)) {
            array_push($this->openAPINullablesSetToNull, 'notes');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('notes', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($notes) && (mb_strlen($notes) > 5000)) {
            throw new \InvalidArgumentException('invalid length for $notes when calling UpdateEndUserRequest., must be smaller than or equal to 5000.');
        }

        $this->container['notes'] = $notes;

        return $this;
    }

    /**
     * Gets type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->container['type'];
    }

    /**
     * Sets type
     *
     * @param string|null $type type
     *
     * @return self
     */
    public function setType($type)
    {
        if (is_null($type)) {
            throw new \InvalidArgumentException('non-nullable type cannot be null');
        }
        $allowedValues = $this->getTypeAllowableValues();
        if (!in_array($type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'type', must be one of '%s'",
                    $type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['type'] = $type;

        return $this;
    }

    /**
     * Gets timezone
     *
     * @return string|null
     */
    public function getTimezone()
    {
        return $this->container['timezone'];
    }

    /**
     * Sets timezone
     *
     * @param string|null $timezone The end user's own time zone, one of `Australia/Brisbane`, `Australia/Sydney`, `Australia/Melbourne`, `Australia/Adelaide`, `Australia/Perth`, `Australia/Darwin` or `Australia/Hobart`. Any other is refused (422). Null clears it, so they are in your zone.
     *
     * @return self
     */
    public function setTimezone($timezone)
    {
        if (is_null($timezone)) {
            array_push($this->openAPINullablesSetToNull, 'timezone');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('timezone', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['timezone'] = $timezone;

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


