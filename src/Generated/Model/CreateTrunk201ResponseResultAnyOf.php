<?php
/**
 * CreateTrunk201ResponseResultAnyOf
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
 * CreateTrunk201ResponseResultAnyOf Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class CreateTrunk201ResponseResultAnyOf implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'create_trunk_201_response_result_anyOf';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'name' => 'string',
        'description' => 'string',
        'external_ref' => 'string',
        'metadata' => 'array<string,string>',
        'auth_method' => 'string',
        'sip_username' => 'string',
        'sip_domain' => 'string',
        'type' => 'string',
        'channels' => 'int',
        'max_international' => 'int',
        'cps_limit' => 'int',
        'fax_enabled' => 'bool',
        'active_calls' => 'int',
        'recording_enabled' => 'bool',
        'calling_permissions' => '\CheckNetworks\Api\Generated\Model\CallingPermission[]',
        'default_caller_id' => 'string',
        'force_private_cli' => 'bool',
        'force_outbound_cli' => 'bool',
        'default_state' => 'string',
        'status' => 'string',
        'suspended_by' => 'string',
        'called_number_format' => '\CheckNetworks\Api\Generated\Model\TrunkNumberFormat',
        'caller_number_format' => '\CheckNetworks\Api\Generated\Model\TrunkNumberFormat',
        'provisioning_state' => 'string',
        'provisioned_at' => '\DateTime',
        'international_hold' => '\CheckNetworks\Api\Generated\Model\SipTrunkInternationalHold',
        'pending_plan_change' => '\CheckNetworks\Api\Generated\Model\SipTrunkPendingPlanChange',
        'booked_change' => '\CheckNetworks\Api\Generated\Model\BookedChangeRef',
        'connection' => '\CheckNetworks\Api\Generated\Model\SipTrunkConnection',
        'end_user' => 'string',
        'allowed_countries' => 'string[]',
        'effective_allowed_countries' => 'string[]',
        'follows_account_list' => 'bool',
        'daily_spend_cap_ex_gst' => 'string',
        'daily_spend_cap_gst' => 'string',
        'daily_spend_cap_inc_gst' => 'string',
        'spend_today_ex_gst' => 'string',
        'spend_today_gst' => 'string',
        'spend_today_inc_gst' => 'string',
        'spend_cap_held' => 'bool',
        'spend_cap_releases_at' => '\DateTime',
        'currency' => 'string',
        'ips' => '\CheckNetworks\Api\Generated\Model\TrunkIpEntry[]',
        'created_at' => '\DateTime',
        'updated_at' => '\DateTime',
        'sip_password' => 'string'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'id' => 'uuid',
        'name' => null,
        'description' => null,
        'external_ref' => null,
        'metadata' => null,
        'auth_method' => null,
        'sip_username' => null,
        'sip_domain' => null,
        'type' => null,
        'channels' => null,
        'max_international' => null,
        'cps_limit' => null,
        'fax_enabled' => null,
        'active_calls' => null,
        'recording_enabled' => null,
        'calling_permissions' => null,
        'default_caller_id' => null,
        'force_private_cli' => null,
        'force_outbound_cli' => null,
        'default_state' => null,
        'status' => null,
        'suspended_by' => null,
        'called_number_format' => null,
        'caller_number_format' => null,
        'provisioning_state' => null,
        'provisioned_at' => 'date-time',
        'international_hold' => null,
        'pending_plan_change' => null,
        'booked_change' => null,
        'connection' => null,
        'end_user' => null,
        'allowed_countries' => null,
        'effective_allowed_countries' => null,
        'follows_account_list' => null,
        'daily_spend_cap_ex_gst' => null,
        'daily_spend_cap_gst' => null,
        'daily_spend_cap_inc_gst' => null,
        'spend_today_ex_gst' => null,
        'spend_today_gst' => null,
        'spend_today_inc_gst' => null,
        'spend_cap_held' => null,
        'spend_cap_releases_at' => 'date-time',
        'currency' => null,
        'ips' => null,
        'created_at' => 'date-time',
        'updated_at' => 'date-time',
        'sip_password' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'name' => false,
        'description' => true,
        'external_ref' => true,
        'metadata' => false,
        'auth_method' => false,
        'sip_username' => true,
        'sip_domain' => false,
        'type' => false,
        'channels' => false,
        'max_international' => true,
        'cps_limit' => false,
        'fax_enabled' => false,
        'active_calls' => false,
        'recording_enabled' => false,
        'calling_permissions' => false,
        'default_caller_id' => true,
        'force_private_cli' => false,
        'force_outbound_cli' => false,
        'default_state' => true,
        'status' => false,
        'suspended_by' => true,
        'called_number_format' => false,
        'caller_number_format' => false,
        'provisioning_state' => false,
        'provisioned_at' => true,
        'international_hold' => true,
        'pending_plan_change' => true,
        'booked_change' => true,
        'connection' => false,
        'end_user' => true,
        'allowed_countries' => true,
        'effective_allowed_countries' => false,
        'follows_account_list' => false,
        'daily_spend_cap_ex_gst' => true,
        'daily_spend_cap_gst' => true,
        'daily_spend_cap_inc_gst' => true,
        'spend_today_ex_gst' => false,
        'spend_today_gst' => false,
        'spend_today_inc_gst' => false,
        'spend_cap_held' => false,
        'spend_cap_releases_at' => true,
        'currency' => false,
        'ips' => false,
        'created_at' => true,
        'updated_at' => true,
        'sip_password' => false
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
        'name' => 'name',
        'description' => 'description',
        'external_ref' => 'externalRef',
        'metadata' => 'metadata',
        'auth_method' => 'authMethod',
        'sip_username' => 'sipUsername',
        'sip_domain' => 'sipDomain',
        'type' => 'type',
        'channels' => 'channels',
        'max_international' => 'maxInternational',
        'cps_limit' => 'cpsLimit',
        'fax_enabled' => 'faxEnabled',
        'active_calls' => 'activeCalls',
        'recording_enabled' => 'recordingEnabled',
        'calling_permissions' => 'callingPermissions',
        'default_caller_id' => 'defaultCallerId',
        'force_private_cli' => 'forcePrivateCli',
        'force_outbound_cli' => 'forceOutboundCli',
        'default_state' => 'defaultState',
        'status' => 'status',
        'suspended_by' => 'suspendedBy',
        'called_number_format' => 'calledNumberFormat',
        'caller_number_format' => 'callerNumberFormat',
        'provisioning_state' => 'provisioningState',
        'provisioned_at' => 'provisionedAt',
        'international_hold' => 'internationalHold',
        'pending_plan_change' => 'pendingPlanChange',
        'booked_change' => 'bookedChange',
        'connection' => 'connection',
        'end_user' => 'endUser',
        'allowed_countries' => 'allowedCountries',
        'effective_allowed_countries' => 'effectiveAllowedCountries',
        'follows_account_list' => 'followsAccountList',
        'daily_spend_cap_ex_gst' => 'dailySpendCapExGst',
        'daily_spend_cap_gst' => 'dailySpendCapGst',
        'daily_spend_cap_inc_gst' => 'dailySpendCapIncGst',
        'spend_today_ex_gst' => 'spendTodayExGst',
        'spend_today_gst' => 'spendTodayGst',
        'spend_today_inc_gst' => 'spendTodayIncGst',
        'spend_cap_held' => 'spendCapHeld',
        'spend_cap_releases_at' => 'spendCapReleasesAt',
        'currency' => 'currency',
        'ips' => 'ips',
        'created_at' => 'createdAt',
        'updated_at' => 'updatedAt',
        'sip_password' => 'sipPassword'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'name' => 'setName',
        'description' => 'setDescription',
        'external_ref' => 'setExternalRef',
        'metadata' => 'setMetadata',
        'auth_method' => 'setAuthMethod',
        'sip_username' => 'setSipUsername',
        'sip_domain' => 'setSipDomain',
        'type' => 'setType',
        'channels' => 'setChannels',
        'max_international' => 'setMaxInternational',
        'cps_limit' => 'setCpsLimit',
        'fax_enabled' => 'setFaxEnabled',
        'active_calls' => 'setActiveCalls',
        'recording_enabled' => 'setRecordingEnabled',
        'calling_permissions' => 'setCallingPermissions',
        'default_caller_id' => 'setDefaultCallerId',
        'force_private_cli' => 'setForcePrivateCli',
        'force_outbound_cli' => 'setForceOutboundCli',
        'default_state' => 'setDefaultState',
        'status' => 'setStatus',
        'suspended_by' => 'setSuspendedBy',
        'called_number_format' => 'setCalledNumberFormat',
        'caller_number_format' => 'setCallerNumberFormat',
        'provisioning_state' => 'setProvisioningState',
        'provisioned_at' => 'setProvisionedAt',
        'international_hold' => 'setInternationalHold',
        'pending_plan_change' => 'setPendingPlanChange',
        'booked_change' => 'setBookedChange',
        'connection' => 'setConnection',
        'end_user' => 'setEndUser',
        'allowed_countries' => 'setAllowedCountries',
        'effective_allowed_countries' => 'setEffectiveAllowedCountries',
        'follows_account_list' => 'setFollowsAccountList',
        'daily_spend_cap_ex_gst' => 'setDailySpendCapExGst',
        'daily_spend_cap_gst' => 'setDailySpendCapGst',
        'daily_spend_cap_inc_gst' => 'setDailySpendCapIncGst',
        'spend_today_ex_gst' => 'setSpendTodayExGst',
        'spend_today_gst' => 'setSpendTodayGst',
        'spend_today_inc_gst' => 'setSpendTodayIncGst',
        'spend_cap_held' => 'setSpendCapHeld',
        'spend_cap_releases_at' => 'setSpendCapReleasesAt',
        'currency' => 'setCurrency',
        'ips' => 'setIps',
        'created_at' => 'setCreatedAt',
        'updated_at' => 'setUpdatedAt',
        'sip_password' => 'setSipPassword'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'name' => 'getName',
        'description' => 'getDescription',
        'external_ref' => 'getExternalRef',
        'metadata' => 'getMetadata',
        'auth_method' => 'getAuthMethod',
        'sip_username' => 'getSipUsername',
        'sip_domain' => 'getSipDomain',
        'type' => 'getType',
        'channels' => 'getChannels',
        'max_international' => 'getMaxInternational',
        'cps_limit' => 'getCpsLimit',
        'fax_enabled' => 'getFaxEnabled',
        'active_calls' => 'getActiveCalls',
        'recording_enabled' => 'getRecordingEnabled',
        'calling_permissions' => 'getCallingPermissions',
        'default_caller_id' => 'getDefaultCallerId',
        'force_private_cli' => 'getForcePrivateCli',
        'force_outbound_cli' => 'getForceOutboundCli',
        'default_state' => 'getDefaultState',
        'status' => 'getStatus',
        'suspended_by' => 'getSuspendedBy',
        'called_number_format' => 'getCalledNumberFormat',
        'caller_number_format' => 'getCallerNumberFormat',
        'provisioning_state' => 'getProvisioningState',
        'provisioned_at' => 'getProvisionedAt',
        'international_hold' => 'getInternationalHold',
        'pending_plan_change' => 'getPendingPlanChange',
        'booked_change' => 'getBookedChange',
        'connection' => 'getConnection',
        'end_user' => 'getEndUser',
        'allowed_countries' => 'getAllowedCountries',
        'effective_allowed_countries' => 'getEffectiveAllowedCountries',
        'follows_account_list' => 'getFollowsAccountList',
        'daily_spend_cap_ex_gst' => 'getDailySpendCapExGst',
        'daily_spend_cap_gst' => 'getDailySpendCapGst',
        'daily_spend_cap_inc_gst' => 'getDailySpendCapIncGst',
        'spend_today_ex_gst' => 'getSpendTodayExGst',
        'spend_today_gst' => 'getSpendTodayGst',
        'spend_today_inc_gst' => 'getSpendTodayIncGst',
        'spend_cap_held' => 'getSpendCapHeld',
        'spend_cap_releases_at' => 'getSpendCapReleasesAt',
        'currency' => 'getCurrency',
        'ips' => 'getIps',
        'created_at' => 'getCreatedAt',
        'updated_at' => 'getUpdatedAt',
        'sip_password' => 'getSipPassword'
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

    public const AUTH_METHOD_REGISTRATION = 'registration';
    public const AUTH_METHOD_IP_ACL = 'ip_acl';
    public const AUTH_METHOD_BOTH = 'both';
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
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';
    public const SUSPENDED_BY_ACCOUNT = 'account';
    public const SUSPENDED_BY_CHECK_NETWORKS = 'checkNetworks';
    public const PROVISIONING_STATE_PENDING = 'pending';
    public const PROVISIONING_STATE_ACTIVE = 'active';
    public const CURRENCY_AUD = 'AUD';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAuthMethodAllowableValues()
    {
        return [
            self::AUTH_METHOD_REGISTRATION,
            self::AUTH_METHOD_IP_ACL,
            self::AUTH_METHOD_BOTH,
        ];
    }

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
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_SUSPENDED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getSuspendedByAllowableValues()
    {
        return [
            self::SUSPENDED_BY_ACCOUNT,
            self::SUSPENDED_BY_CHECK_NETWORKS,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getProvisioningStateAllowableValues()
    {
        return [
            self::PROVISIONING_STATE_PENDING,
            self::PROVISIONING_STATE_ACTIVE,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getCurrencyAllowableValues()
    {
        return [
            self::CURRENCY_AUD,
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
        $this->setIfExists('name', $data ?? [], null);
        $this->setIfExists('description', $data ?? [], null);
        $this->setIfExists('external_ref', $data ?? [], null);
        $this->setIfExists('metadata', $data ?? [], null);
        $this->setIfExists('auth_method', $data ?? [], null);
        $this->setIfExists('sip_username', $data ?? [], null);
        $this->setIfExists('sip_domain', $data ?? [], null);
        $this->setIfExists('type', $data ?? [], null);
        $this->setIfExists('channels', $data ?? [], null);
        $this->setIfExists('max_international', $data ?? [], null);
        $this->setIfExists('cps_limit', $data ?? [], null);
        $this->setIfExists('fax_enabled', $data ?? [], null);
        $this->setIfExists('active_calls', $data ?? [], null);
        $this->setIfExists('recording_enabled', $data ?? [], null);
        $this->setIfExists('calling_permissions', $data ?? [], null);
        $this->setIfExists('default_caller_id', $data ?? [], null);
        $this->setIfExists('force_private_cli', $data ?? [], null);
        $this->setIfExists('force_outbound_cli', $data ?? [], null);
        $this->setIfExists('default_state', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('suspended_by', $data ?? [], null);
        $this->setIfExists('called_number_format', $data ?? [], null);
        $this->setIfExists('caller_number_format', $data ?? [], null);
        $this->setIfExists('provisioning_state', $data ?? [], null);
        $this->setIfExists('provisioned_at', $data ?? [], null);
        $this->setIfExists('international_hold', $data ?? [], null);
        $this->setIfExists('pending_plan_change', $data ?? [], null);
        $this->setIfExists('booked_change', $data ?? [], null);
        $this->setIfExists('connection', $data ?? [], null);
        $this->setIfExists('end_user', $data ?? [], null);
        $this->setIfExists('allowed_countries', $data ?? [], null);
        $this->setIfExists('effective_allowed_countries', $data ?? [], null);
        $this->setIfExists('follows_account_list', $data ?? [], null);
        $this->setIfExists('daily_spend_cap_ex_gst', $data ?? [], null);
        $this->setIfExists('daily_spend_cap_gst', $data ?? [], null);
        $this->setIfExists('daily_spend_cap_inc_gst', $data ?? [], null);
        $this->setIfExists('spend_today_ex_gst', $data ?? [], null);
        $this->setIfExists('spend_today_gst', $data ?? [], null);
        $this->setIfExists('spend_today_inc_gst', $data ?? [], null);
        $this->setIfExists('spend_cap_held', $data ?? [], null);
        $this->setIfExists('spend_cap_releases_at', $data ?? [], null);
        $this->setIfExists('currency', $data ?? [], null);
        $this->setIfExists('ips', $data ?? [], null);
        $this->setIfExists('created_at', $data ?? [], null);
        $this->setIfExists('updated_at', $data ?? [], null);
        $this->setIfExists('sip_password', $data ?? [], null);
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

        $allowedValues = $this->getAuthMethodAllowableValues();
        if (!is_null($this->container['auth_method']) && !in_array($this->container['auth_method'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'auth_method', must be one of '%s'",
                $this->container['auth_method'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getTypeAllowableValues();
        if (!is_null($this->container['type']) && !in_array($this->container['type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'type', must be one of '%s'",
                $this->container['type'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getDefaultStateAllowableValues();
        if (!is_null($this->container['default_state']) && !in_array($this->container['default_state'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'default_state', must be one of '%s'",
                $this->container['default_state'],
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

        $allowedValues = $this->getSuspendedByAllowableValues();
        if (!is_null($this->container['suspended_by']) && !in_array($this->container['suspended_by'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'suspended_by', must be one of '%s'",
                $this->container['suspended_by'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getProvisioningStateAllowableValues();
        if (!is_null($this->container['provisioning_state']) && !in_array($this->container['provisioning_state'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'provisioning_state', must be one of '%s'",
                $this->container['provisioning_state'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getCurrencyAllowableValues();
        if (!is_null($this->container['currency']) && !in_array($this->container['currency'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'currency', must be one of '%s'",
                $this->container['currency'],
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
     * @param string|null $id The trunk's public id. Every trunk has one, including a trunk set up without a SIP credential, so every `/trunks/{username}` endpoint accepts it in place of the SIP username. Trunk events carry it as `trunkId`. The internal database id is never exposed.
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
     * @param string|null $name A friendly name for this trunk.
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
     * @param string|null $description An optional description for this trunk.
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
            throw new \InvalidArgumentException('invalid length for $external_ref when calling CreateTrunk201ResponseResultAnyOf., must be smaller than or equal to 64.');
        }
        if (!is_null($external_ref) && (mb_strlen($external_ref) < 1)) {
            throw new \InvalidArgumentException('invalid length for $external_ref when calling CreateTrunk201ResponseResultAnyOf., must be bigger than or equal to 1.');
        }
        if (!is_null($external_ref) && (!preg_match("/^[A-Za-z0-9_.\\-]+$/", ObjectSerializer::toString($external_ref)))) {
            throw new \InvalidArgumentException("invalid value for \$external_ref when calling CreateTrunk201ResponseResultAnyOf., must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
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
            throw new \InvalidArgumentException('invalid value for $metadata when calling CreateTrunk201ResponseResultAnyOf., number of items must be less than or equal to 20.');
        }
        $this->container['metadata'] = $metadata;

        return $this;
    }

    /**
     * Gets auth_method
     *
     * @return string|null
     */
    public function getAuthMethod()
    {
        return $this->container['auth_method'];
    }

    /**
     * Sets auth_method
     *
     * @param string|null $auth_method The SIP authentication method. A trunk is always `both`: it accepts either registration with its credential or a source IP on its IP ACL. The other values remain for compatibility and are not returned for a trunk.
     *
     * @return self
     */
    public function setAuthMethod($auth_method)
    {
        if (is_null($auth_method)) {
            throw new \InvalidArgumentException('non-nullable auth_method cannot be null');
        }
        $allowedValues = $this->getAuthMethodAllowableValues();
        if (!in_array($auth_method, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'auth_method', must be one of '%s'",
                    $auth_method,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['auth_method'] = $auth_method;

        return $this;
    }

    /**
     * Gets sip_username
     *
     * @return string|null
     */
    public function getSipUsername()
    {
        return $this->container['sip_username'];
    }

    /**
     * Sets sip_username
     *
     * @param string|null $sip_username The trunk's platform-generated SIP digest username. Every `/trunks/{username}` endpoint accepts it, as it accepts `id`. Null on a trunk set up without a SIP credential, which authenticates only by IP: address that trunk by its `id`.
     *
     * @return self
     */
    public function setSipUsername($sip_username)
    {
        if (is_null($sip_username)) {
            array_push($this->openAPINullablesSetToNull, 'sip_username');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('sip_username', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['sip_username'] = $sip_username;

        return $this;
    }

    /**
     * Gets sip_domain
     *
     * @return string|null
     */
    public function getSipDomain()
    {
        return $this->container['sip_domain'];
    }

    /**
     * Sets sip_domain
     *
     * @param string|null $sip_domain The SIP domain for this trunk.
     *
     * @return self
     */
    public function setSipDomain($sip_domain)
    {
        if (is_null($sip_domain)) {
            throw new \InvalidArgumentException('non-nullable sip_domain cannot be null');
        }
        $this->container['sip_domain'] = $sip_domain;

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
     * @param string|null $type The trunk's plan. This is what decides the price: on `ultimate` each channel is a recurring billed assignment, on `payg` the channels are a ceiling and outbound calls are metered per use.
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
     * @param int|null $channels Maximum concurrent calls allowed on this trunk. On `ultimate` this is also the number of channels billed.
     *
     * @return self
     */
    public function setChannels($channels)
    {
        if (is_null($channels)) {
            throw new \InvalidArgumentException('non-nullable channels cannot be null');
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
     * @param int|null $max_international Maximum concurrent international calls. **Null means no trunk-level limit** — the trunk inherits your account's cap rather than being capped at zero. Setting it to `0` is a different thing entirely: that blocks international calling on this trunk. A limit applies only while `International` is among `callingPermissions`; without it this is null.
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
        $this->container['max_international'] = $max_international;

        return $this;
    }

    /**
     * Gets cps_limit
     *
     * @return int|null
     */
    public function getCpsLimit()
    {
        return $this->container['cps_limit'];
    }

    /**
     * Sets cps_limit
     *
     * @param int|null $cps_limit Maximum calls per second.
     *
     * @return self
     */
    public function setCpsLimit($cps_limit)
    {
        if (is_null($cps_limit)) {
            throw new \InvalidArgumentException('non-nullable cps_limit cannot be null');
        }
        $this->container['cps_limit'] = $cps_limit;

        return $this;
    }

    /**
     * Gets fax_enabled
     *
     * @return bool|null
     */
    public function getFaxEnabled()
    {
        return $this->container['fax_enabled'];
    }

    /**
     * Sets fax_enabled
     *
     * @param bool|null $fax_enabled Whether T.38 fax is enabled.
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
     * Gets active_calls
     *
     * @return int|null
     */
    public function getActiveCalls()
    {
        return $this->container['active_calls'];
    }

    /**
     * Sets active_calls
     *
     * @param int|null $active_calls Calls up on the trunk now, as the portal's Concurrency card counts them. `GET /voice/calls/active?trunk=` lists them.
     *
     * @return self
     */
    public function setActiveCalls($active_calls)
    {
        if (is_null($active_calls)) {
            throw new \InvalidArgumentException('non-nullable active_calls cannot be null');
        }
        $this->container['active_calls'] = $active_calls;

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
     * @param bool|null $recording_enabled Whether call recording is on. When it is, every call on the trunk is recorded.
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
     * @param \CheckNetworks\Api\Generated\Model\CallingPermission[]|null $calling_permissions The calling-permission classes applied to this trunk (its call barring). `AUEmergency` and `AUFreecall` are always listed: 000 and freecall numbers are never barred.
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
     * @param string|null $default_caller_id The default outbound caller ID presented for calls from this trunk.
     *
     * @return self
     */
    public function setDefaultCallerId($default_caller_id)
    {
        if (is_null($default_caller_id)) {
            array_push($this->openAPINullablesSetToNull, 'default_caller_id');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('default_caller_id', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['default_caller_id'] = $default_caller_id;

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
     * @param bool|null $force_private_cli Whether all outbound calls present as Private/Anonymous regardless of the caller ID set.
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
     * @param bool|null $force_outbound_cli Whether every outbound call presents `defaultCallerId`, whatever caller ID the PBX sends.
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
     * @param string|null $default_state The default registration state for this trunk. Null when none is set.
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
     * @param string|null $status A `suspended` trunk makes and receives no calls: new outbound calls are refused, and calls to its numbers are refused before they reach your PBX. It still places 000 (and 112 and 106) calls, and for an hour after one, calls to its numbers are delivered so the emergency service can call back. See `suspendedBy`.
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
     * Gets suspended_by
     *
     * @return string|null
     */
    public function getSuspendedBy()
    {
        return $this->container['suspended_by'];
    }

    /**
     * Sets suspended_by
     *
     * @param string|null $suspended_by Who suspended the trunk, while `status` is `suspended`. `account`: you did (in the portal or with `POST /trunks/{username}/suspend`), and you can resume it. `checkNetworks`: we placed a hold (for example for non-payment or fraud), which only we lift; `POST /trunks/{username}/resume` answers 409. Null when the trunk is not suspended.
     *
     * @return self
     */
    public function setSuspendedBy($suspended_by)
    {
        if (is_null($suspended_by)) {
            array_push($this->openAPINullablesSetToNull, 'suspended_by');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('suspended_by', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getSuspendedByAllowableValues();
        if (!is_null($suspended_by) && !in_array($suspended_by, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'suspended_by', must be one of '%s'",
                    $suspended_by,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['suspended_by'] = $suspended_by;

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
     * @param \CheckNetworks\Api\Generated\Model\TrunkNumberFormat|null $called_number_format How the dialled number is written in the Request-URI and To of calls delivered to this trunk. `national` is an Australian number as `0` plus the national number (`0755501234`); a number that is not Australian is delivered as digits only.
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
     * @param \CheckNetworks\Api\Generated\Model\TrunkNumberFormat|null $caller_number_format How an Australian caller's number is written in From on calls delivered to this trunk. A number's own inbound format, where set, overrides this for calls to that number. An overseas or withheld caller is delivered as received.
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
     * Gets provisioning_state
     *
     * @return string|null
     */
    public function getProvisioningState()
    {
        return $this->container['provisioning_state'];
    }

    /**
     * Sets provisioning_state
     *
     * @param string|null $provisioning_state Whether the trunk's latest change (to the trunk, its SIP credential or its IP ACL) has reached every customer SBC. `pending` until the next network reload, which runs every minute; `active` once every SBC has it. A new trunk reads `pending` at first: wait for `active` before a test call.
     *
     * @return self
     */
    public function setProvisioningState($provisioning_state)
    {
        if (is_null($provisioning_state)) {
            throw new \InvalidArgumentException('non-nullable provisioning_state cannot be null');
        }
        $allowedValues = $this->getProvisioningStateAllowableValues();
        if (!in_array($provisioning_state, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'provisioning_state', must be one of '%s'",
                    $provisioning_state,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['provisioning_state'] = $provisioning_state;

        return $this;
    }

    /**
     * Gets provisioned_at
     *
     * @return \DateTime|null
     */
    public function getProvisionedAt()
    {
        return $this->container['provisioned_at'];
    }

    /**
     * Sets provisioned_at
     *
     * @param \DateTime|null $provisioned_at When the voice network was first confirmed to hold the trunk's latest change. Null while `provisioningState` is `pending`.
     *
     * @return self
     */
    public function setProvisionedAt($provisioned_at)
    {
        if (is_null($provisioned_at)) {
            array_push($this->openAPINullablesSetToNull, 'provisioned_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('provisioned_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['provisioned_at'] = $provisioned_at;

        return $this;
    }

    /**
     * Gets international_hold
     *
     * @return \CheckNetworks\Api\Generated\Model\SipTrunkInternationalHold|null
     */
    public function getInternationalHold()
    {
        return $this->container['international_hold'];
    }

    /**
     * Sets international_hold
     *
     * @param \CheckNetworks\Api\Generated\Model\SipTrunkInternationalHold|null $international_hold international_hold
     *
     * @return self
     */
    public function setInternationalHold($international_hold)
    {
        if (is_null($international_hold)) {
            array_push($this->openAPINullablesSetToNull, 'international_hold');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('international_hold', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['international_hold'] = $international_hold;

        return $this;
    }

    /**
     * Gets pending_plan_change
     *
     * @return \CheckNetworks\Api\Generated\Model\SipTrunkPendingPlanChange|null
     */
    public function getPendingPlanChange()
    {
        return $this->container['pending_plan_change'];
    }

    /**
     * Sets pending_plan_change
     *
     * @param \CheckNetworks\Api\Generated\Model\SipTrunkPendingPlanChange|null $pending_plan_change pending_plan_change
     *
     * @return self
     */
    public function setPendingPlanChange($pending_plan_change)
    {
        if (is_null($pending_plan_change)) {
            array_push($this->openAPINullablesSetToNull, 'pending_plan_change');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('pending_plan_change', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['pending_plan_change'] = $pending_plan_change;

        return $this;
    }

    /**
     * Gets booked_change
     *
     * @return \CheckNetworks\Api\Generated\Model\BookedChangeRef|null
     */
    public function getBookedChange()
    {
        return $this->container['booked_change'];
    }

    /**
     * Sets booked_change
     *
     * @param \CheckNetworks\Api\Generated\Model\BookedChangeRef|null $booked_change booked_change
     *
     * @return self
     */
    public function setBookedChange($booked_change)
    {
        if (is_null($booked_change)) {
            array_push($this->openAPINullablesSetToNull, 'booked_change');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('booked_change', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['booked_change'] = $booked_change;

        return $this;
    }

    /**
     * Gets connection
     *
     * @return \CheckNetworks\Api\Generated\Model\SipTrunkConnection|null
     */
    public function getConnection()
    {
        return $this->container['connection'];
    }

    /**
     * Sets connection
     *
     * @param \CheckNetworks\Api\Generated\Model\SipTrunkConnection|null $connection connection
     *
     * @return self
     */
    public function setConnection($connection)
    {
        if (is_null($connection)) {
            throw new \InvalidArgumentException('non-nullable connection cannot be null');
        }
        $this->container['connection'] = $connection;

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
     * @param string|null $end_user The end user this trunk belongs to, by its public number; its calls are billed to that end user. Null when the trunk is your own.
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
     * @param string[]|null $allowed_countries This trunk's own list of countries it may call (ISO 3166 alpha-2), or null when it follows your account's list.
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
        $this->container['allowed_countries'] = $allowed_countries;

        return $this;
    }

    /**
     * Gets effective_allowed_countries
     *
     * @return string[]|null
     */
    public function getEffectiveAllowedCountries()
    {
        return $this->container['effective_allowed_countries'];
    }

    /**
     * Sets effective_allowed_countries
     *
     * @param string[]|null $effective_allowed_countries The countries this trunk's international calls may reach: its own list, else your account's. A call to any other country is refused. Calls to Australia and New Zealand follow `callingPermissions` (New Zealand is `NZMobile` and `NZNational`, and is never in a list), high-risk destinations stay blocked whatever the list says, and 000 always connects.
     *
     * @return self
     */
    public function setEffectiveAllowedCountries($effective_allowed_countries)
    {
        if (is_null($effective_allowed_countries)) {
            throw new \InvalidArgumentException('non-nullable effective_allowed_countries cannot be null');
        }
        $this->container['effective_allowed_countries'] = $effective_allowed_countries;

        return $this;
    }

    /**
     * Gets follows_account_list
     *
     * @return bool|null
     */
    public function getFollowsAccountList()
    {
        return $this->container['follows_account_list'];
    }

    /**
     * Sets follows_account_list
     *
     * @param bool|null $follows_account_list True when the trunk has no list of its own and follows your account's.
     *
     * @return self
     */
    public function setFollowsAccountList($follows_account_list)
    {
        if (is_null($follows_account_list)) {
            throw new \InvalidArgumentException('non-nullable follows_account_list cannot be null');
        }
        $this->container['follows_account_list'] = $follows_account_list;

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
     * @param string|null $daily_spend_cap_ex_gst The trunk's daily spend cap, ex GST, as a dollar string; null is no cap. The cap is measured on our rated cost to you for the trunk's outbound calls since midnight Brisbane time.
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
        $this->container['daily_spend_cap_ex_gst'] = $daily_spend_cap_ex_gst;

        return $this;
    }

    /**
     * Gets daily_spend_cap_gst
     *
     * @return string|null
     */
    public function getDailySpendCapGst()
    {
        return $this->container['daily_spend_cap_gst'];
    }

    /**
     * Sets daily_spend_cap_gst
     *
     * @param string|null $daily_spend_cap_gst The GST on the cap.
     *
     * @return self
     */
    public function setDailySpendCapGst($daily_spend_cap_gst)
    {
        if (is_null($daily_spend_cap_gst)) {
            array_push($this->openAPINullablesSetToNull, 'daily_spend_cap_gst');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('daily_spend_cap_gst', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['daily_spend_cap_gst'] = $daily_spend_cap_gst;

        return $this;
    }

    /**
     * Gets daily_spend_cap_inc_gst
     *
     * @return string|null
     */
    public function getDailySpendCapIncGst()
    {
        return $this->container['daily_spend_cap_inc_gst'];
    }

    /**
     * Sets daily_spend_cap_inc_gst
     *
     * @param string|null $daily_spend_cap_inc_gst The cap with GST.
     *
     * @return self
     */
    public function setDailySpendCapIncGst($daily_spend_cap_inc_gst)
    {
        if (is_null($daily_spend_cap_inc_gst)) {
            array_push($this->openAPINullablesSetToNull, 'daily_spend_cap_inc_gst');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('daily_spend_cap_inc_gst', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['daily_spend_cap_inc_gst'] = $daily_spend_cap_inc_gst;

        return $this;
    }

    /**
     * Gets spend_today_ex_gst
     *
     * @return string|null
     */
    public function getSpendTodayExGst()
    {
        return $this->container['spend_today_ex_gst'];
    }

    /**
     * Sets spend_today_ex_gst
     *
     * @param string|null $spend_today_ex_gst The trunk's outbound calls today, ex GST, since midnight Brisbane time. A call counts when it ends, so calls still up are not in it yet.
     *
     * @return self
     */
    public function setSpendTodayExGst($spend_today_ex_gst)
    {
        if (is_null($spend_today_ex_gst)) {
            throw new \InvalidArgumentException('non-nullable spend_today_ex_gst cannot be null');
        }
        $this->container['spend_today_ex_gst'] = $spend_today_ex_gst;

        return $this;
    }

    /**
     * Gets spend_today_gst
     *
     * @return string|null
     */
    public function getSpendTodayGst()
    {
        return $this->container['spend_today_gst'];
    }

    /**
     * Sets spend_today_gst
     *
     * @param string|null $spend_today_gst spend_today_gst
     *
     * @return self
     */
    public function setSpendTodayGst($spend_today_gst)
    {
        if (is_null($spend_today_gst)) {
            throw new \InvalidArgumentException('non-nullable spend_today_gst cannot be null');
        }
        $this->container['spend_today_gst'] = $spend_today_gst;

        return $this;
    }

    /**
     * Gets spend_today_inc_gst
     *
     * @return string|null
     */
    public function getSpendTodayIncGst()
    {
        return $this->container['spend_today_inc_gst'];
    }

    /**
     * Sets spend_today_inc_gst
     *
     * @param string|null $spend_today_inc_gst spend_today_inc_gst
     *
     * @return self
     */
    public function setSpendTodayIncGst($spend_today_inc_gst)
    {
        if (is_null($spend_today_inc_gst)) {
            throw new \InvalidArgumentException('non-nullable spend_today_inc_gst cannot be null');
        }
        $this->container['spend_today_inc_gst'] = $spend_today_inc_gst;

        return $this;
    }

    /**
     * Gets spend_cap_held
     *
     * @return bool|null
     */
    public function getSpendCapHeld()
    {
        return $this->container['spend_cap_held'];
    }

    /**
     * Sets spend_cap_held
     *
     * @param bool|null $spend_cap_held True while the trunk is held for reaching its daily spend cap: its outbound calls are refused, all but 000 and 1800. Inbound calls are not affected.
     *
     * @return self
     */
    public function setSpendCapHeld($spend_cap_held)
    {
        if (is_null($spend_cap_held)) {
            throw new \InvalidArgumentException('non-nullable spend_cap_held cannot be null');
        }
        $this->container['spend_cap_held'] = $spend_cap_held;

        return $this;
    }

    /**
     * Gets spend_cap_releases_at
     *
     * @return \DateTime|null
     */
    public function getSpendCapReleasesAt()
    {
        return $this->container['spend_cap_releases_at'];
    }

    /**
     * Sets spend_cap_releases_at
     *
     * @param \DateTime|null $spend_cap_releases_at When the hold lifts by itself, the next midnight Brisbane time. Null when the trunk is not held. Release Spend Cap Hold lifts it sooner.
     *
     * @return self
     */
    public function setSpendCapReleasesAt($spend_cap_releases_at)
    {
        if (is_null($spend_cap_releases_at)) {
            array_push($this->openAPINullablesSetToNull, 'spend_cap_releases_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('spend_cap_releases_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['spend_cap_releases_at'] = $spend_cap_releases_at;

        return $this;
    }

    /**
     * Gets currency
     *
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->container['currency'];
    }

    /**
     * Sets currency
     *
     * @param string|null $currency currency
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        if (is_null($currency)) {
            throw new \InvalidArgumentException('non-nullable currency cannot be null');
        }
        $allowedValues = $this->getCurrencyAllowableValues();
        if (!in_array($currency, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'currency', must be one of '%s'",
                    $currency,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['currency'] = $currency;

        return $this;
    }

    /**
     * Gets ips
     *
     * @return \CheckNetworks\Api\Generated\Model\TrunkIpEntry[]|null
     */
    public function getIps()
    {
        return $this->container['ips'];
    }

    /**
     * Sets ips
     *
     * @param \CheckNetworks\Api\Generated\Model\TrunkIpEntry[]|null $ips IP ACL entries for this trunk. Empty when the trunk authenticates only by registration.
     *
     * @return self
     */
    public function setIps($ips)
    {
        if (is_null($ips)) {
            throw new \InvalidArgumentException('non-nullable ips cannot be null');
        }
        $this->container['ips'] = $ips;

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
     * @param \DateTime|null $created_at When the trunk was created, in UTC.
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
     * @param \DateTime|null $updated_at When the trunk last changed, in UTC.
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
     * Gets sip_password
     *
     * @return string|null
     */
    public function getSipPassword()
    {
        return $this->container['sip_password'];
    }

    /**
     * Sets sip_password
     *
     * @param string|null $sip_password The generated SIP password. To read it again later, call `GET /trunks/{username}/credential`, which needs `secrets:read`.
     *
     * @return self
     */
    public function setSipPassword($sip_password)
    {
        if (is_null($sip_password)) {
            throw new \InvalidArgumentException('non-nullable sip_password cannot be null');
        }
        $this->container['sip_password'] = $sip_password;

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


