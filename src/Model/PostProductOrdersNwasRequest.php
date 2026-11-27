<?php
/**
 * PostProductOrdersNwasRequest
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
 * PostProductOrdersNwasRequest Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class PostProductOrdersNwasRequest implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'post_product_orders_nwas_request';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'loc_id' => 'string',
        'bandwidth_product_sku' => 'string',
        'restoration_sla_sku' => 'string',
        'order_reference' => 'string',
        'end_user_ref' => 'string',
        'priority_assist' => 'bool',
        'end_user' => 'string',
        'appointment_id' => 'string',
        'ntd_id' => 'string',
        'unid_port_id' => 'string',
        'transfer_type' => 'string',
        'occupancy_document_id' => 'string',
        'service_ref' => 'string',
        'customer_authority_date' => '\CheckNetworks\Model\PostProductOrdersNcasRequestCustomerAuthorityDate',
        'informed_consent' => 'bool',
        'overlap_period' => 'string',
        'from_port_id' => 'string',
        'installation_workforce' => 'string',
        'vlan_mode' => 'string',
        'uni_vlan_id' => 'int'
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
        'restoration_sla_sku' => null,
        'order_reference' => null,
        'end_user_ref' => null,
        'priority_assist' => null,
        'end_user' => null,
        'appointment_id' => null,
        'ntd_id' => null,
        'unid_port_id' => null,
        'transfer_type' => null,
        'occupancy_document_id' => 'uuid',
        'service_ref' => null,
        'customer_authority_date' => null,
        'informed_consent' => null,
        'overlap_period' => null,
        'from_port_id' => null,
        'installation_workforce' => null,
        'vlan_mode' => null,
        'uni_vlan_id' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'loc_id' => false,
        'bandwidth_product_sku' => false,
        'restoration_sla_sku' => false,
        'order_reference' => false,
        'end_user_ref' => false,
        'priority_assist' => false,
        'end_user' => false,
        'appointment_id' => false,
        'ntd_id' => false,
        'unid_port_id' => true,
        'transfer_type' => false,
        'occupancy_document_id' => false,
        'service_ref' => false,
        'customer_authority_date' => false,
        'informed_consent' => false,
        'overlap_period' => false,
        'from_port_id' => false,
        'installation_workforce' => false,
        'vlan_mode' => false,
        'uni_vlan_id' => false
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
        'restoration_sla_sku' => 'restorationSlaSku',
        'order_reference' => 'orderReference',
        'end_user_ref' => 'endUserRef',
        'priority_assist' => 'priorityAssist',
        'end_user' => 'endUser',
        'appointment_id' => 'appointmentId',
        'ntd_id' => 'ntdId',
        'unid_port_id' => 'unidPortId',
        'transfer_type' => 'transferType',
        'occupancy_document_id' => 'occupancyDocumentId',
        'service_ref' => 'serviceRef',
        'customer_authority_date' => 'customerAuthorityDate',
        'informed_consent' => 'informedConsent',
        'overlap_period' => 'overlapPeriod',
        'from_port_id' => 'fromPortId',
        'installation_workforce' => 'installationWorkforce',
        'vlan_mode' => 'vlanMode',
        'uni_vlan_id' => 'uniVlanId'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'loc_id' => 'setLocId',
        'bandwidth_product_sku' => 'setBandwidthProductSku',
        'restoration_sla_sku' => 'setRestorationSlaSku',
        'order_reference' => 'setOrderReference',
        'end_user_ref' => 'setEndUserRef',
        'priority_assist' => 'setPriorityAssist',
        'end_user' => 'setEndUser',
        'appointment_id' => 'setAppointmentId',
        'ntd_id' => 'setNtdId',
        'unid_port_id' => 'setUnidPortId',
        'transfer_type' => 'setTransferType',
        'occupancy_document_id' => 'setOccupancyDocumentId',
        'service_ref' => 'setServiceRef',
        'customer_authority_date' => 'setCustomerAuthorityDate',
        'informed_consent' => 'setInformedConsent',
        'overlap_period' => 'setOverlapPeriod',
        'from_port_id' => 'setFromPortId',
        'installation_workforce' => 'setInstallationWorkforce',
        'vlan_mode' => 'setVlanMode',
        'uni_vlan_id' => 'setUniVlanId'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'loc_id' => 'getLocId',
        'bandwidth_product_sku' => 'getBandwidthProductSku',
        'restoration_sla_sku' => 'getRestorationSlaSku',
        'order_reference' => 'getOrderReference',
        'end_user_ref' => 'getEndUserRef',
        'priority_assist' => 'getPriorityAssist',
        'end_user' => 'getEndUser',
        'appointment_id' => 'getAppointmentId',
        'ntd_id' => 'getNtdId',
        'unid_port_id' => 'getUnidPortId',
        'transfer_type' => 'getTransferType',
        'occupancy_document_id' => 'getOccupancyDocumentId',
        'service_ref' => 'getServiceRef',
        'customer_authority_date' => 'getCustomerAuthorityDate',
        'informed_consent' => 'getInformedConsent',
        'overlap_period' => 'getOverlapPeriod',
        'from_port_id' => 'getFromPortId',
        'installation_workforce' => 'getInstallationWorkforce',
        'vlan_mode' => 'getVlanMode',
        'uni_vlan_id' => 'getUniVlanId'
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

    public const UNID_PORT_ID__1 = '1';
    public const UNID_PORT_ID__2 = '2';
    public const UNID_PORT_ID__3 = '3';
    public const UNID_PORT_ID__4 = '4';
    public const TRANSFER_TYPE_SERVICE_TRANSFER = 'Service Transfer';
    public const TRANSFER_TYPE_SERVICE_TRANSFER_REVERSAL = 'Service Transfer Reversal';
    public const TRANSFER_TYPE_CONNECT_OUTSTANDING = 'Connect Outstanding';
    public const OVERLAP_PERIOD__0 = '0';
    public const OVERLAP_PERIOD__4 = '4';
    public const FROM_PORT_ID__1 = '1';
    public const FROM_PORT_ID__2 = '2';
    public const FROM_PORT_ID__3 = '3';
    public const FROM_PORT_ID__4 = '4';
    public const INSTALLATION_WORKFORCE_NBN_CO_INSTALL = 'NBN Co Install';
    public const INSTALLATION_WORKFORCE_NBN_CO_INSTALL_END_USER = 'NBN Co Install End User';
    public const INSTALLATION_WORKFORCE_RSP_INSTALL = 'RSP Install';
    public const INSTALLATION_WORKFORCE_RSP_INSTALL_END_USER = 'RSP Install End User';
    public const VLAN_MODE_DEFAULT_MAPPED = 'Default-Mapped';
    public const VLAN_MODE_TAGGED = 'Tagged';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getUnidPortIdAllowableValues()
    {
        return [
            self::UNID_PORT_ID__1,
            self::UNID_PORT_ID__2,
            self::UNID_PORT_ID__3,
            self::UNID_PORT_ID__4,
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
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getOverlapPeriodAllowableValues()
    {
        return [
            self::OVERLAP_PERIOD__0,
            self::OVERLAP_PERIOD__4,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getFromPortIdAllowableValues()
    {
        return [
            self::FROM_PORT_ID__1,
            self::FROM_PORT_ID__2,
            self::FROM_PORT_ID__3,
            self::FROM_PORT_ID__4,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getInstallationWorkforceAllowableValues()
    {
        return [
            self::INSTALLATION_WORKFORCE_NBN_CO_INSTALL,
            self::INSTALLATION_WORKFORCE_NBN_CO_INSTALL_END_USER,
            self::INSTALLATION_WORKFORCE_RSP_INSTALL,
            self::INSTALLATION_WORKFORCE_RSP_INSTALL_END_USER,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getVlanModeAllowableValues()
    {
        return [
            self::VLAN_MODE_DEFAULT_MAPPED,
            self::VLAN_MODE_TAGGED,
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
        $this->setIfExists('restoration_sla_sku', $data ?? [], null);
        $this->setIfExists('order_reference', $data ?? [], null);
        $this->setIfExists('end_user_ref', $data ?? [], null);
        $this->setIfExists('priority_assist', $data ?? [], false);
        $this->setIfExists('end_user', $data ?? [], null);
        $this->setIfExists('appointment_id', $data ?? [], null);
        $this->setIfExists('ntd_id', $data ?? [], null);
        $this->setIfExists('unid_port_id', $data ?? [], null);
        $this->setIfExists('transfer_type', $data ?? [], null);
        $this->setIfExists('occupancy_document_id', $data ?? [], null);
        $this->setIfExists('service_ref', $data ?? [], null);
        $this->setIfExists('customer_authority_date', $data ?? [], null);
        $this->setIfExists('informed_consent', $data ?? [], null);
        $this->setIfExists('overlap_period', $data ?? [], null);
        $this->setIfExists('from_port_id', $data ?? [], null);
        $this->setIfExists('installation_workforce', $data ?? [], null);
        $this->setIfExists('vlan_mode', $data ?? [], null);
        $this->setIfExists('uni_vlan_id', $data ?? [], null);
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
        if (!preg_match("/^LOC\\d{12}$/", $this->container['loc_id'])) {
            $invalidProperties[] = "invalid value for 'loc_id', must be conform to the pattern /^LOC\\d{12}$/.";
        }

        if ($this->container['bandwidth_product_sku'] === null) {
            $invalidProperties[] = "'bandwidth_product_sku' can't be null";
        }
        if ((mb_strlen($this->container['bandwidth_product_sku']) > 64)) {
            $invalidProperties[] = "invalid value for 'bandwidth_product_sku', the character length must be smaller than or equal to 64.";
        }

        if (!is_null($this->container['restoration_sla_sku']) && (mb_strlen($this->container['restoration_sla_sku']) > 64)) {
            $invalidProperties[] = "invalid value for 'restoration_sla_sku', the character length must be smaller than or equal to 64.";
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

        if (!is_null($this->container['end_user']) && (mb_strlen($this->container['end_user']) > 64)) {
            $invalidProperties[] = "invalid value for 'end_user', the character length must be smaller than or equal to 64.";
        }

        if (!is_null($this->container['appointment_id']) && (mb_strlen($this->container['appointment_id']) > 48)) {
            $invalidProperties[] = "invalid value for 'appointment_id', the character length must be smaller than or equal to 48.";
        }

        if ($this->container['ntd_id'] === null) {
            $invalidProperties[] = "'ntd_id' can't be null";
        }
        if ((mb_strlen($this->container['ntd_id']) > 32)) {
            $invalidProperties[] = "invalid value for 'ntd_id', the character length must be smaller than or equal to 32.";
        }

        $allowedValues = $this->getUnidPortIdAllowableValues();
        if (!is_null($this->container['unid_port_id']) && !in_array($this->container['unid_port_id'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'unid_port_id', must be one of '%s'",
                $this->container['unid_port_id'],
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

        if (!is_null($this->container['transfer_type']) && (mb_strlen($this->container['transfer_type']) > 32)) {
            $invalidProperties[] = "invalid value for 'transfer_type', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['service_ref']) && (mb_strlen($this->container['service_ref']) > 32)) {
            $invalidProperties[] = "invalid value for 'service_ref', the character length must be smaller than or equal to 32.";
        }

        if ($this->container['informed_consent'] === null) {
            $invalidProperties[] = "'informed_consent' can't be null";
        }
        $allowedValues = $this->getOverlapPeriodAllowableValues();
        if (!is_null($this->container['overlap_period']) && !in_array($this->container['overlap_period'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'overlap_period', must be one of '%s'",
                $this->container['overlap_period'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getFromPortIdAllowableValues();
        if (!is_null($this->container['from_port_id']) && !in_array($this->container['from_port_id'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'from_port_id', must be one of '%s'",
                $this->container['from_port_id'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getInstallationWorkforceAllowableValues();
        if (!is_null($this->container['installation_workforce']) && !in_array($this->container['installation_workforce'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'installation_workforce', must be one of '%s'",
                $this->container['installation_workforce'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getVlanModeAllowableValues();
        if (!is_null($this->container['vlan_mode']) && !in_array($this->container['vlan_mode'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'vlan_mode', must be one of '%s'",
                $this->container['vlan_mode'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['uni_vlan_id']) && ($this->container['uni_vlan_id'] > 4094)) {
            $invalidProperties[] = "invalid value for 'uni_vlan_id', must be smaller than or equal to 4094.";
        }

        if (!is_null($this->container['uni_vlan_id']) && ($this->container['uni_vlan_id'] < 1)) {
            $invalidProperties[] = "invalid value for 'uni_vlan_id', must be bigger than or equal to 1.";
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
     * @param string $loc_id The NBN Location ID for the premises the order is for.
     *
     * @return self
     */
    public function setLocId($loc_id)
    {
        if (is_null($loc_id)) {
            throw new \InvalidArgumentException('non-nullable loc_id cannot be null');
        }

        if ((!preg_match("/^LOC\\d{12}$/", ObjectSerializer::toString($loc_id)))) {
            throw new \InvalidArgumentException("invalid value for \$loc_id when calling PostProductOrdersNwasRequest., must conform to the pattern /^LOC\\d{12}$/.");
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
     * @param string $bandwidth_product_sku Product SKU of the Bandwidth Product being ordered. This controls which Service Layer (Layer 2 or Layer 3) this Order can be Provisioned with. Use the Product Qualification > Eligible Bandwidth Products API to find selectable options for the Location (LOC #)
     *
     * @return self
     */
    public function setBandwidthProductSku($bandwidth_product_sku)
    {
        if (is_null($bandwidth_product_sku)) {
            throw new \InvalidArgumentException('non-nullable bandwidth_product_sku cannot be null');
        }
        if ((mb_strlen($bandwidth_product_sku) > 64)) {
            throw new \InvalidArgumentException('invalid length for $bandwidth_product_sku when calling PostProductOrdersNwasRequest., must be smaller than or equal to 64.');
        }

        $this->container['bandwidth_product_sku'] = $bandwidth_product_sku;

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
     * @param string|null $restoration_sla_sku (Optional) Product SKU of the Service Restoration SLA. If omitted, the Standard SLA (\"NBN-SLA-TC4-STD\") is applied. Indicates how quick NBN responds should this service be affected by an issue, depending upon the SLA Region. Response times are Business Hours unless a (24/7) SLA is selected. Enhanced or Premium SLAs may incur an additional monthly cost. Bundled SLA Product SKUs will only be accepted when provided with an eligible Bandwidth Product SKU. Use the Product Qualification > Eligible Restoration SLA Products API to find selectable options for the Location (LOC #).
     *
     * @return self
     */
    public function setRestorationSlaSku($restoration_sla_sku)
    {
        if (is_null($restoration_sla_sku)) {
            throw new \InvalidArgumentException('non-nullable restoration_sla_sku cannot be null');
        }
        if ((mb_strlen($restoration_sla_sku) > 64)) {
            throw new \InvalidArgumentException('invalid length for $restoration_sla_sku when calling PostProductOrdersNwasRequest., must be smaller than or equal to 64.');
        }

        $this->container['restoration_sla_sku'] = $restoration_sla_sku;

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
     * @param string $order_reference Your Reference Number for this order. Max of 35 characters. Only alphabetic characters (\"a-Z\"), numbers (\"0-9\"), underscores (\"_\"), hyphens (\"-\") and periods (\".\") are allowed.  EXAMPLES:  - \"New.Foo_BAR-2020-09-20\"  - \"ExampleCompanyName\"  - \"JaneCitizen-123456\"  - \"Reseller.EndUser.123456\"
     *
     * @return self
     */
    public function setOrderReference($order_reference)
    {
        if (is_null($order_reference)) {
            throw new \InvalidArgumentException('non-nullable order_reference cannot be null');
        }
        if ((mb_strlen($order_reference) > 35)) {
            throw new \InvalidArgumentException('invalid length for $order_reference when calling PostProductOrdersNwasRequest., must be smaller than or equal to 35.');
        }
        if ((!preg_match("/^[A-Za-z0-9._-]+$/", ObjectSerializer::toString($order_reference)))) {
            throw new \InvalidArgumentException("invalid value for \$order_reference when calling PostProductOrdersNwasRequest., must conform to the pattern /^[A-Za-z0-9._-]+$/.");
        }

        $this->container['order_reference'] = $order_reference;

        return $this;
    }

    /**
     * Gets end_user_ref
     *
     * @return string|null
     */
    public function getEndUserRef()
    {
        return $this->container['end_user_ref'];
    }

    /**
     * Sets end_user_ref
     *
     * @param string|null $end_user_ref NOT ACCEPTED: supplying this field returns 422. The platform assigns the End User; this API has no End User (Party) records. The Reference (EUP #) of the End User to associate this Order to.  EXAMPLES:  - \"EUP000000000000\"
     *
     * @return self
     */
    public function setEndUserRef($end_user_ref)
    {
        if (is_null($end_user_ref)) {
            throw new \InvalidArgumentException('non-nullable end_user_ref cannot be null');
        }
        $this->container['end_user_ref'] = $end_user_ref;

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
     * @param bool|null $priority_assist (Optional) Whether Priority Assistance is required. Defaults to \"false\" if value not set. Only specify \"true\" where the end user is eligible for a Downstream Priority Assistance Service in accordance with NBN Co's Wholesale Broadband Agreement. \"true\" is accepted only for a customer on our Priority Assistance register (the end user named in `endUser`, or when none is named the account or your own end user, never another end user's); otherwise the order is refused with a 422 on `priorityAssist` before anything is drafted.
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
     * @param string|null $end_user (Optional) Ours, not the carrier's `endUserRef`: the number of the end user the order is for, as `POST /data/orders` takes it. The order and its service are that end user's. With `priorityAssist` true it is that end user's registration that counts; an end user that is not this account's is refused with a 422 on `priorityAssist`, or on `endUser` without the flag.
     *
     * @return self
     */
    public function setEndUser($end_user)
    {
        if (is_null($end_user)) {
            throw new \InvalidArgumentException('non-nullable end_user cannot be null');
        }
        if ((mb_strlen($end_user) > 64)) {
            throw new \InvalidArgumentException('invalid length for $end_user when calling PostProductOrdersNwasRequest., must be smaller than or equal to 64.');
        }

        $this->container['end_user'] = $end_user;

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
     * @param string|null $appointment_id (Conditional) NBN Appointment ID to attach to this order. An Appointment with Demand Type \"Standard Install\" is required when `ntdId` is \"NEW\" — a technician attends to install the NTD and antenna. Taking a port on an NTD that is already installed requires no appointment.
     *
     * @return self
     */
    public function setAppointmentId($appointment_id)
    {
        if (is_null($appointment_id)) {
            throw new \InvalidArgumentException('non-nullable appointment_id cannot be null');
        }
        if ((mb_strlen($appointment_id) > 48)) {
            throw new \InvalidArgumentException('invalid length for $appointment_id when calling PostProductOrdersNwasRequest., must be smaller than or equal to 48.');
        }

        $this->container['appointment_id'] = $appointment_id;

        return $this;
    }

    /**
     * Gets ntd_id
     *
     * @return string
     */
    public function getNtdId()
    {
        return $this->container['ntd_id'];
    }

    /**
     * Sets ntd_id
     *
     * @param string $ntd_id The Reference Number of the Network Termination Device (NTD) to place the order against. Specify \"NEW\" in this field to order against a new NTD.
     *
     * @return self
     */
    public function setNtdId($ntd_id)
    {
        if (is_null($ntd_id)) {
            throw new \InvalidArgumentException('non-nullable ntd_id cannot be null');
        }
        if ((mb_strlen($ntd_id) > 32)) {
            throw new \InvalidArgumentException('invalid length for $ntd_id when calling PostProductOrdersNwasRequest., must be smaller than or equal to 32.');
        }

        $this->container['ntd_id'] = $ntd_id;

        return $this;
    }

    /**
     * Gets unid_port_id
     *
     * @return string|null
     */
    public function getUnidPortId()
    {
        return $this->container['unid_port_id'];
    }

    /**
     * Sets unid_port_id
     *
     * @param string|null $unid_port_id (Conditional) The Port ID for the UNI-D Resource. Required for Service Transfers or where you want to specify an exact port. NBN will assign the next available port where this field is not supplied or is `null`.
     *
     * @return self
     */
    public function setUnidPortId($unid_port_id)
    {
        if (is_null($unid_port_id)) {
            array_push($this->openAPINullablesSetToNull, 'unid_port_id');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('unid_port_id', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getUnidPortIdAllowableValues();
        if (!is_null($unid_port_id) && !in_array($unid_port_id, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'unid_port_id', must be one of '%s'",
                    $unid_port_id,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['unid_port_id'] = $unid_port_id;

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
     * @param string|null $transfer_type (Conditional) Service Transfer Type. Only applicable to, and is mandatory for Service Transfers.  - \"Service Transfer\" - transfer an existing nbn service from another provider to you.  - \"Service Transfer Reversal\" - recover an existing service that was incorrectly transferred away from you.  - \"Connect Outstanding\" - connect a new service over a line that still has an existing active service not controlled by the End User.  This requires time to legally cancel the existing service.
     *
     * @return self
     */
    public function setTransferType($transfer_type)
    {
        if (is_null($transfer_type)) {
            throw new \InvalidArgumentException('non-nullable transfer_type cannot be null');
        }
        $allowedValues = $this->getTransferTypeAllowableValues();
        if (!in_array($transfer_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'transfer_type', must be one of '%s'",
                    $transfer_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        if ((mb_strlen($transfer_type) > 32)) {
            throw new \InvalidArgumentException('invalid length for $transfer_type when calling PostProductOrdersNwasRequest., must be smaller than or equal to 32.');
        }

        $this->container['transfer_type'] = $transfer_type;

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
     * @param string|null $occupancy_document_id (Optional, ours) On a Connect Outstanding, the `id` of a proof of occupancy uploaded with `POST /data/occupancy-documents`: evidence that the End User occupies the premises. It is kept with the order and used by that order only: a retry with the same `orderReference` may name it again. Another account's, or one another order used, is a 422.
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
     * @param string|null $service_ref (Conditional) Service ID (AVC #) for Service Transfer. Only applicable to, and is mandatory for, Service Transfers.  IMPORTANT > Validate the AVC # against the CPI # or NTD + UNI Port using the SQ Single Site API first.
     *
     * @return self
     */
    public function setServiceRef($service_ref)
    {
        if (is_null($service_ref)) {
            throw new \InvalidArgumentException('non-nullable service_ref cannot be null');
        }
        if ((mb_strlen($service_ref) > 32)) {
            throw new \InvalidArgumentException('invalid length for $service_ref when calling PostProductOrdersNwasRequest., must be smaller than or equal to 32.');
        }

        $this->container['service_ref'] = $service_ref;

        return $this;
    }

    /**
     * Gets customer_authority_date
     *
     * @return \CheckNetworks\Model\PostProductOrdersNcasRequestCustomerAuthorityDate|null
     */
    public function getCustomerAuthorityDate()
    {
        return $this->container['customer_authority_date'];
    }

    /**
     * Sets customer_authority_date
     *
     * @param \CheckNetworks\Model\PostProductOrdersNcasRequestCustomerAuthorityDate|null $customer_authority_date customer_authority_date
     *
     * @return self
     */
    public function setCustomerAuthorityDate($customer_authority_date)
    {
        if (is_null($customer_authority_date)) {
            throw new \InvalidArgumentException('non-nullable customer_authority_date cannot be null');
        }
        $this->container['customer_authority_date'] = $customer_authority_date;

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
     * @param bool $informed_consent Verification that the End User has provided Informed Consent for this order to be placed.
     *
     * @return self
     */
    public function setInformedConsent($informed_consent)
    {
        if (is_null($informed_consent)) {
            throw new \InvalidArgumentException('non-nullable informed_consent cannot be null');
        }
        $this->container['informed_consent'] = $informed_consent;

        return $this;
    }

    /**
     * Gets overlap_period
     *
     * @return string|null
     */
    public function getOverlapPeriod()
    {
        return $this->container['overlap_period'];
    }

    /**
     * Sets overlap_period
     *
     * @param string|null $overlap_period (Optional) The overlap period for Service Transfers. For use when transferring a service to a new UNI-D Port.  The number of hours after Order Completion during which the service will be active on both the old and new ports.  The End User infrastructure should be updated during this period. Only applicable to Service Transfers.
     *
     * @return self
     */
    public function setOverlapPeriod($overlap_period)
    {
        if (is_null($overlap_period)) {
            throw new \InvalidArgumentException('non-nullable overlap_period cannot be null');
        }
        $allowedValues = $this->getOverlapPeriodAllowableValues();
        if (!in_array($overlap_period, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'overlap_period', must be one of '%s'",
                    $overlap_period,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['overlap_period'] = $overlap_period;

        return $this;
    }

    /**
     * Gets from_port_id
     *
     * @return string|null
     */
    public function getFromPortId()
    {
        return $this->container['from_port_id'];
    }

    /**
     * Sets from_port_id
     *
     * @param string|null $from_port_id (Conditional) The UNI-D Port ID of the existing service. Specify the new Port ID via `unidPortId`. Only applicable to Service Transfers and is mandatory if the `overlapPeriod` value is \"4\".
     *
     * @return self
     */
    public function setFromPortId($from_port_id)
    {
        if (is_null($from_port_id)) {
            throw new \InvalidArgumentException('non-nullable from_port_id cannot be null');
        }
        $allowedValues = $this->getFromPortIdAllowableValues();
        if (!in_array($from_port_id, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'from_port_id', must be one of '%s'",
                    $from_port_id,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['from_port_id'] = $from_port_id;

        return $this;
    }

    /**
     * Gets installation_workforce
     *
     * @return string|null
     */
    public function getInstallationWorkforce()
    {
        return $this->container['installation_workforce'];
    }

    /**
     * Sets installation_workforce
     *
     * @param string|null $installation_workforce (Conditional) Installation Workforce for the NTD and/or antenna device. Only applicable where `ntdId` is \"NEW\" — an installed NTD needs no installation.  Defaults to \"NBN Co Install\" when not supplied.
     *
     * @return self
     */
    public function setInstallationWorkforce($installation_workforce)
    {
        if (is_null($installation_workforce)) {
            throw new \InvalidArgumentException('non-nullable installation_workforce cannot be null');
        }
        $allowedValues = $this->getInstallationWorkforceAllowableValues();
        if (!in_array($installation_workforce, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'installation_workforce', must be one of '%s'",
                    $installation_workforce,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['installation_workforce'] = $installation_workforce;

        return $this;
    }

    /**
     * Gets vlan_mode
     *
     * @return string|null
     */
    public function getVlanMode()
    {
        return $this->container['vlan_mode'];
    }

    /**
     * Sets vlan_mode
     *
     * @param string|null $vlan_mode (Optional) The VLAN Mode determines which Product Template will be used when ordering this connection.  IMPORTANT> Do not specify unless explicitly needed.  When this field is not specified, \"Default-Mapped\" will be the assumed value. Intended mainly for transitioning existing \"Tagged\" PPPoE services without needing to change the client CPE configuration.  IMPORTANT> You cannot change this value with a Modify Order. Instead, the service must be cancelled and reordered, potentially bringing the service down for 24-48 hours.
     *
     * @return self
     */
    public function setVlanMode($vlan_mode)
    {
        if (is_null($vlan_mode)) {
            throw new \InvalidArgumentException('non-nullable vlan_mode cannot be null');
        }
        $allowedValues = $this->getVlanModeAllowableValues();
        if (!in_array($vlan_mode, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'vlan_mode', must be one of '%s'",
                    $vlan_mode,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['vlan_mode'] = $vlan_mode;

        return $this;
    }

    /**
     * Gets uni_vlan_id
     *
     * @return int|null
     */
    public function getUniVlanId()
    {
        return $this->container['uni_vlan_id'];
    }

    /**
     * Sets uni_vlan_id
     *
     * @param int|null $uni_vlan_id (Conditional) Required if `vlanMode` is \"Tagged\" The UNI VLAN ID # used to tag the service traffic. The value should be between 1 and 4094.
     *
     * @return self
     */
    public function setUniVlanId($uni_vlan_id)
    {
        if (is_null($uni_vlan_id)) {
            throw new \InvalidArgumentException('non-nullable uni_vlan_id cannot be null');
        }
        if (($uni_vlan_id > 4094)) {
            throw new \InvalidArgumentException('invalid value for $uni_vlan_id when calling PostProductOrdersNwasRequest., must be smaller than or equal to 4094.');
        }
        if (($uni_vlan_id < 1)) {
            throw new \InvalidArgumentException('invalid value for $uni_vlan_id when calling PostProductOrdersNwasRequest., must be bigger than or equal to 1.');
        }

        $this->container['uni_vlan_id'] = $uni_vlan_id;

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


