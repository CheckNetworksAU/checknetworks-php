<?php
/**
 * PutComplianceDfvStatement200ResponseResult
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
 * PutComplianceDfvStatement200ResponseResult Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class PutComplianceDfvStatement200ResponseResult implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'put_compliance_dfv_statement_200_response_result';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'safety_commitment' => 'string',
        'continuity_commitment' => 'string',
        'non_payment_commitment' => 'string',
        'contact_instructions' => 'string',
        'support_organisations' => 'string',
        'accessibility_note' => 'string',
        'interim_notice' => 'string',
        'support_available_from' => '\DateTime',
        'channels' => '\CheckNetworks\Model\DfvSupportChannel[]',
        'approval' => '\CheckNetworks\Model\DfvStatementApproval',
        'published_at' => '\DateTime',
        'ready_to_publish' => 'bool',
        'outstanding' => 'string[]',
        'portal_url' => 'string',
        'approval_revoked' => 'bool'
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     * @phpstan-var array<string, string|null>
     * @psalm-var array<string, string|null>
     */
    protected static $openAPIFormats = [
        'safety_commitment' => null,
        'continuity_commitment' => null,
        'non_payment_commitment' => null,
        'contact_instructions' => null,
        'support_organisations' => null,
        'accessibility_note' => null,
        'interim_notice' => null,
        'support_available_from' => 'date',
        'channels' => null,
        'approval' => null,
        'published_at' => 'date-time',
        'ready_to_publish' => null,
        'outstanding' => null,
        'portal_url' => 'uri',
        'approval_revoked' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'safety_commitment' => true,
        'continuity_commitment' => true,
        'non_payment_commitment' => true,
        'contact_instructions' => true,
        'support_organisations' => true,
        'accessibility_note' => true,
        'interim_notice' => true,
        'support_available_from' => true,
        'channels' => false,
        'approval' => true,
        'published_at' => true,
        'ready_to_publish' => false,
        'outstanding' => false,
        'portal_url' => false,
        'approval_revoked' => false
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
        'safety_commitment' => 'safetyCommitment',
        'continuity_commitment' => 'continuityCommitment',
        'non_payment_commitment' => 'nonPaymentCommitment',
        'contact_instructions' => 'contactInstructions',
        'support_organisations' => 'supportOrganisations',
        'accessibility_note' => 'accessibilityNote',
        'interim_notice' => 'interimNotice',
        'support_available_from' => 'supportAvailableFrom',
        'channels' => 'channels',
        'approval' => 'approval',
        'published_at' => 'publishedAt',
        'ready_to_publish' => 'readyToPublish',
        'outstanding' => 'outstanding',
        'portal_url' => 'portalUrl',
        'approval_revoked' => 'approvalRevoked'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'safety_commitment' => 'setSafetyCommitment',
        'continuity_commitment' => 'setContinuityCommitment',
        'non_payment_commitment' => 'setNonPaymentCommitment',
        'contact_instructions' => 'setContactInstructions',
        'support_organisations' => 'setSupportOrganisations',
        'accessibility_note' => 'setAccessibilityNote',
        'interim_notice' => 'setInterimNotice',
        'support_available_from' => 'setSupportAvailableFrom',
        'channels' => 'setChannels',
        'approval' => 'setApproval',
        'published_at' => 'setPublishedAt',
        'ready_to_publish' => 'setReadyToPublish',
        'outstanding' => 'setOutstanding',
        'portal_url' => 'setPortalUrl',
        'approval_revoked' => 'setApprovalRevoked'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'safety_commitment' => 'getSafetyCommitment',
        'continuity_commitment' => 'getContinuityCommitment',
        'non_payment_commitment' => 'getNonPaymentCommitment',
        'contact_instructions' => 'getContactInstructions',
        'support_organisations' => 'getSupportOrganisations',
        'accessibility_note' => 'getAccessibilityNote',
        'interim_notice' => 'getInterimNotice',
        'support_available_from' => 'getSupportAvailableFrom',
        'channels' => 'getChannels',
        'approval' => 'getApproval',
        'published_at' => 'getPublishedAt',
        'ready_to_publish' => 'getReadyToPublish',
        'outstanding' => 'getOutstanding',
        'portal_url' => 'getPortalUrl',
        'approval_revoked' => 'getApprovalRevoked'
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
        $this->setIfExists('safety_commitment', $data ?? [], null);
        $this->setIfExists('continuity_commitment', $data ?? [], null);
        $this->setIfExists('non_payment_commitment', $data ?? [], null);
        $this->setIfExists('contact_instructions', $data ?? [], null);
        $this->setIfExists('support_organisations', $data ?? [], null);
        $this->setIfExists('accessibility_note', $data ?? [], null);
        $this->setIfExists('interim_notice', $data ?? [], null);
        $this->setIfExists('support_available_from', $data ?? [], null);
        $this->setIfExists('channels', $data ?? [], null);
        $this->setIfExists('approval', $data ?? [], null);
        $this->setIfExists('published_at', $data ?? [], null);
        $this->setIfExists('ready_to_publish', $data ?? [], null);
        $this->setIfExists('outstanding', $data ?? [], null);
        $this->setIfExists('portal_url', $data ?? [], null);
        $this->setIfExists('approval_revoked', $data ?? [], null);
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

        if ($this->container['safety_commitment'] === null && !$this->isNullableSetToNull('safety_commitment')) {
            $invalidProperties[] = "'safety_commitment' is required";
        }
        if (!is_null($this->container['safety_commitment']) && (mb_strlen($this->container['safety_commitment']) > 4000)) {
            $invalidProperties[] = "invalid value for 'safety_commitment', the character length must be smaller than or equal to 4000.";
        }

        if ($this->container['continuity_commitment'] === null && !$this->isNullableSetToNull('continuity_commitment')) {
            $invalidProperties[] = "'continuity_commitment' is required";
        }
        if (!is_null($this->container['continuity_commitment']) && (mb_strlen($this->container['continuity_commitment']) > 4000)) {
            $invalidProperties[] = "invalid value for 'continuity_commitment', the character length must be smaller than or equal to 4000.";
        }

        if ($this->container['non_payment_commitment'] === null && !$this->isNullableSetToNull('non_payment_commitment')) {
            $invalidProperties[] = "'non_payment_commitment' is required";
        }
        if (!is_null($this->container['non_payment_commitment']) && (mb_strlen($this->container['non_payment_commitment']) > 4000)) {
            $invalidProperties[] = "invalid value for 'non_payment_commitment', the character length must be smaller than or equal to 4000.";
        }

        if ($this->container['contact_instructions'] === null && !$this->isNullableSetToNull('contact_instructions')) {
            $invalidProperties[] = "'contact_instructions' is required";
        }
        if (!is_null($this->container['contact_instructions']) && (mb_strlen($this->container['contact_instructions']) > 4000)) {
            $invalidProperties[] = "invalid value for 'contact_instructions', the character length must be smaller than or equal to 4000.";
        }

        if ($this->container['support_organisations'] === null && !$this->isNullableSetToNull('support_organisations')) {
            $invalidProperties[] = "'support_organisations' is required";
        }
        if (!is_null($this->container['support_organisations']) && (mb_strlen($this->container['support_organisations']) > 4000)) {
            $invalidProperties[] = "invalid value for 'support_organisations', the character length must be smaller than or equal to 4000.";
        }

        if ($this->container['accessibility_note'] === null && !$this->isNullableSetToNull('accessibility_note')) {
            $invalidProperties[] = "'accessibility_note' is required";
        }
        if (!is_null($this->container['accessibility_note']) && (mb_strlen($this->container['accessibility_note']) > 4000)) {
            $invalidProperties[] = "invalid value for 'accessibility_note', the character length must be smaller than or equal to 4000.";
        }

        if ($this->container['interim_notice'] === null && !$this->isNullableSetToNull('interim_notice')) {
            $invalidProperties[] = "'interim_notice' is required";
        }
        if (!is_null($this->container['interim_notice']) && (mb_strlen($this->container['interim_notice']) > 4000)) {
            $invalidProperties[] = "invalid value for 'interim_notice', the character length must be smaller than or equal to 4000.";
        }

        if ($this->container['support_available_from'] === null && !$this->isNullableSetToNull('support_available_from')) {
            $invalidProperties[] = "'support_available_from' is required";
        }
        if ($this->container['channels'] === null) {
            $invalidProperties[] = "'channels' can't be null";
        }
        if ($this->container['approval'] === null && !$this->isNullableSetToNull('approval')) {
            $invalidProperties[] = "'approval' is required";
        }
        if ($this->container['published_at'] === null && !$this->isNullableSetToNull('published_at')) {
            $invalidProperties[] = "'published_at' is required";
        }
        if ($this->container['ready_to_publish'] === null) {
            $invalidProperties[] = "'ready_to_publish' can't be null";
        }
        if ($this->container['outstanding'] === null) {
            $invalidProperties[] = "'outstanding' can't be null";
        }
        if ($this->container['portal_url'] === null) {
            $invalidProperties[] = "'portal_url' can't be null";
        }
        if ($this->container['approval_revoked'] === null) {
            $invalidProperties[] = "'approval_revoked' can't be null";
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
     * Gets safety_commitment
     *
     * @return string|null
     */
    public function getSafetyCommitment()
    {
        return $this->container['safety_commitment'];
    }

    /**
     * Sets safety_commitment
     *
     * @param string|null $safety_commitment s17(2)(a), your safety policies and procedures.
     *
     * @return self
     */
    public function setSafetyCommitment($safety_commitment)
    {
        if (is_null($safety_commitment)) {
            array_push($this->openAPINullablesSetToNull, 'safety_commitment');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('safety_commitment', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($safety_commitment) && (mb_strlen($safety_commitment) > 4000)) {
            throw new \InvalidArgumentException('invalid length for $safety_commitment when calling PutComplianceDfvStatement200ResponseResult., must be smaller than or equal to 4000.');
        }

        $this->container['safety_commitment'] = $safety_commitment;

        return $this;
    }

    /**
     * Gets continuity_commitment
     *
     * @return string|null
     */
    public function getContinuityCommitment()
    {
        return $this->container['continuity_commitment'];
    }

    /**
     * Sets continuity_commitment
     *
     * @param string|null $continuity_commitment s17(2)(b), staying connected and reversing restrictions.
     *
     * @return self
     */
    public function setContinuityCommitment($continuity_commitment)
    {
        if (is_null($continuity_commitment)) {
            array_push($this->openAPINullablesSetToNull, 'continuity_commitment');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('continuity_commitment', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($continuity_commitment) && (mb_strlen($continuity_commitment) > 4000)) {
            throw new \InvalidArgumentException('invalid length for $continuity_commitment when calling PutComplianceDfvStatement200ResponseResult., must be smaller than or equal to 4000.');
        }

        $this->container['continuity_commitment'] = $continuity_commitment;

        return $this;
    }

    /**
     * Gets non_payment_commitment
     *
     * @return string|null
     */
    public function getNonPaymentCommitment()
    {
        return $this->container['non_payment_commitment'];
    }

    /**
     * Sets non_payment_commitment
     *
     * @param string|null $non_payment_commitment s17(2)(c), non-payment and financial hardship assistance.
     *
     * @return self
     */
    public function setNonPaymentCommitment($non_payment_commitment)
    {
        if (is_null($non_payment_commitment)) {
            array_push($this->openAPINullablesSetToNull, 'non_payment_commitment');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('non_payment_commitment', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($non_payment_commitment) && (mb_strlen($non_payment_commitment) > 4000)) {
            throw new \InvalidArgumentException('invalid length for $non_payment_commitment when calling PutComplianceDfvStatement200ResponseResult., must be smaller than or equal to 4000.');
        }

        $this->container['non_payment_commitment'] = $non_payment_commitment;

        return $this;
    }

    /**
     * Gets contact_instructions
     *
     * @return string|null
     */
    public function getContactInstructions()
    {
        return $this->container['contact_instructions'];
    }

    /**
     * Sets contact_instructions
     *
     * @param string|null $contact_instructions s17(2)(d), how to contact you for assistance.
     *
     * @return self
     */
    public function setContactInstructions($contact_instructions)
    {
        if (is_null($contact_instructions)) {
            array_push($this->openAPINullablesSetToNull, 'contact_instructions');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('contact_instructions', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($contact_instructions) && (mb_strlen($contact_instructions) > 4000)) {
            throw new \InvalidArgumentException('invalid length for $contact_instructions when calling PutComplianceDfvStatement200ResponseResult., must be smaller than or equal to 4000.');
        }

        $this->container['contact_instructions'] = $contact_instructions;

        return $this;
    }

    /**
     * Gets support_organisations
     *
     * @return string|null
     */
    public function getSupportOrganisations()
    {
        return $this->container['support_organisations'];
    }

    /**
     * Sets support_organisations
     *
     * @param string|null $support_organisations Your own wording, shown beside the six statutory support services.
     *
     * @return self
     */
    public function setSupportOrganisations($support_organisations)
    {
        if (is_null($support_organisations)) {
            array_push($this->openAPINullablesSetToNull, 'support_organisations');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('support_organisations', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($support_organisations) && (mb_strlen($support_organisations) > 4000)) {
            throw new \InvalidArgumentException('invalid length for $support_organisations when calling PutComplianceDfvStatement200ResponseResult., must be smaller than or equal to 4000.');
        }

        $this->container['support_organisations'] = $support_organisations;

        return $this;
    }

    /**
     * Gets accessibility_note
     *
     * @return string|null
     */
    public function getAccessibilityNote()
    {
        return $this->container['accessibility_note'];
    }

    /**
     * Sets accessibility_note
     *
     * @param string|null $accessibility_note accessibility_note
     *
     * @return self
     */
    public function setAccessibilityNote($accessibility_note)
    {
        if (is_null($accessibility_note)) {
            array_push($this->openAPINullablesSetToNull, 'accessibility_note');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('accessibility_note', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($accessibility_note) && (mb_strlen($accessibility_note) > 4000)) {
            throw new \InvalidArgumentException('invalid length for $accessibility_note when calling PutComplianceDfvStatement200ResponseResult., must be smaller than or equal to 4000.');
        }

        $this->container['accessibility_note'] = $accessibility_note;

        return $this;
    }

    /**
     * Gets interim_notice
     *
     * @return string|null
     */
    public function getInterimNotice()
    {
        return $this->container['interim_notice'];
    }

    /**
     * Sets interim_notice
     *
     * @param string|null $interim_notice Shown until `supportAvailableFrom`; changing it keeps approval.
     *
     * @return self
     */
    public function setInterimNotice($interim_notice)
    {
        if (is_null($interim_notice)) {
            array_push($this->openAPINullablesSetToNull, 'interim_notice');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('interim_notice', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        if (!is_null($interim_notice) && (mb_strlen($interim_notice) > 4000)) {
            throw new \InvalidArgumentException('invalid length for $interim_notice when calling PutComplianceDfvStatement200ResponseResult., must be smaller than or equal to 4000.');
        }

        $this->container['interim_notice'] = $interim_notice;

        return $this;
    }

    /**
     * Gets support_available_from
     *
     * @return \DateTime|null
     */
    public function getSupportAvailableFrom()
    {
        return $this->container['support_available_from'];
    }

    /**
     * Sets support_available_from
     *
     * @param \DateTime|null $support_available_from support_available_from
     *
     * @return self
     */
    public function setSupportAvailableFrom($support_available_from)
    {
        if (is_null($support_available_from)) {
            array_push($this->openAPINullablesSetToNull, 'support_available_from');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('support_available_from', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['support_available_from'] = $support_available_from;

        return $this;
    }

    /**
     * Gets channels
     *
     * @return \CheckNetworks\Model\DfvSupportChannel[]
     */
    public function getChannels()
    {
        return $this->container['channels'];
    }

    /**
     * Sets channels
     *
     * @param \CheckNetworks\Model\DfvSupportChannel[] $channels channels
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
     * Gets approval
     *
     * @return \CheckNetworks\Model\DfvStatementApproval|null
     */
    public function getApproval()
    {
        return $this->container['approval'];
    }

    /**
     * Sets approval
     *
     * @param \CheckNetworks\Model\DfvStatementApproval|null $approval approval
     *
     * @return self
     */
    public function setApproval($approval)
    {
        if (is_null($approval)) {
            array_push($this->openAPINullablesSetToNull, 'approval');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('approval', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['approval'] = $approval;

        return $this;
    }

    /**
     * Gets published_at
     *
     * @return \DateTime|null
     */
    public function getPublishedAt()
    {
        return $this->container['published_at'];
    }

    /**
     * Sets published_at
     *
     * @param \DateTime|null $published_at published_at
     *
     * @return self
     */
    public function setPublishedAt($published_at)
    {
        if (is_null($published_at)) {
            array_push($this->openAPINullablesSetToNull, 'published_at');
        } else {
            $nullablesSetToNull = $this->getOpenAPINullablesSetToNull();
            $index = array_search('published_at', $nullablesSetToNull);
            if ($index !== FALSE) {
                unset($nullablesSetToNull[$index]);
                $this->setOpenAPINullablesSetToNull($nullablesSetToNull);
            }
        }
        $this->container['published_at'] = $published_at;

        return $this;
    }

    /**
     * Gets ready_to_publish
     *
     * @return bool
     */
    public function getReadyToPublish()
    {
        return $this->container['ready_to_publish'];
    }

    /**
     * Sets ready_to_publish
     *
     * @param bool $ready_to_publish Approved, with nothing outstanding.
     *
     * @return self
     */
    public function setReadyToPublish($ready_to_publish)
    {
        if (is_null($ready_to_publish)) {
            throw new \InvalidArgumentException('non-nullable ready_to_publish cannot be null');
        }
        $this->container['ready_to_publish'] = $ready_to_publish;

        return $this;
    }

    /**
     * Gets outstanding
     *
     * @return string[]
     */
    public function getOutstanding()
    {
        return $this->container['outstanding'];
    }

    /**
     * Sets outstanding
     *
     * @param string[] $outstanding What stands between the statement and publishing.
     *
     * @return self
     */
    public function setOutstanding($outstanding)
    {
        if (is_null($outstanding)) {
            throw new \InvalidArgumentException('non-nullable outstanding cannot be null');
        }
        $this->container['outstanding'] = $outstanding;

        return $this;
    }

    /**
     * Gets portal_url
     *
     * @return string
     */
    public function getPortalUrl()
    {
        return $this->container['portal_url'];
    }

    /**
     * Sets portal_url
     *
     * @param string $portal_url The portal page where the statement is approved and published.
     *
     * @return self
     */
    public function setPortalUrl($portal_url)
    {
        if (is_null($portal_url)) {
            throw new \InvalidArgumentException('non-nullable portal_url cannot be null');
        }
        $this->container['portal_url'] = $portal_url;

        return $this;
    }

    /**
     * Gets approval_revoked
     *
     * @return bool
     */
    public function getApprovalRevoked()
    {
        return $this->container['approval_revoked'];
    }

    /**
     * Sets approval_revoked
     *
     * @param bool $approval_revoked This change revoked an approval and unpublished the statement.
     *
     * @return self
     */
    public function setApprovalRevoked($approval_revoked)
    {
        if (is_null($approval_revoked)) {
            throw new \InvalidArgumentException('non-nullable approval_revoked cannot be null');
        }
        $this->container['approval_revoked'] = $approval_revoked;

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


