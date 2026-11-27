<?php
/**
 * RetailInvoiceSettings
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
 * RetailInvoiceSettings Class Doc Comment
 *
 * @category Class
 * @description The settings printed on every invoice to your end users, as Retail Settings sets them in the portal. The logo is set under the portal&#39;s branding and domain settings, not here.
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class RetailInvoiceSettings implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'RetailInvoiceSettings';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'display_name' => 'string',
        'abn' => 'string',
        'default_terms_days' => 'int',
        'default_invoice_delivery' => 'string',
        'invoice_copy_email' => 'string',
        'default_invoice_format' => 'string',
        'invoice_number_prefix' => 'string',
        'invoice_next_number' => 'int',
        'remittance_text' => 'string',
        'bpay_biller_code' => 'string'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'display_name' => null,
        'abn' => null,
        'default_terms_days' => null,
        'default_invoice_delivery' => null,
        'invoice_copy_email' => 'email',
        'default_invoice_format' => null,
        'invoice_number_prefix' => null,
        'invoice_next_number' => null,
        'remittance_text' => null,
        'bpay_biller_code' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'display_name' => true,
        'abn' => true,
        'default_terms_days' => true,
        'default_invoice_delivery' => false,
        'invoice_copy_email' => true,
        'default_invoice_format' => false,
        'invoice_number_prefix' => true,
        'invoice_next_number' => true,
        'remittance_text' => true,
        'bpay_biller_code' => true
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
        'display_name' => 'displayName',
        'abn' => 'abn',
        'default_terms_days' => 'defaultTermsDays',
        'default_invoice_delivery' => 'defaultInvoiceDelivery',
        'invoice_copy_email' => 'invoiceCopyEmail',
        'default_invoice_format' => 'defaultInvoiceFormat',
        'invoice_number_prefix' => 'invoiceNumberPrefix',
        'invoice_next_number' => 'invoiceNextNumber',
        'remittance_text' => 'remittanceText',
        'bpay_biller_code' => 'bpayBillerCode'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'display_name' => 'setDisplayName',
        'abn' => 'setAbn',
        'default_terms_days' => 'setDefaultTermsDays',
        'default_invoice_delivery' => 'setDefaultInvoiceDelivery',
        'invoice_copy_email' => 'setInvoiceCopyEmail',
        'default_invoice_format' => 'setDefaultInvoiceFormat',
        'invoice_number_prefix' => 'setInvoiceNumberPrefix',
        'invoice_next_number' => 'setInvoiceNextNumber',
        'remittance_text' => 'setRemittanceText',
        'bpay_biller_code' => 'setBpayBillerCode'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'display_name' => 'getDisplayName',
        'abn' => 'getAbn',
        'default_terms_days' => 'getDefaultTermsDays',
        'default_invoice_delivery' => 'getDefaultInvoiceDelivery',
        'invoice_copy_email' => 'getInvoiceCopyEmail',
        'default_invoice_format' => 'getDefaultInvoiceFormat',
        'invoice_number_prefix' => 'getInvoiceNumberPrefix',
        'invoice_next_number' => 'getInvoiceNextNumber',
        'remittance_text' => 'getRemittanceText',
        'bpay_biller_code' => 'getBpayBillerCode'
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

    public const DEFAULT_INVOICE_DELIVERY_EMAIL = 'email';
    public const DEFAULT_INVOICE_DELIVERY_POST = 'post';
    public const DEFAULT_INVOICE_DELIVERY_NONE = 'none';
    public const DEFAULT_INVOICE_FORMAT_SUMMARY = 'summary';
    public const DEFAULT_INVOICE_FORMAT_DETAILED = 'detailed';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getDefaultInvoiceDeliveryAllowableValues()
    {
        return [
            self::DEFAULT_INVOICE_DELIVERY_EMAIL,
            self::DEFAULT_INVOICE_DELIVERY_POST,
            self::DEFAULT_INVOICE_DELIVERY_NONE,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getDefaultInvoiceFormatAllowableValues()
    {
        return [
            self::DEFAULT_INVOICE_FORMAT_SUMMARY,
            self::DEFAULT_INVOICE_FORMAT_DETAILED,
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
        $this->setIfExists('display_name', $data ?? [], null);
        $this->setIfExists('abn', $data ?? [], null);
        $this->setIfExists('default_terms_days', $data ?? [], null);
        $this->setIfExists('default_invoice_delivery', $data ?? [], null);
        $this->setIfExists('invoice_copy_email', $data ?? [], null);
        $this->setIfExists('default_invoice_format', $data ?? [], null);
        $this->setIfExists('invoice_number_prefix', $data ?? [], null);
        $this->setIfExists('invoice_next_number', $data ?? [], null);
        $this->setIfExists('remittance_text', $data ?? [], null);
        $this->setIfExists('bpay_biller_code', $data ?? [], null);
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

        if (!is_null($this->container['display_name']) && (mb_strlen($this->container['display_name']) > 255)) {
            $invalidProperties[] = "invalid value for 'display_name', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['default_terms_days']) && ($this->container['default_terms_days'] > 90)) {
            $invalidProperties[] = "invalid value for 'default_terms_days', must be smaller than or equal to 90.";
        }

        if (!is_null($this->container['default_terms_days']) && ($this->container['default_terms_days'] < 0)) {
            $invalidProperties[] = "invalid value for 'default_terms_days', must be bigger than or equal to 0.";
        }

        $allowedValues = $this->getDefaultInvoiceDeliveryAllowableValues();
        if (!is_null($this->container['default_invoice_delivery']) && !in_array($this->container['default_invoice_delivery'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'default_invoice_delivery', must be one of '%s'",
                $this->container['default_invoice_delivery'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getDefaultInvoiceFormatAllowableValues();
        if (!is_null($this->container['default_invoice_format']) && !in_array($this->container['default_invoice_format'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'default_invoice_format', must be one of '%s'",
                $this->container['default_invoice_format'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['invoice_number_prefix']) && (mb_strlen($this->container['invoice_number_prefix']) > 12)) {
            $invalidProperties[] = "invalid value for 'invoice_number_prefix', the character length must be smaller than or equal to 12.";
        }

        if (!is_null($this->container['invoice_number_prefix']) && !preg_match("/^[A-Za-z0-9][A-Za-z0-9\\-]*$/", $this->container['invoice_number_prefix'])) {
            $invalidProperties[] = "invalid value for 'invoice_number_prefix', must be conform to the pattern /^[A-Za-z0-9][A-Za-z0-9\\-]*$/.";
        }

        if (!is_null($this->container['invoice_next_number']) && ($this->container['invoice_next_number'] > 999999999999)) {
            $invalidProperties[] = "invalid value for 'invoice_next_number', must be smaller than or equal to 999999999999.";
        }

        if (!is_null($this->container['invoice_next_number']) && ($this->container['invoice_next_number'] < 1)) {
            $invalidProperties[] = "invalid value for 'invoice_next_number', must be bigger than or equal to 1.";
        }

        if (!is_null($this->container['remittance_text']) && (mb_strlen($this->container['remittance_text']) > 2000)) {
            $invalidProperties[] = "invalid value for 'remittance_text', the character length must be smaller than or equal to 2000.";
        }

        if (!is_null($this->container['bpay_biller_code']) && !preg_match("/^\\d{3,10}$/", $this->container['bpay_biller_code'])) {
            $invalidProperties[] = "invalid value for 'bpay_biller_code', must be conform to the pattern /^\\d{3,10}$/.";
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
     * Gets display_name
     *
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->container['display_name'];
    }

    /**
     * Sets display_name
     *
     * @param string|null $display_name The business name your end-user invoices are issued by. Null for your account's name.
     *
     * @return self
     */
    public function setDisplayName($display_name)
    {
        if (is_null($display_name)) {
            array_push($this->openAPINullablesSetToNull, 'display_name');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('display_name', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($display_name) && (mb_strlen($display_name) > 255)) {
            throw new \InvalidArgumentException('invalid length for $display_name when calling RetailInvoiceSettings., must be smaller than or equal to 255.');
        }

        $this->container['display_name'] = $display_name;

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
     * @param string|null $abn Your ABN, printed on each tax invoice: 11 digits, which must pass the ATO check digit. Spaces are accepted on a write and stored as bare digits.
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
     * Gets default_terms_days
     *
     * @return int|null
     */
    public function getDefaultTermsDays()
    {
        return $this->container['default_terms_days'];
    }

    /**
     * Sets default_terms_days
     *
     * @param int|null $default_terms_days Days to pay for an end user with no terms of its own. Null for 14 days.
     *
     * @return self
     */
    public function setDefaultTermsDays($default_terms_days)
    {
        if (is_null($default_terms_days)) {
            array_push($this->openAPINullablesSetToNull, 'default_terms_days');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('default_terms_days', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($default_terms_days) && ($default_terms_days > 90)) {
            throw new \InvalidArgumentException('invalid value for $default_terms_days when calling RetailInvoiceSettings., must be smaller than or equal to 90.');
        }
        if (!is_null($default_terms_days) && ($default_terms_days < 0)) {
            throw new \InvalidArgumentException('invalid value for $default_terms_days when calling RetailInvoiceSettings., must be bigger than or equal to 0.');
        }

        $this->container['default_terms_days'] = $default_terms_days;

        return $this;
    }

    /**
     * Gets default_invoice_delivery
     *
     * @return string|null
     */
    public function getDefaultInvoiceDelivery()
    {
        return $this->container['default_invoice_delivery'];
    }

    /**
     * Sets default_invoice_delivery
     *
     * @param string|null $default_invoice_delivery The invoice delivery a new end user starts with.
     *
     * @return self
     */
    public function setDefaultInvoiceDelivery($default_invoice_delivery)
    {
        if (is_null($default_invoice_delivery)) {
            throw new \InvalidArgumentException('non-nullable default_invoice_delivery cannot be null');
        }
        $allowedValues = $this->getDefaultInvoiceDeliveryAllowableValues();
        if (!in_array($default_invoice_delivery, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'default_invoice_delivery', must be one of '%s'",
                    $default_invoice_delivery,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['default_invoice_delivery'] = $default_invoice_delivery;

        return $this;
    }

    /**
     * Gets invoice_copy_email
     *
     * @return string|null
     */
    public function getInvoiceCopyEmail()
    {
        return $this->container['invoice_copy_email'];
    }

    /**
     * Sets invoice_copy_email
     *
     * @param string|null $invoice_copy_email Blind-copied on each end-user invoice email. Null sends no copy.
     *
     * @return self
     */
    public function setInvoiceCopyEmail($invoice_copy_email)
    {
        if (is_null($invoice_copy_email)) {
            array_push($this->openAPINullablesSetToNull, 'invoice_copy_email');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('invoice_copy_email', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['invoice_copy_email'] = $invoice_copy_email;

        return $this;
    }

    /**
     * Gets default_invoice_format
     *
     * @return string|null
     */
    public function getDefaultInvoiceFormat()
    {
        return $this->container['default_invoice_format'];
    }

    /**
     * Sets default_invoice_format
     *
     * @param string|null $default_invoice_format Summary or detailed invoices, for an end user with no choice of its own. Detailed lists each call under its usage line.
     *
     * @return self
     */
    public function setDefaultInvoiceFormat($default_invoice_format)
    {
        if (is_null($default_invoice_format)) {
            throw new \InvalidArgumentException('non-nullable default_invoice_format cannot be null');
        }
        $allowedValues = $this->getDefaultInvoiceFormatAllowableValues();
        if (!in_array($default_invoice_format, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'default_invoice_format', must be one of '%s'",
                    $default_invoice_format,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['default_invoice_format'] = $default_invoice_format;

        return $this;
    }

    /**
     * Gets invoice_number_prefix
     *
     * @return string|null
     */
    public function getInvoiceNumberPrefix()
    {
        return $this->container['invoice_number_prefix'];
    }

    /**
     * Sets invoice_number_prefix
     *
     * @param string|null $invoice_number_prefix Leads every end-user invoice and credit note number. Null for none.
     *
     * @return self
     */
    public function setInvoiceNumberPrefix($invoice_number_prefix)
    {
        if (is_null($invoice_number_prefix)) {
            array_push($this->openAPINullablesSetToNull, 'invoice_number_prefix');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('invoice_number_prefix', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($invoice_number_prefix) && (mb_strlen($invoice_number_prefix) > 12)) {
            throw new \InvalidArgumentException('invalid length for $invoice_number_prefix when calling RetailInvoiceSettings., must be smaller than or equal to 12.');
        }
        if (!is_null($invoice_number_prefix) && (!preg_match("/^[A-Za-z0-9][A-Za-z0-9\\-]*$/", ObjectSerializer::toString($invoice_number_prefix)))) {
            throw new \InvalidArgumentException("invalid value for \$invoice_number_prefix when calling RetailInvoiceSettings., must conform to the pattern /^[A-Za-z0-9][A-Za-z0-9\\-]*$/.");
        }

        $this->container['invoice_number_prefix'] = $invoice_number_prefix;

        return $this;
    }

    /**
     * Gets invoice_next_number
     *
     * @return int|null
     */
    public function getInvoiceNextNumber()
    {
        return $this->container['invoice_next_number'];
    }

    /**
     * Sets invoice_next_number
     *
     * @param int|null $invoice_next_number The next number of one running sequence across all your end users, shared by invoices and credit notes. Null numbers each invoice by end user and month. Each bill run moves it on.
     *
     * @return self
     */
    public function setInvoiceNextNumber($invoice_next_number)
    {
        if (is_null($invoice_next_number)) {
            array_push($this->openAPINullablesSetToNull, 'invoice_next_number');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('invoice_next_number', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($invoice_next_number) && ($invoice_next_number > 999999999999)) {
            throw new \InvalidArgumentException('invalid value for $invoice_next_number when calling RetailInvoiceSettings., must be smaller than or equal to 999999999999.');
        }
        if (!is_null($invoice_next_number) && ($invoice_next_number < 1)) {
            throw new \InvalidArgumentException('invalid value for $invoice_next_number when calling RetailInvoiceSettings., must be bigger than or equal to 1.');
        }

        $this->container['invoice_next_number'] = $invoice_next_number;

        return $this;
    }

    /**
     * Gets remittance_text
     *
     * @return string|null
     */
    public function getRemittanceText()
    {
        return $this->container['remittance_text'];
    }

    /**
     * Sets remittance_text
     *
     * @param string|null $remittance_text The bank transfer details printed on each invoice.
     *
     * @return self
     */
    public function setRemittanceText($remittance_text)
    {
        if (is_null($remittance_text)) {
            array_push($this->openAPINullablesSetToNull, 'remittance_text');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('remittance_text', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($remittance_text) && (mb_strlen($remittance_text) > 2000)) {
            throw new \InvalidArgumentException('invalid length for $remittance_text when calling RetailInvoiceSettings., must be smaller than or equal to 2000.');
        }

        $this->container['remittance_text'] = $remittance_text;

        return $this;
    }

    /**
     * Gets bpay_biller_code
     *
     * @return string|null
     */
    public function getBpayBillerCode()
    {
        return $this->container['bpay_biller_code'];
    }

    /**
     * Sets bpay_biller_code
     *
     * @param string|null $bpay_biller_code Your own BPAY biller code, 3 to 10 digits.
     *
     * @return self
     */
    public function setBpayBillerCode($bpay_biller_code)
    {
        if (is_null($bpay_biller_code)) {
            array_push($this->openAPINullablesSetToNull, 'bpay_biller_code');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('bpay_biller_code', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }

        if (!is_null($bpay_biller_code) && (!preg_match("/^\\d{3,10}$/", ObjectSerializer::toString($bpay_biller_code)))) {
            throw new \InvalidArgumentException("invalid value for \$bpay_biller_code when calling RetailInvoiceSettings., must conform to the pattern /^\\d{3,10}$/.");
        }

        $this->container['bpay_biller_code'] = $bpay_biller_code;

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


