<?php
/**
 * EndUserSafetyRecord
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
 * EndUserSafetyRecord Class Doc Comment
 *
 * @category Class
 * @description A family violence safety record on an end user (F2025L00664). Legal obligations attach to this data: see the End users safety help page. While one is active with &#x60;suppressContactDisclosure&#x60;, the end user&#39;s contacts are masked on every other read. The record&#39;s own safe contacts (the safe number, email and postal address) are masked on every read of it, this one included: &#x60;safeContactNumber&#x60;, &#x60;safeContactEmail&#x60; and &#x60;safePostalAddress&#x60; are always null here, &#x60;safeContactsMasked&#x60; says whether any is recorded, and only &#x60;POST /end-users/{endUser}/safety-records/{recordId}/safe-contacts/reveal&#x60; returns them.
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class EndUserSafetyRecord implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'EndUserSafetyRecord';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'status' => 'string',
        'identified_reason' => 'string',
        'agreed_communication_method' => 'string',
        'agreed_communication_time' => 'string',
        'suppress_contact_disclosure' => 'bool',
        'safe_contacts_masked' => 'bool',
        'safe_contact_number' => 'string',
        'safe_contact_email' => 'string',
        'safe_postal_address' => '\CheckNetworks\Model\EndUserAddress',
        'bill_delivery' => 'string',
        'bill_delivery_note' => 'string',
        'bill_delivery_request' => 'string',
        'notes' => 'string',
        'identified_at' => '\DateTime',
        'identified_by' => 'string',
        'safety_confirmed_at' => '\DateTime',
        'support_advised_at' => '\DateTime',
        'support_advised_by' => 'string',
        'further_support_requested' => 'bool',
        'closed_at' => '\DateTime',
        'closed_by' => 'string',
        'retention_expires_at' => '\DateTime'
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
        'status' => null,
        'identified_reason' => null,
        'agreed_communication_method' => null,
        'agreed_communication_time' => null,
        'suppress_contact_disclosure' => null,
        'safe_contacts_masked' => null,
        'safe_contact_number' => null,
        'safe_contact_email' => null,
        'safe_postal_address' => null,
        'bill_delivery' => null,
        'bill_delivery_note' => null,
        'bill_delivery_request' => null,
        'notes' => null,
        'identified_at' => 'date-time',
        'identified_by' => null,
        'safety_confirmed_at' => 'date-time',
        'support_advised_at' => 'date-time',
        'support_advised_by' => null,
        'further_support_requested' => null,
        'closed_at' => 'date-time',
        'closed_by' => null,
        'retention_expires_at' => 'date-time'
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'status' => false,
        'identified_reason' => false,
        'agreed_communication_method' => true,
        'agreed_communication_time' => true,
        'suppress_contact_disclosure' => false,
        'safe_contacts_masked' => false,
        'safe_contact_number' => true,
        'safe_contact_email' => true,
        'safe_postal_address' => true,
        'bill_delivery' => true,
        'bill_delivery_note' => true,
        'bill_delivery_request' => true,
        'notes' => true,
        'identified_at' => true,
        'identified_by' => true,
        'safety_confirmed_at' => true,
        'support_advised_at' => true,
        'support_advised_by' => true,
        'further_support_requested' => true,
        'closed_at' => true,
        'closed_by' => true,
        'retention_expires_at' => true
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
        'status' => 'status',
        'identified_reason' => 'identifiedReason',
        'agreed_communication_method' => 'agreedCommunicationMethod',
        'agreed_communication_time' => 'agreedCommunicationTime',
        'suppress_contact_disclosure' => 'suppressContactDisclosure',
        'safe_contacts_masked' => 'safeContactsMasked',
        'safe_contact_number' => 'safeContactNumber',
        'safe_contact_email' => 'safeContactEmail',
        'safe_postal_address' => 'safePostalAddress',
        'bill_delivery' => 'billDelivery',
        'bill_delivery_note' => 'billDeliveryNote',
        'bill_delivery_request' => 'billDeliveryRequest',
        'notes' => 'notes',
        'identified_at' => 'identifiedAt',
        'identified_by' => 'identifiedBy',
        'safety_confirmed_at' => 'safetyConfirmedAt',
        'support_advised_at' => 'supportAdvisedAt',
        'support_advised_by' => 'supportAdvisedBy',
        'further_support_requested' => 'furtherSupportRequested',
        'closed_at' => 'closedAt',
        'closed_by' => 'closedBy',
        'retention_expires_at' => 'retentionExpiresAt'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'status' => 'setStatus',
        'identified_reason' => 'setIdentifiedReason',
        'agreed_communication_method' => 'setAgreedCommunicationMethod',
        'agreed_communication_time' => 'setAgreedCommunicationTime',
        'suppress_contact_disclosure' => 'setSuppressContactDisclosure',
        'safe_contacts_masked' => 'setSafeContactsMasked',
        'safe_contact_number' => 'setSafeContactNumber',
        'safe_contact_email' => 'setSafeContactEmail',
        'safe_postal_address' => 'setSafePostalAddress',
        'bill_delivery' => 'setBillDelivery',
        'bill_delivery_note' => 'setBillDeliveryNote',
        'bill_delivery_request' => 'setBillDeliveryRequest',
        'notes' => 'setNotes',
        'identified_at' => 'setIdentifiedAt',
        'identified_by' => 'setIdentifiedBy',
        'safety_confirmed_at' => 'setSafetyConfirmedAt',
        'support_advised_at' => 'setSupportAdvisedAt',
        'support_advised_by' => 'setSupportAdvisedBy',
        'further_support_requested' => 'setFurtherSupportRequested',
        'closed_at' => 'setClosedAt',
        'closed_by' => 'setClosedBy',
        'retention_expires_at' => 'setRetentionExpiresAt'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'status' => 'getStatus',
        'identified_reason' => 'getIdentifiedReason',
        'agreed_communication_method' => 'getAgreedCommunicationMethod',
        'agreed_communication_time' => 'getAgreedCommunicationTime',
        'suppress_contact_disclosure' => 'getSuppressContactDisclosure',
        'safe_contacts_masked' => 'getSafeContactsMasked',
        'safe_contact_number' => 'getSafeContactNumber',
        'safe_contact_email' => 'getSafeContactEmail',
        'safe_postal_address' => 'getSafePostalAddress',
        'bill_delivery' => 'getBillDelivery',
        'bill_delivery_note' => 'getBillDeliveryNote',
        'bill_delivery_request' => 'getBillDeliveryRequest',
        'notes' => 'getNotes',
        'identified_at' => 'getIdentifiedAt',
        'identified_by' => 'getIdentifiedBy',
        'safety_confirmed_at' => 'getSafetyConfirmedAt',
        'support_advised_at' => 'getSupportAdvisedAt',
        'support_advised_by' => 'getSupportAdvisedBy',
        'further_support_requested' => 'getFurtherSupportRequested',
        'closed_at' => 'getClosedAt',
        'closed_by' => 'getClosedBy',
        'retention_expires_at' => 'getRetentionExpiresAt'
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

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';
    public const IDENTIFIED_REASON_DOMESTIC_FAMILY_VIOLENCE = 'domestic_family_violence';
    public const IDENTIFIED_REASON_NON_DOMESTIC_SEXUAL_VIOLENCE = 'non_domestic_sexual_violence';
    public const AGREED_COMMUNICATION_METHOD_PHONE = 'phone';
    public const AGREED_COMMUNICATION_METHOD_SMS = 'sms';
    public const AGREED_COMMUNICATION_METHOD_EMAIL = 'email';
    public const AGREED_COMMUNICATION_METHOD_POST = 'post';
    public const AGREED_COMMUNICATION_METHOD_ONLINE_CHAT = 'online_chat';
    public const AGREED_COMMUNICATION_METHOD_WEBFORM = 'webform';
    public const BILL_DELIVERY_SAFE_EMAIL = 'safe_email';
    public const BILL_DELIVERY_SAFE_POST = 'safe_post';
    public const BILL_DELIVERY_PORTAL_ONLY = 'portal_only';
    public const BILL_DELIVERY_NONE = 'none';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getStatusAllowableValues()
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_CLOSED,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getIdentifiedReasonAllowableValues()
    {
        return [
            self::IDENTIFIED_REASON_DOMESTIC_FAMILY_VIOLENCE,
            self::IDENTIFIED_REASON_NON_DOMESTIC_SEXUAL_VIOLENCE,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAgreedCommunicationMethodAllowableValues()
    {
        return [
            self::AGREED_COMMUNICATION_METHOD_PHONE,
            self::AGREED_COMMUNICATION_METHOD_SMS,
            self::AGREED_COMMUNICATION_METHOD_EMAIL,
            self::AGREED_COMMUNICATION_METHOD_POST,
            self::AGREED_COMMUNICATION_METHOD_ONLINE_CHAT,
            self::AGREED_COMMUNICATION_METHOD_WEBFORM,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getBillDeliveryAllowableValues()
    {
        return [
            self::BILL_DELIVERY_SAFE_EMAIL,
            self::BILL_DELIVERY_SAFE_POST,
            self::BILL_DELIVERY_PORTAL_ONLY,
            self::BILL_DELIVERY_NONE,
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
        $this->setIfExists('status', $data ?? [], null);
        $this->setIfExists('identified_reason', $data ?? [], null);
        $this->setIfExists('agreed_communication_method', $data ?? [], null);
        $this->setIfExists('agreed_communication_time', $data ?? [], null);
        $this->setIfExists('suppress_contact_disclosure', $data ?? [], null);
        $this->setIfExists('safe_contacts_masked', $data ?? [], null);
        $this->setIfExists('safe_contact_number', $data ?? [], null);
        $this->setIfExists('safe_contact_email', $data ?? [], null);
        $this->setIfExists('safe_postal_address', $data ?? [], null);
        $this->setIfExists('bill_delivery', $data ?? [], null);
        $this->setIfExists('bill_delivery_note', $data ?? [], null);
        $this->setIfExists('bill_delivery_request', $data ?? [], null);
        $this->setIfExists('notes', $data ?? [], null);
        $this->setIfExists('identified_at', $data ?? [], null);
        $this->setIfExists('identified_by', $data ?? [], null);
        $this->setIfExists('safety_confirmed_at', $data ?? [], null);
        $this->setIfExists('support_advised_at', $data ?? [], null);
        $this->setIfExists('support_advised_by', $data ?? [], null);
        $this->setIfExists('further_support_requested', $data ?? [], null);
        $this->setIfExists('closed_at', $data ?? [], null);
        $this->setIfExists('closed_by', $data ?? [], null);
        $this->setIfExists('retention_expires_at', $data ?? [], null);
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

        if ($this->container['id'] === null) {
            $invalidProperties[] = "'id' can't be null";
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

        if ($this->container['identified_reason'] === null) {
            $invalidProperties[] = "'identified_reason' can't be null";
        }
        $allowedValues = $this->getIdentifiedReasonAllowableValues();
        if (!is_null($this->container['identified_reason']) && !in_array($this->container['identified_reason'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'identified_reason', must be one of '%s'",
                $this->container['identified_reason'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getAgreedCommunicationMethodAllowableValues();
        if (!is_null($this->container['agreed_communication_method']) && !in_array($this->container['agreed_communication_method'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'agreed_communication_method', must be one of '%s'",
                $this->container['agreed_communication_method'],
                implode("', '", $allowedValues)
            );
        }

        if ($this->container['suppress_contact_disclosure'] === null) {
            $invalidProperties[] = "'suppress_contact_disclosure' can't be null";
        }
        if ($this->container['safe_contacts_masked'] === null) {
            $invalidProperties[] = "'safe_contacts_masked' can't be null";
        }
        $allowedValues = $this->getBillDeliveryAllowableValues();
        if (!is_null($this->container['bill_delivery']) && !in_array($this->container['bill_delivery'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'bill_delivery', must be one of '%s'",
                $this->container['bill_delivery'],
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
     * @return string
     */
    public function getId()
    {
        return $this->container['id'];
    }

    /**
     * Sets id
     *
     * @param string $id The record's public id.
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
     * @param string $status status
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
     * Gets identified_reason
     *
     * @return string
     */
    public function getIdentifiedReason()
    {
        return $this->container['identified_reason'];
    }

    /**
     * Sets identified_reason
     *
     * @param string $identified_reason identified_reason
     *
     * @return self
     */
    public function setIdentifiedReason($identified_reason)
    {
        if (is_null($identified_reason)) {
            throw new \InvalidArgumentException('non-nullable identified_reason cannot be null');
        }
        $allowedValues = $this->getIdentifiedReasonAllowableValues();
        if (!in_array($identified_reason, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'identified_reason', must be one of '%s'",
                    $identified_reason,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['identified_reason'] = $identified_reason;

        return $this;
    }

    /**
     * Gets agreed_communication_method
     *
     * @return string|null
     */
    public function getAgreedCommunicationMethod()
    {
        return $this->container['agreed_communication_method'];
    }

    /**
     * Sets agreed_communication_method
     *
     * @param string|null $agreed_communication_method agreed_communication_method
     *
     * @return self
     */
    public function setAgreedCommunicationMethod($agreed_communication_method)
    {
        if (is_null($agreed_communication_method)) {
            array_push($this->openAPINullablesSetToNull, 'agreed_communication_method');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('agreed_communication_method', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getAgreedCommunicationMethodAllowableValues();
        if (!is_null($agreed_communication_method) && !in_array($agreed_communication_method, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'agreed_communication_method', must be one of '%s'",
                    $agreed_communication_method,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['agreed_communication_method'] = $agreed_communication_method;

        return $this;
    }

    /**
     * Gets agreed_communication_time
     *
     * @return string|null
     */
    public function getAgreedCommunicationTime()
    {
        return $this->container['agreed_communication_time'];
    }

    /**
     * Sets agreed_communication_time
     *
     * @param string|null $agreed_communication_time agreed_communication_time
     *
     * @return self
     */
    public function setAgreedCommunicationTime($agreed_communication_time)
    {
        if (is_null($agreed_communication_time)) {
            array_push($this->openAPINullablesSetToNull, 'agreed_communication_time');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('agreed_communication_time', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['agreed_communication_time'] = $agreed_communication_time;

        return $this;
    }

    /**
     * Gets suppress_contact_disclosure
     *
     * @return bool
     */
    public function getSuppressContactDisclosure()
    {
        return $this->container['suppress_contact_disclosure'];
    }

    /**
     * Sets suppress_contact_disclosure
     *
     * @param bool $suppress_contact_disclosure suppress_contact_disclosure
     *
     * @return self
     */
    public function setSuppressContactDisclosure($suppress_contact_disclosure)
    {
        if (is_null($suppress_contact_disclosure)) {
            throw new \InvalidArgumentException('non-nullable suppress_contact_disclosure cannot be null');
        }
        $this->container['suppress_contact_disclosure'] = $suppress_contact_disclosure;

        return $this;
    }

    /**
     * Gets safe_contacts_masked
     *
     * @return bool
     */
    public function getSafeContactsMasked()
    {
        return $this->container['safe_contacts_masked'];
    }

    /**
     * Sets safe_contacts_masked
     *
     * @param bool $safe_contacts_masked Whether the record holds a safe number, safe email or safe postal address. They are masked on this read; the safe-contacts reveal returns them, audited.
     *
     * @return self
     */
    public function setSafeContactsMasked($safe_contacts_masked)
    {
        if (is_null($safe_contacts_masked)) {
            throw new \InvalidArgumentException('non-nullable safe_contacts_masked cannot be null');
        }
        $this->container['safe_contacts_masked'] = $safe_contacts_masked;

        return $this;
    }

    /**
     * Gets safe_contact_number
     *
     * @return string|null
     */
    public function getSafeContactNumber()
    {
        return $this->container['safe_contact_number'];
    }

    /**
     * Sets safe_contact_number
     *
     * @param string|null $safe_contact_number Always null: masked. Read it with the safe-contacts reveal. Write it on the record's create or update; left out of an update, it is kept.
     *
     * @return self
     */
    public function setSafeContactNumber($safe_contact_number)
    {
        if (is_null($safe_contact_number)) {
            array_push($this->openAPINullablesSetToNull, 'safe_contact_number');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('safe_contact_number', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['safe_contact_number'] = $safe_contact_number;

        return $this;
    }

    /**
     * Gets safe_contact_email
     *
     * @return string|null
     */
    public function getSafeContactEmail()
    {
        return $this->container['safe_contact_email'];
    }

    /**
     * Sets safe_contact_email
     *
     * @param string|null $safe_contact_email Always null: masked. Read it with the safe-contacts reveal. Write it on the record's create or update; left out of an update, it is kept.
     *
     * @return self
     */
    public function setSafeContactEmail($safe_contact_email)
    {
        if (is_null($safe_contact_email)) {
            array_push($this->openAPINullablesSetToNull, 'safe_contact_email');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('safe_contact_email', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['safe_contact_email'] = $safe_contact_email;

        return $this;
    }

    /**
     * Gets safe_postal_address
     *
     * @return \CheckNetworks\Model\EndUserAddress|null
     */
    public function getSafePostalAddress()
    {
        return $this->container['safe_postal_address'];
    }

    /**
     * Sets safe_postal_address
     *
     * @param \CheckNetworks\Model\EndUserAddress|null $safe_postal_address Where their bills are posted under `safe_post`, never the end user's own address. Always null here: masked, as the safe number and email are. Read it with the safe-contacts reveal.
     *
     * @return self
     */
    public function setSafePostalAddress($safe_postal_address)
    {
        if (is_null($safe_postal_address)) {
            array_push($this->openAPINullablesSetToNull, 'safe_postal_address');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('safe_postal_address', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['safe_postal_address'] = $safe_postal_address;

        return $this;
    }

    /**
     * Gets bill_delivery
     *
     * @return string|null
     */
    public function getBillDelivery()
    {
        return $this->container['bill_delivery'];
    }

    /**
     * Sets bill_delivery
     *
     * @param string|null $bill_delivery How the end user wants their bills while the record is active: `safe_email` emails bills and billing notices to `safeContactEmail` only; `safe_post` sends none and alerts you to post them to `safePostalAddress`; `portal_only` sends none, and they read their bills in the portal; `none` sends no bills or billing notices. Null: bills follow `agreedCommunicationMethod`.
     *
     * @return self
     */
    public function setBillDelivery($bill_delivery)
    {
        if (is_null($bill_delivery)) {
            array_push($this->openAPINullablesSetToNull, 'bill_delivery');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('bill_delivery', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $allowedValues = $this->getBillDeliveryAllowableValues();
        if (!is_null($bill_delivery) && !in_array($bill_delivery, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'bill_delivery', must be one of '%s'",
                    $bill_delivery,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['bill_delivery'] = $bill_delivery;

        return $this;
    }

    /**
     * Gets bill_delivery_note
     *
     * @return string|null
     */
    public function getBillDeliveryNote()
    {
        return $this->container['bill_delivery_note'];
    }

    /**
     * Sets bill_delivery_note
     *
     * @param string|null $bill_delivery_note Anything more about how they want their bills.
     *
     * @return self
     */
    public function setBillDeliveryNote($bill_delivery_note)
    {
        if (is_null($bill_delivery_note)) {
            array_push($this->openAPINullablesSetToNull, 'bill_delivery_note');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('bill_delivery_note', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['bill_delivery_note'] = $bill_delivery_note;

        return $this;
    }

    /**
     * Gets bill_delivery_request
     *
     * @return string|null
     * @deprecated
     */
    public function getBillDeliveryRequest()
    {
        return $this->container['bill_delivery_request'];
    }

    /**
     * Sets bill_delivery_request
     *
     * @param string|null $bill_delivery_request The same as `billDeliveryNote`. Deprecated: read `billDelivery` and `billDeliveryNote`.
     *
     * @return self
     * @deprecated
     */
    public function setBillDeliveryRequest($bill_delivery_request)
    {
        if (is_null($bill_delivery_request)) {
            array_push($this->openAPINullablesSetToNull, 'bill_delivery_request');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('bill_delivery_request', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['bill_delivery_request'] = $bill_delivery_request;

        return $this;
    }

    /**
     * Gets notes
     *
     * @return string|null
     */
    public function getNotes()
    {
        return $this->container['notes'];
    }

    /**
     * Sets notes
     *
     * @param string|null $notes notes
     *
     * @return self
     */
    public function setNotes($notes)
    {
        if (is_null($notes)) {
            array_push($this->openAPINullablesSetToNull, 'notes');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('notes', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['notes'] = $notes;

        return $this;
    }

    /**
     * Gets identified_at
     *
     * @return \DateTime|null
     */
    public function getIdentifiedAt()
    {
        return $this->container['identified_at'];
    }

    /**
     * Sets identified_at
     *
     * @param \DateTime|null $identified_at identified_at
     *
     * @return self
     */
    public function setIdentifiedAt($identified_at)
    {
        if (is_null($identified_at)) {
            array_push($this->openAPINullablesSetToNull, 'identified_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('identified_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['identified_at'] = $identified_at;

        return $this;
    }

    /**
     * Gets identified_by
     *
     * @return string|null
     */
    public function getIdentifiedBy()
    {
        return $this->container['identified_by'];
    }

    /**
     * Sets identified_by
     *
     * @param string|null $identified_by The portal user who raised it; null when raised by API (the audit log names the key).
     *
     * @return self
     */
    public function setIdentifiedBy($identified_by)
    {
        if (is_null($identified_by)) {
            array_push($this->openAPINullablesSetToNull, 'identified_by');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('identified_by', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['identified_by'] = $identified_by;

        return $this;
    }

    /**
     * Gets safety_confirmed_at
     *
     * @return \DateTime|null
     */
    public function getSafetyConfirmedAt()
    {
        return $this->container['safety_confirmed_at'];
    }

    /**
     * Sets safety_confirmed_at
     *
     * @param \DateTime|null $safety_confirmed_at safety_confirmed_at
     *
     * @return self
     */
    public function setSafetyConfirmedAt($safety_confirmed_at)
    {
        if (is_null($safety_confirmed_at)) {
            array_push($this->openAPINullablesSetToNull, 'safety_confirmed_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('safety_confirmed_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['safety_confirmed_at'] = $safety_confirmed_at;

        return $this;
    }

    /**
     * Gets support_advised_at
     *
     * @return \DateTime|null
     */
    public function getSupportAdvisedAt()
    {
        return $this->container['support_advised_at'];
    }

    /**
     * Sets support_advised_at
     *
     * @param \DateTime|null $support_advised_at support_advised_at
     *
     * @return self
     */
    public function setSupportAdvisedAt($support_advised_at)
    {
        if (is_null($support_advised_at)) {
            array_push($this->openAPINullablesSetToNull, 'support_advised_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('support_advised_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['support_advised_at'] = $support_advised_at;

        return $this;
    }

    /**
     * Gets support_advised_by
     *
     * @return string|null
     */
    public function getSupportAdvisedBy()
    {
        return $this->container['support_advised_by'];
    }

    /**
     * Sets support_advised_by
     *
     * @param string|null $support_advised_by support_advised_by
     *
     * @return self
     */
    public function setSupportAdvisedBy($support_advised_by)
    {
        if (is_null($support_advised_by)) {
            array_push($this->openAPINullablesSetToNull, 'support_advised_by');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('support_advised_by', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['support_advised_by'] = $support_advised_by;

        return $this;
    }

    /**
     * Gets further_support_requested
     *
     * @return bool|null
     */
    public function getFurtherSupportRequested()
    {
        return $this->container['further_support_requested'];
    }

    /**
     * Sets further_support_requested
     *
     * @param bool|null $further_support_requested further_support_requested
     *
     * @return self
     */
    public function setFurtherSupportRequested($further_support_requested)
    {
        if (is_null($further_support_requested)) {
            array_push($this->openAPINullablesSetToNull, 'further_support_requested');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('further_support_requested', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['further_support_requested'] = $further_support_requested;

        return $this;
    }

    /**
     * Gets closed_at
     *
     * @return \DateTime|null
     */
    public function getClosedAt()
    {
        return $this->container['closed_at'];
    }

    /**
     * Sets closed_at
     *
     * @param \DateTime|null $closed_at closed_at
     *
     * @return self
     */
    public function setClosedAt($closed_at)
    {
        if (is_null($closed_at)) {
            array_push($this->openAPINullablesSetToNull, 'closed_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('closed_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['closed_at'] = $closed_at;

        return $this;
    }

    /**
     * Gets closed_by
     *
     * @return string|null
     */
    public function getClosedBy()
    {
        return $this->container['closed_by'];
    }

    /**
     * Sets closed_by
     *
     * @param string|null $closed_by closed_by
     *
     * @return self
     */
    public function setClosedBy($closed_by)
    {
        if (is_null($closed_by)) {
            array_push($this->openAPINullablesSetToNull, 'closed_by');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('closed_by', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['closed_by'] = $closed_by;

        return $this;
    }

    /**
     * Gets retention_expires_at
     *
     * @return \DateTime|null
     */
    public function getRetentionExpiresAt()
    {
        return $this->container['retention_expires_at'];
    }

    /**
     * Sets retention_expires_at
     *
     * @param \DateTime|null $retention_expires_at Set on close; the earliest the record may be disposed of.
     *
     * @return self
     */
    public function setRetentionExpiresAt($retention_expires_at)
    {
        if (is_null($retention_expires_at)) {
            array_push($this->openAPINullablesSetToNull, 'retention_expires_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('retention_expires_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['retention_expires_at'] = $retention_expires_at;

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


