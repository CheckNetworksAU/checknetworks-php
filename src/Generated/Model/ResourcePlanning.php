<?php
/**
 * ResourcePlanning
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
 * ResourcePlanning Class Doc Comment
 *
 * @category Class
 * @description Change of Access Technology (COAT) status for an nbn™ location — the current access technology, the planned technology flip, and related planning data.
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class ResourcePlanning implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'ResourcePlanning';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'change_date' => 'string',
        'changeover_end_date' => 'string',
        'current_access_technology' => 'string',
        'last_updated' => '\DateTime',
        'location_connection_details' => '\CheckNetworks\Api\Generated\Model\ResourcePlanningLocationConnectionDetails',
        'nbn_identifiers' => '\CheckNetworks\Api\Generated\Model\ResourcePlanningNbnIdentifiersInner[]',
        'related_place' => '\CheckNetworks\Api\Generated\Model\ResourcePlanningRelatedPlace',
        'service_class' => 'string',
        'service_class_description' => 'string',
        'service_level_region' => 'string',
        'service_type' => 'string',
        'speed_equipment_upgrade' => '\CheckNetworks\Api\Generated\Model\ResourcePlanningSpeedEquipmentUpgrade',
        'status' => 'string',
        'supporting_related_location_features' => '\CheckNetworks\Api\Generated\Model\ResourcePlanningSupportingRelatedLocationFeatures',
        'supporting_resource' => '\CheckNetworks\Api\Generated\Model\ResourcePlanningSupportingResource',
        'technology_flip_descriptor' => 'string',
        'coat_name' => 'string',
        'ee_features' => '\CheckNetworks\Api\Generated\Model\ResourcePlanningEEFeatures',
        'tc2_calr' => 'string',
        'tc4_calr' => 'string'
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
        'change_date' => null,
        'changeover_end_date' => null,
        'current_access_technology' => null,
        'last_updated' => 'date-time',
        'location_connection_details' => null,
        'nbn_identifiers' => null,
        'related_place' => null,
        'service_class' => null,
        'service_class_description' => null,
        'service_level_region' => null,
        'service_type' => null,
        'speed_equipment_upgrade' => null,
        'status' => null,
        'supporting_related_location_features' => null,
        'supporting_resource' => null,
        'technology_flip_descriptor' => null,
        'coat_name' => null,
        'ee_features' => null,
        'tc2_calr' => null,
        'tc4_calr' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'change_date' => false,
        'changeover_end_date' => false,
        'current_access_technology' => false,
        'last_updated' => false,
        'location_connection_details' => false,
        'nbn_identifiers' => false,
        'related_place' => false,
        'service_class' => false,
        'service_class_description' => false,
        'service_level_region' => false,
        'service_type' => false,
        'speed_equipment_upgrade' => false,
        'status' => false,
        'supporting_related_location_features' => false,
        'supporting_resource' => false,
        'technology_flip_descriptor' => false,
        'coat_name' => false,
        'ee_features' => false,
        'tc2_calr' => false,
        'tc4_calr' => false
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
        'change_date' => 'changeDate',
        'changeover_end_date' => 'changeoverEndDate',
        'current_access_technology' => 'currentAccessTechnology',
        'last_updated' => 'lastUpdated',
        'location_connection_details' => 'locationConnectionDetails',
        'nbn_identifiers' => 'nbnIdentifiers',
        'related_place' => 'relatedPlace',
        'service_class' => 'serviceClass',
        'service_class_description' => 'serviceClassDescription',
        'service_level_region' => 'serviceLevelRegion',
        'service_type' => 'serviceType',
        'speed_equipment_upgrade' => 'speedEquipmentUpgrade',
        'status' => 'status',
        'supporting_related_location_features' => 'supportingRelatedLocationFeatures',
        'supporting_resource' => 'supportingResource',
        'technology_flip_descriptor' => 'technologyFlipDescriptor',
        'coat_name' => 'COATName',
        'ee_features' => 'EEFeatures',
        'tc2_calr' => 'TC2CALR',
        'tc4_calr' => 'TC4CALR'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'change_date' => 'setChangeDate',
        'changeover_end_date' => 'setChangeoverEndDate',
        'current_access_technology' => 'setCurrentAccessTechnology',
        'last_updated' => 'setLastUpdated',
        'location_connection_details' => 'setLocationConnectionDetails',
        'nbn_identifiers' => 'setNbnIdentifiers',
        'related_place' => 'setRelatedPlace',
        'service_class' => 'setServiceClass',
        'service_class_description' => 'setServiceClassDescription',
        'service_level_region' => 'setServiceLevelRegion',
        'service_type' => 'setServiceType',
        'speed_equipment_upgrade' => 'setSpeedEquipmentUpgrade',
        'status' => 'setStatus',
        'supporting_related_location_features' => 'setSupportingRelatedLocationFeatures',
        'supporting_resource' => 'setSupportingResource',
        'technology_flip_descriptor' => 'setTechnologyFlipDescriptor',
        'coat_name' => 'setCoatName',
        'ee_features' => 'setEeFeatures',
        'tc2_calr' => 'setTc2Calr',
        'tc4_calr' => 'setTc4Calr'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'change_date' => 'getChangeDate',
        'changeover_end_date' => 'getChangeoverEndDate',
        'current_access_technology' => 'getCurrentAccessTechnology',
        'last_updated' => 'getLastUpdated',
        'location_connection_details' => 'getLocationConnectionDetails',
        'nbn_identifiers' => 'getNbnIdentifiers',
        'related_place' => 'getRelatedPlace',
        'service_class' => 'getServiceClass',
        'service_class_description' => 'getServiceClassDescription',
        'service_level_region' => 'getServiceLevelRegion',
        'service_type' => 'getServiceType',
        'speed_equipment_upgrade' => 'getSpeedEquipmentUpgrade',
        'status' => 'getStatus',
        'supporting_related_location_features' => 'getSupportingRelatedLocationFeatures',
        'supporting_resource' => 'getSupportingResource',
        'technology_flip_descriptor' => 'getTechnologyFlipDescriptor',
        'coat_name' => 'getCoatName',
        'ee_features' => 'getEeFeatures',
        'tc2_calr' => 'getTc2Calr',
        'tc4_calr' => 'getTc4Calr'
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
        $this->setIfExists('change_date', $data ?? [], null);
        $this->setIfExists('changeover_end_date', $data ?? [], null);
        $this->setIfExists('current_access_technology', $data ?? [], null);
        $this->setIfExists('last_updated', $data ?? [], null);
        $this->setIfExists('location_connection_details', $data ?? [], null);
        $this->setIfExists('nbn_identifiers', $data ?? [], null);
        $this->setIfExists('related_place', $data ?? [], null);
        $this->setIfExists('service_class', $data ?? [], null);
        $this->setIfExists('service_class_description', $data ?? [], null);
        $this->setIfExists('service_level_region', $data ?? [], null);
        $this->setIfExists('service_type', $data ?? [], null);
        $this->setIfExists('speed_equipment_upgrade', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('supporting_related_location_features', $data ?? [], null);
        $this->setIfExists('supporting_resource', $data ?? [], null);
        $this->setIfExists('technology_flip_descriptor', $data ?? [], null);
        $this->setIfExists('coat_name', $data ?? [], null);
        $this->setIfExists('ee_features', $data ?? [], null);
        $this->setIfExists('tc2_calr', $data ?? [], null);
        $this->setIfExists('tc4_calr', $data ?? [], null);
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
     * @param string|null $id nbn™ Location reference (LOC…) this report is for.
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
     * Gets change_date
     *
     * @return string|null
     */
    public function getChangeDate()
    {
        return $this->container['change_date'];
    }

    /**
     * Sets change_date
     *
     * @param string|null $change_date Date the access-technology change took (or will take) effect.
     *
     * @return self
     */
    public function setChangeDate($change_date)
    {
        if (is_null($change_date)) {
            throw new \InvalidArgumentException('non-nullable change_date cannot be null');
        }
        $this->container['change_date'] = $change_date;

        return $this;
    }

    /**
     * Gets changeover_end_date
     *
     * @return string|null
     */
    public function getChangeoverEndDate()
    {
        return $this->container['changeover_end_date'];
    }

    /**
     * Sets changeover_end_date
     *
     * @param string|null $changeover_end_date End date of the technology changeover window.
     *
     * @return self
     */
    public function setChangeoverEndDate($changeover_end_date)
    {
        if (is_null($changeover_end_date)) {
            throw new \InvalidArgumentException('non-nullable changeover_end_date cannot be null');
        }
        $this->container['changeover_end_date'] = $changeover_end_date;

        return $this;
    }

    /**
     * Gets current_access_technology
     *
     * @return string|null
     */
    public function getCurrentAccessTechnology()
    {
        return $this->container['current_access_technology'];
    }

    /**
     * Sets current_access_technology
     *
     * @param string|null $current_access_technology The access technology currently in place at the location (e.g. Fibre).
     *
     * @return self
     */
    public function setCurrentAccessTechnology($current_access_technology)
    {
        if (is_null($current_access_technology)) {
            throw new \InvalidArgumentException('non-nullable current_access_technology cannot be null');
        }
        $this->container['current_access_technology'] = $current_access_technology;

        return $this;
    }

    /**
     * Gets last_updated
     *
     * @return \DateTime|null
     */
    public function getLastUpdated()
    {
        return $this->container['last_updated'];
    }

    /**
     * Sets last_updated
     *
     * @param \DateTime|null $last_updated Time this planning record was last updated by nbn™.
     *
     * @return self
     */
    public function setLastUpdated($last_updated)
    {
        if (is_null($last_updated)) {
            throw new \InvalidArgumentException('non-nullable last_updated cannot be null');
        }
        $this->container['last_updated'] = $last_updated;

        return $this;
    }

    /**
     * Gets location_connection_details
     *
     * @return \CheckNetworks\Api\Generated\Model\ResourcePlanningLocationConnectionDetails|null
     */
    public function getLocationConnectionDetails()
    {
        return $this->container['location_connection_details'];
    }

    /**
     * Sets location_connection_details
     *
     * @param \CheckNetworks\Api\Generated\Model\ResourcePlanningLocationConnectionDetails|null $location_connection_details location_connection_details
     *
     * @return self
     */
    public function setLocationConnectionDetails($location_connection_details)
    {
        if (is_null($location_connection_details)) {
            throw new \InvalidArgumentException('non-nullable location_connection_details cannot be null');
        }
        $this->container['location_connection_details'] = $location_connection_details;

        return $this;
    }

    /**
     * Gets nbn_identifiers
     *
     * @return \CheckNetworks\Api\Generated\Model\ResourcePlanningNbnIdentifiersInner[]|null
     */
    public function getNbnIdentifiers()
    {
        return $this->container['nbn_identifiers'];
    }

    /**
     * Sets nbn_identifiers
     *
     * @param \CheckNetworks\Api\Generated\Model\ResourcePlanningNbnIdentifiersInner[]|null $nbn_identifiers nbn™ network identifiers associated with the location (CSA, POI, GNAF, etc.).
     *
     * @return self
     */
    public function setNbnIdentifiers($nbn_identifiers)
    {
        if (is_null($nbn_identifiers)) {
            throw new \InvalidArgumentException('non-nullable nbn_identifiers cannot be null');
        }
        $this->container['nbn_identifiers'] = $nbn_identifiers;

        return $this;
    }

    /**
     * Gets related_place
     *
     * @return \CheckNetworks\Api\Generated\Model\ResourcePlanningRelatedPlace|null
     */
    public function getRelatedPlace()
    {
        return $this->container['related_place'];
    }

    /**
     * Sets related_place
     *
     * @param \CheckNetworks\Api\Generated\Model\ResourcePlanningRelatedPlace|null $related_place related_place
     *
     * @return self
     */
    public function setRelatedPlace($related_place)
    {
        if (is_null($related_place)) {
            throw new \InvalidArgumentException('non-nullable related_place cannot be null');
        }
        $this->container['related_place'] = $related_place;

        return $this;
    }

    /**
     * Gets service_class
     *
     * @return string|null
     */
    public function getServiceClass()
    {
        return $this->container['service_class'];
    }

    /**
     * Sets service_class
     *
     * @param string|null $service_class nbn™ service class for the location.
     *
     * @return self
     */
    public function setServiceClass($service_class)
    {
        if (is_null($service_class)) {
            throw new \InvalidArgumentException('non-nullable service_class cannot be null');
        }
        $this->container['service_class'] = $service_class;

        return $this;
    }

    /**
     * Gets service_class_description
     *
     * @return string|null
     */
    public function getServiceClassDescription()
    {
        return $this->container['service_class_description'];
    }

    /**
     * Sets service_class_description
     *
     * @param string|null $service_class_description Human-readable description of the service class.
     *
     * @return self
     */
    public function setServiceClassDescription($service_class_description)
    {
        if (is_null($service_class_description)) {
            throw new \InvalidArgumentException('non-nullable service_class_description cannot be null');
        }
        $this->container['service_class_description'] = $service_class_description;

        return $this;
    }

    /**
     * Gets service_level_region
     *
     * @return string|null
     */
    public function getServiceLevelRegion()
    {
        return $this->container['service_level_region'];
    }

    /**
     * Sets service_level_region
     *
     * @param string|null $service_level_region Service-level region (e.g. MAJOR RURAL).
     *
     * @return self
     */
    public function setServiceLevelRegion($service_level_region)
    {
        if (is_null($service_level_region)) {
            throw new \InvalidArgumentException('non-nullable service_level_region cannot be null');
        }
        $this->container['service_level_region'] = $service_level_region;

        return $this;
    }

    /**
     * Gets service_type
     *
     * @return string|null
     */
    public function getServiceType()
    {
        return $this->container['service_type'];
    }

    /**
     * Sets service_type
     *
     * @param string|null $service_type Service type (e.g. Brownfields FIBRE).
     *
     * @return self
     */
    public function setServiceType($service_type)
    {
        if (is_null($service_type)) {
            throw new \InvalidArgumentException('non-nullable service_type cannot be null');
        }
        $this->container['service_type'] = $service_type;

        return $this;
    }

    /**
     * Gets speed_equipment_upgrade
     *
     * @return \CheckNetworks\Api\Generated\Model\ResourcePlanningSpeedEquipmentUpgrade|null
     */
    public function getSpeedEquipmentUpgrade()
    {
        return $this->container['speed_equipment_upgrade'];
    }

    /**
     * Sets speed_equipment_upgrade
     *
     * @param \CheckNetworks\Api\Generated\Model\ResourcePlanningSpeedEquipmentUpgrade|null $speed_equipment_upgrade speed_equipment_upgrade
     *
     * @return self
     */
    public function setSpeedEquipmentUpgrade($speed_equipment_upgrade)
    {
        if (is_null($speed_equipment_upgrade)) {
            throw new \InvalidArgumentException('non-nullable speed_equipment_upgrade cannot be null');
        }
        $this->container['speed_equipment_upgrade'] = $speed_equipment_upgrade;

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
     * @param string|null $status Overall COAT status (e.g. TechnologyChangeCompleted).
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (is_null($status)) {
            throw new \InvalidArgumentException('non-nullable status cannot be null');
        }
        $this->container['status'] = $status;

        return $this;
    }

    /**
     * Gets supporting_related_location_features
     *
     * @return \CheckNetworks\Api\Generated\Model\ResourcePlanningSupportingRelatedLocationFeatures|null
     */
    public function getSupportingRelatedLocationFeatures()
    {
        return $this->container['supporting_related_location_features'];
    }

    /**
     * Sets supporting_related_location_features
     *
     * @param \CheckNetworks\Api\Generated\Model\ResourcePlanningSupportingRelatedLocationFeatures|null $supporting_related_location_features supporting_related_location_features
     *
     * @return self
     */
    public function setSupportingRelatedLocationFeatures($supporting_related_location_features)
    {
        if (is_null($supporting_related_location_features)) {
            throw new \InvalidArgumentException('non-nullable supporting_related_location_features cannot be null');
        }
        $this->container['supporting_related_location_features'] = $supporting_related_location_features;

        return $this;
    }

    /**
     * Gets supporting_resource
     *
     * @return \CheckNetworks\Api\Generated\Model\ResourcePlanningSupportingResource|null
     */
    public function getSupportingResource()
    {
        return $this->container['supporting_resource'];
    }

    /**
     * Sets supporting_resource
     *
     * @param \CheckNetworks\Api\Generated\Model\ResourcePlanningSupportingResource|null $supporting_resource supporting_resource
     *
     * @return self
     */
    public function setSupportingResource($supporting_resource)
    {
        if (is_null($supporting_resource)) {
            throw new \InvalidArgumentException('non-nullable supporting_resource cannot be null');
        }
        $this->container['supporting_resource'] = $supporting_resource;

        return $this;
    }

    /**
     * Gets technology_flip_descriptor
     *
     * @return string|null
     */
    public function getTechnologyFlipDescriptor()
    {
        return $this->container['technology_flip_descriptor'];
    }

    /**
     * Sets technology_flip_descriptor
     *
     * @param string|null $technology_flip_descriptor Descriptor of the technology-flip activity driving the change.
     *
     * @return self
     */
    public function setTechnologyFlipDescriptor($technology_flip_descriptor)
    {
        if (is_null($technology_flip_descriptor)) {
            throw new \InvalidArgumentException('non-nullable technology_flip_descriptor cannot be null');
        }
        $this->container['technology_flip_descriptor'] = $technology_flip_descriptor;

        return $this;
    }

    /**
     * Gets coat_name
     *
     * @return string|null
     */
    public function getCoatName()
    {
        return $this->container['coat_name'];
    }

    /**
     * Sets coat_name
     *
     * @param string|null $coat_name COAT programme name (e.g. nbn-initiated).
     *
     * @return self
     */
    public function setCoatName($coat_name)
    {
        if (is_null($coat_name)) {
            throw new \InvalidArgumentException('non-nullable coat_name cannot be null');
        }
        $this->container['coat_name'] = $coat_name;

        return $this;
    }

    /**
     * Gets ee_features
     *
     * @return \CheckNetworks\Api\Generated\Model\ResourcePlanningEEFeatures|null
     */
    public function getEeFeatures()
    {
        return $this->container['ee_features'];
    }

    /**
     * Sets ee_features
     *
     * @param \CheckNetworks\Api\Generated\Model\ResourcePlanningEEFeatures|null $ee_features ee_features
     *
     * @return self
     */
    public function setEeFeatures($ee_features)
    {
        if (is_null($ee_features)) {
            throw new \InvalidArgumentException('non-nullable ee_features cannot be null');
        }
        $this->container['ee_features'] = $ee_features;

        return $this;
    }

    /**
     * Gets tc2_calr
     *
     * @return string|null
     */
    public function getTc2Calr()
    {
        return $this->container['tc2_calr'];
    }

    /**
     * Sets tc2_calr
     *
     * @param string|null $tc2_calr TC2 Committed Access Line Rate (e.g. 20/20).
     *
     * @return self
     */
    public function setTc2Calr($tc2_calr)
    {
        if (is_null($tc2_calr)) {
            throw new \InvalidArgumentException('non-nullable tc2_calr cannot be null');
        }
        $this->container['tc2_calr'] = $tc2_calr;

        return $this;
    }

    /**
     * Gets tc4_calr
     *
     * @return string|null
     */
    public function getTc4Calr()
    {
        return $this->container['tc4_calr'];
    }

    /**
     * Sets tc4_calr
     *
     * @param string|null $tc4_calr TC4 Committed Access Line Rate (e.g. 50/20).
     *
     * @return self
     */
    public function setTc4Calr($tc4_calr)
    {
        if (is_null($tc4_calr)) {
            throw new \InvalidArgumentException('non-nullable tc4_calr cannot be null');
        }
        $this->container['tc4_calr'] = $tc4_calr;

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


