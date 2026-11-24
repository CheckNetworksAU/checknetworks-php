<?php
/**
 * UpdateTrunkRequest
 *
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

namespace CheckNetworks\Api\Generated\Model;

use \ArrayAccess;
use \CheckNetworks\Api\Generated\ObjectSerializer;

/**
 * UpdateTrunkRequest Class Doc Comment
 *
 * @category Class
 * @description &#x60;cpsLimit&#x60; and &#x60;faxEnabled&#x60; are managed by Check Networks; a request that sends either is refused.
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class UpdateTrunkRequest implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'update_trunk_request';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'name' => 'string',
        'external_ref' => 'string',
        'metadata' => '\CheckNetworks\Api\Generated\Model\MetadataInput',
        'description' => 'string',
        'type' => 'string',
        'channels' => 'int',
        'max_international' => 'int',
        'calling_permissions' => '\CheckNetworks\Api\Generated\Model\CallingPermission[]',
        'default_state' => 'string',
        'default_caller_id' => 'string',
        'force_outbound_cli' => 'bool',
        'force_private_cli' => 'bool',
        'recording_enabled' => 'bool',
        'acknowledge_recording' => 'bool',
        'allowed_countries' => 'string[]',
        'daily_spend_cap_ex_gst' => 'string',
        'called_number_format' => '\CheckNetworks\Api\Generated\Model\TrunkNumberFormat',
        'caller_number_format' => '\CheckNetworks\Api\Generated\Model\TrunkNumberFormat'
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
        'external_ref' => null,
        'metadata' => null,
        'description' => null,
        'type' => null,
        'channels' => null,
        'max_international' => null,
        'calling_permissions' => null,
        'default_state' => null,
        'default_caller_id' => null,
        'force_outbound_cli' => null,
        'force_private_cli' => null,
        'recording_enabled' => null,
        'acknowledge_recording' => null,
        'allowed_countries' => null,
        'daily_spend_cap_ex_gst' => null,
        'called_number_format' => null,
        'caller_number_format' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'name' => false,
        'external_ref' => true,
        'metadata' => true,
        'description' => true,
        'type' => false,
        'channels' => false,
        'max_international' => true,
        'calling_permissions' => false,
        'default_state' => true,
        'default_caller_id' => false,
        'force_outbound_cli' => false,
        'force_private_cli' => false,
        'recording_enabled' => false,
        'acknowledge_recording' => false,
        'allowed_countries' => true,
        'daily_spend_cap_ex_gst' => true,
        'called_number_format' => false,
        'caller_number_format' => false
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
        'external_ref' => 'externalRef',
        'metadata' => 'metadata',
        'description' => 'description',
        'type' => 'type',
        'channels' => 'channels',
        'max_international' => 'maxInternational',
        'calling_permissions' => 'callingPermissions',
        'default_state' => 'defaultState',
        'default_caller_id' => 'defaultCallerId',
        'force_outbound_cli' => 'forceOutboundCli',
        'force_private_cli' => 'forcePrivateCli',
        'recording_enabled' => 'recordingEnabled',
        'acknowledge_recording' => 'acknowledgeRecording',
        'allowed_countries' => 'allowedCountries',
        'daily_spend_cap_ex_gst' => 'dailySpendCapExGst',
        'called_number_format' => 'calledNumberFormat',
        'caller_number_format' => 'callerNumberFormat'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'name' => 'setName',
        'external_ref' => 'setExternalRef',
        'metadata' => 'setMetadata',
        'description' => 'setDescription',
        'type' => 'setType',
        'channels' => 'setChannels',
        'max_international' => 'setMaxInternational',
        'calling_permissions' => 'setCallingPermissions',
        'default_state' => 'setDefaultState',
        'default_caller_id' => 'setDefaultCallerId',
        'force_outbound_cli' => 'setForceOutboundCli',
        'force_private_cli' => 'setForcePrivateCli',
        'recording_enabled' => 'setRecordingEnabled',
        'acknowledge_recording' => 'setAcknowledgeRecording',
        'allowed_countries' => 'setAllowedCountries',
        'daily_spend_cap_ex_gst' => 'setDailySpendCapExGst',
        'called_number_format' => 'setCalledNumberFormat',
        'caller_number_format' => 'setCallerNumberFormat'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'name' => 'getName',
        'external_ref' => 'getExternalRef',
        'metadata' => 'getMetadata',
        'description' => 'getDescription',
        'type' => 'getType',
        'channels' => 'getChannels',
        'max_international' => 'getMaxInternational',
        'calling_permissions' => 'getCallingPermissions',
        'default_state' => 'getDefaultState',
        'default_caller_id' => 'getDefaultCallerId',
        'force_outbound_cli' => 'getForceOutboundCli',
        'force_private_cli' => 'getForcePrivateCli',
        'recording_enabled' => 'getRecordingEnabled',
        'acknowledge_recording' => 'getAcknowledgeRecording',
        'allowed_countries' => 'getAllowedCountries',
        'daily_spend_cap_ex_gst' => 'getDailySpendCapExGst',
        'called_number_format' => 'getCalledNumberFormat',
        'caller_number_format' => 'getCallerNumberFormat'
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

    public const TYPE_PAYG = 'payg';
    public const TYPE_ULTIMATE = 'ultimate';
    public const DEFAULT_STATE_NSW = 'NSW';
    public const DEFAULT_STATE_VIC = 'VIC';
    public const DEFAULT_STATE_QLD = 'QLD';
    public const DEFAULT_STATE_SA = 'SA';
    public const DEFAULT_STATE_WA = 'WA';
    public const DEFAULT_STATE_TAS = 'TAS';
    public const DEFAULT_STATE_NT = 'NT';
    public const DEFAULT_STATE_ACT = 'ACT';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getTypeAllowableValues()
    {
        return [
            self::TYPE_PAYG,
            self::TYPE_ULTIMATE,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getDefaultStateAllowableValues()
    {
        return [
            self::DEFAULT_STATE_NSW,
            self::DEFAULT_STATE_VIC,
            self::DEFAULT_STATE_QLD,
            self::DEFAULT_STATE_SA,
            self::DEFAULT_STATE_WA,
            self::DEFAULT_STATE_TAS,
            self::DEFAULT_STATE_NT,
            self::DEFAULT_STATE_ACT,
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
        $this->setIfExists('external_ref', $data ?? [], null);
        $this->setIfExists('metadata', $data ?? [], null);
        $this->setIfExists('description', $data ?? [], null);
        $this->setIfExists('type', $data ?? [], null);
        $this->setIfExists('channels', $data ?? [], null);
        $this->setIfExists('max_international', $data ?? [], null);
        $this->setIfExists('calling_permissions', $data ?? [], null);
        $this->setIfExists('default_state', $data ?? [], null);
        $this->setIfExists('default_caller_id', $data ?? [], null);
        $this->setIfExists('force_outbound_cli', $data ?? [], null);
        $this->setIfExists('force_private_cli', $data ?? [], null);
        $this->setIfExists('recording_enabled', $data ?? [], null);
        $this->setIfExists('acknowledge_recording', $data ?? [], false);
        $this->setIfExists('allowed_countries', $data ?? [], null);
        $this->setIfExists('daily_spend_cap_ex_gst', $data ?? [], null);
        $this->setIfExists('called_number_format', $data ?? [], null);
        $this->setIfExists('caller_number_format', $data ?? [], null);
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

        if (!is_null($this->container['name']) && (mb_strlen($this->container['name']) > 120)) {
            $invalidProperties[] = "invalid value for 'name', the character length must be smaller than or equal to 120.";
        }

        if (!is_null($this->container['external_ref']) && (mb_strlen($this->container['external_ref']) > 64)) {
            $invalidProperties[] = "invalid value for 'external_ref', the character length must be smaller than or equal to 64.";
        }

        if (!is_null($this->container['external_ref']) && (mb_strlen($this->container['external_ref']) < 1)) {
            $invalidProperties[] = "invalid value for 'external_ref', the character length must be bigger than or equal to 1.";
        }

        if (!is_null($this->container['external_ref']) && !preg_match("/^[A-Za-z0-9_.\\-]+$/", $this->container['external_ref'])) {
            $invalidProperties[] = "invalid value for 'external_ref', must be conform to the pattern /^[A-Za-z0-9_.\\-]+$/.";
        }

        if (!is_null($this->container['description']) && (mb_strlen($this->container['description']) > 500)) {
            $invalidProperties[] = "invalid value for 'description', the character length must be smaller than or equal to 500.";
        }

        $allowedValues = $this->getTypeAllowableValues();
        if (!is_null($this->container['type']) && !in_array($this->container['type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'type', must be one of '%s'",
                $this->container['type'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['channels']) && ($this->container['channels'] > 100)) {
            $invalidProperties[] = "invalid value for 'channels', must be smaller than or equal to 100.";
        }

        if (!is_null($this->container['channels']) && ($this->container['channels'] < 0)) {
            $invalidProperties[] = "invalid value for 'channels', must be bigger than or equal to 0.";
        }

        if (!is_null($this->container['max_international']) && ($this->container['max_international'] > 9999)) {
            $invalidProperties[] = "invalid value for 'max_international', must be smaller than or equal to 9999.";
        }

        if (!is_null($this->container['max_international']) && ($this->container['max_international'] < 0)) {
            $invalidProperties[] = "invalid value for 'max_international', must be bigger than or equal to 0.";
        }

        $allowedValues = $this->getDefaultStateAllowableValues();
        if (!is_null($this->container['default_state']) && !in_array($this->container['default_state'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'default_state', must be one of '%s'",
                $this->container['default_state'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['default_caller_id']) && (mb_strlen($this->container['default_caller_id']) > 24)) {
            $invalidProperties[] = "invalid value for 'default_caller_id', the character length must be smaller than or equal to 24.";
        }

        if (!is_null($this->container['default_caller_id']) && !preg_match("/^[0-9+\\s]*[0-9][0-9+\\s]*$/", $this->container['default_caller_id'])) {
            $invalidProperties[] = "invalid value for 'default_caller_id', must be conform to the pattern /^[0-9+\\s]*[0-9][0-9+\\s]*$/.";
        }

        if (!is_null($this->container['allowed_countries']) && (count($this->container['allowed_countries']) < 1)) {
            $invalidProperties[] = "invalid value for 'allowed_countries', number of items must be greater than or equal to 1.";
        }

        if (!is_null($this->container['daily_spend_cap_ex_gst']) && !preg_match("/^\\d{1,7}(\\.\\d{1,2})?$/", $this->container['daily_spend_cap_ex_gst'])) {
            $invalidProperties[] = "invalid value for 'daily_spend_cap_ex_gst', must be conform to the pattern /^\\d{1,7}(\\.\\d{1,2})?$/.";
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
        if ((mb_strlen($name) > 120)) {
            throw new \InvalidArgumentException('invalid length for $name when calling UpdateTrunkRequest., must be smaller than or equal to 120.');
        }

        $this->container['name'] = $name;

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
     * @param string|null $external_ref Your own reference for this object, such as your customer or job id, so you need not keep a map from our ids to yours. Up to 64 letters, digits, `_`, `.` or `-`. Unique on your account for each kind of object (two trunks cannot share one, but a trunk and a ticket can); matched exactly by the list's `externalRef` filter. Set it on create or with PATCH; send null to clear it. Null when none is set.
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
            throw new \InvalidArgumentException('invalid length for $external_ref when calling UpdateTrunkRequest., must be smaller than or equal to 64.');
        }
        if (!is_null($external_ref) && (mb_strlen($external_ref) < 1)) {
            throw new \InvalidArgumentException('invalid length for $external_ref when calling UpdateTrunkRequest., must be bigger than or equal to 1.');
        }
        if (!is_null($external_ref) && (!preg_match("/^[A-Za-z0-9_.\\-]+$/", ObjectSerializer::toString($external_ref)))) {
            throw new \InvalidArgumentException("invalid value for \$external_ref when calling UpdateTrunkRequest., must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
        }

        $this->container['external_ref'] = $external_ref;

        return $this;
    }

    /**
     * Gets metadata
     *
     * @return \CheckNetworks\Api\Generated\Model\MetadataInput|null
     */
    public function getMetadata()
    {
        return $this->container['metadata'];
    }

    /**
     * Sets metadata
     *
     * @param \CheckNetworks\Api\Generated\Model\MetadataInput|null $metadata metadata
     *
     * @return self
     */
    public function setMetadata($metadata)
    {
        if (is_null($metadata)) {
            array_push($this->openAPINullablesSetToNull, 'metadata');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('metadata', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['metadata'] = $metadata;

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
     * @param string|null $description Your free-text notes on the trunk, up to 500 characters. Null clears them.
     *
     * @return self
     */
    public function setDescription($description)
    {
        if (is_null($description)) {
            array_push($this->openAPINullablesSetToNull, 'description');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('description', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($description) && (mb_strlen($description) > 500)) {
            throw new \InvalidArgumentException('invalid length for $description when calling UpdateTrunkRequest., must be smaller than or equal to 500.');
        }

        $this->container['description'] = $description;

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
     * @param string|null $type The trunk's billing plan. Omit to leave it unchanged. Moving onto `ultimate` requires `channels`, since each channel is a recurring charge and one will never be assumed for you.
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
     * Gets channels
     *
     * @return int|null
     */
    public function getChannels()
    {
        return $this->container['channels'];
    }

    /**
     * Sets channels
     *
     * @param int|null $channels Maximum concurrent calls on this trunk. On `ultimate` this is the number of billed channels, and changing it is an upgrade or a downgrade (see above); omit it to leave the count alone. On `payg` it is a concurrency ceiling only, with no billing effect. An `ultimate` trunk needs at least 1.
     *
     * @return self
     */
    public function setChannels($channels)
    {
        if (is_null($channels)) {
            throw new \InvalidArgumentException('non-nullable channels cannot be null');
        }
        if (($channels > 100)) {
            throw new \InvalidArgumentException('invalid value for $channels when calling UpdateTrunkRequest., must be smaller than or equal to 100.');
        }
        if (($channels < 0)) {
            throw new \InvalidArgumentException('invalid value for $channels when calling UpdateTrunkRequest., must be bigger than or equal to 0.');
        }

        $this->container['channels'] = $channels;

        return $this;
    }

    /**
     * Gets max_international
     *
     * @return int|null
     */
    public function getMaxInternational()
    {
        return $this->container['max_international'];
    }

    /**
     * Sets max_international
     *
     * @param int|null $max_international Maximum concurrent international calls; null removes the trunk's own limit. Only for a trunk that may call internationally: a limit sent while `International` is not among the calling permissions (after this request's change) is refused with a 422. Removing `International` clears the limit.
     *
     * @return self
     */
    public function setMaxInternational($max_international)
    {
        if (is_null($max_international)) {
            array_push($this->openAPINullablesSetToNull, 'max_international');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('max_international', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($max_international) && ($max_international > 9999)) {
            throw new \InvalidArgumentException('invalid value for $max_international when calling UpdateTrunkRequest., must be smaller than or equal to 9999.');
        }
        if (!is_null($max_international) && ($max_international < 0)) {
            throw new \InvalidArgumentException('invalid value for $max_international when calling UpdateTrunkRequest., must be bigger than or equal to 0.');
        }

        $this->container['max_international'] = $max_international;

        return $this;
    }

    /**
     * Gets calling_permissions
     *
     * @return \CheckNetworks\Api\Generated\Model\CallingPermission[]|null
     */
    public function getCallingPermissions()
    {
        return $this->container['calling_permissions'];
    }

    /**
     * Sets calling_permissions
     *
     * @param \CheckNetworks\Api\Generated\Model\CallingPermission[]|null $calling_permissions The trunk's call barring: the calling-permission classes it may dial, replacing the current list. Remove `International` to cut international calling in one call. 000 and freecall are never barred: `AUEmergency` and `AUFreecall` stay on whether sent or not, and the answer's `callingPermissionsKept` names any this list left out.
     *
     * @return self
     */
    public function setCallingPermissions($calling_permissions)
    {
        if (is_null($calling_permissions)) {
            throw new \InvalidArgumentException('non-nullable calling_permissions cannot be null');
        }
        $this->container['calling_permissions'] = $calling_permissions;

        return $this;
    }

    /**
     * Gets default_state
     *
     * @return string|null
     */
    public function getDefaultState()
    {
        return $this->container['default_state'];
    }

    /**
     * Sets default_state
     *
     * @param string|null $default_state The trunk's default state; null clears it.
     *
     * @return self
     */
    public function setDefaultState($default_state)
    {
        if (is_null($default_state)) {
            array_push($this->openAPINullablesSetToNull, 'default_state');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('default_state', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getDefaultStateAllowableValues();
        if (!is_null($default_state) && !in_array($default_state, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'default_state', must be one of '%s'",
                    $default_state,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['default_state'] = $default_state;

        return $this;
    }

    /**
     * Gets default_caller_id
     *
     * @return string|null
     */
    public function getDefaultCallerId()
    {
        return $this->container['default_caller_id'];
    }

    /**
     * Sets default_caller_id
     *
     * @param string|null $default_caller_id Default outbound caller ID: a number on your account, or one approved through `POST /caller-id-authorisations`. Digits, which may include `+` and spaces. Any other number is a 422 with the code `caller_id_authorisation_required`. The change is in the trunk's history with its before and after values.
     *
     * @return self
     */
    public function setDefaultCallerId($default_caller_id)
    {
        if (is_null($default_caller_id)) {
            throw new \InvalidArgumentException('non-nullable default_caller_id cannot be null');
        }
        if ((mb_strlen($default_caller_id) > 24)) {
            throw new \InvalidArgumentException('invalid length for $default_caller_id when calling UpdateTrunkRequest., must be smaller than or equal to 24.');
        }
        if ((!preg_match("/^[0-9+\\s]*[0-9][0-9+\\s]*$/", ObjectSerializer::toString($default_caller_id)))) {
            throw new \InvalidArgumentException("invalid value for \$default_caller_id when calling UpdateTrunkRequest., must conform to the pattern /^[0-9+\\s]*[0-9][0-9+\\s]*$/.");
        }

        $this->container['default_caller_id'] = $default_caller_id;

        return $this;
    }

    /**
     * Gets force_outbound_cli
     *
     * @return bool|null
     */
    public function getForceOutboundCli()
    {
        return $this->container['force_outbound_cli'];
    }

    /**
     * Sets force_outbound_cli
     *
     * @param bool|null $force_outbound_cli Present `defaultCallerId` on every outbound call, whatever caller ID the PBX sends. The caller ID must pass the same checks as `defaultCallerId`.
     *
     * @return self
     */
    public function setForceOutboundCli($force_outbound_cli)
    {
        if (is_null($force_outbound_cli)) {
            throw new \InvalidArgumentException('non-nullable force_outbound_cli cannot be null');
        }
        $this->container['force_outbound_cli'] = $force_outbound_cli;

        return $this;
    }

    /**
     * Gets force_private_cli
     *
     * @return bool|null
     */
    public function getForcePrivateCli()
    {
        return $this->container['force_private_cli'];
    }

    /**
     * Sets force_private_cli
     *
     * @param bool|null $force_private_cli Force all outbound calls to present as Private/Anonymous.
     *
     * @return self
     */
    public function setForcePrivateCli($force_private_cli)
    {
        if (is_null($force_private_cli)) {
            throw new \InvalidArgumentException('non-nullable force_private_cli cannot be null');
        }
        $this->container['force_private_cli'] = $force_private_cli;

        return $this;
    }

    /**
     * Gets recording_enabled
     *
     * @return bool|null
     */
    public function getRecordingEnabled()
    {
        return $this->container['recording_enabled'];
    }

    /**
     * Sets recording_enabled
     *
     * @param bool|null $recording_enabled `true` orders call recording, a monthly charge per trunk; `false` cancels it, and recordings already made are kept. Every call is recorded, with no announcement: you are responsible for telling your callers that calls may be recorded. Turning it on needs `acknowledgeRecording: true`, and an account that may place orders.
     *
     * @return self
     */
    public function setRecordingEnabled($recording_enabled)
    {
        if (is_null($recording_enabled)) {
            throw new \InvalidArgumentException('non-nullable recording_enabled cannot be null');
        }
        $this->container['recording_enabled'] = $recording_enabled;

        return $this;
    }

    /**
     * Gets acknowledge_recording
     *
     * @return bool|null
     */
    public function getAcknowledgeRecording()
    {
        return $this->container['acknowledge_recording'];
    }

    /**
     * Sets acknowledge_recording
     *
     * @param bool|null $acknowledge_recording With `recordingEnabled: true` on a trunk without recording, confirms that you accept the monthly recording charge and are responsible for telling your callers. Without it the request answers `422` `charge_acknowledgement_required`, with `error.consent` and `error.charge`. A credit hold answers `422` and nothing is ordered. Not needed to turn recording off.
     *
     * @return self
     */
    public function setAcknowledgeRecording($acknowledge_recording)
    {
        if (is_null($acknowledge_recording)) {
            throw new \InvalidArgumentException('non-nullable acknowledge_recording cannot be null');
        }
        $this->container['acknowledge_recording'] = $acknowledge_recording;

        return $this;
    }

    /**
     * Gets allowed_countries
     *
     * @return string[]|null
     */
    public function getAllowedCountries()
    {
        return $this->container['allowed_countries'];
    }

    /**
     * Sets allowed_countries
     *
     * @param string[]|null $allowed_countries This trunk's own list of countries it may call, as ISO 3166 alpha-2 codes (the United Kingdom is `GB`). `null` drops the override, so the trunk follows your account's list again (Get Allowed Countries). It applies when `International` is in `callingPermissions`; calls to Australia and New Zealand follow their own permissions, high-risk destinations stay blocked, and 000 always connects.
     *
     * @return self
     */
    public function setAllowedCountries($allowed_countries)
    {
        if (is_null($allowed_countries)) {
            array_push($this->openAPINullablesSetToNull, 'allowed_countries');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('allowed_countries', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }

        if (!is_null($allowed_countries) && (count($allowed_countries) < 1)) {
            throw new \InvalidArgumentException('invalid length for $allowed_countries when calling UpdateTrunkRequest., number of items must be greater than or equal to 1.');
        }
        $this->container['allowed_countries'] = $allowed_countries;

        return $this;
    }

    /**
     * Gets daily_spend_cap_ex_gst
     *
     * @return string|null
     */
    public function getDailySpendCapExGst()
    {
        return $this->container['daily_spend_cap_ex_gst'];
    }

    /**
     * Sets daily_spend_cap_ex_gst
     *
     * @param string|null $daily_spend_cap_ex_gst The most this trunk's outbound calls may cost in a day, ex GST, as a dollar string. At the cap its new outbound calls stop until midnight Brisbane time; calls already up carry on. Spend counts a call when it ends. A cap at or below `spendTodayExGst`, `\"0.00\"` included, holds the trunk at once, and the answer reads `spendCapHeld: true`; raising it above `spendTodayExGst` resumes calls at once. `null` removes the cap. 000 always connects.
     *
     * @return self
     */
    public function setDailySpendCapExGst($daily_spend_cap_ex_gst)
    {
        if (is_null($daily_spend_cap_ex_gst)) {
            array_push($this->openAPINullablesSetToNull, 'daily_spend_cap_ex_gst');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('daily_spend_cap_ex_gst', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }

        if (!is_null($daily_spend_cap_ex_gst) && (!preg_match("/^\\d{1,7}(\\.\\d{1,2})?$/", ObjectSerializer::toString($daily_spend_cap_ex_gst)))) {
            throw new \InvalidArgumentException("invalid value for \$daily_spend_cap_ex_gst when calling UpdateTrunkRequest., must conform to the pattern /^\\d{1,7}(\\.\\d{1,2})?$/.");
        }

        $this->container['daily_spend_cap_ex_gst'] = $daily_spend_cap_ex_gst;

        return $this;
    }

    /**
     * Gets called_number_format
     *
     * @return \CheckNetworks\Api\Generated\Model\TrunkNumberFormat|null
     */
    public function getCalledNumberFormat()
    {
        return $this->container['called_number_format'];
    }

    /**
     * Sets called_number_format
     *
     * @param \CheckNetworks\Api\Generated\Model\TrunkNumberFormat|null $called_number_format How the dialled number arrives on calls to this trunk.
     *
     * @return self
     */
    public function setCalledNumberFormat($called_number_format)
    {
        if (is_null($called_number_format)) {
            throw new \InvalidArgumentException('non-nullable called_number_format cannot be null');
        }
        $this->container['called_number_format'] = $called_number_format;

        return $this;
    }

    /**
     * Gets caller_number_format
     *
     * @return \CheckNetworks\Api\Generated\Model\TrunkNumberFormat|null
     */
    public function getCallerNumberFormat()
    {
        return $this->container['caller_number_format'];
    }

    /**
     * Sets caller_number_format
     *
     * @param \CheckNetworks\Api\Generated\Model\TrunkNumberFormat|null $caller_number_format How an Australian caller's number arrives on calls to this trunk. A number's own inbound format still overrides it.
     *
     * @return self
     */
    public function setCallerNumberFormat($caller_number_format)
    {
        if (is_null($caller_number_format)) {
            throw new \InvalidArgumentException('non-nullable caller_number_format cannot be null');
        }
        $this->container['caller_number_format'] = $caller_number_format;

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


