<?php
/**
 * ErrorError
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
 * ErrorError Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class ErrorError implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'Error_error';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'status' => 'int',
        'code' => 'string',
        'reason' => 'string',
        'message_id' => 'string',
        'doc_url' => 'string',
        'carrier_code' => 'string',
        'remaining_rows' => 'int',
        'charge' => '\CheckNetworks\Model\ErrorErrorCharge',
        'consent' => 'string',
        'hold_id' => 'string',
        'held_until' => '\DateTime',
        'allowance' => '\CheckNetworks\Model\ErrorErrorAllowance',
        'res_lines' => '\CheckNetworks\Model\ErrorErrorResLinesInner[]',
        'existing_webhook_id' => 'string',
        'fields' => '\CheckNetworks\Model\ErrorErrorFieldsInner[]'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'status' => null,
        'code' => null,
        'reason' => null,
        'message_id' => null,
        'doc_url' => 'uri',
        'carrier_code' => null,
        'remaining_rows' => null,
        'charge' => null,
        'consent' => null,
        'hold_id' => null,
        'held_until' => 'date-time',
        'allowance' => null,
        'res_lines' => null,
        'existing_webhook_id' => null,
        'fields' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'status' => false,
        'code' => false,
        'reason' => false,
        'message_id' => false,
        'doc_url' => false,
        'carrier_code' => false,
        'remaining_rows' => true,
        'charge' => false,
        'consent' => false,
        'hold_id' => false,
        'held_until' => false,
        'allowance' => false,
        'res_lines' => false,
        'existing_webhook_id' => false,
        'fields' => false
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
        'status' => 'status',
        'code' => 'code',
        'reason' => 'reason',
        'message_id' => 'messageId',
        'doc_url' => 'docUrl',
        'carrier_code' => 'carrierCode',
        'remaining_rows' => 'remainingRows',
        'charge' => 'charge',
        'consent' => 'consent',
        'hold_id' => 'holdId',
        'held_until' => 'heldUntil',
        'allowance' => 'allowance',
        'res_lines' => 'resLines',
        'existing_webhook_id' => 'existingWebhookId',
        'fields' => 'fields'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'status' => 'setStatus',
        'code' => 'setCode',
        'reason' => 'setReason',
        'message_id' => 'setMessageId',
        'doc_url' => 'setDocUrl',
        'carrier_code' => 'setCarrierCode',
        'remaining_rows' => 'setRemainingRows',
        'charge' => 'setCharge',
        'consent' => 'setConsent',
        'hold_id' => 'setHoldId',
        'held_until' => 'setHeldUntil',
        'allowance' => 'setAllowance',
        'res_lines' => 'setResLines',
        'existing_webhook_id' => 'setExistingWebhookId',
        'fields' => 'setFields'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'status' => 'getStatus',
        'code' => 'getCode',
        'reason' => 'getReason',
        'message_id' => 'getMessageId',
        'doc_url' => 'getDocUrl',
        'carrier_code' => 'getCarrierCode',
        'remaining_rows' => 'getRemainingRows',
        'charge' => 'getCharge',
        'consent' => 'getConsent',
        'hold_id' => 'getHoldId',
        'held_until' => 'getHeldUntil',
        'allowance' => 'getAllowance',
        'res_lines' => 'getResLines',
        'existing_webhook_id' => 'getExistingWebhookId',
        'fields' => 'getFields'
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

    public const CODE_BAD_REQUEST = 'bad_request';
    public const CODE_UNAUTHENTICATED = 'unauthenticated';
    public const CODE_FORBIDDEN = 'forbidden';
    public const CODE_NOT_FOUND = 'not_found';
    public const CODE_METHOD_NOT_ALLOWED = 'method_not_allowed';
    public const CODE_CONFLICT = 'conflict';
    public const CODE_VALIDATION_FAILED = 'validation_failed';
    public const CODE_RATE_LIMITED = 'rate_limited';
    public const CODE_SERVER_ERROR = 'server_error';
    public const CODE_NOT_IMPLEMENTED = 'not_implemented';
    public const CODE_SERVICE_UNAVAILABLE = 'service_unavailable';
    public const CODE_CALLER_ID_AUTHORISATION_REQUIRED = 'caller_id_authorisation_required';
    public const CODE_HOLD_EXPIRED = 'hold_expired';
    public const CODE_NO_END_USER_PORTAL_ACCESS = 'no_end_user_portal_access';
    public const CODE_SENDER_NOT_APPROVED = 'sender_not_approved';
    public const CODE_TICKET_NOT_OPEN = 'ticket_not_open';
    public const CODE_COMPLAINT_OUTCOME_REQUIRED = 'complaint_outcome_required';
    public const CODE_FAULT_TICKET_LIMIT = 'fault_ticket_limit';
    public const CODE_KEY_REVOKED = 'key_revoked';
    public const CODE_KEY_EXPIRED = 'key_expired';
    public const CODE_SECRET_ROTATED = 'secret_rotated';
    public const CODE_SCOPES_CHANGED = 'scopes_changed';
    public const CODE_MISSING_SCOPE = 'missing_scope';
    public const CODE_IP_NOT_ALLOWED = 'ip_not_allowed';
    public const CODE_ACCOUNT_SUSPENDED = 'account_suspended';
    public const CODE_ACCOUNT_INACTIVE = 'account_inactive';
    public const CODE_OWNER_PROTECTED = 'owner_protected';
    public const CODE_APP_DISABLED = 'app_disabled';
    public const CODE_APP_ACCESS_REVOKED = 'app_access_revoked';
    public const CODE_IDEMPOTENCY_IN_PROGRESS = 'idempotency_in_progress';
    public const CODE_IDEMPOTENCY_KEY_REUSED = 'idempotency_key_reused';
    public const CODE_ADDRESS_NOT_SERVICEABLE = 'address_not_serviceable';
    public const CODE_DUPLICATE_ORDER = 'duplicate_order';
    public const CODE_INSUFFICIENT_CAPACITY = 'insufficient_capacity';
    public const CODE_AUTH_VALIDATION_FAILED = 'auth_validation_failed';
    public const CODE_CUSTOMER_AUTHORITY_REQUIRED = 'customer_authority_required';
    public const CODE_CHARGE_ACKNOWLEDGEMENT_REQUIRED = 'charge_acknowledgement_required';
    public const CODE_CARRIER_BAD_REQUEST = 'carrier_bad_request';
    public const CODE_CARRIER_UNAUTHENTICATED = 'carrier_unauthenticated';
    public const CODE_CARRIER_RATE_LIMITED = 'carrier_rate_limited';
    public const CODE_CARRIER_UNAVAILABLE = 'carrier_unavailable';
    public const CODE_CARRIER_UNKNOWN = 'carrier_unknown';
    public const CODE_EMERGENCY_CALL = 'emergency_call';
    public const CODE_SQ_BATCH_ALLOWANCE_EXCEEDED = 'sq_batch_allowance_exceeded';
    public const CODE_NO_LIVE_SESSION = 'no_live_session';
    public const CODE_SESSION_NOT_RESETTABLE = 'session_not_resettable';
    public const CODE_RES_LINE_NEEDS_NUMBER = 'res_line_needs_number';
    public const CODE_BUNDLE_DRAFT_LIMIT = 'bundle_draft_limit';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getCodeAllowableValues()
    {
        return [
            self::CODE_BAD_REQUEST,
            self::CODE_UNAUTHENTICATED,
            self::CODE_FORBIDDEN,
            self::CODE_NOT_FOUND,
            self::CODE_METHOD_NOT_ALLOWED,
            self::CODE_CONFLICT,
            self::CODE_VALIDATION_FAILED,
            self::CODE_RATE_LIMITED,
            self::CODE_SERVER_ERROR,
            self::CODE_NOT_IMPLEMENTED,
            self::CODE_SERVICE_UNAVAILABLE,
            self::CODE_CALLER_ID_AUTHORISATION_REQUIRED,
            self::CODE_HOLD_EXPIRED,
            self::CODE_NO_END_USER_PORTAL_ACCESS,
            self::CODE_SENDER_NOT_APPROVED,
            self::CODE_TICKET_NOT_OPEN,
            self::CODE_COMPLAINT_OUTCOME_REQUIRED,
            self::CODE_FAULT_TICKET_LIMIT,
            self::CODE_KEY_REVOKED,
            self::CODE_KEY_EXPIRED,
            self::CODE_SECRET_ROTATED,
            self::CODE_SCOPES_CHANGED,
            self::CODE_MISSING_SCOPE,
            self::CODE_IP_NOT_ALLOWED,
            self::CODE_ACCOUNT_SUSPENDED,
            self::CODE_ACCOUNT_INACTIVE,
            self::CODE_OWNER_PROTECTED,
            self::CODE_APP_DISABLED,
            self::CODE_APP_ACCESS_REVOKED,
            self::CODE_IDEMPOTENCY_IN_PROGRESS,
            self::CODE_IDEMPOTENCY_KEY_REUSED,
            self::CODE_ADDRESS_NOT_SERVICEABLE,
            self::CODE_DUPLICATE_ORDER,
            self::CODE_INSUFFICIENT_CAPACITY,
            self::CODE_AUTH_VALIDATION_FAILED,
            self::CODE_CUSTOMER_AUTHORITY_REQUIRED,
            self::CODE_CHARGE_ACKNOWLEDGEMENT_REQUIRED,
            self::CODE_CARRIER_BAD_REQUEST,
            self::CODE_CARRIER_UNAUTHENTICATED,
            self::CODE_CARRIER_RATE_LIMITED,
            self::CODE_CARRIER_UNAVAILABLE,
            self::CODE_CARRIER_UNKNOWN,
            self::CODE_EMERGENCY_CALL,
            self::CODE_SQ_BATCH_ALLOWANCE_EXCEEDED,
            self::CODE_NO_LIVE_SESSION,
            self::CODE_SESSION_NOT_RESETTABLE,
            self::CODE_RES_LINE_NEEDS_NUMBER,
            self::CODE_BUNDLE_DRAFT_LIMIT,
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
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('code', $data ?? [], null);
        $this->setIfExists('reason', $data ?? [], null);
        $this->setIfExists('message_id', $data ?? [], null);
        $this->setIfExists('doc_url', $data ?? [], null);
        $this->setIfExists('carrier_code', $data ?? [], null);
        $this->setIfExists('remaining_rows', $data ?? [], null);
        $this->setIfExists('charge', $data ?? [], null);
        $this->setIfExists('consent', $data ?? [], null);
        $this->setIfExists('hold_id', $data ?? [], null);
        $this->setIfExists('held_until', $data ?? [], null);
        $this->setIfExists('allowance', $data ?? [], null);
        $this->setIfExists('res_lines', $data ?? [], null);
        $this->setIfExists('existing_webhook_id', $data ?? [], null);
        $this->setIfExists('fields', $data ?? [], null);
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

        if ($this->container['status'] === null) {
            $invalidProperties[] = "'status' can't be null";
        }
        if ($this->container['code'] === null) {
            $invalidProperties[] = "'code' can't be null";
        }
        $allowedValues = $this->getCodeAllowableValues();
        if (!is_null($this->container['code']) && !in_array($this->container['code'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'code', must be one of '%s'",
                $this->container['code'],
                implode("', '", $allowedValues)
            );
        }

        if ($this->container['reason'] === null) {
            $invalidProperties[] = "'reason' can't be null";
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
     * Gets status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->container['status'];
    }

    /**
     * Sets status
     *
     * @param int $status HTTP status code for the error.
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
     * Gets code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->container['code'];
    }

    /**
     * Sets code
     *
     * @param string $code A stable code to branch on; `reason` is free text and may change. Each has an entry at `docUrl` saying what to do. A refusal says why: `key_revoked`, `key_expired`, `secret_rotated`, `scopes_changed` and `missing_scope` need a new key, token or scope; `ip_not_allowed` needs the caller's address on the key's allowlist; `account_suspended` and `account_inactive` need a person on your side; `owner_protected` is a change to an Owner (name, email, role, two-factor or removal), which a key, acting as an Admin, may never make; `app_disabled` and `app_access_revoked` (an OAuth app's token) mean the app must be connected again; `idempotency_in_progress` (with `Retry-After`) means retry later, and `idempotency_key_reused` means send the new request under a new key. The `carrier_` codes and the five before them are the canonical carrier errors, the same whichever carrier refused: `address_not_serviceable`, `duplicate_order`, `insufficient_capacity`, `auth_validation_failed`, `customer_authority_required` and `charge_acknowledgement_required` are a carrier refusing the request on its merits; `carrier_bad_request` is a carrier refusing it for any other reason (its own code is in `carrierCode`); `carrier_unauthenticated`, `carrier_rate_limited` and `carrier_unavailable` are our link to the carrier failing, so retry later; `carrier_unknown` is a carrier failure we could not classify. `caller_id_authorisation_required` is a trunk's caller ID that your account neither holds nor has had approved: ask for it with `POST /caller-id-authorisations`. `hold_expired` is a number order naming a hold that has run out: hold the number again. `no_end_user_portal_access` is a sign-in link asked for an end user who cannot use the end-user portal. `sender_not_approved` is a `POST /sms` from a sender your account has no approval for: request one with `POST /sms/senders`. `emergency_call` is a hang-up refused because the call is to 000, 112 or 106. `sq_batch_allowance_exceeded` is a qualification batch over your account's allowance: `remainingRows` says how many rows you may still send today. `no_live_session` is a session reset with nothing connected to drop, and `session_not_resettable` one on a network where a reset is not possible. `res_line_needs_number` is a change that would leave a Res Line with no number: `resLines` names each line, and the request is sent again with a `resLineResolutions` choice for each. `bundle_draft_limit` is a data order for a bundle that already holds as many draft orders as one may: send or remove some of its drafts, or start another bundle. New codes may be added: treat an unknown code by its `status`.
     *
     * @return self
     */
    public function setCode($code)
    {
        if (is_null($code)) {
            throw new \InvalidArgumentException('non-nullable code cannot be null');
        }
        $allowedValues = $this->getCodeAllowableValues();
        if (!in_array($code, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'code', must be one of '%s'",
                    $code,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['code'] = $code;

        return $this;
    }

    /**
     * Gets reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->container['reason'];
    }

    /**
     * Sets reason
     *
     * @param string $reason Client-friendly reason for the error.
     *
     * @return self
     */
    public function setReason($reason)
    {
        if (is_null($reason)) {
            throw new \InvalidArgumentException('non-nullable reason cannot be null');
        }
        $this->container['reason'] = $reason;

        return $this;
    }

    /**
     * Gets message_id
     *
     * @return string|null
     */
    public function getMessageId()
    {
        return $this->container['message_id'];
    }

    /**
     * Sets message_id
     *
     * @param string|null $message_id This request's id, the same as the `CN-Message-Id` response header. Quote it to support.
     *
     * @return self
     */
    public function setMessageId($message_id)
    {
        if (is_null($message_id)) {
            throw new \InvalidArgumentException('non-nullable message_id cannot be null');
        }
        $this->container['message_id'] = $message_id;

        return $this;
    }

    /**
     * Gets doc_url
     *
     * @return string|null
     */
    public function getDocUrl()
    {
        return $this->container['doc_url'];
    }

    /**
     * Sets doc_url
     *
     * @param string|null $doc_url The `code`'s entry on the error reference page.
     *
     * @return self
     */
    public function setDocUrl($doc_url)
    {
        if (is_null($doc_url)) {
            throw new \InvalidArgumentException('non-nullable doc_url cannot be null');
        }
        $this->container['doc_url'] = $doc_url;

        return $this;
    }

    /**
     * Gets carrier_code
     *
     * @return string|null
     */
    public function getCarrierCode()
    {
        return $this->container['carrier_code'];
    }

    /**
     * Sets carrier_code
     *
     * @param string|null $carrier_code The upstream carrier's own error code, where the failure was a carrier rejection of the request (e.g. NBN's RJ002003 or 001013). Absent when the error did not originate at a carrier.
     *
     * @return self
     */
    public function setCarrierCode($carrier_code)
    {
        if (is_null($carrier_code)) {
            throw new \InvalidArgumentException('non-nullable carrier_code cannot be null');
        }
        $this->container['carrier_code'] = $carrier_code;

        return $this;
    }

    /**
     * Gets remaining_rows
     *
     * @return int|null
     */
    public function getRemainingRows()
    {
        return $this->container['remaining_rows'];
    }

    /**
     * Sets remaining_rows
     *
     * @param int|null $remaining_rows On a `sq_batch_allowance_exceeded` error, the rows your account may still qualify by batch today; null when the day has no row limit and the refusal is the hourly batch count. Absent on every other error.
     *
     * @return self
     */
    public function setRemainingRows($remaining_rows)
    {
        if (is_null($remaining_rows)) {
            array_push($this->openAPINullablesSetToNull, 'remaining_rows');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('remaining_rows', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['remaining_rows'] = $remaining_rows;

        return $this;
    }

    /**
     * Gets charge
     *
     * @return \CheckNetworks\Model\ErrorErrorCharge|null
     */
    public function getCharge()
    {
        return $this->container['charge'];
    }

    /**
     * Sets charge
     *
     * @param \CheckNetworks\Model\ErrorErrorCharge|null $charge charge
     *
     * @return self
     */
    public function setCharge($charge)
    {
        if (is_null($charge)) {
            throw new \InvalidArgumentException('non-nullable charge cannot be null');
        }
        $this->container['charge'] = $charge;

        return $this;
    }

    /**
     * Gets consent
     *
     * @return string|null
     */
    public function getConsent()
    {
        return $this->container['consent'];
    }

    /**
     * Sets consent
     *
     * @param string|null $consent On a `charge_acknowledgement_required` error for call recording, the consent you accept by sending `acknowledgeRecording: true`. Absent on every other error.
     *
     * @return self
     */
    public function setConsent($consent)
    {
        if (is_null($consent)) {
            throw new \InvalidArgumentException('non-nullable consent cannot be null');
        }
        $this->container['consent'] = $consent;

        return $this;
    }

    /**
     * Gets hold_id
     *
     * @return string|null
     */
    public function getHoldId()
    {
        return $this->container['hold_id'];
    }

    /**
     * Sets hold_id
     *
     * @param string|null $hold_id On a `POST /numbers` that bought a number from our supplier but could not place the order, a hold on the number bought, until `heldUntil`. Order again with this `holdId` (same `type`, `szu` and `blockSize`) and the number is taken without buying it again. Absent on every other error.
     *
     * @return self
     */
    public function setHoldId($hold_id)
    {
        if (is_null($hold_id)) {
            throw new \InvalidArgumentException('non-nullable hold_id cannot be null');
        }
        $this->container['hold_id'] = $hold_id;

        return $this;
    }

    /**
     * Gets held_until
     *
     * @return \DateTime|null
     */
    public function getHeldUntil()
    {
        return $this->container['held_until'];
    }

    /**
     * Sets held_until
     *
     * @param \DateTime|null $held_until When the `holdId` beside it runs out. Absent on every other error.
     *
     * @return self
     */
    public function setHeldUntil($held_until)
    {
        if (is_null($held_until)) {
            throw new \InvalidArgumentException('non-nullable held_until cannot be null');
        }
        $this->container['held_until'] = $held_until;

        return $this;
    }

    /**
     * Gets allowance
     *
     * @return \CheckNetworks\Model\ErrorErrorAllowance|null
     */
    public function getAllowance()
    {
        return $this->container['allowance'];
    }

    /**
     * Sets allowance
     *
     * @param \CheckNetworks\Model\ErrorErrorAllowance|null $allowance allowance
     *
     * @return self
     */
    public function setAllowance($allowance)
    {
        if (is_null($allowance)) {
            throw new \InvalidArgumentException('non-nullable allowance cannot be null');
        }
        $this->container['allowance'] = $allowance;

        return $this;
    }

    /**
     * Gets res_lines
     *
     * @return \CheckNetworks\Model\ErrorErrorResLinesInner[]|null
     */
    public function getResLines()
    {
        return $this->container['res_lines'];
    }

    /**
     * Sets res_lines
     *
     * @param \CheckNetworks\Model\ErrorErrorResLinesInner[]|null $res_lines On a `res_line_needs_number` error, each Res Line the change would leave with no number, and the choices for it. Send the request again with `resLineResolutions` holding one choice per line. Absent on every other error.
     *
     * @return self
     */
    public function setResLines($res_lines)
    {
        if (is_null($res_lines)) {
            throw new \InvalidArgumentException('non-nullable res_lines cannot be null');
        }
        $this->container['res_lines'] = $res_lines;

        return $this;
    }

    /**
     * Gets existing_webhook_id
     *
     * @return string|null
     */
    public function getExistingWebhookId()
    {
        return $this->container['existing_webhook_id'];
    }

    /**
     * Sets existing_webhook_id
     *
     * @param string|null $existing_webhook_id On a `409` from `POST /webhooks`, the endpoint already registered at that URL. Absent on every other error.
     *
     * @return self
     */
    public function setExistingWebhookId($existing_webhook_id)
    {
        if (is_null($existing_webhook_id)) {
            throw new \InvalidArgumentException('non-nullable existing_webhook_id cannot be null');
        }
        $this->container['existing_webhook_id'] = $existing_webhook_id;

        return $this;
    }

    /**
     * Gets fields
     *
     * @return \CheckNetworks\Model\ErrorErrorFieldsInner[]|null
     */
    public function getFields()
    {
        return $this->container['fields'];
    }

    /**
     * Sets fields
     *
     * @param \CheckNetworks\Model\ErrorErrorFieldsInner[]|null $fields On a `validation_failed` error, one entry per failed rule, naming the input it was about. Absent on every other error.
     *
     * @return self
     */
    public function setFields($fields)
    {
        if (is_null($fields)) {
            throw new \InvalidArgumentException('non-nullable fields cannot be null');
        }
        $this->container['fields'] = $fields;

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


