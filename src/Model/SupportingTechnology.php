<?php
/**
 * SupportingTechnology
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
 * SupportingTechnology Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class SupportingTechnology implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'SupportingTechnology';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'business_fibre' => 'bool',
        'primary_access_technology' => 'string',
        'alternative_technology' => 'string',
        'access_type' => 'string',
        'serviceability_class' => 'string',
        'serviceability_class_reason' => 'string',
        'serviceability_date' => 'string',
        'estimated_availability_date' => 'string'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'business_fibre' => null,
        'primary_access_technology' => null,
        'alternative_technology' => null,
        'access_type' => null,
        'serviceability_class' => null,
        'serviceability_class_reason' => null,
        'serviceability_date' => null,
        'estimated_availability_date' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'business_fibre' => false,
        'primary_access_technology' => false,
        'alternative_technology' => false,
        'access_type' => false,
        'serviceability_class' => false,
        'serviceability_class_reason' => false,
        'serviceability_date' => false,
        'estimated_availability_date' => false
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
        'business_fibre' => 'businessFibre',
        'primary_access_technology' => 'primaryAccessTechnology',
        'alternative_technology' => 'alternativeTechnology',
        'access_type' => 'accessType',
        'serviceability_class' => 'serviceabilityClass',
        'serviceability_class_reason' => 'serviceabilityClassReason',
        'serviceability_date' => 'serviceabilityDate',
        'estimated_availability_date' => 'estimatedAvailabilityDate'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'business_fibre' => 'setBusinessFibre',
        'primary_access_technology' => 'setPrimaryAccessTechnology',
        'alternative_technology' => 'setAlternativeTechnology',
        'access_type' => 'setAccessType',
        'serviceability_class' => 'setServiceabilityClass',
        'serviceability_class_reason' => 'setServiceabilityClassReason',
        'serviceability_date' => 'setServiceabilityDate',
        'estimated_availability_date' => 'setEstimatedAvailabilityDate'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'business_fibre' => 'getBusinessFibre',
        'primary_access_technology' => 'getPrimaryAccessTechnology',
        'alternative_technology' => 'getAlternativeTechnology',
        'access_type' => 'getAccessType',
        'serviceability_class' => 'getServiceabilityClass',
        'serviceability_class_reason' => 'getServiceabilityClassReason',
        'serviceability_date' => 'getServiceabilityDate',
        'estimated_availability_date' => 'getEstimatedAvailabilityDate'
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

    public const PRIMARY_ACCESS_TECHNOLOGY_FIBRE = 'Fibre';
    public const PRIMARY_ACCESS_TECHNOLOGY_FIBRE_TO_THE_BUILDING = 'Fibre To The Building';
    public const PRIMARY_ACCESS_TECHNOLOGY_FIBRE_TO_THE_CURB = 'Fibre To The Curb';
    public const PRIMARY_ACCESS_TECHNOLOGY_FIBRE_TO_THE_NODE = 'Fibre To The Node';
    public const PRIMARY_ACCESS_TECHNOLOGY_HFC = 'HFC';
    public const PRIMARY_ACCESS_TECHNOLOGY_SATELLITE = 'Satellite';
    public const PRIMARY_ACCESS_TECHNOLOGY_WIRELESS = 'Wireless';
    public const SERVICEABILITY_CLASS__0 = '0';
    public const SERVICEABILITY_CLASS__1 = '1';
    public const SERVICEABILITY_CLASS__2 = '2';
    public const SERVICEABILITY_CLASS__3 = '3';
    public const SERVICEABILITY_CLASS__4 = '4';
    public const SERVICEABILITY_CLASS__5 = '5';
    public const SERVICEABILITY_CLASS__6 = '6';
    public const SERVICEABILITY_CLASS__7 = '7';
    public const SERVICEABILITY_CLASS__8 = '8';
    public const SERVICEABILITY_CLASS__9 = '9';
    public const SERVICEABILITY_CLASS__10 = '10';
    public const SERVICEABILITY_CLASS__11 = '11';
    public const SERVICEABILITY_CLASS__12 = '12';
    public const SERVICEABILITY_CLASS__13 = '13';
    public const SERVICEABILITY_CLASS__20 = '20';
    public const SERVICEABILITY_CLASS__21 = '21';
    public const SERVICEABILITY_CLASS__22 = '22';
    public const SERVICEABILITY_CLASS__23 = '23';
    public const SERVICEABILITY_CLASS__24 = '24';
    public const SERVICEABILITY_CLASS__30 = '30';
    public const SERVICEABILITY_CLASS__31 = '31';
    public const SERVICEABILITY_CLASS__32 = '32';
    public const SERVICEABILITY_CLASS__33 = '33';
    public const SERVICEABILITY_CLASS__34 = '34';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getPrimaryAccessTechnologyAllowableValues()
    {
        return [
            self::PRIMARY_ACCESS_TECHNOLOGY_FIBRE,
            self::PRIMARY_ACCESS_TECHNOLOGY_FIBRE_TO_THE_BUILDING,
            self::PRIMARY_ACCESS_TECHNOLOGY_FIBRE_TO_THE_CURB,
            self::PRIMARY_ACCESS_TECHNOLOGY_FIBRE_TO_THE_NODE,
            self::PRIMARY_ACCESS_TECHNOLOGY_HFC,
            self::PRIMARY_ACCESS_TECHNOLOGY_SATELLITE,
            self::PRIMARY_ACCESS_TECHNOLOGY_WIRELESS,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getServiceabilityClassAllowableValues()
    {
        return [
            self::SERVICEABILITY_CLASS__0,
            self::SERVICEABILITY_CLASS__1,
            self::SERVICEABILITY_CLASS__2,
            self::SERVICEABILITY_CLASS__3,
            self::SERVICEABILITY_CLASS__4,
            self::SERVICEABILITY_CLASS__5,
            self::SERVICEABILITY_CLASS__6,
            self::SERVICEABILITY_CLASS__7,
            self::SERVICEABILITY_CLASS__8,
            self::SERVICEABILITY_CLASS__9,
            self::SERVICEABILITY_CLASS__10,
            self::SERVICEABILITY_CLASS__11,
            self::SERVICEABILITY_CLASS__12,
            self::SERVICEABILITY_CLASS__13,
            self::SERVICEABILITY_CLASS__20,
            self::SERVICEABILITY_CLASS__21,
            self::SERVICEABILITY_CLASS__22,
            self::SERVICEABILITY_CLASS__23,
            self::SERVICEABILITY_CLASS__24,
            self::SERVICEABILITY_CLASS__30,
            self::SERVICEABILITY_CLASS__31,
            self::SERVICEABILITY_CLASS__32,
            self::SERVICEABILITY_CLASS__33,
            self::SERVICEABILITY_CLASS__34,
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
        $this->setIfExists('business_fibre', $data ?? [], null);
        $this->setIfExists('primary_access_technology', $data ?? [], null);
        $this->setIfExists('alternative_technology', $data ?? [], null);
        $this->setIfExists('access_type', $data ?? [], null);
        $this->setIfExists('serviceability_class', $data ?? [], null);
        $this->setIfExists('serviceability_class_reason', $data ?? [], null);
        $this->setIfExists('serviceability_date', $data ?? [], null);
        $this->setIfExists('estimated_availability_date', $data ?? [], null);
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

        $allowedValues = $this->getPrimaryAccessTechnologyAllowableValues();
        if (!is_null($this->container['primary_access_technology']) && !in_array($this->container['primary_access_technology'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'primary_access_technology', must be one of '%s'",
                $this->container['primary_access_technology'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getServiceabilityClassAllowableValues();
        if (!is_null($this->container['serviceability_class']) && !in_array($this->container['serviceability_class'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'serviceability_class', must be one of '%s'",
                $this->container['serviceability_class'],
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
     * Gets business_fibre
     *
     * @return bool|null
     */
    public function getBusinessFibre()
    {
        return $this->container['business_fibre'];
    }

    /**
     * Sets business_fibre
     *
     * @param bool|null $business_fibre Indicates whether Business Grade Services over Fibre is available at the Location.
     *
     * @return self
     */
    public function setBusinessFibre($business_fibre)
    {
        if (is_null($business_fibre)) {
            throw new \InvalidArgumentException('non-nullable business_fibre cannot be null');
        }
        $this->container['business_fibre'] = $business_fibre;

        return $this;
    }

    /**
     * Gets primary_access_technology
     *
     * @return string|null
     */
    public function getPrimaryAccessTechnology()
    {
        return $this->container['primary_access_technology'];
    }

    /**
     * Sets primary_access_technology
     *
     * @param string|null $primary_access_technology Primary Access Technology
     *
     * @return self
     */
    public function setPrimaryAccessTechnology($primary_access_technology)
    {
        if (is_null($primary_access_technology)) {
            throw new \InvalidArgumentException('non-nullable primary_access_technology cannot be null');
        }
        $allowedValues = $this->getPrimaryAccessTechnologyAllowableValues();
        if (!in_array($primary_access_technology, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'primary_access_technology', must be one of '%s'",
                    $primary_access_technology,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['primary_access_technology'] = $primary_access_technology;

        return $this;
    }

    /**
     * Gets alternative_technology
     *
     * @return string|null
     */
    public function getAlternativeTechnology()
    {
        return $this->container['alternative_technology'];
    }

    /**
     * Sets alternative_technology
     *
     * @param string|null $alternative_technology Another technology the location can move to. \"Fibre\" on an FTTN or FTTC premises means it is eligible for Fibre Uplift (`/nbn/service_qualification/fibre_uplift`).
     *
     * @return self
     */
    public function setAlternativeTechnology($alternative_technology)
    {
        if (is_null($alternative_technology)) {
            throw new \InvalidArgumentException('non-nullable alternative_technology cannot be null');
        }
        $this->container['alternative_technology'] = $alternative_technology;

        return $this;
    }

    /**
     * Gets access_type
     *
     * @return string|null
     */
    public function getAccessType()
    {
        return $this->container['access_type'];
    }

    /**
     * Sets access_type
     *
     * @param string|null $access_type Whether this is the location's primary or secondary access.
     *
     * @return self
     */
    public function setAccessType($access_type)
    {
        if (is_null($access_type)) {
            throw new \InvalidArgumentException('non-nullable access_type cannot be null');
        }
        $this->container['access_type'] = $access_type;

        return $this;
    }

    /**
     * Gets serviceability_class
     *
     * @return string|null
     */
    public function getServiceabilityClass()
    {
        return $this->container['serviceability_class'];
    }

    /**
     * Sets serviceability_class
     *
     * @param string|null $serviceability_class The servicability classes are:  0 - Planned to be serviced by Fibre in the future (boundary status isnt In Service yet) 1 - Serviceable by Fibre, no drop or NTD in place 2 - Serviceable by Fibre, drop in place, no NTD in place 3 - Serviceable by Fibre, drop and NTD in place 4 - Planned to be serviced by Wireless 5 - Serviceable by Wireless, CPE not installed 6 - Serviceable by Wireless, CPE installed 7 - Planned to be serviced by Satellite 8 - Serviceable by Satellite, Satellite - NTD not installed 9 - Serviceable by Satellite, Satellite - NTD installed 10 - Planned to be serviceable by Copper 11 - Serviceable by copper, active node present 12 - Serviceable by copper, jumpering is required 13 - Serviceable by copper, infrastructure in place 20 - Planned to be serviced by HFC, outside plant does not exist 21 - Serviceable by HFC, the location has a street TAP but requires a lead-in, PCD, internal tie cable, and wall-plate/socket 22 - Serviceable by HFC, the location has a street TAP, lead-in & PCD in place, but no internal tie-cables with wall plates/sockets 23 - Serviceable by HFC, the location has a wall-plate/socket but no HFC NTD 24 - Serviceable by HFC, Location has a wall-plate/socket & HFC NTD and is Ready to Connect 30 - Planned to be serviceable by FTTC 31 - Serviceable by FTTC, no line available (NCD required) 32 - Serviceable by FTTC, cut-in required (NCD required) 33 - Serviceable by FTTC, cut-in complete (NCD required) 34 - Serviceable by FTTC, infrastructure in place (Check NCD status)
     *
     * @return self
     */
    public function setServiceabilityClass($serviceability_class)
    {
        if (is_null($serviceability_class)) {
            throw new \InvalidArgumentException('non-nullable serviceability_class cannot be null');
        }
        $allowedValues = $this->getServiceabilityClassAllowableValues();
        if (!in_array($serviceability_class, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'serviceability_class', must be one of '%s'",
                    $serviceability_class,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['serviceability_class'] = $serviceability_class;

        return $this;
    }

    /**
     * Gets serviceability_class_reason
     *
     * @return string|null
     */
    public function getServiceabilityClassReason()
    {
        return $this->container['serviceability_class_reason'];
    }

    /**
     * Sets serviceability_class_reason
     *
     * @param string|null $serviceability_class_reason This value will contain the serviceability class reason for unserviceable Premises. It is not mandatory as only applicable for serviceability classes 0,4,7,10 and 20.
     *
     * @return self
     */
    public function setServiceabilityClassReason($serviceability_class_reason)
    {
        if (is_null($serviceability_class_reason)) {
            throw new \InvalidArgumentException('non-nullable serviceability_class_reason cannot be null');
        }
        $this->container['serviceability_class_reason'] = $serviceability_class_reason;

        return $this;
    }

    /**
     * Gets serviceability_date
     *
     * @return string|null
     */
    public function getServiceabilityDate()
    {
        return $this->container['serviceability_date'];
    }

    /**
     * Sets serviceability_date
     *
     * @param string|null $serviceability_date (Optional) Expected date when you can successfully submit an order. The value will be a DateTime stamp in UTC format. Example \"yyyy-mmddThh:mm:ssZ\".
     *
     * @return self
     */
    public function setServiceabilityDate($serviceability_date)
    {
        if (is_null($serviceability_date)) {
            throw new \InvalidArgumentException('non-nullable serviceability_date cannot be null');
        }
        $this->container['serviceability_date'] = $serviceability_date;

        return $this;
    }

    /**
     * Gets estimated_availability_date
     *
     * @return string|null
     */
    public function getEstimatedAvailabilityDate()
    {
        return $this->container['estimated_availability_date'];
    }

    /**
     * Sets estimated_availability_date
     *
     * @param string|null $estimated_availability_date (Optional) Expected date when you can successfully submit an EEAS order. The value will be a Date in ISO 8601 format. Example \"YYYY-MM-DD\".
     *
     * @return self
     */
    public function setEstimatedAvailabilityDate($estimated_availability_date)
    {
        if (is_null($estimated_availability_date)) {
            throw new \InvalidArgumentException('non-nullable estimated_availability_date cannot be null');
        }
        $this->container['estimated_availability_date'] = $estimated_availability_date;

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


