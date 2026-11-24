<?php
/**
 * WebhookTicketEventData
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
 * WebhookTicketEventData Class Doc Comment
 *
 * @category Class
 * @description Exactly what every ticket event sends, and the event&#39;s own fields. What depends on who reads the ticket (&#x60;canResolve&#x60;, &#x60;actions&#x60; and the like) is on the ticket read only.
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class WebhookTicketEventData implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'WebhookTicketEvent_data';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'reference' => 'string',
        'subject' => 'string',
        'status' => 'string',
        'priority' => 'string',
        'category' => '\CheckNetworks\Api\Generated\Model\TicketCategory',
        'created_at' => '\DateTime',
        'last_activity_at' => '\DateTime',
        'external_ref' => 'string',
        'metadata' => 'array<string,string>',
        'message_id' => 'string',
        'author' => 'string',
        'from' => 'string',
        'to' => 'string',
        'rating' => 'int'
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
        'subject' => null,
        'status' => null,
        'priority' => null,
        'category' => null,
        'created_at' => 'date-time',
        'last_activity_at' => 'date-time',
        'external_ref' => null,
        'metadata' => null,
        'message_id' => 'uuid',
        'author' => null,
        'from' => null,
        'to' => null,
        'rating' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'reference' => false,
        'subject' => false,
        'status' => false,
        'priority' => false,
        'category' => false,
        'created_at' => false,
        'last_activity_at' => true,
        'external_ref' => true,
        'metadata' => false,
        'message_id' => false,
        'author' => false,
        'from' => false,
        'to' => false,
        'rating' => false
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
        'subject' => 'subject',
        'status' => 'status',
        'priority' => 'priority',
        'category' => 'category',
        'created_at' => 'createdAt',
        'last_activity_at' => 'lastActivityAt',
        'external_ref' => 'externalRef',
        'metadata' => 'metadata',
        'message_id' => 'messageId',
        'author' => 'author',
        'from' => 'from',
        'to' => 'to',
        'rating' => 'rating'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'reference' => 'setReference',
        'subject' => 'setSubject',
        'status' => 'setStatus',
        'priority' => 'setPriority',
        'category' => 'setCategory',
        'created_at' => 'setCreatedAt',
        'last_activity_at' => 'setLastActivityAt',
        'external_ref' => 'setExternalRef',
        'metadata' => 'setMetadata',
        'message_id' => 'setMessageId',
        'author' => 'setAuthor',
        'from' => 'setFrom',
        'to' => 'setTo',
        'rating' => 'setRating'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'reference' => 'getReference',
        'subject' => 'getSubject',
        'status' => 'getStatus',
        'priority' => 'getPriority',
        'category' => 'getCategory',
        'created_at' => 'getCreatedAt',
        'last_activity_at' => 'getLastActivityAt',
        'external_ref' => 'getExternalRef',
        'metadata' => 'getMetadata',
        'message_id' => 'getMessageId',
        'author' => 'getAuthor',
        'from' => 'getFrom',
        'to' => 'getTo',
        'rating' => 'getRating'
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

    public const STATUS_OPEN = 'open';
    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';
    public const AUTHOR_SUPPORT = 'support';
    public const AUTHOR_CUSTOMER = 'customer';
    public const AUTHOR_API = 'api';
    public const FROM_OPEN = 'open';
    public const FROM_PENDING = 'pending';
    public const FROM_RESOLVED = 'resolved';
    public const FROM_CLOSED = 'closed';
    public const TO_OPEN = 'open';
    public const TO_PENDING = 'pending';
    public const TO_RESOLVED = 'resolved';
    public const TO_CLOSED = 'closed';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_PENDING,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getPriorityAllowableValues()
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_NORMAL,
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAuthorAllowableValues()
    {
        return [
            self::AUTHOR_SUPPORT,
            self::AUTHOR_CUSTOMER,
            self::AUTHOR_API,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getFromAllowableValues()
    {
        return [
            self::FROM_OPEN,
            self::FROM_PENDING,
            self::FROM_RESOLVED,
            self::FROM_CLOSED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getToAllowableValues()
    {
        return [
            self::TO_OPEN,
            self::TO_PENDING,
            self::TO_RESOLVED,
            self::TO_CLOSED,
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
        $this->setIfExists('subject', $data ?? [], null);
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('priority', $data ?? [], null);
        $this->setIfExists('category', $data ?? [], null);
        $this->setIfExists('created_at', $data ?? [], null);
        $this->setIfExists('last_activity_at', $data ?? [], null);
        $this->setIfExists('external_ref', $data ?? [], null);
        $this->setIfExists('metadata', $data ?? [], null);
        $this->setIfExists('message_id', $data ?? [], null);
        $this->setIfExists('author', $data ?? [], null);
        $this->setIfExists('from', $data ?? [], null);
        $this->setIfExists('to', $data ?? [], null);
        $this->setIfExists('rating', $data ?? [], null);
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

        if ($this->container['reference'] === null) {
            $invalidProperties[] = "'reference' can't be null";
        }
        if ($this->container['subject'] === null) {
            $invalidProperties[] = "'subject' can't be null";
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

        if ($this->container['priority'] === null) {
            $invalidProperties[] = "'priority' can't be null";
        }
        $allowedValues = $this->getPriorityAllowableValues();
        if (!is_null($this->container['priority']) && !in_array($this->container['priority'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'priority', must be one of '%s'",
                $this->container['priority'],
                implode("', '", $allowedValues)
            );
        }

        if ($this->container['category'] === null) {
            $invalidProperties[] = "'category' can't be null";
        }
        if ($this->container['created_at'] === null) {
            $invalidProperties[] = "'created_at' can't be null";
        }
        if ($this->container['last_activity_at'] === null && !$this->isNullableSetToNull('last_activity_at')) {
            $invalidProperties[] = "'last_activity_at' is required";
        }
        if ($this->container['external_ref'] === null && !$this->isNullableSetToNull('external_ref')) {
            $invalidProperties[] = "'external_ref' is required";
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

        if ($this->container['metadata'] === null) {
            $invalidProperties[] = "'metadata' can't be null";
        }
        if ((count($this->container['metadata']) > 20)) {
            $invalidProperties[] = "invalid value for 'metadata', number of items must be less than or equal to 20.";
        }

        $allowedValues = $this->getAuthorAllowableValues();
        if (!is_null($this->container['author']) && !in_array($this->container['author'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'author', must be one of '%s'",
                $this->container['author'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getFromAllowableValues();
        if (!is_null($this->container['from']) && !in_array($this->container['from'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'from', must be one of '%s'",
                $this->container['from'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getToAllowableValues();
        if (!is_null($this->container['to']) && !in_array($this->container['to'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'to', must be one of '%s'",
                $this->container['to'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['rating']) && ($this->container['rating'] > 5)) {
            $invalidProperties[] = "invalid value for 'rating', must be smaller than or equal to 5.";
        }

        if (!is_null($this->container['rating']) && ($this->container['rating'] < 1)) {
            $invalidProperties[] = "invalid value for 'rating', must be bigger than or equal to 1.";
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
     * @return string
     */
    public function getReference()
    {
        return $this->container['reference'];
    }

    /**
     * Sets reference
     *
     * @param string $reference The public ticket reference, as `GET /tickets/{ticketRef}` takes it.
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
     * Gets subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->container['subject'];
    }

    /**
     * Sets subject
     *
     * @param string $subject subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        if (is_null($subject)) {
            throw new \InvalidArgumentException('non-nullable subject cannot be null');
        }
        $this->container['subject'] = $subject;

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
     * @param string $status The ticket's status after the event.
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
     * Gets priority
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->container['priority'];
    }

    /**
     * Sets priority
     *
     * @param string $priority priority
     *
     * @return self
     */
    public function setPriority($priority)
    {
        if (is_null($priority)) {
            throw new \InvalidArgumentException('non-nullable priority cannot be null');
        }
        $allowedValues = $this->getPriorityAllowableValues();
        if (!in_array($priority, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'priority', must be one of '%s'",
                    $priority,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['priority'] = $priority;

        return $this;
    }

    /**
     * Gets category
     *
     * @return \CheckNetworks\Api\Generated\Model\TicketCategory
     */
    public function getCategory()
    {
        return $this->container['category'];
    }

    /**
     * Sets category
     *
     * @param \CheckNetworks\Api\Generated\Model\TicketCategory $category category
     *
     * @return self
     */
    public function setCategory($category)
    {
        if (is_null($category)) {
            throw new \InvalidArgumentException('non-nullable category cannot be null');
        }
        $this->container['category'] = $category;

        return $this;
    }

    /**
     * Gets created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->container['created_at'];
    }

    /**
     * Sets created_at
     *
     * @param \DateTime $created_at created_at
     *
     * @return self
     */
    public function setCreatedAt($created_at)
    {
        if (is_null($created_at)) {
            throw new \InvalidArgumentException('non-nullable created_at cannot be null');
        }
        $this->container['created_at'] = $created_at;

        return $this;
    }

    /**
     * Gets last_activity_at
     *
     * @return \DateTime|null
     */
    public function getLastActivityAt()
    {
        return $this->container['last_activity_at'];
    }

    /**
     * Sets last_activity_at
     *
     * @param \DateTime|null $last_activity_at The ticket's last message or change.
     *
     * @return self
     */
    public function setLastActivityAt($last_activity_at)
    {
        if (is_null($last_activity_at)) {
            array_push($this->openAPINullablesSetToNull, 'last_activity_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('last_activity_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['last_activity_at'] = $last_activity_at;

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
            throw new \InvalidArgumentException('invalid length for $external_ref when calling WebhookTicketEventData., must be smaller than or equal to 64.');
        }
        if (!is_null($external_ref) && (mb_strlen($external_ref) < 1)) {
            throw new \InvalidArgumentException('invalid length for $external_ref when calling WebhookTicketEventData., must be bigger than or equal to 1.');
        }
        if (!is_null($external_ref) && (!preg_match("/^[A-Za-z0-9_.\\-]+$/", ObjectSerializer::toString($external_ref)))) {
            throw new \InvalidArgumentException("invalid value for \$external_ref when calling WebhookTicketEventData., must conform to the pattern /^[A-Za-z0-9_.\\-]+$/.");
        }

        $this->container['external_ref'] = $external_ref;

        return $this;
    }

    /**
     * Gets metadata
     *
     * @return array<string,string>
     */
    public function getMetadata()
    {
        return $this->container['metadata'];
    }

    /**
     * Sets metadata
     *
     * @param array<string,string> $metadata Your own key-value pairs on this object: at most 20 keys, each up to 40 letters, digits, `_`, `.` or `-`, each value text of up to 255 characters. We store them and send them back on reads and webhooks; we never act on them. A map you send replaces the one held; send `{}` or null to clear it. An empty object when none is set.
     *
     * @return self
     */
    public function setMetadata($metadata)
    {
        if (is_null($metadata)) {
            throw new \InvalidArgumentException('non-nullable metadata cannot be null');
        }
        if ((count($metadata) > 20)) {
            throw new \InvalidArgumentException('invalid value for $metadata when calling WebhookTicketEventData., number of items must be less than or equal to 20.');
        }
        $this->container['metadata'] = $metadata;

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
     * @param string|null $message_id `ticket.replied` only — the new message's `id`, as `GET /tickets/{ticketRef}` lists it.
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
     * Gets author
     *
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->container['author'];
    }

    /**
     * Sets author
     *
     * @param string|null $author `ticket.replied` only — who wrote the reply.
     *
     * @return self
     */
    public function setAuthor($author)
    {
        if (is_null($author)) {
            throw new \InvalidArgumentException('non-nullable author cannot be null');
        }
        $allowedValues = $this->getAuthorAllowableValues();
        if (!in_array($author, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'author', must be one of '%s'",
                    $author,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['author'] = $author;

        return $this;
    }

    /**
     * Gets from
     *
     * @return string|null
     */
    public function getFrom()
    {
        return $this->container['from'];
    }

    /**
     * Sets from
     *
     * @param string|null $from `ticket.statusChanged` and `ticket.informationRequired` — the previous status.
     *
     * @return self
     */
    public function setFrom($from)
    {
        if (is_null($from)) {
            throw new \InvalidArgumentException('non-nullable from cannot be null');
        }
        $allowedValues = $this->getFromAllowableValues();
        if (!in_array($from, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'from', must be one of '%s'",
                    $from,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['from'] = $from;

        return $this;
    }

    /**
     * Gets to
     *
     * @return string|null
     */
    public function getTo()
    {
        return $this->container['to'];
    }

    /**
     * Sets to
     *
     * @param string|null $to `ticket.statusChanged` only — the new status.
     *
     * @return self
     */
    public function setTo($to)
    {
        if (is_null($to)) {
            throw new \InvalidArgumentException('non-nullable to cannot be null');
        }
        $allowedValues = $this->getToAllowableValues();
        if (!in_array($to, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'to', must be one of '%s'",
                    $to,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['to'] = $to;

        return $this;
    }

    /**
     * Gets rating
     *
     * @return int|null
     */
    public function getRating()
    {
        return $this->container['rating'];
    }

    /**
     * Sets rating
     *
     * @param int|null $rating `ticket.csatSubmitted` only.
     *
     * @return self
     */
    public function setRating($rating)
    {
        if (is_null($rating)) {
            throw new \InvalidArgumentException('non-nullable rating cannot be null');
        }
        if (($rating > 5)) {
            throw new \InvalidArgumentException('invalid value for $rating when calling WebhookTicketEventData., must be smaller than or equal to 5.');
        }
        if (($rating < 1)) {
            throw new \InvalidArgumentException('invalid value for $rating when calling WebhookTicketEventData., must be bigger than or equal to 1.');
        }

        $this->container['rating'] = $rating;

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


