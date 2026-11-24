<?php
/**
 * DataOrderCreate
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
 * DataOrderCreate Class Doc Comment
 *
 * @category Class
 * @description A Connect order for any data network. Take each value from the Service Qualification for the location (GET /data/sq/{locId}).
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class DataOrderCreate implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'DataOrderCreate';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'loc_id' => 'string',
        'bandwidth_product_sku' => 'string',
        'order_reference' => 'string',
        'tags' => 'string[]',
        'external_ref' => 'string',
        'metadata' => '\CheckNetworks\Api\Generated\Model\MetadataInput',
        'informed_consent' => 'bool',
        'resource_id' => 'string',
        'appointment_id' => 'string',
        'preferred_appointment_date' => '\DateTime',
        'pots_interconnect' => 'string',
        'priority_assist' => 'bool',
        'end_user' => 'string',
        'occupancy_document_id' => 'string',
        'bundle_id' => 'string',
        'new_end_user' => 'object',
        'end_user_type' => 'string',
        'addons' => '\CheckNetworks\Api\Generated\Model\DataOrderCreateAddonsInner[]',
        'restoration_sla_sku' => 'string',
        'static_ip' => 'bool',
        'ipv6' => 'bool',
        'install' => 'string',
        'transfer_type' => 'string',
        'service_ref' => 'string',
        'customer_authority_date' => '\DateTime'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'loc_id' => null,
        'bandwidth_product_sku' => null,
        'order_reference' => null,
        'tags' => null,
        'external_ref' => null,
        'metadata' => null,
        'informed_consent' => null,
        'resource_id' => null,
        'appointment_id' => null,
        'preferred_appointment_date' => 'date',
        'pots_interconnect' => null,
        'priority_assist' => null,
        'end_user' => null,
        'occupancy_document_id' => 'uuid',
        'bundle_id' => 'uuid',
        'new_end_user' => null,
        'end_user_type' => null,
        'addons' => null,
        'restoration_sla_sku' => null,
        'static_ip' => null,
        'ipv6' => null,
        'install' => null,
        'transfer_type' => null,
        'service_ref' => null,
        'customer_authority_date' => 'date'
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'loc_id' => false,
        'bandwidth_product_sku' => false,
        'order_reference' => false,
        'tags' => false,
        'external_ref' => true,
        'metadata' => true,
        'informed_consent' => false,
        'resource_id' => false,
        'appointment_id' => false,
        'preferred_appointment_date' => false,
        'pots_interconnect' => false,
        'priority_assist' => false,
        'end_user' => true,
        'occupancy_document_id' => false,
        'bundle_id' => false,
        'new_end_user' => false,
        'end_user_type' => true,
        'addons' => true,
        'restoration_sla_sku' => true,
        'static_ip' => false,
        'ipv6' => false,
        'install' => false,
        'transfer_type' => true,
        'service_ref' => true,
        'customer_authority_date' => true
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
        'loc_id' => 'locId',
        'bandwidth_product_sku' => 'bandwidthProductSku',
        'order_reference' => 'orderReference',
        'tags' => 'tags',
        'external_ref' => 'externalRef',
        'metadata' => 'metadata',
        'informed_consent' => 'informedConsent',
        'resource_id' => 'resourceId',
        'appointment_id' => 'appointmentId',
        'preferred_appointment_date' => 'preferredAppointmentDate',
        'pots_interconnect' => 'potsInterconnect',
        'priority_assist' => 'priorityAssist',
        'end_user' => 'endUser',
        'occupancy_document_id' => 'occupancyDocumentId',
        'bundle_id' => 'bundleId',
        'new_end_user' => 'newEndUser',
        'end_user_type' => 'endUserType',
        'addons' => 'addons',
        'restoration_sla_sku' => 'restorationSlaSku',
        'static_ip' => 'staticIp',
        'ipv6' => 'ipv6',
        'install' => 'install',
        'transfer_type' => 'transferType',
        'service_ref' => 'serviceRef',
        'customer_authority_date' => 'customerAuthorityDate'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'loc_id' => 'setLocId',
        'bandwidth_product_sku' => 'setBandwidthProductSku',
        'order_reference' => 'setOrderReference',
        'tags' => 'setTags',
        'external_ref' => 'setExternalRef',
        'metadata' => 'setMetadata',
        'informed_consent' => 'setInformedConsent',
        'resource_id' => 'setResourceId',
        'appointment_id' => 'setAppointmentId',
        'preferred_appointment_date' => 'setPreferredAppointmentDate',
        'pots_interconnect' => 'setPotsInterconnect',
        'priority_assist' => 'setPriorityAssist',
        'end_user' => 'setEndUser',
        'occupancy_document_id' => 'setOccupancyDocumentId',
        'bundle_id' => 'setBundleId',
        'new_end_user' => 'setNewEndUser',
        'end_user_type' => 'setEndUserType',
        'addons' => 'setAddons',
        'restoration_sla_sku' => 'setRestorationSlaSku',
        'static_ip' => 'setStaticIp',
        'ipv6' => 'setIpv6',
        'install' => 'setInstall',
        'transfer_type' => 'setTransferType',
        'service_ref' => 'setServiceRef',
        'customer_authority_date' => 'setCustomerAuthorityDate'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'loc_id' => 'getLocId',
        'bandwidth_product_sku' => 'getBandwidthProductSku',
        'order_reference' => 'getOrderReference',
        'tags' => 'getTags',
        'external_ref' => 'getExternalRef',
        'metadata' => 'getMetadata',
        'informed_consent' => 'getInformedConsent',
        'resource_id' => 'getResourceId',
        'appointment_id' => 'getAppointmentId',
        'preferred_appointment_date' => 'getPreferredAppointmentDate',
        'pots_interconnect' => 'getPotsInterconnect',
        'priority_assist' => 'getPriorityAssist',
        'end_user' => 'getEndUser',
        'occupancy_document_id' => 'getOccupancyDocumentId',
        'bundle_id' => 'getBundleId',
        'new_end_user' => 'getNewEndUser',
        'end_user_type' => 'getEndUserType',
        'addons' => 'getAddons',
        'restoration_sla_sku' => 'getRestorationSlaSku',
        'static_ip' => 'getStaticIp',
        'ipv6' => 'getIpv6',
        'install' => 'getInstall',
        'transfer_type' => 'getTransferType',
        'service_ref' => 'getServiceRef',
        'customer_authority_date' => 'getCustomerAuthorityDate'
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

    public const INFORMED_CONSENT_TRUE = 'true';
    public const END_USER_TYPE_RESIDENTIAL = 'Residential';
    public const END_USER_TYPE_BUSINESS = 'Business';
    public const INSTALL_EXISTING = 'existing';
    public const INSTALL__NEW = 'new';
    public const INSTALL_ADDITIONAL = 'additional';
    public const INSTALL_FIBRE_UPLIFT = 'fibreUplift';
    public const TRANSFER_TYPE_SERVICE_TRANSFER = 'Service Transfer';
    public const TRANSFER_TYPE_SERVICE_TRANSFER_REVERSAL = 'Service Transfer Reversal';
    public const TRANSFER_TYPE_CONNECT_OUTSTANDING = 'Connect Outstanding';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getInformedConsentAllowableValues()
    {
        return [
            self::INFORMED_CONSENT_TRUE,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getEndUserTypeAllowableValues()
    {
        return [
            self::END_USER_TYPE_RESIDENTIAL,
            self::END_USER_TYPE_BUSINESS,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getInstallAllowableValues()
    {
        return [
            self::INSTALL_EXISTING,
            self::INSTALL__NEW,
            self::INSTALL_ADDITIONAL,
            self::INSTALL_FIBRE_UPLIFT,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getTransferTypeAllowableValues()
    {
        return [
            self::TRANSFER_TYPE_SERVICE_TRANSFER,
            self::TRANSFER_TYPE_SERVICE_TRANSFER_REVERSAL,
            self::TRANSFER_TYPE_CONNECT_OUTSTANDING,
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
        $this->setIfExists('loc_id', $data ?? [], null);
        $this->setIfExists('bandwidth_product_sku', $data ?? [], null);
        $this->setIfExists('order_reference', $data ?? [], null);
        $this->setIfExists('tags', $data ?? [], null);
        $this->setIfExists('external_ref', $data ?? [], null);
        $this->setIfExists('metadata', $data ?? [], null);
        $this->setIfExists('informed_consent', $data ?? [], null);
        $this->setIfExists('resource_id', $data ?? [], null);
        $this->setIfExists('appointment_id', $data ?? [], null);
        $this->setIfExists('preferred_appointment_date', $data ?? [], null);
        $this->setIfExists('pots_interconnect', $data ?? [], null);
        $this->setIfExists('priority_assist', $data ?? [], false);
        $this->setIfExists('end_user', $data ?? [], null);
        $this->setIfExists('occupancy_document_id', $data ?? [], null);
        $this->setIfExists('bundle_id', $data ?? [], null);
        $this->setIfExists('new_end_user', $data ?? [], null);
        $this->setIfExists('end_user_type', $data ?? [], null);
        $this->setIfExists('addons', $data ?? [], null);
        $this->setIfExists('restoration_sla_sku', $data ?? [], null);
        $this->setIfExists('static_ip', $data ?? [], false);
        $this->setIfExists('ipv6', $data ?? [], true);
        $this->setIfExists('install', $data ?? [], 'existing');
        $this->setIfExists('transfer_type', $data ?? [], null);
        $this->setIfExists('service_ref', $data ?? [], null);
        $this->setIfExists('customer_authority_date', $data ?? [], null);
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

        if ($this->container['loc_id'] === null) {
            $invalidProperties[] = "'loc_id' can't be null";
        }
        if ((mb_strlen($this->container['loc_id']) > 20)) {
            $invalidProperties[] = "invalid value for 'loc_id', the character length must be smaller than or equal to 20.";
        }

        if ($this->container['bandwidth_product_sku'] === null) {
            $invalidProperties[] = "'bandwidth_product_sku' can't be null";
        }
        if ((mb_strlen($this->container['bandwidth_product_sku']) > 64)) {
            $invalidProperties[] = "invalid value for 'bandwidth_product_sku', the character length must be smaller than or equal to 64.";
        }

        if ($this->container['order_reference'] === null) {
            $invalidProperties[] = "'order_reference' can't be null";
        }
        if ((mb_strlen($this->container['order_reference']) > 35)) {
            $invalidProperties[] = "invalid value for 'order_reference', the character length must be smaller than or equal to 35.";
        }

        if (!preg_match("/^[A-Za-z0-9._-]+$/", $this->container['order_reference'])) {
            $invalidProperties[] = "invalid value for 'order_reference', must be conform to the pattern /^[A-Za-z0-9._-]+$/.";
        }

        if (!is_null($this->container['tags']) && (count($this->container['tags']) > 20)) {
            $invalidProperties[] = "invalid value for 'tags', number of items must be less than or equal to 20.";
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

        if ($this->container['informed_consent'] === null) {
            $invalidProperties[] = "'informed_consent' can't be null";
        }
        $allowedValues = $this->getInformedConsentAllowableValues();
        if (!is_null($this->container['informed_consent']) && !in_array($this->container['informed_consent'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'informed_consent', must be one of '%s'",
                $this->container['informed_consent'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['resource_id']) && (mb_strlen($this->container['resource_id']) > 32)) {
            $invalidProperties[] = "invalid value for 'resource_id', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['appointment_id']) && (mb_strlen($this->container['appointment_id']) > 48)) {
            $invalidProperties[] = "invalid value for 'appointment_id', the character length must be smaller than or equal to 48.";
        }

        if (!is_null($this->container['pots_interconnect']) && (mb_strlen($this->container['pots_interconnect']) > 32)) {
            $invalidProperties[] = "invalid value for 'pots_interconnect', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['end_user']) && (mb_strlen($this->container['end_user']) > 64)) {
            $invalidProperties[] = "invalid value for 'end_user', the character length must be smaller than or equal to 64.";
        }

        $allowedValues = $this->getEndUserTypeAllowableValues();
        if (!is_null($this->container['end_user_type']) && !in_array($this->container['end_user_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'end_user_type', must be one of '%s'",
                $this->container['end_user_type'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['addons']) && (count($this->container['addons']) > 50)) {
            $invalidProperties[] = "invalid value for 'addons', number of items must be less than or equal to 50.";
        }

        if (!is_null($this->container['restoration_sla_sku']) && (mb_strlen($this->container['restoration_sla_sku']) > 64)) {
            $invalidProperties[] = "invalid value for 'restoration_sla_sku', the character length must be smaller than or equal to 64.";
        }

        $allowedValues = $this->getInstallAllowableValues();
        if (!is_null($this->container['install']) && !in_array($this->container['install'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'install', must be one of '%s'",
                $this->container['install'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getTransferTypeAllowableValues();
        if (!is_null($this->container['transfer_type']) && !in_array($this->container['transfer_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'transfer_type', must be one of '%s'",
                $this->container['transfer_type'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['service_ref']) && (mb_strlen($this->container['service_ref']) > 32)) {
            $invalidProperties[] = "invalid value for 'service_ref', the character length must be smaller than or equal to 32.";
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
     * Gets loc_id
     *
     * @return string
     */
    public function getLocId()
    {
        return $this->container['loc_id'];
    }

    /**
     * Sets loc_id
     *
     * @param string $loc_id The premises: an NBN Location ID (LOC…) or an Opticomm Property ID (OPC…). The location decides which network and product the order is placed as.
     *
     * @return self
     */
    public function setLocId($loc_id)
    {
        if (is_null($loc_id)) {
            throw new \InvalidArgumentException('non-nullable loc_id cannot be null');
        }
        if ((mb_strlen($loc_id) > 20)) {
            throw new \InvalidArgumentException('invalid length for $loc_id when calling DataOrderCreate., must be smaller than or equal to 20.');
        }

        $this->container['loc_id'] = $loc_id;

        return $this;
    }

    /**
     * Gets bandwidth_product_sku
     *
     * @return string
     */
    public function getBandwidthProductSku()
    {
        return $this->container['bandwidth_product_sku'];
    }

    /**
     * Sets bandwidth_product_sku
     *
     * @param string $bandwidth_product_sku One of the `availablePlans` SKUs from the Service Qualification.
     *
     * @return self
     */
    public function setBandwidthProductSku($bandwidth_product_sku)
    {
        if (is_null($bandwidth_product_sku)) {
            throw new \InvalidArgumentException('non-nullable bandwidth_product_sku cannot be null');
        }
        if ((mb_strlen($bandwidth_product_sku) > 64)) {
            throw new \InvalidArgumentException('invalid length for $bandwidth_product_sku when calling DataOrderCreate., must be smaller than or equal to 64.');
        }

        $this->container['bandwidth_product_sku'] = $bandwidth_product_sku;

        return $this;
    }

    /**
     * Gets order_reference
     *
     * @return string
     */
    public function getOrderReference()
    {
        return $this->container['order_reference'];
    }

    /**
     * Sets order_reference
     *
     * @param string $order_reference Your own reference for the order. Sending the same reference again returns the order it already placed rather than placing a second one.
     *
     * @return self
     */
    public function setOrderReference($order_reference)
    {
        if (is_null($order_reference)) {
            throw new \InvalidArgumentException('non-nullable order_reference cannot be null');
        }
        if ((mb_strlen($order_reference) > 35)) {
            throw new \InvalidArgumentException('invalid length for $order_reference when calling DataOrderCreate., must be smaller than or equal to 35.');
        }
        if ((!preg_match("/^[A-Za-z0-9._-]+$/", ObjectSerializer::toString($order_reference)))) {
            throw new \InvalidArgumentException("invalid value for \$order_reference when calling DataOrderCreate., must conform to the pattern /^[A-Za-z0-9._-]+$/.");
        }

        $this->container['order_reference'] = $order_reference;

        return $this;
    }

    /**
     * Gets tags
     *
     * @return string[]|null
     */
    public function getTags()
    {
        return $this->container['tags'];
    }

    /**
     * Sets tags
     *
     * @param string[]|null $tags Your own tags on the order, carried to the service it builds (see PUT /data/services/{ref}/tags). Each is trimmed, at most 255 characters, matched ignoring case; at most 20. Checked before anything is sent to the carrier. A retry under the same `orderReference` keeps the tags the order was placed with.
     *
     * @return self
     */
    public function setTags($tags)
    {
        if (is_null($tags)) {
            throw new \InvalidArgumentException('non-nullable tags cannot be null');
        }
        if ((count($tags) > 20)) {
            throw new \InvalidArgumentException('invalid value for $tags when calling DataOrderCreate., number of items must be less than or equal to 20.');
        }
        $this->container['tags'] = $tags;

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
     * @param string|null $external_ref Your own reference for the data service this order builds, held on the order and set on the service when it is built (see PATCH /data/services/{ref}). One another of your data services holds, or another open order holds for its service, is a 422 before anything is sent to the carrier. A retry under the same `orderReference` keeps the reference the order was placed with.
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
            throw new \InvalidArgumentException('invalid length for $external_ref when calling DataOrderCreate., must be smaller than or equal to 64.');
        }
        if (!is_null($external_ref) && (mb_strlen($external_ref) < 1)) {
            throw new \InvalidArgumentException('invalid length for $external_ref when calling DataOrderCreate., must be bigger than or equal to 1.');
        }
        if (!is_null($external_ref) && (!preg_match("/^[A-Za-z0-9_.\\-]+$/", ObjectSerializer::toString($external_ref)))) {
            throw new \InvalidArgumentException("invalid value for \$external_ref when calling DataOrderCreate., must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
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
     * @param \CheckNetworks\Api\Generated\Model\MetadataInput|null $metadata Your own metadata for the data service this order builds, set on it when it is built.
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
     * Gets informed_consent
     *
     * @return bool
     */
    public function getInformedConsent()
    {
        return $this->container['informed_consent'];
    }

    /**
     * Sets informed_consent
     *
     * @param bool $informed_consent Confirms the End User has given Informed Consent for the order.
     *
     * @return self
     */
    public function setInformedConsent($informed_consent)
    {
        if (is_null($informed_consent)) {
            throw new \InvalidArgumentException('non-nullable informed_consent cannot be null');
        }
        $allowedValues = $this->getInformedConsentAllowableValues();
        if (!in_array($informed_consent, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'informed_consent', must be one of '%s'",
                    $informed_consent,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['informed_consent'] = $informed_consent;

        return $this;
    }

    /**
     * Gets resource_id
     *
     * @return string|null
     */
    public function getResourceId()
    {
        return $this->container['resource_id'];
    }

    /**
     * Sets resource_id
     *
     * @param string|null $resource_id (Optional) One of the `resources` from the Service Qualification: the NTD or OTD port, or the copper pair, the service runs on. Leave it out and we choose: the next free port on an installed NTD; on copper, the one spare pair, or a new line where there is none. Where no NTD is installed, the order waits for an appointment (`order.appointmentRequired`). Where every port is in use, it waits for you to choose a new NTD or a port to take over (`order.ntdFull`): we never order a second NTD unasked. Required on copper with more than one spare pair.
     *
     * @return self
     */
    public function setResourceId($resource_id)
    {
        if (is_null($resource_id)) {
            throw new \InvalidArgumentException('non-nullable resource_id cannot be null');
        }
        if ((mb_strlen($resource_id) > 32)) {
            throw new \InvalidArgumentException('invalid length for $resource_id when calling DataOrderCreate., must be smaller than or equal to 32.');
        }

        $this->container['resource_id'] = $resource_id;

        return $this;
    }

    /**
     * Gets appointment_id
     *
     * @return string|null
     */
    public function getAppointmentId()
    {
        return $this->container['appointment_id'];
    }

    /**
     * Sets appointment_id
     *
     * @param string|null $appointment_id (Optional) An appointment you reserved for this location, where the order needs a technician. Without one, such an order waits for it (see `order.appointmentRequired`).
     *
     * @return self
     */
    public function setAppointmentId($appointment_id)
    {
        if (is_null($appointment_id)) {
            throw new \InvalidArgumentException('non-nullable appointment_id cannot be null');
        }
        if ((mb_strlen($appointment_id) > 48)) {
            throw new \InvalidArgumentException('invalid length for $appointment_id when calling DataOrderCreate., must be smaller than or equal to 48.');
        }

        $this->container['appointment_id'] = $appointment_id;

        return $this;
    }

    /**
     * Gets preferred_appointment_date
     *
     * @return \DateTime|null
     */
    public function getPreferredAppointmentDate()
    {
        return $this->container['preferred_appointment_date'];
    }

    /**
     * Sets preferred_appointment_date
     *
     * @param \DateTime|null $preferred_appointment_date (Optional) The first day you would like a technician, should the order need one: today or later, on your account's time zone (or the one you sent in `CN-Time-Zone`). The order's timeslots read (GET /data/orders/{orderId}/timeslots) starts here.
     *
     * @return self
     */
    public function setPreferredAppointmentDate($preferred_appointment_date)
    {
        if (is_null($preferred_appointment_date)) {
            throw new \InvalidArgumentException('non-nullable preferred_appointment_date cannot be null');
        }
        $this->container['preferred_appointment_date'] = $preferred_appointment_date;

        return $this;
    }

    /**
     * Gets pots_interconnect
     *
     * @return string|null
     */
    public function getPotsInterconnect()
    {
        return $this->container['pots_interconnect'];
    }

    /**
     * Sets pots_interconnect
     *
     * @param string|null $pots_interconnect (Conditional, copper) The Full National Number (FNN) or Unconditioned Local Loop (ULL) of the phone service being transitioned. Required for an Active Service Class 12 pair, and must not be sent otherwise.
     *
     * @return self
     */
    public function setPotsInterconnect($pots_interconnect)
    {
        if (is_null($pots_interconnect)) {
            throw new \InvalidArgumentException('non-nullable pots_interconnect cannot be null');
        }
        if ((mb_strlen($pots_interconnect) > 32)) {
            throw new \InvalidArgumentException('invalid length for $pots_interconnect when calling DataOrderCreate., must be smaller than or equal to 32.');
        }

        $this->container['pots_interconnect'] = $pots_interconnect;

        return $this;
    }

    /**
     * Gets priority_assist
     *
     * @return bool|null
     */
    public function getPriorityAssist()
    {
        return $this->container['priority_assist'];
    }

    /**
     * Sets priority_assist
     *
     * @param bool|null $priority_assist (Optional) Sent to the carrier as its priority assist flag. \"true\" is accepted only for an `endUser` on our Priority Assistance register; otherwise the order is refused with a 422 on `priorityAssist` before anything is drafted. A `newEndUser` is never on the register.
     *
     * @return self
     */
    public function setPriorityAssist($priority_assist)
    {
        if (is_null($priority_assist)) {
            throw new \InvalidArgumentException('non-nullable priority_assist cannot be null');
        }
        $this->container['priority_assist'] = $priority_assist;

        return $this;
    }

    /**
     * Gets end_user
     *
     * @return string|null
     */
    public function getEndUser()
    {
        return $this->container['end_user'];
    }

    /**
     * Sets end_user
     *
     * @param string|null $end_user (Optional) The end user the service is for, by its public number. The service, its charges and its usage are then theirs, as an order placed in the portal for them. One of your end users that is not archived; any other number is a 422 and no order is placed.
     *
     * @return self
     */
    public function setEndUser($end_user)
    {
        if (is_null($end_user)) {
            array_push($this->openAPINullablesSetToNull, 'end_user');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('end_user', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($end_user) && (mb_strlen($end_user) > 64)) {
            throw new \InvalidArgumentException('invalid length for $end_user when calling DataOrderCreate., must be smaller than or equal to 64.');
        }

        $this->container['end_user'] = $end_user;

        return $this;
    }

    /**
     * Gets occupancy_document_id
     *
     * @return string|null
     */
    public function getOccupancyDocumentId()
    {
        return $this->container['occupancy_document_id'];
    }

    /**
     * Sets occupancy_document_id
     *
     * @param string|null $occupancy_document_id (Optional) On an NBN Connect Outstanding, the `id` of a proof of occupancy uploaded with `POST /data/occupancy-documents`, instead of the old service's id: evidence that the end user occupies the premises. It is kept with the order, and each one is used by one order: a retry with the same `orderReference` may name it again. A retry after a call that stopped before the order was sent keeps it with the order before sending it, unless the order already keeps one. Not with `serviceRef`; another account's, or one another order used, is a 422.
     *
     * @return self
     */
    public function setOccupancyDocumentId($occupancy_document_id)
    {
        if (is_null($occupancy_document_id)) {
            throw new \InvalidArgumentException('non-nullable occupancy_document_id cannot be null');
        }
        $this->container['occupancy_document_id'] = $occupancy_document_id;

        return $this;
    }

    /**
     * Gets bundle_id
     *
     * @return string|null
     */
    public function getBundleId()
    {
        return $this->container['bundle_id'];
    }

    /**
     * Sets bundle_id
     *
     * @param string|null $bundle_id (Optional) An open bundle to add the order to, as a draft that is placed when the bundle is submitted. A bundle that has been submitted, or is not yours, is a 422.
     *
     * @return self
     */
    public function setBundleId($bundle_id)
    {
        if (is_null($bundle_id)) {
            throw new \InvalidArgumentException('non-nullable bundle_id cannot be null');
        }
        $this->container['bundle_id'] = $bundle_id;

        return $this;
    }

    /**
     * Gets new_end_user
     *
     * @return object|null
     */
    public function getNewEndUser()
    {
        return $this->container['new_end_user'];
    }

    /**
     * Sets new_end_user
     *
     * @param object|null $new_end_user (Optional) A new end user to make and order for in this one call, instead of `endUser`; never both. It takes the same fields and rules as creating an end user. Both parts are checked before anything is made; if the order is refused, the end user is not kept and the answer is the order's 422. The end user's welcome email goes only once the order is accepted. A retry with the same Idempotency-Key returns the same end user and order. The response's `endUser` is the new end user's number.
     *
     * @return self
     */
    public function setNewEndUser($new_end_user)
    {
        if (is_null($new_end_user)) {
            throw new \InvalidArgumentException('non-nullable new_end_user cannot be null');
        }
        $this->container['new_end_user'] = $new_end_user;

        return $this;
    }

    /**
     * Gets end_user_type
     *
     * @return string|null
     */
    public function getEndUserType()
    {
        return $this->container['end_user_type'];
    }

    /**
     * Sets end_user_type
     *
     * @param string|null $end_user_type (Optional) What the carrier is told the premises is. Left out, it is taken from the end user's own type, as the portal does.
     *
     * @return self
     */
    public function setEndUserType($end_user_type)
    {
        if (is_null($end_user_type)) {
            array_push($this->openAPINullablesSetToNull, 'end_user_type');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('end_user_type', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getEndUserTypeAllowableValues();
        if (!is_null($end_user_type) && !in_array($end_user_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'end_user_type', must be one of '%s'",
                    $end_user_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['end_user_type'] = $end_user_type;

        return $this;
    }

    /**
     * Gets addons
     *
     * @return \CheckNetworks\Api\Generated\Model\DataOrderCreateAddonsInner[]|null
     */
    public function getAddons()
    {
        return $this->container['addons'];
    }

    /**
     * Sets addons
     *
     * @param \CheckNetworks\Api\Generated\Model\DataOrderCreateAddonsInner[]|null $addons (Optional) The add-on products to sell with the service, each with a quantity. Only the add-ons the portal offers for the chosen plan may be sent; any other is a 422. Left out, the order carries the plan's default add-ons, as the portal ticks them; an empty list carries none. Each starts billing when the carrier accepts the order.
     *
     * @return self
     */
    public function setAddons($addons)
    {
        if (is_null($addons)) {
            array_push($this->openAPINullablesSetToNull, 'addons');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('addons', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($addons) && (count($addons) > 50)) {
            throw new \InvalidArgumentException('invalid value for $addons when calling DataOrderCreate., number of items must be less than or equal to 50.');
        }
        $this->container['addons'] = $addons;

        return $this;
    }

    /**
     * Gets restoration_sla_sku
     *
     * @return string|null
     */
    public function getRestorationSlaSku()
    {
        return $this->container['restoration_sla_sku'];
    }

    /**
     * Sets restoration_sla_sku
     *
     * @param string|null $restoration_sla_sku (Optional, NBN) The restoration SLA, one the chosen plan allows at this location; the standard SLA when left out. Refused on an Opticomm order.
     *
     * @return self
     */
    public function setRestorationSlaSku($restoration_sla_sku)
    {
        if (is_null($restoration_sla_sku)) {
            array_push($this->openAPINullablesSetToNull, 'restoration_sla_sku');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('restoration_sla_sku', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($restoration_sla_sku) && (mb_strlen($restoration_sla_sku) > 64)) {
            throw new \InvalidArgumentException('invalid length for $restoration_sla_sku when calling DataOrderCreate., must be smaller than or equal to 64.');
        }

        $this->container['restoration_sla_sku'] = $restoration_sla_sku;

        return $this;
    }

    /**
     * Gets static_ip
     *
     * @return bool|null
     */
    public function getStaticIp()
    {
        return $this->container['static_ip'];
    }

    /**
     * Sets static_ip
     *
     * @param bool|null $static_ip (Optional) A static IPv4 address rather than one from the dynamic pool. A monthly charge applies.
     *
     * @return self
     */
    public function setStaticIp($static_ip)
    {
        if (is_null($static_ip)) {
            throw new \InvalidArgumentException('non-nullable static_ip cannot be null');
        }
        $this->container['static_ip'] = $static_ip;

        return $this;
    }

    /**
     * Gets ipv6
     *
     * @return bool|null
     */
    public function getIpv6()
    {
        return $this->container['ipv6'];
    }

    /**
     * Sets ipv6
     *
     * @param bool|null $ipv6 (Optional) IPv6 (a framed /64 and a delegated prefix). Free, and on unless you turn it off.
     *
     * @return self
     */
    public function setIpv6($ipv6)
    {
        if (is_null($ipv6)) {
            throw new \InvalidArgumentException('non-nullable ipv6 cannot be null');
        }
        $this->container['ipv6'] = $ipv6;

        return $this;
    }

    /**
     * Gets install
     *
     * @return string|null
     */
    public function getInstall()
    {
        return $this->container['install'];
    }

    /**
     * Sets install
     *
     * @param string|null $install (Optional) How the service reaches the premises:  - `existing`: on a port or pair already there (`resourceId`, or the one we choose). - `new`: a new NTD, OTD or copper pair, where the Service Qualification offers a new install. - `additional`: another NTD, OTD or pair beside the ones in use, where it offers an additional install. - `fibreUplift`: not yet available through the API; order it in the portal.  A `new` or `additional` install takes no `resourceId` and needs a technician appointment, on NBN and Opticomm alike: without an `appointmentId` the order waits for one (`order.appointmentRequired`). It is a 422 where the location does not offer it.
     *
     * @return self
     */
    public function setInstall($install)
    {
        if (is_null($install)) {
            throw new \InvalidArgumentException('non-nullable install cannot be null');
        }
        $allowedValues = $this->getInstallAllowableValues();
        if (!in_array($install, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'install', must be one of '%s'",
                    $install,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['install'] = $install;

        return $this;
    }

    /**
     * Gets transfer_type
     *
     * @return string|null
     */
    public function getTransferType()
    {
        return $this->container['transfer_type'];
    }

    /**
     * Sets transfer_type
     *
     * @param string|null $transfer_type (Optional) Take over a service another provider has at the premises, in this one call. On NBN, as the NBN order routes take it, with `serviceRef` and `customerAuthorityDate`. On Opticomm, `Service Transfer` or `Service Transfer Reversal` with `customerAuthorityDate` and no `serviceRef`.
     *
     * @return self
     */
    public function setTransferType($transfer_type)
    {
        if (is_null($transfer_type)) {
            array_push($this->openAPINullablesSetToNull, 'transfer_type');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('transfer_type', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getTransferTypeAllowableValues();
        if (!is_null($transfer_type) && !in_array($transfer_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'transfer_type', must be one of '%s'",
                    $transfer_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['transfer_type'] = $transfer_type;

        return $this;
    }

    /**
     * Gets service_ref
     *
     * @return string|null
     */
    public function getServiceRef()
    {
        return $this->container['service_ref'];
    }

    /**
     * Sets service_ref
     *
     * @param string|null $service_ref (Conditional, NBN transfer) The AVC of the service being taken over.
     *
     * @return self
     */
    public function setServiceRef($service_ref)
    {
        if (is_null($service_ref)) {
            array_push($this->openAPINullablesSetToNull, 'service_ref');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('service_ref', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($service_ref) && (mb_strlen($service_ref) > 32)) {
            throw new \InvalidArgumentException('invalid length for $service_ref when calling DataOrderCreate., must be smaller than or equal to 32.');
        }

        $this->container['service_ref'] = $service_ref;

        return $this;
    }

    /**
     * Gets customer_authority_date
     *
     * @return \DateTime|null
     */
    public function getCustomerAuthorityDate()
    {
        return $this->container['customer_authority_date'];
    }

    /**
     * Sets customer_authority_date
     *
     * @param \DateTime|null $customer_authority_date (Conditional, transfer) The day the end user authorised the transfer. Required with `transferType`. Today or earlier, on your account's time zone (or the one you sent in `CN-Time-Zone`).
     *
     * @return self
     */
    public function setCustomerAuthorityDate($customer_authority_date)
    {
        if (is_null($customer_authority_date)) {
            array_push($this->openAPINullablesSetToNull, 'customer_authority_date');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('customer_authority_date', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['customer_authority_date'] = $customer_authority_date;

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


