<?php
/**
 * UpdateNumber200ResponseResult
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
 * UpdateNumber200ResponseResult Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class UpdateNumber200ResponseResult implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'update_number_200_response_result';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'number' => 'string',
        'description' => 'string',
        'external_ref' => 'string',
        'metadata' => 'array<string,string>',
        'route_type' => 'string',
        'route_target' => 'string',
        'trunk_type' => 'string',
        'routing_steps' => 'int',
        'has_advanced_routing' => 'bool',
        'routing' => '\CheckNetworksModelNumberRoutingStep[]',
        'fax_enabled' => 'bool',
        'fax_plan_id' => 'string',
        'sms_enabled' => 'bool',
        'sms_delivery' => '\CheckNetworks\Model\SmsDelivery',
        'status' => 'string',
        'emergency_address_id' => 'string',
        'ipnd' => '\CheckNetworks\Model\DidNumberIpnd',
        'port_out_code' => 'string',
        'end_user' => 'string',
        'monthly_cost' => 'string',
        'monthly_cost_ex_gst' => 'string',
        'monthly_cost_gst' => 'string',
        'monthly_cost_inc_gst' => 'string',
        'currency' => 'string',
        'created_at' => '\DateTime',
        'updated_at' => '\DateTime',
        'emergency_address_change' => '\CheckNetworks\Model\UpdateNumber200ResponseResultAllOfEmergencyAddressChange'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'number' => null,
        'description' => null,
        'external_ref' => null,
        'metadata' => null,
        'route_type' => null,
        'route_target' => null,
        'trunk_type' => null,
        'routing_steps' => null,
        'has_advanced_routing' => null,
        'routing' => null,
        'fax_enabled' => null,
        'fax_plan_id' => 'uuid',
        'sms_enabled' => null,
        'sms_delivery' => null,
        'status' => null,
        'emergency_address_id' => 'uuid',
        'ipnd' => null,
        'port_out_code' => null,
        'end_user' => null,
        'monthly_cost' => null,
        'monthly_cost_ex_gst' => null,
        'monthly_cost_gst' => null,
        'monthly_cost_inc_gst' => null,
        'currency' => null,
        'created_at' => 'date-time',
        'updated_at' => 'date-time',
        'emergency_address_change' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'number' => false,
        'description' => true,
        'external_ref' => true,
        'metadata' => false,
        'route_type' => true,
        'route_target' => true,
        'trunk_type' => true,
        'routing_steps' => false,
        'has_advanced_routing' => false,
        'routing' => false,
        'fax_enabled' => false,
        'fax_plan_id' => true,
        'sms_enabled' => false,
        'sms_delivery' => true,
        'status' => false,
        'emergency_address_id' => true,
        'ipnd' => true,
        'port_out_code' => true,
        'end_user' => true,
        'monthly_cost' => true,
        'monthly_cost_ex_gst' => true,
        'monthly_cost_gst' => true,
        'monthly_cost_inc_gst' => true,
        'currency' => false,
        'created_at' => true,
        'updated_at' => true,
        'emergency_address_change' => false
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
        'number' => 'number',
        'description' => 'description',
        'external_ref' => 'externalRef',
        'metadata' => 'metadata',
        'route_type' => 'routeType',
        'route_target' => 'routeTarget',
        'trunk_type' => 'trunkType',
        'routing_steps' => 'routingSteps',
        'has_advanced_routing' => 'hasAdvancedRouting',
        'routing' => 'routing',
        'fax_enabled' => 'faxEnabled',
        'fax_plan_id' => 'faxPlanId',
        'sms_enabled' => 'smsEnabled',
        'sms_delivery' => 'smsDelivery',
        'status' => 'status',
        'emergency_address_id' => 'emergencyAddressId',
        'ipnd' => 'ipnd',
        'port_out_code' => 'portOutCode',
        'end_user' => 'endUser',
        'monthly_cost' => 'monthlyCost',
        'monthly_cost_ex_gst' => 'monthlyCostExGst',
        'monthly_cost_gst' => 'monthlyCostGst',
        'monthly_cost_inc_gst' => 'monthlyCostIncGst',
        'currency' => 'currency',
        'created_at' => 'createdAt',
        'updated_at' => 'updatedAt',
        'emergency_address_change' => 'emergencyAddressChange'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'number' => 'setNumber',
        'description' => 'setDescription',
        'external_ref' => 'setExternalRef',
        'metadata' => 'setMetadata',
        'route_type' => 'setRouteType',
        'route_target' => 'setRouteTarget',
        'trunk_type' => 'setTrunkType',
        'routing_steps' => 'setRoutingSteps',
        'has_advanced_routing' => 'setHasAdvancedRouting',
        'routing' => 'setRouting',
        'fax_enabled' => 'setFaxEnabled',
        'fax_plan_id' => 'setFaxPlanId',
        'sms_enabled' => 'setSmsEnabled',
        'sms_delivery' => 'setSmsDelivery',
        'status' => 'setStatus',
        'emergency_address_id' => 'setEmergencyAddressId',
        'ipnd' => 'setIpnd',
        'port_out_code' => 'setPortOutCode',
        'end_user' => 'setEndUser',
        'monthly_cost' => 'setMonthlyCost',
        'monthly_cost_ex_gst' => 'setMonthlyCostExGst',
        'monthly_cost_gst' => 'setMonthlyCostGst',
        'monthly_cost_inc_gst' => 'setMonthlyCostIncGst',
        'currency' => 'setCurrency',
        'created_at' => 'setCreatedAt',
        'updated_at' => 'setUpdatedAt',
        'emergency_address_change' => 'setEmergencyAddressChange'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'number' => 'getNumber',
        'description' => 'getDescription',
        'external_ref' => 'getExternalRef',
        'metadata' => 'getMetadata',
        'route_type' => 'getRouteType',
        'route_target' => 'getRouteTarget',
        'trunk_type' => 'getTrunkType',
        'routing_steps' => 'getRoutingSteps',
        'has_advanced_routing' => 'getHasAdvancedRouting',
        'routing' => 'getRouting',
        'fax_enabled' => 'getFaxEnabled',
        'fax_plan_id' => 'getFaxPlanId',
        'sms_enabled' => 'getSmsEnabled',
        'sms_delivery' => 'getSmsDelivery',
        'status' => 'getStatus',
        'emergency_address_id' => 'getEmergencyAddressId',
        'ipnd' => 'getIpnd',
        'port_out_code' => 'getPortOutCode',
        'end_user' => 'getEndUser',
        'monthly_cost' => 'getMonthlyCost',
        'monthly_cost_ex_gst' => 'getMonthlyCostExGst',
        'monthly_cost_gst' => 'getMonthlyCostGst',
        'monthly_cost_inc_gst' => 'getMonthlyCostIncGst',
        'currency' => 'getCurrency',
        'created_at' => 'getCreatedAt',
        'updated_at' => 'getUpdatedAt',
        'emergency_address_change' => 'getEmergencyAddressChange'
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

    public const ROUTE_TYPE_TRUNK = 'trunk';
    public const ROUTE_TYPE_REDIRECT = 'redirect';
    public const ROUTE_TYPE_SIP_ADDRESS = 'sip_address';
    public const ROUTE_TYPE_FAX2EMAIL = 'fax2email';
    public const ROUTE_TYPE_DISCONNECTED = 'disconnected';
    public const ROUTE_TYPE_TIME_OF_DAY = 'time_of_day';
    public const ROUTE_TYPE_GEOGRAPHIC = 'geographic';
    public const ROUTE_TYPE_ROUTE_TO_SERVER = 'route_to_server';
    public const TRUNK_TYPE_TRUNK = 'trunk';
    public const TRUNK_TYPE_RESIDENTIAL_LINE = 'residentialLine';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_RELEASING = 'releasing';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getRouteTypeAllowableValues()
    {
        return [
            self::ROUTE_TYPE_TRUNK,
            self::ROUTE_TYPE_REDIRECT,
            self::ROUTE_TYPE_SIP_ADDRESS,
            self::ROUTE_TYPE_FAX2EMAIL,
            self::ROUTE_TYPE_DISCONNECTED,
            self::ROUTE_TYPE_TIME_OF_DAY,
            self::ROUTE_TYPE_GEOGRAPHIC,
            self::ROUTE_TYPE_ROUTE_TO_SERVER,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getTrunkTypeAllowableValues()
    {
        return [
            self::TRUNK_TYPE_TRUNK,
            self::TRUNK_TYPE_RESIDENTIAL_LINE,
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
            self::STATUS_ACTIVE,
            self::STATUS_SUSPENDED,
            self::STATUS_RELEASING,
            self::STATUS_CANCELLED,
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
        $this->setIfExists('number', $data ?? [], null);
        $this->setIfExists('description', $data ?? [], null);
        $this->setIfExists('external_ref', $data ?? [], null);
        $this->setIfExists('metadata', $data ?? [], null);
        $this->setIfExists('route_type', $data ?? [], null);
        $this->setIfExists('route_target', $data ?? [], null);
        $this->setIfExists('trunk_type', $data ?? [], null);
        $this->setIfExists('routing_steps', $data ?? [], null);
        $this->setIfExists('has_advanced_routing', $data ?? [], null);
        $this->setIfExists('routing', $data ?? [], null);
        $this->setIfExists('fax_enabled', $data ?? [], null);
        $this->setIfExists('fax_plan_id', $data ?? [], null);
        $this->setIfExists('sms_enabled', $data ?? [], null);
        $this->setIfExists('sms_delivery', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('emergency_address_id', $data ?? [], null);
        $this->setIfExists('ipnd', $data ?? [], null);
        $this->setIfExists('port_out_code', $data ?? [], null);
        $this->setIfExists('end_user', $data ?? [], null);
        $this->setIfExists('monthly_cost', $data ?? [], null);
        $this->setIfExists('monthly_cost_ex_gst', $data ?? [], null);
        $this->setIfExists('monthly_cost_gst', $data ?? [], null);
        $this->setIfExists('monthly_cost_inc_gst', $data ?? [], null);
        $this->setIfExists('currency', $data ?? [], null);
        $this->setIfExists('created_at', $data ?? [], null);
        $this->setIfExists('updated_at', $data ?? [], null);
        $this->setIfExists('emergency_address_change', $data ?? [], null);
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

        if ($this->container['number'] === null) {
            $invalidProperties[] = "'number' can't be null";
        }
        if ($this->container['description'] === null && !$this->isNullableSetToNull('description')) {
            $invalidProperties[] = "'description' is required";
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

        if (!is_null($this->container['metadata']) && (count($this->container['metadata']) > 20)) {
            $invalidProperties[] = "invalid value for 'metadata', number of items must be less than or equal to 20.";
        }

        if ($this->container['route_type'] === null && !$this->isNullableSetToNull('route_type')) {
            $invalidProperties[] = "'route_type' is required";
        }
        $allowedValues = $this->getRouteTypeAllowableValues();
        if (!is_null($this->container['route_type']) && !in_array($this->container['route_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'route_type', must be one of '%s'",
                $this->container['route_type'],
                implode("', '", $allowedValues)
            );
        }

        if ($this->container['route_target'] === null && !$this->isNullableSetToNull('route_target')) {
            $invalidProperties[] = "'route_target' is required";
        }
        $allowedValues = $this->getTrunkTypeAllowableValues();
        if (!is_null($this->container['trunk_type']) && !in_array($this->container['trunk_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'trunk_type', must be one of '%s'",
                $this->container['trunk_type'],
                implode("', '", $allowedValues)
            );
        }

        if ($this->container['routing_steps'] === null) {
            $invalidProperties[] = "'routing_steps' can't be null";
        }
        if (($this->container['routing_steps'] < 0)) {
            $invalidProperties[] = "invalid value for 'routing_steps', must be bigger than or equal to 0.";
        }

        if ($this->container['has_advanced_routing'] === null) {
            $invalidProperties[] = "'has_advanced_routing' can't be null";
        }
        if ($this->container['routing'] === null) {
            $invalidProperties[] = "'routing' can't be null";
        }
        if ($this->container['fax_enabled'] === null) {
            $invalidProperties[] = "'fax_enabled' can't be null";
        }
        if ($this->container['fax_plan_id'] === null && !$this->isNullableSetToNull('fax_plan_id')) {
            $invalidProperties[] = "'fax_plan_id' is required";
        }
        if ($this->container['sms_enabled'] === null) {
            $invalidProperties[] = "'sms_enabled' can't be null";
        }
        if ($this->container['sms_delivery'] === null && !$this->isNullableSetToNull('sms_delivery')) {
            $invalidProperties[] = "'sms_delivery' is required";
        }
        if ($this->container['status'] === null) {
            $invalidProperties[] = "'status' can't be null";
        }
        $allowedValues = $this->getStatusAllowableValues();
        if (!is_null($this->container['status']) && !in_array($this->container['status'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'status', must be one of '%s'",
                $this->container['status'],
                implode("', '", $allowedValues)
            );
        }

        if ($this->container['emergency_address_id'] === null && !$this->isNullableSetToNull('emergency_address_id')) {
            $invalidProperties[] = "'emergency_address_id' is required";
        }
        if ($this->container['ipnd'] === null && !$this->isNullableSetToNull('ipnd')) {
            $invalidProperties[] = "'ipnd' is required";
        }
        if ($this->container['port_out_code'] === null && !$this->isNullableSetToNull('port_out_code')) {
            $invalidProperties[] = "'port_out_code' is required";
        }
        if ($this->container['end_user'] === null && !$this->isNullableSetToNull('end_user')) {
            $invalidProperties[] = "'end_user' is required";
        }
        if ($this->container['monthly_cost'] === null && !$this->isNullableSetToNull('monthly_cost')) {
            $invalidProperties[] = "'monthly_cost' is required";
        }
        if ($this->container['currency'] === null) {
            $invalidProperties[] = "'currency' can't be null";
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
     * Gets number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->container['number'];
    }

    /**
     * Sets number
     *
     * @param string $number The E.164 formatted phone number.
     *
     * @return self
     */
    public function setNumber($number)
    {
        if (is_null($number)) {
            throw new \InvalidArgumentException('non-nullable number cannot be null');
        }
        $this->container['number'] = $number;

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
     * @param string|null $description A label or description for this number. Null when you have not set one.
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
        $this->container['description'] = $description;

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
            throw new \InvalidArgumentException('invalid length for $external_ref when calling UpdateNumber200ResponseResult., must be smaller than or equal to 64.');
        }
        if (!is_null($external_ref) && (mb_strlen($external_ref) < 1)) {
            throw new \InvalidArgumentException('invalid length for $external_ref when calling UpdateNumber200ResponseResult., must be bigger than or equal to 1.');
        }
        if (!is_null($external_ref) && (!preg_match("/^[A-Za-z0-9_.\\-]+$/", ObjectSerializer::toString($external_ref)))) {
            throw new \InvalidArgumentException("invalid value for \$external_ref when calling UpdateNumber200ResponseResult., must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
        }

        $this->container['external_ref'] = $external_ref;

        return $this;
    }

    /**
     * Gets metadata
     *
     * @return array<string,string>|null
     */
    public function getMetadata()
    {
        return $this->container['metadata'];
    }

    /**
     * Sets metadata
     *
     * @param array<string,string>|null $metadata Your own key-value pairs on this object: at most 20 keys, each up to 40 letters, digits, `_`, `.` or `-`, each value text of up to 255 characters. We store them and send them back on reads and webhooks; we never act on them. A map you send replaces the one held; send `{}` or null to clear it. An empty object when none is set.
     *
     * @return self
     */
    public function setMetadata($metadata)
    {
        if (is_null($metadata)) {
            throw new \InvalidArgumentException('non-nullable metadata cannot be null');
        }
        if ((count($metadata) > 20)) {
            throw new \InvalidArgumentException('invalid value for $metadata when calling UpdateNumber200ResponseResult., number of items must be less than or equal to 20.');
        }
        $this->container['metadata'] = $metadata;

        return $this;
    }

    /**
     * Gets route_type
     *
     * @return string|null
     */
    public function getRouteType()
    {
        return $this->container['route_type'];
    }

    /**
     * Sets route_type
     *
     * @param string|null $route_type How incoming calls to this number are routed first: the first step of `routing`. Null for unrouted numbers. `route_to_server` is not yet generally available.
     *
     * @return self
     */
    public function setRouteType($route_type)
    {
        if (is_null($route_type)) {
            array_push($this->openAPINullablesSetToNull, 'route_type');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('route_type', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getRouteTypeAllowableValues();
        if (!is_null($route_type) && !in_array($route_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'route_type', must be one of '%s'",
                    $route_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['route_type'] = $route_type;

        return $this;
    }

    /**
     * Gets route_target
     *
     * @return string|null
     */
    public function getRouteTarget()
    {
        return $this->container['route_target'];
    }

    /**
     * Sets route_target
     *
     * @param string|null $route_target The target for the first route (trunk sipUsername, redirect number, SIP URI or fax email); null for disconnected, unrouted and composite routes.
     *
     * @return self
     */
    public function setRouteTarget($route_target)
    {
        if (is_null($route_target)) {
            array_push($this->openAPINullablesSetToNull, 'route_target');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('route_target', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['route_target'] = $route_target;

        return $this;
    }

    /**
     * Gets trunk_type
     *
     * @return string|null
     */
    public function getTrunkType()
    {
        return $this->container['trunk_type'];
    }

    /**
     * Sets trunk_type
     *
     * @param string|null $trunk_type On a `trunk` route, where `routeTarget` is found: `trunk` at `GET /trunks/{username}`, `residentialLine` at `GET /residential-lines/{username}`. Null on every other route.
     *
     * @return self
     */
    public function setTrunkType($trunk_type)
    {
        if (is_null($trunk_type)) {
            array_push($this->openAPINullablesSetToNull, 'trunk_type');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('trunk_type', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getTrunkTypeAllowableValues();
        if (!is_null($trunk_type) && !in_array($trunk_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'trunk_type', must be one of '%s'",
                    $trunk_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['trunk_type'] = $trunk_type;

        return $this;
    }

    /**
     * Gets routing_steps
     *
     * @return int
     */
    public function getRoutingSteps()
    {
        return $this->container['routing_steps'];
    }

    /**
     * Sets routing_steps
     *
     * @param int $routing_steps How many routes the number's chain has: the first route and its failovers. 0 for an unrouted number.
     *
     * @return self
     */
    public function setRoutingSteps($routing_steps)
    {
        if (is_null($routing_steps)) {
            throw new \InvalidArgumentException('non-nullable routing_steps cannot be null');
        }
        if (($routing_steps < 0)) {
            throw new \InvalidArgumentException('invalid value for $routing_steps when calling UpdateNumber200ResponseResult., must be bigger than or equal to 0.');
        }

        $this->container['routing_steps'] = $routing_steps;

        return $this;
    }

    /**
     * Gets has_advanced_routing
     *
     * @return bool
     */
    public function getHasAdvancedRouting()
    {
        return $this->container['has_advanced_routing'];
    }

    /**
     * Sets has_advanced_routing
     *
     * @param bool $has_advanced_routing True when the chain has a failover, or its first route is time of day or geographic. A `PATCH` with `routeType` on such a number answers `409` unless it sends `replaceRouting: true`.
     *
     * @return self
     */
    public function setHasAdvancedRouting($has_advanced_routing)
    {
        if (is_null($has_advanced_routing)) {
            throw new \InvalidArgumentException('non-nullable has_advanced_routing cannot be null');
        }
        $this->container['has_advanced_routing'] = $has_advanced_routing;

        return $this;
    }

    /**
     * Gets routing
     *
     * @return \CheckNetworksModelNumberRoutingStep[]
     */
    public function getRouting()
    {
        return $this->container['routing'];
    }

    /**
     * Sets routing
     *
     * @param \CheckNetworksModelNumberRoutingStep[] $routing The whole routing chain, first route first, as the portal shows it. Send it back, changed, as `routing` on `PATCH /numbers/{number}`.
     *
     * @return self
     */
    public function setRouting($routing)
    {
        if (is_null($routing)) {
            throw new \InvalidArgumentException('non-nullable routing cannot be null');
        }
        $this->container['routing'] = $routing;

        return $this;
    }

    /**
     * Gets fax_enabled
     *
     * @return bool
     */
    public function getFaxEnabled()
    {
        return $this->container['fax_enabled'];
    }

    /**
     * Sets fax_enabled
     *
     * @param bool $fax_enabled Whether this number takes faxes: true while it is on one of your fax plans (`faxPlanId`). Only such a number can be routed to `fax2email`.
     *
     * @return self
     */
    public function setFaxEnabled($fax_enabled)
    {
        if (is_null($fax_enabled)) {
            throw new \InvalidArgumentException('non-nullable fax_enabled cannot be null');
        }
        $this->container['fax_enabled'] = $fax_enabled;

        return $this;
    }

    /**
     * Gets fax_plan_id
     *
     * @return string|null
     */
    public function getFaxPlanId()
    {
        return $this->container['fax_plan_id'];
    }

    /**
     * Sets fax_plan_id
     *
     * @param string|null $fax_plan_id The `id` of the fax plan this number is on (`GET /fax/plans`); null when it is on none.
     *
     * @return self
     */
    public function setFaxPlanId($fax_plan_id)
    {
        if (is_null($fax_plan_id)) {
            array_push($this->openAPINullablesSetToNull, 'fax_plan_id');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('fax_plan_id', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['fax_plan_id'] = $fax_plan_id;

        return $this;
    }

    /**
     * Gets sms_enabled
     *
     * @return bool
     */
    public function getSmsEnabled()
    {
        return $this->container['sms_enabled'];
    }

    /**
     * Sets sms_enabled
     *
     * @param bool $sms_enabled Whether this number can send and receive SMS (a mobile number you hold).
     *
     * @return self
     */
    public function setSmsEnabled($sms_enabled)
    {
        if (is_null($sms_enabled)) {
            throw new \InvalidArgumentException('non-nullable sms_enabled cannot be null');
        }
        $this->container['sms_enabled'] = $sms_enabled;

        return $this;
    }

    /**
     * Gets sms_delivery
     *
     * @return \CheckNetworks\Model\SmsDelivery|null
     */
    public function getSmsDelivery()
    {
        return $this->container['sms_delivery'];
    }

    /**
     * Sets sms_delivery
     *
     * @param \CheckNetworks\Model\SmsDelivery|null $sms_delivery Where this mobile number's inbound SMS are delivered. Null for any number that is not a mobile number.
     *
     * @return self
     */
    public function setSmsDelivery($sms_delivery)
    {
        if (is_null($sms_delivery)) {
            array_push($this->openAPINullablesSetToNull, 'sms_delivery');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('sms_delivery', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['sms_delivery'] = $sms_delivery;

        return $this;
    }

    /**
     * Gets status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->container['status'];
    }

    /**
     * Sets status
     *
     * @param string $status `releasing` while a release is being carried out; once it is done the number is gone.
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
     * Gets emergency_address_id
     *
     * @return string|null
     */
    public function getEmergencyAddressId()
    {
        return $this->container['emergency_address_id'];
    }

    /**
     * Sets emergency_address_id
     *
     * @param string|null $emergency_address_id The `id` of the emergency address this number is listed at (`GET /emergency-addresses`). Null until one is linked. A change to a number in use shows here once the register has been updated, a moment after the call.
     *
     * @return self
     */
    public function setEmergencyAddressId($emergency_address_id)
    {
        if (is_null($emergency_address_id)) {
            array_push($this->openAPINullablesSetToNull, 'emergency_address_id');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('emergency_address_id', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['emergency_address_id'] = $emergency_address_id;

        return $this;
    }

    /**
     * Gets ipnd
     *
     * @return \CheckNetworks\Model\DidNumberIpnd|null
     */
    public function getIpnd()
    {
        return $this->container['ipnd'];
    }

    /**
     * Sets ipnd
     *
     * @param \CheckNetworks\Model\DidNumberIpnd|null $ipnd ipnd
     *
     * @return self
     */
    public function setIpnd($ipnd)
    {
        if (is_null($ipnd)) {
            array_push($this->openAPINullablesSetToNull, 'ipnd');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('ipnd', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['ipnd'] = $ipnd;

        return $this;
    }

    /**
     * Gets port_out_code
     *
     * @return string|null
     */
    public function getPortOutCode()
    {
        return $this->container['port_out_code'];
    }

    /**
     * Sets port_out_code
     *
     * @param string|null $port_out_code A mobile number's port-out code from the carrier. Your end user gives it to their new provider to move the number away. It is an identifier, not a password. Null on a fixed number, or when the carrier has not given one.
     *
     * @return self
     */
    public function setPortOutCode($port_out_code)
    {
        if (is_null($port_out_code)) {
            array_push($this->openAPINullablesSetToNull, 'port_out_code');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('port_out_code', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['port_out_code'] = $port_out_code;

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
     * @param string|null $end_user The end user who holds this number, by its public number: the one it is tagged to, or else the end user of the trunk it is on. Null when the number is your own.
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
        $this->container['end_user'] = $end_user;

        return $this;
    }

    /**
     * Gets monthly_cost
     *
     * @return string|null
     * @deprecated
     */
    public function getMonthlyCost()
    {
        return $this->container['monthly_cost'];
    }

    /**
     * Sets monthly_cost
     *
     * @param string|null $monthly_cost Monthly recurring cost for this number as a decimal dollar amount, without GST. Null when no hosting product prices this number's category, which is not the same as free. Deprecated: read `monthlyCostExGst`, `monthlyCostGst` and `monthlyCostIncGst` instead.
     *
     * @return self
     * @deprecated
     */
    public function setMonthlyCost($monthly_cost)
    {
        if (is_null($monthly_cost)) {
            array_push($this->openAPINullablesSetToNull, 'monthly_cost');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('monthly_cost', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['monthly_cost'] = $monthly_cost;

        return $this;
    }

    /**
     * Gets monthly_cost_ex_gst
     *
     * @return string|null
     */
    public function getMonthlyCostExGst()
    {
        return $this->container['monthly_cost_ex_gst'];
    }

    /**
     * Sets monthly_cost_ex_gst
     *
     * @param string|null $monthly_cost_ex_gst The monthly cost without GST, as a decimal dollar amount. Null, with the other two, when nothing prices this number.
     *
     * @return self
     */
    public function setMonthlyCostExGst($monthly_cost_ex_gst)
    {
        if (is_null($monthly_cost_ex_gst)) {
            array_push($this->openAPINullablesSetToNull, 'monthly_cost_ex_gst');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('monthly_cost_ex_gst', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['monthly_cost_ex_gst'] = $monthly_cost_ex_gst;

        return $this;
    }

    /**
     * Gets monthly_cost_gst
     *
     * @return string|null
     */
    public function getMonthlyCostGst()
    {
        return $this->container['monthly_cost_gst'];
    }

    /**
     * Sets monthly_cost_gst
     *
     * @param string|null $monthly_cost_gst The GST on the monthly cost; `0.00` on a GST-free account.
     *
     * @return self
     */
    public function setMonthlyCostGst($monthly_cost_gst)
    {
        if (is_null($monthly_cost_gst)) {
            array_push($this->openAPINullablesSetToNull, 'monthly_cost_gst');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('monthly_cost_gst', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['monthly_cost_gst'] = $monthly_cost_gst;

        return $this;
    }

    /**
     * Gets monthly_cost_inc_gst
     *
     * @return string|null
     */
    public function getMonthlyCostIncGst()
    {
        return $this->container['monthly_cost_inc_gst'];
    }

    /**
     * Sets monthly_cost_inc_gst
     *
     * @param string|null $monthly_cost_inc_gst The monthly cost with GST.
     *
     * @return self
     */
    public function setMonthlyCostIncGst($monthly_cost_inc_gst)
    {
        if (is_null($monthly_cost_inc_gst)) {
            array_push($this->openAPINullablesSetToNull, 'monthly_cost_inc_gst');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('monthly_cost_inc_gst', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['monthly_cost_inc_gst'] = $monthly_cost_inc_gst;

        return $this;
    }

    /**
     * Gets currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->container['currency'];
    }

    /**
     * Sets currency
     *
     * @param string $currency The currency for `monthlyCost`.
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        if (is_null($currency)) {
            throw new \InvalidArgumentException('non-nullable currency cannot be null');
        }
        $this->container['currency'] = $currency;

        return $this;
    }

    /**
     * Gets created_at
     *
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->container['created_at'];
    }

    /**
     * Sets created_at
     *
     * @param \DateTime|null $created_at When the number was added to the account's stock, in UTC.
     *
     * @return self
     */
    public function setCreatedAt($created_at)
    {
        if (is_null($created_at)) {
            array_push($this->openAPINullablesSetToNull, 'created_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('created_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['created_at'] = $created_at;

        return $this;
    }

    /**
     * Gets updated_at
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->container['updated_at'];
    }

    /**
     * Sets updated_at
     *
     * @param \DateTime|null $updated_at When the number last changed, in UTC.
     *
     * @return self
     */
    public function setUpdatedAt($updated_at)
    {
        if (is_null($updated_at)) {
            array_push($this->openAPINullablesSetToNull, 'updated_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('updated_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['updated_at'] = $updated_at;

        return $this;
    }

    /**
     * Gets emergency_address_change
     *
     * @return \CheckNetworks\Model\UpdateNumber200ResponseResultAllOfEmergencyAddressChange|null
     */
    public function getEmergencyAddressChange()
    {
        return $this->container['emergency_address_change'];
    }

    /**
     * Sets emergency_address_change
     *
     * @param \CheckNetworks\Model\UpdateNumber200ResponseResultAllOfEmergencyAddressChange|null $emergency_address_change emergency_address_change
     *
     * @return self
     */
    public function setEmergencyAddressChange($emergency_address_change)
    {
        if (is_null($emergency_address_change)) {
            throw new \InvalidArgumentException('non-nullable emergency_address_change cannot be null');
        }
        $this->container['emergency_address_change'] = $emergency_address_change;

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


