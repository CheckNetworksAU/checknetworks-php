<?php
/**
 * GetDataServiceIncidents200ResponseResultOutagesInner
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
 * GetDataServiceIncidents200ResponseResultOutagesInner Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class GetDataServiceIncidents200ResponseResultOutagesInner implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'get_data_service_incidents_200_response_result_outages_inner';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'reference' => 'string',
        'title' => 'string',
        'type' => 'string',
        'severity' => 'string',
        'status' => 'string',
        'started_at' => '\DateTime',
        'resolved_at' => '\DateTime',
        'estimated_restoration_at' => '\DateTime',
        'timezone' => 'string',
        'affected_suburbs' => 'string[]',
        'affected_states' => 'string[]',
        'affected_postcodes' => 'string[]',
        'affected_service_types' => 'string[]',
        'affected_sites' => 'string[]',
        'premises_affected' => 'int',
        'responsible_party' => 'string',
        'cause' => 'string',
        'cause_natural' => 'bool',
        'additional_info' => 'string',
        'first_published_at' => '\DateTime',
        'last_rectified_at' => '\DateTime',
        'your_affected_service_count' => 'int',
        'your_affected_services' => '\CheckNetworks\Api\Generated\Model\NetworkOutageYourAffectedServicesInner[]',
        'service_counts' => 'array<string,int>'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'reference' => null,
        'title' => null,
        'type' => null,
        'severity' => null,
        'status' => null,
        'started_at' => 'date-time',
        'resolved_at' => 'date-time',
        'estimated_restoration_at' => 'date-time',
        'timezone' => null,
        'affected_suburbs' => null,
        'affected_states' => null,
        'affected_postcodes' => null,
        'affected_service_types' => null,
        'affected_sites' => null,
        'premises_affected' => null,
        'responsible_party' => null,
        'cause' => null,
        'cause_natural' => null,
        'additional_info' => null,
        'first_published_at' => 'date-time',
        'last_rectified_at' => 'date-time',
        'your_affected_service_count' => null,
        'your_affected_services' => null,
        'service_counts' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'reference' => false,
        'title' => false,
        'type' => false,
        'severity' => true,
        'status' => false,
        'started_at' => false,
        'resolved_at' => true,
        'estimated_restoration_at' => true,
        'timezone' => false,
        'affected_suburbs' => false,
        'affected_states' => false,
        'affected_postcodes' => false,
        'affected_service_types' => false,
        'affected_sites' => false,
        'premises_affected' => true,
        'responsible_party' => true,
        'cause' => true,
        'cause_natural' => false,
        'additional_info' => true,
        'first_published_at' => true,
        'last_rectified_at' => true,
        'your_affected_service_count' => true,
        'your_affected_services' => true,
        'service_counts' => false
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
        'reference' => 'reference',
        'title' => 'title',
        'type' => 'type',
        'severity' => 'severity',
        'status' => 'status',
        'started_at' => 'startedAt',
        'resolved_at' => 'resolvedAt',
        'estimated_restoration_at' => 'estimatedRestorationAt',
        'timezone' => 'timezone',
        'affected_suburbs' => 'affectedSuburbs',
        'affected_states' => 'affectedStates',
        'affected_postcodes' => 'affectedPostcodes',
        'affected_service_types' => 'affectedServiceTypes',
        'affected_sites' => 'affectedSites',
        'premises_affected' => 'premisesAffected',
        'responsible_party' => 'responsibleParty',
        'cause' => 'cause',
        'cause_natural' => 'causeNatural',
        'additional_info' => 'additionalInfo',
        'first_published_at' => 'firstPublishedAt',
        'last_rectified_at' => 'lastRectifiedAt',
        'your_affected_service_count' => 'yourAffectedServiceCount',
        'your_affected_services' => 'yourAffectedServices',
        'service_counts' => 'serviceCounts'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'reference' => 'setReference',
        'title' => 'setTitle',
        'type' => 'setType',
        'severity' => 'setSeverity',
        'status' => 'setStatus',
        'started_at' => 'setStartedAt',
        'resolved_at' => 'setResolvedAt',
        'estimated_restoration_at' => 'setEstimatedRestorationAt',
        'timezone' => 'setTimezone',
        'affected_suburbs' => 'setAffectedSuburbs',
        'affected_states' => 'setAffectedStates',
        'affected_postcodes' => 'setAffectedPostcodes',
        'affected_service_types' => 'setAffectedServiceTypes',
        'affected_sites' => 'setAffectedSites',
        'premises_affected' => 'setPremisesAffected',
        'responsible_party' => 'setResponsibleParty',
        'cause' => 'setCause',
        'cause_natural' => 'setCauseNatural',
        'additional_info' => 'setAdditionalInfo',
        'first_published_at' => 'setFirstPublishedAt',
        'last_rectified_at' => 'setLastRectifiedAt',
        'your_affected_service_count' => 'setYourAffectedServiceCount',
        'your_affected_services' => 'setYourAffectedServices',
        'service_counts' => 'setServiceCounts'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'reference' => 'getReference',
        'title' => 'getTitle',
        'type' => 'getType',
        'severity' => 'getSeverity',
        'status' => 'getStatus',
        'started_at' => 'getStartedAt',
        'resolved_at' => 'getResolvedAt',
        'estimated_restoration_at' => 'getEstimatedRestorationAt',
        'timezone' => 'getTimezone',
        'affected_suburbs' => 'getAffectedSuburbs',
        'affected_states' => 'getAffectedStates',
        'affected_postcodes' => 'getAffectedPostcodes',
        'affected_service_types' => 'getAffectedServiceTypes',
        'affected_sites' => 'getAffectedSites',
        'premises_affected' => 'getPremisesAffected',
        'responsible_party' => 'getResponsibleParty',
        'cause' => 'getCause',
        'cause_natural' => 'getCauseNatural',
        'additional_info' => 'getAdditionalInfo',
        'first_published_at' => 'getFirstPublishedAt',
        'last_rectified_at' => 'getLastRectifiedAt',
        'your_affected_service_count' => 'getYourAffectedServiceCount',
        'your_affected_services' => 'getYourAffectedServices',
        'service_counts' => 'getServiceCounts'
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

    public const TYPE_PLANNED = 'planned';
    public const TYPE_UNPLANNED = 'unplanned';
    public const SEVERITY_MAJOR = 'major';
    public const SEVERITY_SIGNIFICANT_LOCAL = 'significant_local';
    public const STATUS_OPEN = 'open';
    public const STATUS_RESOLVED = 'resolved';
    public const AFFECTED_STATES_ACT = 'ACT';
    public const AFFECTED_STATES_NSW = 'NSW';
    public const AFFECTED_STATES_NT = 'NT';
    public const AFFECTED_STATES_QLD = 'QLD';
    public const AFFECTED_STATES_SA = 'SA';
    public const AFFECTED_STATES_TAS = 'TAS';
    public const AFFECTED_STATES_VIC = 'VIC';
    public const AFFECTED_STATES_WA = 'WA';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getTypeAllowableValues()
    {
        return [
            self::TYPE_PLANNED,
            self::TYPE_UNPLANNED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getSeverityAllowableValues()
    {
        return [
            self::SEVERITY_MAJOR,
            self::SEVERITY_SIGNIFICANT_LOCAL,
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
            self::STATUS_OPEN,
            self::STATUS_RESOLVED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAffectedStatesAllowableValues()
    {
        return [
            self::AFFECTED_STATES_ACT,
            self::AFFECTED_STATES_NSW,
            self::AFFECTED_STATES_NT,
            self::AFFECTED_STATES_QLD,
            self::AFFECTED_STATES_SA,
            self::AFFECTED_STATES_TAS,
            self::AFFECTED_STATES_VIC,
            self::AFFECTED_STATES_WA,
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
        $this->setIfExists('reference', $data ?? [], null);
        $this->setIfExists('title', $data ?? [], null);
        $this->setIfExists('type', $data ?? [], null);
        $this->setIfExists('severity', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('started_at', $data ?? [], null);
        $this->setIfExists('resolved_at', $data ?? [], null);
        $this->setIfExists('estimated_restoration_at', $data ?? [], null);
        $this->setIfExists('timezone', $data ?? [], null);
        $this->setIfExists('affected_suburbs', $data ?? [], null);
        $this->setIfExists('affected_states', $data ?? [], null);
        $this->setIfExists('affected_postcodes', $data ?? [], null);
        $this->setIfExists('affected_service_types', $data ?? [], null);
        $this->setIfExists('affected_sites', $data ?? [], null);
        $this->setIfExists('premises_affected', $data ?? [], null);
        $this->setIfExists('responsible_party', $data ?? [], null);
        $this->setIfExists('cause', $data ?? [], null);
        $this->setIfExists('cause_natural', $data ?? [], null);
        $this->setIfExists('additional_info', $data ?? [], null);
        $this->setIfExists('first_published_at', $data ?? [], null);
        $this->setIfExists('last_rectified_at', $data ?? [], null);
        $this->setIfExists('your_affected_service_count', $data ?? [], null);
        $this->setIfExists('your_affected_services', $data ?? [], null);
        $this->setIfExists('service_counts', $data ?? [], null);
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

        $allowedValues = $this->getTypeAllowableValues();
        if (!is_null($this->container['type']) && !in_array($this->container['type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'type', must be one of '%s'",
                $this->container['type'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getSeverityAllowableValues();
        if (!is_null($this->container['severity']) && !in_array($this->container['severity'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'severity', must be one of '%s'",
                $this->container['severity'],
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

        if ($this->container['service_counts'] === null) {
            $invalidProperties[] = "'service_counts' can't be null";
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
     * Gets reference
     *
     * @return string|null
     */
    public function getReference()
    {
        return $this->container['reference'];
    }

    /**
     * Sets reference
     *
     * @param string|null $reference The Check Networks outage reference
     *
     * @return self
     */
    public function setReference($reference)
    {
        if (is_null($reference)) {
            throw new \InvalidArgumentException('non-nullable reference cannot be null');
        }
        $this->container['reference'] = $reference;

        return $this;
    }

    /**
     * Gets title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->container['title'];
    }

    /**
     * Sets title
     *
     * @param string|null $title Short plain-English description of the outage
     *
     * @return self
     */
    public function setTitle($title)
    {
        if (is_null($title)) {
            throw new \InvalidArgumentException('non-nullable title cannot be null');
        }
        $this->container['title'] = $title;

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
     * @param string|null $type Whether the outage is planned maintenance or an unplanned outage
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
     * Gets severity
     *
     * @return string|null
     */
    public function getSeverity()
    {
        return $this->container['severity'];
    }

    /**
     * Sets severity
     *
     * @param string|null $severity For an unplanned outage, its class under the Standard: `major` (at least 100,000 services or a whole State, for more than 60 minutes) or `significant_local` (at least 1,000 regional or 250 remote services, for more than 6 or 3 hours). Null for planned maintenance.
     *
     * @return self
     */
    public function setSeverity($severity)
    {
        if (is_null($severity)) {
            array_push($this->openAPINullablesSetToNull, 'severity');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('severity', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getSeverityAllowableValues();
        if (!is_null($severity) && !in_array($severity, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'severity', must be one of '%s'",
                    $severity,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['severity'] = $severity;

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
     * @param string|null $status Whether the outage is still in progress or has been resolved
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
     * Gets started_at
     *
     * @return \DateTime|null
     */
    public function getStartedAt()
    {
        return $this->container['started_at'];
    }

    /**
     * Sets started_at
     *
     * @param \DateTime|null $started_at When the outage began, in UTC like every time the API returns
     *
     * @return self
     */
    public function setStartedAt($started_at)
    {
        if (is_null($started_at)) {
            throw new \InvalidArgumentException('non-nullable started_at cannot be null');
        }
        $this->container['started_at'] = $started_at;

        return $this;
    }

    /**
     * Gets resolved_at
     *
     * @return \DateTime|null
     */
    public function getResolvedAt()
    {
        return $this->container['resolved_at'];
    }

    /**
     * Sets resolved_at
     *
     * @param \DateTime|null $resolved_at When service was restored. Null while the outage is open.
     *
     * @return self
     */
    public function setResolvedAt($resolved_at)
    {
        if (is_null($resolved_at)) {
            array_push($this->openAPINullablesSetToNull, 'resolved_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('resolved_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['resolved_at'] = $resolved_at;

        return $this;
    }

    /**
     * Gets estimated_restoration_at
     *
     * @return \DateTime|null
     */
    public function getEstimatedRestorationAt()
    {
        return $this->container['estimated_restoration_at'];
    }

    /**
     * Sets estimated_restoration_at
     *
     * @param \DateTime|null $estimated_restoration_at Current estimate for restoration. Null when no estimate is available yet — absence of an estimate is itself meaningful and is not a placeholder.
     *
     * @return self
     */
    public function setEstimatedRestorationAt($estimated_restoration_at)
    {
        if (is_null($estimated_restoration_at)) {
            array_push($this->openAPINullablesSetToNull, 'estimated_restoration_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('estimated_restoration_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['estimated_restoration_at'] = $estimated_restoration_at;

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
     * @param string|null $timezone IANA timezone of the affected area, for showing the UTC times in local time
     *
     * @return self
     */
    public function setTimezone($timezone)
    {
        if (is_null($timezone)) {
            throw new \InvalidArgumentException('non-nullable timezone cannot be null');
        }
        $this->container['timezone'] = $timezone;

        return $this;
    }

    /**
     * Gets affected_suburbs
     *
     * @return string[]|null
     */
    public function getAffectedSuburbs()
    {
        return $this->container['affected_suburbs'];
    }

    /**
     * Sets affected_suburbs
     *
     * @param string[]|null $affected_suburbs Affected suburbs/towns
     *
     * @return self
     */
    public function setAffectedSuburbs($affected_suburbs)
    {
        if (is_null($affected_suburbs)) {
            throw new \InvalidArgumentException('non-nullable affected_suburbs cannot be null');
        }
        $this->container['affected_suburbs'] = $affected_suburbs;

        return $this;
    }

    /**
     * Gets affected_states
     *
     * @return string[]|null
     */
    public function getAffectedStates()
    {
        return $this->container['affected_states'];
    }

    /**
     * Sets affected_states
     *
     * @param string[]|null $affected_states Affected States/Territories
     *
     * @return self
     */
    public function setAffectedStates($affected_states)
    {
        if (is_null($affected_states)) {
            throw new \InvalidArgumentException('non-nullable affected_states cannot be null');
        }
        $allowedValues = $this->getAffectedStatesAllowableValues();
        if (array_diff($affected_states, $allowedValues)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value for 'affected_states', must be one of '%s'",
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['affected_states'] = $affected_states;

        return $this;
    }

    /**
     * Gets affected_postcodes
     *
     * @return string[]|null
     */
    public function getAffectedPostcodes()
    {
        return $this->container['affected_postcodes'];
    }

    /**
     * Sets affected_postcodes
     *
     * @param string[]|null $affected_postcodes Affected postcodes
     *
     * @return self
     */
    public function setAffectedPostcodes($affected_postcodes)
    {
        if (is_null($affected_postcodes)) {
            throw new \InvalidArgumentException('non-nullable affected_postcodes cannot be null');
        }
        $this->container['affected_postcodes'] = $affected_postcodes;

        return $this;
    }

    /**
     * Gets affected_service_types
     *
     * @return string[]|null
     */
    public function getAffectedServiceTypes()
    {
        return $this->container['affected_service_types'];
    }

    /**
     * Sets affected_service_types
     *
     * @param string[]|null $affected_service_types Types of service affected
     *
     * @return self
     */
    public function setAffectedServiceTypes($affected_service_types)
    {
        if (is_null($affected_service_types)) {
            throw new \InvalidArgumentException('non-nullable affected_service_types cannot be null');
        }
        $this->container['affected_service_types'] = $affected_service_types;

        return $this;
    }

    /**
     * Gets affected_sites
     *
     * @return string[]|null
     */
    public function getAffectedSites()
    {
        return $this->container['affected_sites'];
    }

    /**
     * Sets affected_sites
     *
     * @param string[]|null $affected_sites Our own sites affected (a data centre or exchange), by name. Absent for planned maintenance.
     *
     * @return self
     */
    public function setAffectedSites($affected_sites)
    {
        if (is_null($affected_sites)) {
            throw new \InvalidArgumentException('non-nullable affected_sites cannot be null');
        }
        $this->container['affected_sites'] = $affected_sites;

        return $this;
    }

    /**
     * Gets premises_affected
     *
     * @return int|null
     */
    public function getPremisesAffected()
    {
        return $this->container['premises_affected'];
    }

    /**
     * Sets premises_affected
     *
     * @param int|null $premises_affected Number of premises affected. Null where Check Networks has no technical visibility of the count, and always null for planned maintenance.
     *
     * @return self
     */
    public function setPremisesAffected($premises_affected)
    {
        if (is_null($premises_affected)) {
            array_push($this->openAPINullablesSetToNull, 'premises_affected');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('premises_affected', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['premises_affected'] = $premises_affected;

        return $this;
    }

    /**
     * Gets responsible_party
     *
     * @return string|null
     */
    public function getResponsibleParty()
    {
        return $this->container['responsible_party'];
    }

    /**
     * Sets responsible_party
     *
     * @param string|null $responsible_party The carrier or carriage service provider responsible for resolving the outage. Null where Check Networks is itself responsible.
     *
     * @return self
     */
    public function setResponsibleParty($responsible_party)
    {
        if (is_null($responsible_party)) {
            array_push($this->openAPINullablesSetToNull, 'responsible_party');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('responsible_party', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['responsible_party'] = $responsible_party;

        return $this;
    }

    /**
     * Gets cause
     *
     * @return string|null
     */
    public function getCause()
    {
        return $this->container['cause'];
    }

    /**
     * Sets cause
     *
     * @param string|null $cause High-level cause. The literal value `withheld` means a cause is known but has been withheld from publication; null means no cause is recorded yet.
     *
     * @return self
     */
    public function setCause($cause)
    {
        if (is_null($cause)) {
            array_push($this->openAPINullablesSetToNull, 'cause');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('cause', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['cause'] = $cause;

        return $this;
    }

    /**
     * Gets cause_natural
     *
     * @return bool|null
     */
    public function getCauseNatural()
    {
        return $this->container['cause_natural'];
    }

    /**
     * Sets cause_natural
     *
     * @param bool|null $cause_natural Whether the cause relates to a natural event or weather
     *
     * @return self
     */
    public function setCauseNatural($cause_natural)
    {
        if (is_null($cause_natural)) {
            throw new \InvalidArgumentException('non-nullable cause_natural cannot be null');
        }
        $this->container['cause_natural'] = $cause_natural;

        return $this;
    }

    /**
     * Gets additional_info
     *
     * @return string|null
     */
    public function getAdditionalInfo()
    {
        return $this->container['additional_info'];
    }

    /**
     * Sets additional_info
     *
     * @param string|null $additional_info Any further published detail about the outage
     *
     * @return self
     */
    public function setAdditionalInfo($additional_info)
    {
        if (is_null($additional_info)) {
            array_push($this->openAPINullablesSetToNull, 'additional_info');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('additional_info', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['additional_info'] = $additional_info;

        return $this;
    }

    /**
     * Gets first_published_at
     *
     * @return \DateTime|null
     */
    public function getFirstPublishedAt()
    {
        return $this->container['first_published_at'];
    }

    /**
     * Sets first_published_at
     *
     * @param \DateTime|null $first_published_at When this register entry was first published
     *
     * @return self
     */
    public function setFirstPublishedAt($first_published_at)
    {
        if (is_null($first_published_at)) {
            array_push($this->openAPINullablesSetToNull, 'first_published_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('first_published_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['first_published_at'] = $first_published_at;

        return $this;
    }

    /**
     * Gets last_rectified_at
     *
     * @return \DateTime|null
     */
    public function getLastRectifiedAt()
    {
        return $this->container['last_rectified_at'];
    }

    /**
     * Sets last_rectified_at
     *
     * @param \DateTime|null $last_rectified_at When this register entry was last corrected, if it has been
     *
     * @return self
     */
    public function setLastRectifiedAt($last_rectified_at)
    {
        if (is_null($last_rectified_at)) {
            array_push($this->openAPINullablesSetToNull, 'last_rectified_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('last_rectified_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['last_rectified_at'] = $last_rectified_at;

        return $this;
    }

    /**
     * Gets your_affected_service_count
     *
     * @return int|null
     */
    public function getYourAffectedServiceCount()
    {
        return $this->container['your_affected_service_count'];
    }

    /**
     * Sets your_affected_service_count
     *
     * @param int|null $your_affected_service_count How many of your services this outage hit, from the footprint we recorded when it was published; 0 when it reaches your area but none of your services. Null for planned maintenance, where we do not record which services a window touches.
     *
     * @return self
     */
    public function setYourAffectedServiceCount($your_affected_service_count)
    {
        if (is_null($your_affected_service_count)) {
            array_push($this->openAPINullablesSetToNull, 'your_affected_service_count');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('your_affected_service_count', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['your_affected_service_count'] = $your_affected_service_count;

        return $this;
    }

    /**
     * Gets your_affected_services
     *
     * @return \CheckNetworks\Api\Generated\Model\NetworkOutageYourAffectedServicesInner[]|null
     */
    public function getYourAffectedServices()
    {
        return $this->container['your_affected_services'];
    }

    /**
     * Sets your_affected_services
     *
     * @param \CheckNetworks\Api\Generated\Model\NetworkOutageYourAffectedServicesInner[]|null $your_affected_services Each of your services this outage hit, including those of your end users, so you can tell them. Null for planned maintenance.
     *
     * @return self
     */
    public function setYourAffectedServices($your_affected_services)
    {
        if (is_null($your_affected_services)) {
            array_push($this->openAPINullablesSetToNull, 'your_affected_services');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('your_affected_services', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['your_affected_services'] = $your_affected_services;

        return $this;
    }

    /**
     * Gets service_counts
     *
     * @return array<string,int>
     */
    public function getServiceCounts()
    {
        return $this->container['service_counts'];
    }

    /**
     * Sets service_counts
     *
     * @param array<string,int> $service_counts The count of services the outage hit, by each of its `affectedServiceTypes`; null for a type we have no count of.
     *
     * @return self
     */
    public function setServiceCounts($service_counts)
    {
        if (is_null($service_counts)) {
            throw new \InvalidArgumentException('non-nullable service_counts cannot be null');
        }
        $this->container['service_counts'] = $service_counts;

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


