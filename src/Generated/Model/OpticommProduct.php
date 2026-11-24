<?php
/**
 * OpticommProduct
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
 * OpticommProduct Class Doc Comment
 *
 * @category Class
 * @description An Opticomm service — the carrier&#39;s own npis-product-inventory &#x60;Product&#x60; document, relayed as Opticomm produces it. The differences from the carrier&#39;s response: Check Networks&#39; account and interconnect identifiers are removed, hyperlinks point at this API, and a service we hold suspended carries &#x60;suspensionTakesEffect&#x60;. &#x60;name&#x60; carries YOUR reference (the carrier-side name is Check Networks&#39; own).
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class OpticommProduct implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'OpticommProduct';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'href' => 'string',
        'at_type' => 'string',
        'at_base_type' => 'string',
        'at_referred_type' => 'string',
        'description' => 'string',
        'status' => 'string',
        'suspension_takes_effect' => 'string',
        'name' => 'string',
        'access_service_technology_type' => 'string',
        'is_bundle' => 'string',
        'is_customer_visible' => 'string',
        'order_date' => 'string',
        'start_date' => 'string',
        'termination_date' => 'string',
        'product_serial_number' => 'string',
        'product_term' => 'string',
        'priority_assist' => 'string',
        'order_sla' => 'string',
        'end_user_type' => 'string',
        'oac_type' => 'string',
        'oac_transient_id' => 'string',
        'bandwidth_profile' => 'string',
        'connected_to_oui' => 'string',
        'oui_type' => 'string',
        'vlan_mode' => 'string',
        'speed_and_duplex' => 'string',
        'oui_transient_id' => 'string',
        'osa_id' => 'string',
        'ovc_type' => 'string',
        'tc1_bandwidth_profile' => 'string',
        'tc2_bandwidth_profile' => 'string',
        'tc4_bandwidth_profile' => 'string',
        'tc4_inclusions' => 'string',
        'interface_rate' => 'string',
        'interface_type' => 'string',
        'port_id' => 'mixed',
        'vlan' => 'string',
        'product_specification' => '\CheckNetworks\Api\Generated\Model\OpticommProductProductSpecification',
        'product_ref' => '\CheckNetworks\Api\Generated\Model\OpticommProductRecord[]',
        'place' => '\CheckNetworks\Api\Generated\Model\OpticommProductPlaceInner[]',
        'product_characteristic' => '\CheckNetworks\Api\Generated\Model\OpticommProductProductCharacteristicInner[]',
        'realizing_resource' => '\CheckNetworks\Api\Generated\Model\OpticommProductRealizingResourceInner[]'
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
        'href' => 'uri',
        'at_type' => null,
        'at_base_type' => null,
        'at_referred_type' => null,
        'description' => null,
        'status' => null,
        'suspension_takes_effect' => null,
        'name' => null,
        'access_service_technology_type' => null,
        'is_bundle' => null,
        'is_customer_visible' => null,
        'order_date' => null,
        'start_date' => null,
        'termination_date' => null,
        'product_serial_number' => null,
        'product_term' => null,
        'priority_assist' => null,
        'order_sla' => null,
        'end_user_type' => null,
        'oac_type' => null,
        'oac_transient_id' => null,
        'bandwidth_profile' => null,
        'connected_to_oui' => null,
        'oui_type' => null,
        'vlan_mode' => null,
        'speed_and_duplex' => null,
        'oui_transient_id' => null,
        'osa_id' => null,
        'ovc_type' => null,
        'tc1_bandwidth_profile' => null,
        'tc2_bandwidth_profile' => null,
        'tc4_bandwidth_profile' => null,
        'tc4_inclusions' => null,
        'interface_rate' => null,
        'interface_type' => null,
        'port_id' => null,
        'vlan' => null,
        'product_specification' => null,
        'product_ref' => null,
        'place' => null,
        'product_characteristic' => null,
        'realizing_resource' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'href' => false,
        'at_type' => false,
        'at_base_type' => false,
        'at_referred_type' => false,
        'description' => false,
        'status' => false,
        'suspension_takes_effect' => false,
        'name' => false,
        'access_service_technology_type' => false,
        'is_bundle' => false,
        'is_customer_visible' => false,
        'order_date' => false,
        'start_date' => false,
        'termination_date' => false,
        'product_serial_number' => false,
        'product_term' => false,
        'priority_assist' => false,
        'order_sla' => false,
        'end_user_type' => false,
        'oac_type' => false,
        'oac_transient_id' => false,
        'bandwidth_profile' => false,
        'connected_to_oui' => false,
        'oui_type' => false,
        'vlan_mode' => false,
        'speed_and_duplex' => false,
        'oui_transient_id' => false,
        'osa_id' => false,
        'ovc_type' => false,
        'tc1_bandwidth_profile' => false,
        'tc2_bandwidth_profile' => false,
        'tc4_bandwidth_profile' => false,
        'tc4_inclusions' => false,
        'interface_rate' => false,
        'interface_type' => false,
        'port_id' => true,
        'vlan' => false,
        'product_specification' => false,
        'product_ref' => false,
        'place' => false,
        'product_characteristic' => false,
        'realizing_resource' => false
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
        'href' => 'href',
        'at_type' => '@type',
        'at_base_type' => '@baseType',
        'at_referred_type' => '@referredType',
        'description' => 'description',
        'status' => 'status',
        'suspension_takes_effect' => 'suspensionTakesEffect',
        'name' => 'name',
        'access_service_technology_type' => 'accessServiceTechnologyType',
        'is_bundle' => 'isBundle',
        'is_customer_visible' => 'isCustomerVisible',
        'order_date' => 'orderDate',
        'start_date' => 'startDate',
        'termination_date' => 'terminationDate',
        'product_serial_number' => 'productSerialNumber',
        'product_term' => 'productTerm',
        'priority_assist' => 'priorityAssist',
        'order_sla' => 'orderSla',
        'end_user_type' => 'endUserType',
        'oac_type' => 'oacType',
        'oac_transient_id' => 'oacTransientId',
        'bandwidth_profile' => 'bandwidthProfile',
        'connected_to_oui' => 'connectedToOui',
        'oui_type' => 'ouiType',
        'vlan_mode' => 'vlanMode',
        'speed_and_duplex' => 'speedAndDuplex',
        'oui_transient_id' => 'ouiTransientId',
        'osa_id' => 'osaId',
        'ovc_type' => 'ovcType',
        'tc1_bandwidth_profile' => 'tc1BandwidthProfile',
        'tc2_bandwidth_profile' => 'tc2BandwidthProfile',
        'tc4_bandwidth_profile' => 'tc4BandwidthProfile',
        'tc4_inclusions' => 'tc4Inclusions',
        'interface_rate' => 'InterfaceRate',
        'interface_type' => 'interfaceType',
        'port_id' => 'portId',
        'vlan' => 'vlan',
        'product_specification' => 'productSpecification',
        'product_ref' => 'productRef',
        'place' => 'place',
        'product_characteristic' => 'productCharacteristic',
        'realizing_resource' => 'realizingResource'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'href' => 'setHref',
        'at_type' => 'setAtType',
        'at_base_type' => 'setAtBaseType',
        'at_referred_type' => 'setAtReferredType',
        'description' => 'setDescription',
        'status' => 'setStatus',
        'suspension_takes_effect' => 'setSuspensionTakesEffect',
        'name' => 'setName',
        'access_service_technology_type' => 'setAccessServiceTechnologyType',
        'is_bundle' => 'setIsBundle',
        'is_customer_visible' => 'setIsCustomerVisible',
        'order_date' => 'setOrderDate',
        'start_date' => 'setStartDate',
        'termination_date' => 'setTerminationDate',
        'product_serial_number' => 'setProductSerialNumber',
        'product_term' => 'setProductTerm',
        'priority_assist' => 'setPriorityAssist',
        'order_sla' => 'setOrderSla',
        'end_user_type' => 'setEndUserType',
        'oac_type' => 'setOacType',
        'oac_transient_id' => 'setOacTransientId',
        'bandwidth_profile' => 'setBandwidthProfile',
        'connected_to_oui' => 'setConnectedToOui',
        'oui_type' => 'setOuiType',
        'vlan_mode' => 'setVlanMode',
        'speed_and_duplex' => 'setSpeedAndDuplex',
        'oui_transient_id' => 'setOuiTransientId',
        'osa_id' => 'setOsaId',
        'ovc_type' => 'setOvcType',
        'tc1_bandwidth_profile' => 'setTc1BandwidthProfile',
        'tc2_bandwidth_profile' => 'setTc2BandwidthProfile',
        'tc4_bandwidth_profile' => 'setTc4BandwidthProfile',
        'tc4_inclusions' => 'setTc4Inclusions',
        'interface_rate' => 'setInterfaceRate',
        'interface_type' => 'setInterfaceType',
        'port_id' => 'setPortId',
        'vlan' => 'setVlan',
        'product_specification' => 'setProductSpecification',
        'product_ref' => 'setProductRef',
        'place' => 'setPlace',
        'product_characteristic' => 'setProductCharacteristic',
        'realizing_resource' => 'setRealizingResource'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'href' => 'getHref',
        'at_type' => 'getAtType',
        'at_base_type' => 'getAtBaseType',
        'at_referred_type' => 'getAtReferredType',
        'description' => 'getDescription',
        'status' => 'getStatus',
        'suspension_takes_effect' => 'getSuspensionTakesEffect',
        'name' => 'getName',
        'access_service_technology_type' => 'getAccessServiceTechnologyType',
        'is_bundle' => 'getIsBundle',
        'is_customer_visible' => 'getIsCustomerVisible',
        'order_date' => 'getOrderDate',
        'start_date' => 'getStartDate',
        'termination_date' => 'getTerminationDate',
        'product_serial_number' => 'getProductSerialNumber',
        'product_term' => 'getProductTerm',
        'priority_assist' => 'getPriorityAssist',
        'order_sla' => 'getOrderSla',
        'end_user_type' => 'getEndUserType',
        'oac_type' => 'getOacType',
        'oac_transient_id' => 'getOacTransientId',
        'bandwidth_profile' => 'getBandwidthProfile',
        'connected_to_oui' => 'getConnectedToOui',
        'oui_type' => 'getOuiType',
        'vlan_mode' => 'getVlanMode',
        'speed_and_duplex' => 'getSpeedAndDuplex',
        'oui_transient_id' => 'getOuiTransientId',
        'osa_id' => 'getOsaId',
        'ovc_type' => 'getOvcType',
        'tc1_bandwidth_profile' => 'getTc1BandwidthProfile',
        'tc2_bandwidth_profile' => 'getTc2BandwidthProfile',
        'tc4_bandwidth_profile' => 'getTc4BandwidthProfile',
        'tc4_inclusions' => 'getTc4Inclusions',
        'interface_rate' => 'getInterfaceRate',
        'interface_type' => 'getInterfaceType',
        'port_id' => 'getPortId',
        'vlan' => 'getVlan',
        'product_specification' => 'getProductSpecification',
        'product_ref' => 'getProductRef',
        'place' => 'getPlace',
        'product_characteristic' => 'getProductCharacteristic',
        'realizing_resource' => 'getRealizingResource'
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

    public const STATUS_CREATED = 'created';
    public const STATUS_PENDING_ACTIVE = 'pendingActive';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING_TERMINATE = 'pendingTerminate';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_ABORTED = 'aborted';
    public const SUSPENSION_TAKES_EFFECT_WITHIN_15_MINUTES = 'within 15 minutes';
    public const SUSPENSION_TAKES_EFFECT_WITHIN_1_BUSINESS_DAY = 'within 1 business day';
    public const SUSPENSION_TAKES_EFFECT_CANNOT_BE_SUSPENDED_YET = 'cannot be suspended yet';
    public const PRIORITY_ASSIST_YES = 'Yes';
    public const PRIORITY_ASSIST_NO = 'No';
    public const END_USER_TYPE_RESIDENTIAL = 'Residential';
    public const END_USER_TYPE_BUSINESS = 'Business';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_PENDING_ACTIVE,
            self::STATUS_CANCELLED,
            self::STATUS_ACTIVE,
            self::STATUS_PENDING_TERMINATE,
            self::STATUS_TERMINATED,
            self::STATUS_SUSPENDED,
            self::STATUS_ABORTED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getSuspensionTakesEffectAllowableValues()
    {
        return [
            self::SUSPENSION_TAKES_EFFECT_WITHIN_15_MINUTES,
            self::SUSPENSION_TAKES_EFFECT_WITHIN_1_BUSINESS_DAY,
            self::SUSPENSION_TAKES_EFFECT_CANNOT_BE_SUSPENDED_YET,
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
        $this->setIfExists('href', $data ?? [], null);
        $this->setIfExists('at_type', $data ?? [], null);
        $this->setIfExists('at_base_type', $data ?? [], null);
        $this->setIfExists('at_referred_type', $data ?? [], null);
        $this->setIfExists('description', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('suspension_takes_effect', $data ?? [], null);
        $this->setIfExists('name', $data ?? [], null);
        $this->setIfExists('access_service_technology_type', $data ?? [], null);
        $this->setIfExists('is_bundle', $data ?? [], null);
        $this->setIfExists('is_customer_visible', $data ?? [], null);
        $this->setIfExists('order_date', $data ?? [], null);
        $this->setIfExists('start_date', $data ?? [], null);
        $this->setIfExists('termination_date', $data ?? [], null);
        $this->setIfExists('product_serial_number', $data ?? [], null);
        $this->setIfExists('product_term', $data ?? [], null);
        $this->setIfExists('priority_assist', $data ?? [], null);
        $this->setIfExists('order_sla', $data ?? [], null);
        $this->setIfExists('end_user_type', $data ?? [], null);
        $this->setIfExists('oac_type', $data ?? [], null);
        $this->setIfExists('oac_transient_id', $data ?? [], null);
        $this->setIfExists('bandwidth_profile', $data ?? [], null);
        $this->setIfExists('connected_to_oui', $data ?? [], null);
        $this->setIfExists('oui_type', $data ?? [], null);
        $this->setIfExists('vlan_mode', $data ?? [], null);
        $this->setIfExists('speed_and_duplex', $data ?? [], null);
        $this->setIfExists('oui_transient_id', $data ?? [], null);
        $this->setIfExists('osa_id', $data ?? [], null);
        $this->setIfExists('ovc_type', $data ?? [], null);
        $this->setIfExists('tc1_bandwidth_profile', $data ?? [], null);
        $this->setIfExists('tc2_bandwidth_profile', $data ?? [], null);
        $this->setIfExists('tc4_bandwidth_profile', $data ?? [], null);
        $this->setIfExists('tc4_inclusions', $data ?? [], null);
        $this->setIfExists('interface_rate', $data ?? [], null);
        $this->setIfExists('interface_type', $data ?? [], null);
        $this->setIfExists('port_id', $data ?? [], null);
        $this->setIfExists('vlan', $data ?? [], null);
        $this->setIfExists('product_specification', $data ?? [], null);
        $this->setIfExists('product_ref', $data ?? [], null);
        $this->setIfExists('place', $data ?? [], null);
        $this->setIfExists('product_characteristic', $data ?? [], null);
        $this->setIfExists('realizing_resource', $data ?? [], null);
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

        $allowedValues = $this->getStatusAllowableValues();
        if (!is_null($this->container['status']) && !in_array($this->container['status'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'status', must be one of '%s'",
                $this->container['status'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getSuspensionTakesEffectAllowableValues();
        if (!is_null($this->container['suspension_takes_effect']) && !in_array($this->container['suspension_takes_effect'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'suspension_takes_effect', must be one of '%s'",
                $this->container['suspension_takes_effect'],
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
     * @param string|null $id The Product Instance (OPI #) — the product key across this API.
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
     * Gets href
     *
     * @return string|null
     */
    public function getHref()
    {
        return $this->container['href'];
    }

    /**
     * Sets href
     *
     * @param string|null $href Link to this product on the Check Networks API.
     *
     * @return self
     */
    public function setHref($href)
    {
        if (is_null($href)) {
            throw new \InvalidArgumentException('non-nullable href cannot be null');
        }
        $this->container['href'] = $href;

        return $this;
    }

    /**
     * Gets at_type
     *
     * @return string|null
     */
    public function getAtType()
    {
        return $this->container['at_type'];
    }

    /**
     * Sets at_type
     *
     * @param string|null $at_type The product class — the access technology for an end-user service.
     *
     * @return self
     */
    public function setAtType($at_type)
    {
        if (is_null($at_type)) {
            throw new \InvalidArgumentException('non-nullable at_type cannot be null');
        }
        $this->container['at_type'] = $at_type;

        return $this;
    }

    /**
     * Gets at_base_type
     *
     * @return string|null
     */
    public function getAtBaseType()
    {
        return $this->container['at_base_type'];
    }

    /**
     * Sets at_base_type
     *
     * @param string|null $at_base_type at_base_type
     *
     * @return self
     */
    public function setAtBaseType($at_base_type)
    {
        if (is_null($at_base_type)) {
            throw new \InvalidArgumentException('non-nullable at_base_type cannot be null');
        }
        $this->container['at_base_type'] = $at_base_type;

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
     * @param string|null $at_referred_type at_referred_type
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
     * @param string|null $description description
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
     * @param string|null $status The product lifecycle status, as the carrier holds it.
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
     * Gets suspension_takes_effect
     *
     * @return string|null
     */
    public function getSuspensionTakesEffect()
    {
        return $this->container['suspension_takes_effect'];
    }

    /**
     * Sets suspension_takes_effect
     *
     * @param string|null $suspension_takes_effect Check Networks' own field, present only while we hold the service suspended: how soon the suspension takes effect. `within 1 business day` for an Opticomm L3 service, which Opticomm suspends for us; `within 15 minutes` for a service we cut ourselves. Absent otherwise.
     *
     * @return self
     */
    public function setSuspensionTakesEffect($suspension_takes_effect)
    {
        if (is_null($suspension_takes_effect)) {
            throw new \InvalidArgumentException('non-nullable suspension_takes_effect cannot be null');
        }
        $allowedValues = $this->getSuspensionTakesEffectAllowableValues();
        if (!in_array($suspension_takes_effect, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'suspension_takes_effect', must be one of '%s'",
                    $suspension_takes_effect,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['suspension_takes_effect'] = $suspension_takes_effect;

        return $this;
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
     * @param string|null $name Your reference for the service.
     *
     * @return self
     */
    public function setName($name)
    {
        if (is_null($name)) {
            throw new \InvalidArgumentException('non-nullable name cannot be null');
        }
        $this->container['name'] = $name;

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
     * Gets is_bundle
     *
     * @return string|null
     */
    public function getIsBundle()
    {
        return $this->container['is_bundle'];
    }

    /**
     * Sets is_bundle
     *
     * @param string|null $is_bundle is_bundle
     *
     * @return self
     */
    public function setIsBundle($is_bundle)
    {
        if (is_null($is_bundle)) {
            throw new \InvalidArgumentException('non-nullable is_bundle cannot be null');
        }
        $this->container['is_bundle'] = $is_bundle;

        return $this;
    }

    /**
     * Gets is_customer_visible
     *
     * @return string|null
     */
    public function getIsCustomerVisible()
    {
        return $this->container['is_customer_visible'];
    }

    /**
     * Sets is_customer_visible
     *
     * @param string|null $is_customer_visible is_customer_visible
     *
     * @return self
     */
    public function setIsCustomerVisible($is_customer_visible)
    {
        if (is_null($is_customer_visible)) {
            throw new \InvalidArgumentException('non-nullable is_customer_visible cannot be null');
        }
        $this->container['is_customer_visible'] = $is_customer_visible;

        return $this;
    }

    /**
     * Gets order_date
     *
     * @return string|null
     */
    public function getOrderDate()
    {
        return $this->container['order_date'];
    }

    /**
     * Sets order_date
     *
     * @param string|null $order_date When the product was ordered.
     *
     * @return self
     */
    public function setOrderDate($order_date)
    {
        if (is_null($order_date)) {
            throw new \InvalidArgumentException('non-nullable order_date cannot be null');
        }
        $this->container['order_date'] = $order_date;

        return $this;
    }

    /**
     * Gets start_date
     *
     * @return string|null
     */
    public function getStartDate()
    {
        return $this->container['start_date'];
    }

    /**
     * Sets start_date
     *
     * @param string|null $start_date When the product started.
     *
     * @return self
     */
    public function setStartDate($start_date)
    {
        if (is_null($start_date)) {
            throw new \InvalidArgumentException('non-nullable start_date cannot be null');
        }
        $this->container['start_date'] = $start_date;

        return $this;
    }

    /**
     * Gets termination_date
     *
     * @return string|null
     */
    public function getTerminationDate()
    {
        return $this->container['termination_date'];
    }

    /**
     * Sets termination_date
     *
     * @param string|null $termination_date When the product terminated.
     *
     * @return self
     */
    public function setTerminationDate($termination_date)
    {
        if (is_null($termination_date)) {
            throw new \InvalidArgumentException('non-nullable termination_date cannot be null');
        }
        $this->container['termination_date'] = $termination_date;

        return $this;
    }

    /**
     * Gets product_serial_number
     *
     * @return string|null
     */
    public function getProductSerialNumber()
    {
        return $this->container['product_serial_number'];
    }

    /**
     * Sets product_serial_number
     *
     * @param string|null $product_serial_number product_serial_number
     *
     * @return self
     */
    public function setProductSerialNumber($product_serial_number)
    {
        if (is_null($product_serial_number)) {
            throw new \InvalidArgumentException('non-nullable product_serial_number cannot be null');
        }
        $this->container['product_serial_number'] = $product_serial_number;

        return $this;
    }

    /**
     * Gets product_term
     *
     * @return string|null
     */
    public function getProductTerm()
    {
        return $this->container['product_term'];
    }

    /**
     * Sets product_term
     *
     * @param string|null $product_term product_term
     *
     * @return self
     */
    public function setProductTerm($product_term)
    {
        if (is_null($product_term)) {
            throw new \InvalidArgumentException('non-nullable product_term cannot be null');
        }
        $this->container['product_term'] = $product_term;

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
     * @param string|null $priority_assist priority_assist
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
     * Gets oac_transient_id
     *
     * @return string|null
     */
    public function getOacTransientId()
    {
        return $this->container['oac_transient_id'];
    }

    /**
     * Sets oac_transient_id
     *
     * @param string|null $oac_transient_id oac_transient_id
     *
     * @return self
     */
    public function setOacTransientId($oac_transient_id)
    {
        if (is_null($oac_transient_id)) {
            throw new \InvalidArgumentException('non-nullable oac_transient_id cannot be null');
        }
        $this->container['oac_transient_id'] = $oac_transient_id;

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
     * @param string|null $bandwidth_profile bandwidth_profile
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
     * Gets connected_to_oui
     *
     * @return string|null
     */
    public function getConnectedToOui()
    {
        return $this->container['connected_to_oui'];
    }

    /**
     * Sets connected_to_oui
     *
     * @param string|null $connected_to_oui connected_to_oui
     *
     * @return self
     */
    public function setConnectedToOui($connected_to_oui)
    {
        if (is_null($connected_to_oui)) {
            throw new \InvalidArgumentException('non-nullable connected_to_oui cannot be null');
        }
        $this->container['connected_to_oui'] = $connected_to_oui;

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
     * @param string|null $oui_type oui_type
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
     * @param string|null $osa_id osa_id
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
     * Gets tc1_bandwidth_profile
     *
     * @return string|null
     */
    public function getTc1BandwidthProfile()
    {
        return $this->container['tc1_bandwidth_profile'];
    }

    /**
     * Sets tc1_bandwidth_profile
     *
     * @param string|null $tc1_bandwidth_profile tc1_bandwidth_profile
     *
     * @return self
     */
    public function setTc1BandwidthProfile($tc1_bandwidth_profile)
    {
        if (is_null($tc1_bandwidth_profile)) {
            throw new \InvalidArgumentException('non-nullable tc1_bandwidth_profile cannot be null');
        }
        $this->container['tc1_bandwidth_profile'] = $tc1_bandwidth_profile;

        return $this;
    }

    /**
     * Gets tc2_bandwidth_profile
     *
     * @return string|null
     */
    public function getTc2BandwidthProfile()
    {
        return $this->container['tc2_bandwidth_profile'];
    }

    /**
     * Sets tc2_bandwidth_profile
     *
     * @param string|null $tc2_bandwidth_profile tc2_bandwidth_profile
     *
     * @return self
     */
    public function setTc2BandwidthProfile($tc2_bandwidth_profile)
    {
        if (is_null($tc2_bandwidth_profile)) {
            throw new \InvalidArgumentException('non-nullable tc2_bandwidth_profile cannot be null');
        }
        $this->container['tc2_bandwidth_profile'] = $tc2_bandwidth_profile;

        return $this;
    }

    /**
     * Gets tc4_bandwidth_profile
     *
     * @return string|null
     */
    public function getTc4BandwidthProfile()
    {
        return $this->container['tc4_bandwidth_profile'];
    }

    /**
     * Sets tc4_bandwidth_profile
     *
     * @param string|null $tc4_bandwidth_profile tc4_bandwidth_profile
     *
     * @return self
     */
    public function setTc4BandwidthProfile($tc4_bandwidth_profile)
    {
        if (is_null($tc4_bandwidth_profile)) {
            throw new \InvalidArgumentException('non-nullable tc4_bandwidth_profile cannot be null');
        }
        $this->container['tc4_bandwidth_profile'] = $tc4_bandwidth_profile;

        return $this;
    }

    /**
     * Gets tc4_inclusions
     *
     * @return string|null
     */
    public function getTc4Inclusions()
    {
        return $this->container['tc4_inclusions'];
    }

    /**
     * Sets tc4_inclusions
     *
     * @param string|null $tc4_inclusions tc4_inclusions
     *
     * @return self
     */
    public function setTc4Inclusions($tc4_inclusions)
    {
        if (is_null($tc4_inclusions)) {
            throw new \InvalidArgumentException('non-nullable tc4_inclusions cannot be null');
        }
        $this->container['tc4_inclusions'] = $tc4_inclusions;

        return $this;
    }

    /**
     * Gets interface_rate
     *
     * @return string|null
     */
    public function getInterfaceRate()
    {
        return $this->container['interface_rate'];
    }

    /**
     * Sets interface_rate
     *
     * @param string|null $interface_rate interface_rate
     *
     * @return self
     */
    public function setInterfaceRate($interface_rate)
    {
        if (is_null($interface_rate)) {
            throw new \InvalidArgumentException('non-nullable interface_rate cannot be null');
        }
        $this->container['interface_rate'] = $interface_rate;

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
     * Gets vlan
     *
     * @return string|null
     */
    public function getVlan()
    {
        return $this->container['vlan'];
    }

    /**
     * Sets vlan
     *
     * @param string|null $vlan vlan
     *
     * @return self
     */
    public function setVlan($vlan)
    {
        if (is_null($vlan)) {
            throw new \InvalidArgumentException('non-nullable vlan cannot be null');
        }
        $this->container['vlan'] = $vlan;

        return $this;
    }

    /**
     * Gets product_specification
     *
     * @return \CheckNetworks\Api\Generated\Model\OpticommProductProductSpecification|null
     */
    public function getProductSpecification()
    {
        return $this->container['product_specification'];
    }

    /**
     * Sets product_specification
     *
     * @param \CheckNetworks\Api\Generated\Model\OpticommProductProductSpecification|null $product_specification product_specification
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
     * Gets product_ref
     *
     * @return \CheckNetworks\Api\Generated\Model\OpticommProductRecord[]|null
     */
    public function getProductRef()
    {
        return $this->container['product_ref'];
    }

    /**
     * Sets product_ref
     *
     * @param \CheckNetworks\Api\Generated\Model\OpticommProductRecord[]|null $product_ref The realised records under this product — access service (OAC), NTD (OTD) and, nested within it, the NTD ports (OUI).
     *
     * @return self
     */
    public function setProductRef($product_ref)
    {
        if (is_null($product_ref)) {
            throw new \InvalidArgumentException('non-nullable product_ref cannot be null');
        }
        $this->container['product_ref'] = $product_ref;

        return $this;
    }

    /**
     * Gets place
     *
     * @return \CheckNetworks\Api\Generated\Model\OpticommProductPlaceInner[]|null
     */
    public function getPlace()
    {
        return $this->container['place'];
    }

    /**
     * Sets place
     *
     * @param \CheckNetworks\Api\Generated\Model\OpticommProductPlaceInner[]|null $place The property the service is delivered at.
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
     * Gets product_characteristic
     *
     * @return \CheckNetworks\Api\Generated\Model\OpticommProductProductCharacteristicInner[]|null
     */
    public function getProductCharacteristic()
    {
        return $this->container['product_characteristic'];
    }

    /**
     * Sets product_characteristic
     *
     * @param \CheckNetworks\Api\Generated\Model\OpticommProductProductCharacteristicInner[]|null $product_characteristic product_characteristic
     *
     * @return self
     */
    public function setProductCharacteristic($product_characteristic)
    {
        if (is_null($product_characteristic)) {
            throw new \InvalidArgumentException('non-nullable product_characteristic cannot be null');
        }
        $this->container['product_characteristic'] = $product_characteristic;

        return $this;
    }

    /**
     * Gets realizing_resource
     *
     * @return \CheckNetworks\Api\Generated\Model\OpticommProductRealizingResourceInner[]|null
     */
    public function getRealizingResource()
    {
        return $this->container['realizing_resource'];
    }

    /**
     * Sets realizing_resource
     *
     * @param \CheckNetworks\Api\Generated\Model\OpticommProductRealizingResourceInner[]|null $realizing_resource realizing_resource
     *
     * @return self
     */
    public function setRealizingResource($realizing_resource)
    {
        if (is_null($realizing_resource)) {
            throw new \InvalidArgumentException('non-nullable realizing_resource cannot be null');
        }
        $this->container['realizing_resource'] = $realizing_resource;

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


