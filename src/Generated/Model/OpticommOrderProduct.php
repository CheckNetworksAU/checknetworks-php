<?php
/**
 * OpticommOrderProduct
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
 * OpticommOrderProduct Class Doc Comment
 *
 * @category Class
 * @description A product / resource / service record inside an Opticomm order — the carrier&#39;s own record shape, minus Check Networks&#39; upstream account and interconnect identifiers.
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class OpticommOrderProduct implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'OpticommOrderProduct';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'at_referred_type' => 'string',
        'product_specification' => '\CheckNetworks\Api\Generated\Model\OpticommOrderProductProductSpecification',
        'oui_type' => 'string',
        'port_id' => 'mixed',
        'transfer_type' => 'string',
        'customer_authority_date' => 'string',
        'priority_assist' => 'string',
        'order_sla' => 'string',
        'end_user_type' => 'string',
        'speed_and_duplex' => 'string',
        'interface_type' => 'string',
        'oui_transient_id' => 'string',
        'oac_type' => 'string',
        'ovc_type' => 'string',
        'access_service_technology_type' => 'string',
        'osa_id' => 'string',
        'bandwidth_profile' => 'string',
        'bandwidth_limit' => 'int',
        'vlan_mode' => 'string'
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
        'at_referred_type' => null,
        'product_specification' => null,
        'oui_type' => null,
        'port_id' => null,
        'transfer_type' => null,
        'customer_authority_date' => null,
        'priority_assist' => null,
        'order_sla' => null,
        'end_user_type' => null,
        'speed_and_duplex' => null,
        'interface_type' => null,
        'oui_transient_id' => null,
        'oac_type' => null,
        'ovc_type' => null,
        'access_service_technology_type' => null,
        'osa_id' => null,
        'bandwidth_profile' => null,
        'bandwidth_limit' => null,
        'vlan_mode' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'at_referred_type' => false,
        'product_specification' => false,
        'oui_type' => false,
        'port_id' => true,
        'transfer_type' => false,
        'customer_authority_date' => false,
        'priority_assist' => false,
        'order_sla' => false,
        'end_user_type' => false,
        'speed_and_duplex' => false,
        'interface_type' => false,
        'oui_transient_id' => false,
        'oac_type' => false,
        'ovc_type' => false,
        'access_service_technology_type' => false,
        'osa_id' => false,
        'bandwidth_profile' => false,
        'bandwidth_limit' => false,
        'vlan_mode' => false
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
        'at_referred_type' => '@referredType',
        'product_specification' => 'productSpecification',
        'oui_type' => 'ouiType',
        'port_id' => 'portId',
        'transfer_type' => 'transferType',
        'customer_authority_date' => 'customerAuthorityDate',
        'priority_assist' => 'priorityAssist',
        'order_sla' => 'orderSla',
        'end_user_type' => 'endUserType',
        'speed_and_duplex' => 'speedAndDuplex',
        'interface_type' => 'interfaceType',
        'oui_transient_id' => 'ouiTransientId',
        'oac_type' => 'oacType',
        'ovc_type' => 'ovcType',
        'access_service_technology_type' => 'accessServiceTechnologyType',
        'osa_id' => 'osaId',
        'bandwidth_profile' => 'bandwidthProfile',
        'bandwidth_limit' => 'bandwidth-limit',
        'vlan_mode' => 'vlanMode'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'at_referred_type' => 'setAtReferredType',
        'product_specification' => 'setProductSpecification',
        'oui_type' => 'setOuiType',
        'port_id' => 'setPortId',
        'transfer_type' => 'setTransferType',
        'customer_authority_date' => 'setCustomerAuthorityDate',
        'priority_assist' => 'setPriorityAssist',
        'order_sla' => 'setOrderSla',
        'end_user_type' => 'setEndUserType',
        'speed_and_duplex' => 'setSpeedAndDuplex',
        'interface_type' => 'setInterfaceType',
        'oui_transient_id' => 'setOuiTransientId',
        'oac_type' => 'setOacType',
        'ovc_type' => 'setOvcType',
        'access_service_technology_type' => 'setAccessServiceTechnologyType',
        'osa_id' => 'setOsaId',
        'bandwidth_profile' => 'setBandwidthProfile',
        'bandwidth_limit' => 'setBandwidthLimit',
        'vlan_mode' => 'setVlanMode'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'at_referred_type' => 'getAtReferredType',
        'product_specification' => 'getProductSpecification',
        'oui_type' => 'getOuiType',
        'port_id' => 'getPortId',
        'transfer_type' => 'getTransferType',
        'customer_authority_date' => 'getCustomerAuthorityDate',
        'priority_assist' => 'getPriorityAssist',
        'order_sla' => 'getOrderSla',
        'end_user_type' => 'getEndUserType',
        'speed_and_duplex' => 'getSpeedAndDuplex',
        'interface_type' => 'getInterfaceType',
        'oui_transient_id' => 'getOuiTransientId',
        'oac_type' => 'getOacType',
        'ovc_type' => 'getOvcType',
        'access_service_technology_type' => 'getAccessServiceTechnologyType',
        'osa_id' => 'getOsaId',
        'bandwidth_profile' => 'getBandwidthProfile',
        'bandwidth_limit' => 'getBandwidthLimit',
        'vlan_mode' => 'getVlanMode'
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

    public const TRANSFER_TYPE_SERVICE_TRANSFER = 'Service Transfer';
    public const TRANSFER_TYPE_SERVICE_TRANSFER_REVERSAL = 'Service Transfer Reversal';
    public const PRIORITY_ASSIST_YES = 'Yes';
    public const PRIORITY_ASSIST_NO = 'No';
    public const END_USER_TYPE_RESIDENTIAL = 'Residential';
    public const END_USER_TYPE_BUSINESS = 'Business';

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
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getPriorityAssistAllowableValues()
    {
        return [
            self::PRIORITY_ASSIST_YES,
            self::PRIORITY_ASSIST_NO,
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
        $this->setIfExists('at_referred_type', $data ?? [], null);
        $this->setIfExists('product_specification', $data ?? [], null);
        $this->setIfExists('oui_type', $data ?? [], null);
        $this->setIfExists('port_id', $data ?? [], null);
        $this->setIfExists('transfer_type', $data ?? [], null);
        $this->setIfExists('customer_authority_date', $data ?? [], null);
        $this->setIfExists('priority_assist', $data ?? [], null);
        $this->setIfExists('order_sla', $data ?? [], null);
        $this->setIfExists('end_user_type', $data ?? [], null);
        $this->setIfExists('speed_and_duplex', $data ?? [], null);
        $this->setIfExists('interface_type', $data ?? [], null);
        $this->setIfExists('oui_transient_id', $data ?? [], null);
        $this->setIfExists('oac_type', $data ?? [], null);
        $this->setIfExists('ovc_type', $data ?? [], null);
        $this->setIfExists('access_service_technology_type', $data ?? [], null);
        $this->setIfExists('osa_id', $data ?? [], null);
        $this->setIfExists('bandwidth_profile', $data ?? [], null);
        $this->setIfExists('bandwidth_limit', $data ?? [], null);
        $this->setIfExists('vlan_mode', $data ?? [], null);
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

        $allowedValues = $this->getTransferTypeAllowableValues();
        if (!is_null($this->container['transfer_type']) && !in_array($this->container['transfer_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'transfer_type', must be one of '%s'",
                $this->container['transfer_type'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getPriorityAssistAllowableValues();
        if (!is_null($this->container['priority_assist']) && !in_array($this->container['priority_assist'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'priority_assist', must be one of '%s'",
                $this->container['priority_assist'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getEndUserTypeAllowableValues();
        if (!is_null($this->container['end_user_type']) && !in_array($this->container['end_user_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'end_user_type', must be one of '%s'",
                $this->container['end_user_type'],
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
     * @param string|null $id Identifier of the product: the Product Instance (OPI #), NTD (OTD #), NTD port (OUI #) or access service (OAC #).
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
     * Gets at_referred_type
     *
     * @return string|null
     */
    public function getAtReferredType()
    {
        return $this->container['at_referred_type'];
    }

    /**
     * Sets at_referred_type
     *
     * @param string|null $at_referred_type Indicates the (class) type of resource.
     *
     * @return self
     */
    public function setAtReferredType($at_referred_type)
    {
        if (is_null($at_referred_type)) {
            throw new \InvalidArgumentException('non-nullable at_referred_type cannot be null');
        }
        $this->container['at_referred_type'] = $at_referred_type;

        return $this;
    }

    /**
     * Gets product_specification
     *
     * @return \CheckNetworks\Api\Generated\Model\OpticommOrderProductProductSpecification|null
     */
    public function getProductSpecification()
    {
        return $this->container['product_specification'];
    }

    /**
     * Sets product_specification
     *
     * @param \CheckNetworks\Api\Generated\Model\OpticommOrderProductProductSpecification|null $product_specification product_specification
     *
     * @return self
     */
    public function setProductSpecification($product_specification)
    {
        if (is_null($product_specification)) {
            throw new \InvalidArgumentException('non-nullable product_specification cannot be null');
        }
        $this->container['product_specification'] = $product_specification;

        return $this;
    }

    /**
     * Gets oui_type
     *
     * @return string|null
     */
    public function getOuiType()
    {
        return $this->container['oui_type'];
    }

    /**
     * Sets oui_type
     *
     * @param string|null $oui_type OUI-V or OUI-D.
     *
     * @return self
     */
    public function setOuiType($oui_type)
    {
        if (is_null($oui_type)) {
            throw new \InvalidArgumentException('non-nullable oui_type cannot be null');
        }
        $this->container['oui_type'] = $oui_type;

        return $this;
    }

    /**
     * Gets port_id
     *
     * @return mixed|null
     */
    public function getPortId()
    {
        return $this->container['port_id'];
    }

    /**
     * Sets port_id
     *
     * @param mixed|null $port_id The NTD port number.
     *
     * @return self
     */
    public function setPortId($port_id)
    {
        if (is_null($port_id)) {
            array_push($this->openAPINullablesSetToNull, 'port_id');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('port_id', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['port_id'] = $port_id;

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
     * @param string|null $transfer_type transfer_type
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
        $this->container['transfer_type'] = $transfer_type;

        return $this;
    }

    /**
     * Gets customer_authority_date
     *
     * @return string|null
     */
    public function getCustomerAuthorityDate()
    {
        return $this->container['customer_authority_date'];
    }

    /**
     * Sets customer_authority_date
     *
     * @param string|null $customer_authority_date The date customer authority was obtained (transfers).
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
     * Gets priority_assist
     *
     * @return string|null
     */
    public function getPriorityAssist()
    {
        return $this->container['priority_assist'];
    }

    /**
     * Sets priority_assist
     *
     * @param string|null $priority_assist The carrier's priority assist flag. On a Connect, \"Yes\" is accepted only for a customer on our Priority Assistance register; otherwise the order is refused with a 422 on `priorityAssist` before anything is drafted.
     *
     * @return self
     */
    public function setPriorityAssist($priority_assist)
    {
        if (is_null($priority_assist)) {
            throw new \InvalidArgumentException('non-nullable priority_assist cannot be null');
        }
        $allowedValues = $this->getPriorityAssistAllowableValues();
        if (!in_array($priority_assist, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'priority_assist', must be one of '%s'",
                    $priority_assist,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['priority_assist'] = $priority_assist;

        return $this;
    }

    /**
     * Gets order_sla
     *
     * @return string|null
     */
    public function getOrderSla()
    {
        return $this->container['order_sla'];
    }

    /**
     * Sets order_sla
     *
     * @param string|null $order_sla order_sla
     *
     * @return self
     */
    public function setOrderSla($order_sla)
    {
        if (is_null($order_sla)) {
            throw new \InvalidArgumentException('non-nullable order_sla cannot be null');
        }
        $this->container['order_sla'] = $order_sla;

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
     * @param string|null $end_user_type end_user_type
     *
     * @return self
     */
    public function setEndUserType($end_user_type)
    {
        if (is_null($end_user_type)) {
            throw new \InvalidArgumentException('non-nullable end_user_type cannot be null');
        }
        $allowedValues = $this->getEndUserTypeAllowableValues();
        if (!in_array($end_user_type, $allowedValues, true)) {
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
     * Gets speed_and_duplex
     *
     * @return string|null
     */
    public function getSpeedAndDuplex()
    {
        return $this->container['speed_and_duplex'];
    }

    /**
     * Sets speed_and_duplex
     *
     * @param string|null $speed_and_duplex speed_and_duplex
     *
     * @return self
     */
    public function setSpeedAndDuplex($speed_and_duplex)
    {
        if (is_null($speed_and_duplex)) {
            throw new \InvalidArgumentException('non-nullable speed_and_duplex cannot be null');
        }
        $this->container['speed_and_duplex'] = $speed_and_duplex;

        return $this;
    }

    /**
     * Gets interface_type
     *
     * @return string|null
     */
    public function getInterfaceType()
    {
        return $this->container['interface_type'];
    }

    /**
     * Sets interface_type
     *
     * @param string|null $interface_type interface_type
     *
     * @return self
     */
    public function setInterfaceType($interface_type)
    {
        if (is_null($interface_type)) {
            throw new \InvalidArgumentException('non-nullable interface_type cannot be null');
        }
        $this->container['interface_type'] = $interface_type;

        return $this;
    }

    /**
     * Gets oui_transient_id
     *
     * @return string|null
     */
    public function getOuiTransientId()
    {
        return $this->container['oui_transient_id'];
    }

    /**
     * Sets oui_transient_id
     *
     * @param string|null $oui_transient_id oui_transient_id
     *
     * @return self
     */
    public function setOuiTransientId($oui_transient_id)
    {
        if (is_null($oui_transient_id)) {
            throw new \InvalidArgumentException('non-nullable oui_transient_id cannot be null');
        }
        $this->container['oui_transient_id'] = $oui_transient_id;

        return $this;
    }

    /**
     * Gets oac_type
     *
     * @return string|null
     */
    public function getOacType()
    {
        return $this->container['oac_type'];
    }

    /**
     * Sets oac_type
     *
     * @param string|null $oac_type oac_type
     *
     * @return self
     */
    public function setOacType($oac_type)
    {
        if (is_null($oac_type)) {
            throw new \InvalidArgumentException('non-nullable oac_type cannot be null');
        }
        $this->container['oac_type'] = $oac_type;

        return $this;
    }

    /**
     * Gets ovc_type
     *
     * @return string|null
     */
    public function getOvcType()
    {
        return $this->container['ovc_type'];
    }

    /**
     * Sets ovc_type
     *
     * @param string|null $ovc_type ovc_type
     *
     * @return self
     */
    public function setOvcType($ovc_type)
    {
        if (is_null($ovc_type)) {
            throw new \InvalidArgumentException('non-nullable ovc_type cannot be null');
        }
        $this->container['ovc_type'] = $ovc_type;

        return $this;
    }

    /**
     * Gets access_service_technology_type
     *
     * @return string|null
     */
    public function getAccessServiceTechnologyType()
    {
        return $this->container['access_service_technology_type'];
    }

    /**
     * Sets access_service_technology_type
     *
     * @param string|null $access_service_technology_type access_service_technology_type
     *
     * @return self
     */
    public function setAccessServiceTechnologyType($access_service_technology_type)
    {
        if (is_null($access_service_technology_type)) {
            throw new \InvalidArgumentException('non-nullable access_service_technology_type cannot be null');
        }
        $this->container['access_service_technology_type'] = $access_service_technology_type;

        return $this;
    }

    /**
     * Gets osa_id
     *
     * @return string|null
     */
    public function getOsaId()
    {
        return $this->container['osa_id'];
    }

    /**
     * Sets osa_id
     *
     * @param string|null $osa_id The Opticomm Service Area (OSA #).
     *
     * @return self
     */
    public function setOsaId($osa_id)
    {
        if (is_null($osa_id)) {
            throw new \InvalidArgumentException('non-nullable osa_id cannot be null');
        }
        $this->container['osa_id'] = $osa_id;

        return $this;
    }

    /**
     * Gets bandwidth_profile
     *
     * @return string|null
     */
    public function getBandwidthProfile()
    {
        return $this->container['bandwidth_profile'];
    }

    /**
     * Sets bandwidth_profile
     *
     * @param string|null $bandwidth_profile The Bandwidth Product SKU.
     *
     * @return self
     */
    public function setBandwidthProfile($bandwidth_profile)
    {
        if (is_null($bandwidth_profile)) {
            throw new \InvalidArgumentException('non-nullable bandwidth_profile cannot be null');
        }
        $this->container['bandwidth_profile'] = $bandwidth_profile;

        return $this;
    }

    /**
     * Gets bandwidth_limit
     *
     * @return int|null
     */
    public function getBandwidthLimit()
    {
        return $this->container['bandwidth_limit'];
    }

    /**
     * Sets bandwidth_limit
     *
     * @param int|null $bandwidth_limit bandwidth_limit
     *
     * @return self
     */
    public function setBandwidthLimit($bandwidth_limit)
    {
        if (is_null($bandwidth_limit)) {
            throw new \InvalidArgumentException('non-nullable bandwidth_limit cannot be null');
        }
        $this->container['bandwidth_limit'] = $bandwidth_limit;

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
     * @param string|null $vlan_mode vlan_mode
     *
     * @return self
     */
    public function setVlanMode($vlan_mode)
    {
        if (is_null($vlan_mode)) {
            throw new \InvalidArgumentException('non-nullable vlan_mode cannot be null');
        }
        $this->container['vlan_mode'] = $vlan_mode;

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


