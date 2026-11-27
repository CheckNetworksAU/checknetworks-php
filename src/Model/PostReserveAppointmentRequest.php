<?php
/**
 * PostReserveAppointmentRequest
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
 * PostReserveAppointmentRequest Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class PostReserveAppointmentRequest implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'post_reserve_appointment_request';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'loc_id' => 'string',
        'demand_type' => 'string',
        'service_restoration_sla' => 'string',
        'appointment_slot_type' => 'string',
        'appointment_sla' => 'string',
        'start_date_time' => '\DateTime',
        'end_date_time' => '\DateTime',
        'within_time_window' => 'bool',
        'end_user_type' => 'string',
        'end_user_contact_type' => 'string',
        'end_user_contact_name' => 'string',
        'end_user_contact_phone' => 'string',
        'end_user_contact_email' => 'string',
        'end_user_contact_notes' => 'string',
        'site_access_building_name' => 'string',
        'site_access_building_type' => 'string',
        'site_access_instructions' => 'string',
        'site_access_induction' => 'string',
        'site_access_accreditation' => 'string',
        'site_access_considerations' => 'string',
        'site_access_operational_hours' => 'string',
        'site_access_hazards' => 'string',
        'site_access_conditions' => 'string',
        'site_access_notes' => 'string',
        'related_order_id' => 'string',
        'related_product' => 'string',
        'copper_pair_id' => 'string',
        'priority_assist' => 'bool',
        'end_user' => 'string',
        'trouble_ticket_id' => 'string',
        'incident_id' => 'string',
        'alternative_technology' => 'string'
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
        'demand_type' => null,
        'service_restoration_sla' => null,
        'appointment_slot_type' => null,
        'appointment_sla' => null,
        'start_date_time' => 'date-time',
        'end_date_time' => 'date-time',
        'within_time_window' => null,
        'end_user_type' => null,
        'end_user_contact_type' => null,
        'end_user_contact_name' => null,
        'end_user_contact_phone' => null,
        'end_user_contact_email' => 'email',
        'end_user_contact_notes' => null,
        'site_access_building_name' => null,
        'site_access_building_type' => null,
        'site_access_instructions' => null,
        'site_access_induction' => null,
        'site_access_accreditation' => null,
        'site_access_considerations' => null,
        'site_access_operational_hours' => null,
        'site_access_hazards' => null,
        'site_access_conditions' => null,
        'site_access_notes' => null,
        'related_order_id' => null,
        'related_product' => null,
        'copper_pair_id' => null,
        'priority_assist' => null,
        'end_user' => null,
        'trouble_ticket_id' => null,
        'incident_id' => null,
        'alternative_technology' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'loc_id' => false,
        'demand_type' => false,
        'service_restoration_sla' => false,
        'appointment_slot_type' => false,
        'appointment_sla' => false,
        'start_date_time' => false,
        'end_date_time' => false,
        'within_time_window' => false,
        'end_user_type' => false,
        'end_user_contact_type' => false,
        'end_user_contact_name' => false,
        'end_user_contact_phone' => false,
        'end_user_contact_email' => false,
        'end_user_contact_notes' => false,
        'site_access_building_name' => false,
        'site_access_building_type' => false,
        'site_access_instructions' => false,
        'site_access_induction' => false,
        'site_access_accreditation' => false,
        'site_access_considerations' => false,
        'site_access_operational_hours' => false,
        'site_access_hazards' => false,
        'site_access_conditions' => false,
        'site_access_notes' => false,
        'related_order_id' => false,
        'related_product' => false,
        'copper_pair_id' => false,
        'priority_assist' => false,
        'end_user' => false,
        'trouble_ticket_id' => false,
        'incident_id' => false,
        'alternative_technology' => false
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
        'demand_type' => 'demandType',
        'service_restoration_sla' => 'serviceRestorationSLA',
        'appointment_slot_type' => 'appointmentSlotType',
        'appointment_sla' => 'appointmentSLA',
        'start_date_time' => 'startDateTime',
        'end_date_time' => 'endDateTime',
        'within_time_window' => 'withinTimeWindow',
        'end_user_type' => 'endUserType',
        'end_user_contact_type' => 'endUserContactType',
        'end_user_contact_name' => 'endUserContactName',
        'end_user_contact_phone' => 'endUserContactPhone',
        'end_user_contact_email' => 'endUserContactEmail',
        'end_user_contact_notes' => 'endUserContactNotes',
        'site_access_building_name' => 'siteAccessBuildingName',
        'site_access_building_type' => 'siteAccessBuildingType',
        'site_access_instructions' => 'siteAccessInstructions',
        'site_access_induction' => 'siteAccessInduction',
        'site_access_accreditation' => 'siteAccessAccreditation',
        'site_access_considerations' => 'siteAccessConsiderations',
        'site_access_operational_hours' => 'siteAccessOperationalHours',
        'site_access_hazards' => 'siteAccessHazards',
        'site_access_conditions' => 'siteAccessConditions',
        'site_access_notes' => 'siteAccessNotes',
        'related_order_id' => 'relatedOrderId',
        'related_product' => 'relatedProduct',
        'copper_pair_id' => 'copperPairId',
        'priority_assist' => 'priorityAssist',
        'end_user' => 'endUser',
        'trouble_ticket_id' => 'troubleTicketId',
        'incident_id' => 'incidentId',
        'alternative_technology' => 'alternativeTechnology'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'loc_id' => 'setLocId',
        'demand_type' => 'setDemandType',
        'service_restoration_sla' => 'setServiceRestorationSla',
        'appointment_slot_type' => 'setAppointmentSlotType',
        'appointment_sla' => 'setAppointmentSla',
        'start_date_time' => 'setStartDateTime',
        'end_date_time' => 'setEndDateTime',
        'within_time_window' => 'setWithinTimeWindow',
        'end_user_type' => 'setEndUserType',
        'end_user_contact_type' => 'setEndUserContactType',
        'end_user_contact_name' => 'setEndUserContactName',
        'end_user_contact_phone' => 'setEndUserContactPhone',
        'end_user_contact_email' => 'setEndUserContactEmail',
        'end_user_contact_notes' => 'setEndUserContactNotes',
        'site_access_building_name' => 'setSiteAccessBuildingName',
        'site_access_building_type' => 'setSiteAccessBuildingType',
        'site_access_instructions' => 'setSiteAccessInstructions',
        'site_access_induction' => 'setSiteAccessInduction',
        'site_access_accreditation' => 'setSiteAccessAccreditation',
        'site_access_considerations' => 'setSiteAccessConsiderations',
        'site_access_operational_hours' => 'setSiteAccessOperationalHours',
        'site_access_hazards' => 'setSiteAccessHazards',
        'site_access_conditions' => 'setSiteAccessConditions',
        'site_access_notes' => 'setSiteAccessNotes',
        'related_order_id' => 'setRelatedOrderId',
        'related_product' => 'setRelatedProduct',
        'copper_pair_id' => 'setCopperPairId',
        'priority_assist' => 'setPriorityAssist',
        'end_user' => 'setEndUser',
        'trouble_ticket_id' => 'setTroubleTicketId',
        'incident_id' => 'setIncidentId',
        'alternative_technology' => 'setAlternativeTechnology'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'loc_id' => 'getLocId',
        'demand_type' => 'getDemandType',
        'service_restoration_sla' => 'getServiceRestorationSla',
        'appointment_slot_type' => 'getAppointmentSlotType',
        'appointment_sla' => 'getAppointmentSla',
        'start_date_time' => 'getStartDateTime',
        'end_date_time' => 'getEndDateTime',
        'within_time_window' => 'getWithinTimeWindow',
        'end_user_type' => 'getEndUserType',
        'end_user_contact_type' => 'getEndUserContactType',
        'end_user_contact_name' => 'getEndUserContactName',
        'end_user_contact_phone' => 'getEndUserContactPhone',
        'end_user_contact_email' => 'getEndUserContactEmail',
        'end_user_contact_notes' => 'getEndUserContactNotes',
        'site_access_building_name' => 'getSiteAccessBuildingName',
        'site_access_building_type' => 'getSiteAccessBuildingType',
        'site_access_instructions' => 'getSiteAccessInstructions',
        'site_access_induction' => 'getSiteAccessInduction',
        'site_access_accreditation' => 'getSiteAccessAccreditation',
        'site_access_considerations' => 'getSiteAccessConsiderations',
        'site_access_operational_hours' => 'getSiteAccessOperationalHours',
        'site_access_hazards' => 'getSiteAccessHazards',
        'site_access_conditions' => 'getSiteAccessConditions',
        'site_access_notes' => 'getSiteAccessNotes',
        'related_order_id' => 'getRelatedOrderId',
        'related_product' => 'getRelatedProduct',
        'copper_pair_id' => 'getCopperPairId',
        'priority_assist' => 'getPriorityAssist',
        'end_user' => 'getEndUser',
        'trouble_ticket_id' => 'getTroubleTicketId',
        'incident_id' => 'getIncidentId',
        'alternative_technology' => 'getAlternativeTechnology'
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

    public const DEMAND_TYPE_STANDARD_INSTALL = 'Standard Install';
    public const DEMAND_TYPE_SERVICE_RESTORATION = 'Service Restoration';
    public const DEMAND_TYPE_NON_EU_JUMPER_ONLY = 'Non EU Jumper Only';
    public const DEMAND_TYPE_NON_STANDARD_INSTALL = 'Non Standard Install';
    public const DEMAND_TYPE_INSTALL___DEINSTALL = 'Install - Deinstall';
    public const DEMAND_TYPE_ADDITIONAL_INSTALL = 'Additional Install';
    public const SERVICE_RESTORATION_SLA_ENHANCED___12 = 'Enhanced - 12';
    public const SERVICE_RESTORATION_SLA_ENHANCED___12__24_7 = 'Enhanced - 12 (24/7)';
    public const SERVICE_RESTORATION_SLA_ENHANCED___8 = 'Enhanced - 8';
    public const SERVICE_RESTORATION_SLA_ENHANCED___8__24_7 = 'Enhanced - 8 (24/7)';
    public const SERVICE_RESTORATION_SLA_STANDARD = 'Standard';
    public const APPOINTMENT_SLOT_TYPE_AM = 'AM';
    public const APPOINTMENT_SLOT_TYPE_PM = 'PM';
    public const APPOINTMENT_SLOT_TYPE_AHA = 'AHA';
    public const APPOINTMENT_SLOT_TYPE_CA = 'CA';
    public const APPOINTMENT_SLA_ACCELERATED = 'Accelerated';
    public const APPOINTMENT_SLA_REDUCED_LEAD_TIME = 'Reduced Lead Time';
    public const APPOINTMENT_SLA_STANDARD = 'Standard';
    public const END_USER_TYPE_BUSINESS = 'Business';
    public const END_USER_TYPE_RESIDENTIAL = 'Residential';
    public const END_USER_CONTACT_TYPE_PRIMARY_CONTACT = 'Primary Contact';
    public const END_USER_CONTACT_TYPE_SECONDARY_CONTACT = 'Secondary Contact';
    public const SITE_ACCESS_BUILDING_TYPE_ATM = 'ATM';
    public const SITE_ACCESS_BUILDING_TYPE_EDUCATION = 'Education';
    public const SITE_ACCESS_BUILDING_TYPE_GAS_METER = 'Gas Meter';
    public const SITE_ACCESS_BUILDING_TYPE_GOVERNMENT_SITE = 'Government Site';
    public const SITE_ACCESS_BUILDING_TYPE_INDUSTRIAL = 'Industrial';
    public const SITE_ACCESS_BUILDING_TYPE_KIOSK = 'Kiosk';
    public const SITE_ACCESS_BUILDING_TYPE_MEDICAL_HOSPITAL = 'Medical/Hospital';
    public const SITE_ACCESS_BUILDING_TYPE_MULTI_DWELLING_UNIT = 'Multi Dwelling Unit';
    public const SITE_ACCESS_BUILDING_TYPE_PAYPHONE = 'Payphone';
    public const SITE_ACCESS_BUILDING_TYPE_POWER_SUB_STATION = 'Power Sub-Station';
    public const SITE_ACCESS_BUILDING_TYPE_RESIDENTIAL = 'Residential';
    public const SITE_ACCESS_BUILDING_TYPE_SHOPPING_CENTRE = 'Shopping Centre';
    public const SITE_ACCESS_BUILDING_TYPE_SINGLE_DWELLING_UNIT = 'Single Dwelling Unit';
    public const SITE_ACCESS_BUILDING_TYPE_STRIP_SHOP = 'Strip Shop';
    public const SITE_ACCESS_BUILDING_TYPE_TRANSPORT = 'Transport';
    public const ALTERNATIVE_TECHNOLOGY_FIBRE = 'Fibre';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getDemandTypeAllowableValues()
    {
        return [
            self::DEMAND_TYPE_STANDARD_INSTALL,
            self::DEMAND_TYPE_SERVICE_RESTORATION,
            self::DEMAND_TYPE_NON_EU_JUMPER_ONLY,
            self::DEMAND_TYPE_NON_STANDARD_INSTALL,
            self::DEMAND_TYPE_INSTALL___DEINSTALL,
            self::DEMAND_TYPE_ADDITIONAL_INSTALL,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getServiceRestorationSlaAllowableValues()
    {
        return [
            self::SERVICE_RESTORATION_SLA_ENHANCED___12,
            self::SERVICE_RESTORATION_SLA_ENHANCED___12__24_7,
            self::SERVICE_RESTORATION_SLA_ENHANCED___8,
            self::SERVICE_RESTORATION_SLA_ENHANCED___8__24_7,
            self::SERVICE_RESTORATION_SLA_STANDARD,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAppointmentSlotTypeAllowableValues()
    {
        return [
            self::APPOINTMENT_SLOT_TYPE_AM,
            self::APPOINTMENT_SLOT_TYPE_PM,
            self::APPOINTMENT_SLOT_TYPE_AHA,
            self::APPOINTMENT_SLOT_TYPE_CA,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAppointmentSlaAllowableValues()
    {
        return [
            self::APPOINTMENT_SLA_ACCELERATED,
            self::APPOINTMENT_SLA_REDUCED_LEAD_TIME,
            self::APPOINTMENT_SLA_STANDARD,
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
            self::END_USER_TYPE_BUSINESS,
            self::END_USER_TYPE_RESIDENTIAL,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getEndUserContactTypeAllowableValues()
    {
        return [
            self::END_USER_CONTACT_TYPE_PRIMARY_CONTACT,
            self::END_USER_CONTACT_TYPE_SECONDARY_CONTACT,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getSiteAccessBuildingTypeAllowableValues()
    {
        return [
            self::SITE_ACCESS_BUILDING_TYPE_ATM,
            self::SITE_ACCESS_BUILDING_TYPE_EDUCATION,
            self::SITE_ACCESS_BUILDING_TYPE_GAS_METER,
            self::SITE_ACCESS_BUILDING_TYPE_GOVERNMENT_SITE,
            self::SITE_ACCESS_BUILDING_TYPE_INDUSTRIAL,
            self::SITE_ACCESS_BUILDING_TYPE_KIOSK,
            self::SITE_ACCESS_BUILDING_TYPE_MEDICAL_HOSPITAL,
            self::SITE_ACCESS_BUILDING_TYPE_MULTI_DWELLING_UNIT,
            self::SITE_ACCESS_BUILDING_TYPE_PAYPHONE,
            self::SITE_ACCESS_BUILDING_TYPE_POWER_SUB_STATION,
            self::SITE_ACCESS_BUILDING_TYPE_RESIDENTIAL,
            self::SITE_ACCESS_BUILDING_TYPE_SHOPPING_CENTRE,
            self::SITE_ACCESS_BUILDING_TYPE_SINGLE_DWELLING_UNIT,
            self::SITE_ACCESS_BUILDING_TYPE_STRIP_SHOP,
            self::SITE_ACCESS_BUILDING_TYPE_TRANSPORT,
        ];
    }

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public function getAlternativeTechnologyAllowableValues()
    {
        return [
            self::ALTERNATIVE_TECHNOLOGY_FIBRE,
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
        $this->setIfExists('demand_type', $data ?? [], null);
        $this->setIfExists('service_restoration_sla', $data ?? [], null);
        $this->setIfExists('appointment_slot_type', $data ?? [], null);
        $this->setIfExists('appointment_sla', $data ?? [], null);
        $this->setIfExists('start_date_time', $data ?? [], null);
        $this->setIfExists('end_date_time', $data ?? [], null);
        $this->setIfExists('within_time_window', $data ?? [], null);
        $this->setIfExists('end_user_type', $data ?? [], null);
        $this->setIfExists('end_user_contact_type', $data ?? [], null);
        $this->setIfExists('end_user_contact_name', $data ?? [], null);
        $this->setIfExists('end_user_contact_phone', $data ?? [], null);
        $this->setIfExists('end_user_contact_email', $data ?? [], null);
        $this->setIfExists('end_user_contact_notes', $data ?? [], null);
        $this->setIfExists('site_access_building_name', $data ?? [], null);
        $this->setIfExists('site_access_building_type', $data ?? [], null);
        $this->setIfExists('site_access_instructions', $data ?? [], null);
        $this->setIfExists('site_access_induction', $data ?? [], null);
        $this->setIfExists('site_access_accreditation', $data ?? [], null);
        $this->setIfExists('site_access_considerations', $data ?? [], null);
        $this->setIfExists('site_access_operational_hours', $data ?? [], null);
        $this->setIfExists('site_access_hazards', $data ?? [], null);
        $this->setIfExists('site_access_conditions', $data ?? [], null);
        $this->setIfExists('site_access_notes', $data ?? [], null);
        $this->setIfExists('related_order_id', $data ?? [], null);
        $this->setIfExists('related_product', $data ?? [], null);
        $this->setIfExists('copper_pair_id', $data ?? [], null);
        $this->setIfExists('priority_assist', $data ?? [], false);
        $this->setIfExists('end_user', $data ?? [], null);
        $this->setIfExists('trouble_ticket_id', $data ?? [], null);
        $this->setIfExists('incident_id', $data ?? [], null);
        $this->setIfExists('alternative_technology', $data ?? [], null);
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
        if ($this->container['demand_type'] === null) {
            $invalidProperties[] = "'demand_type' can't be null";
        }
        $allowedValues = $this->getDemandTypeAllowableValues();
        if (!is_null($this->container['demand_type']) && !in_array($this->container['demand_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'demand_type', must be one of '%s'",
                $this->container['demand_type'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getServiceRestorationSlaAllowableValues();
        if (!is_null($this->container['service_restoration_sla']) && !in_array($this->container['service_restoration_sla'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'service_restoration_sla', must be one of '%s'",
                $this->container['service_restoration_sla'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getAppointmentSlotTypeAllowableValues();
        if (!is_null($this->container['appointment_slot_type']) && !in_array($this->container['appointment_slot_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'appointment_slot_type', must be one of '%s'",
                $this->container['appointment_slot_type'],
                implode("', '", $allowedValues)
            );
        }

        $allowedValues = $this->getAppointmentSlaAllowableValues();
        if (!is_null($this->container['appointment_sla']) && !in_array($this->container['appointment_sla'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'appointment_sla', must be one of '%s'",
                $this->container['appointment_sla'],
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

        if ($this->container['end_user_contact_type'] === null) {
            $invalidProperties[] = "'end_user_contact_type' can't be null";
        }
        $allowedValues = $this->getEndUserContactTypeAllowableValues();
        if (!is_null($this->container['end_user_contact_type']) && !in_array($this->container['end_user_contact_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'end_user_contact_type', must be one of '%s'",
                $this->container['end_user_contact_type'],
                implode("', '", $allowedValues)
            );
        }

        if ($this->container['end_user_contact_name'] === null) {
            $invalidProperties[] = "'end_user_contact_name' can't be null";
        }
        if ((mb_strlen($this->container['end_user_contact_name']) > 255)) {
            $invalidProperties[] = "invalid value for 'end_user_contact_name', the character length must be smaller than or equal to 255.";
        }

        if ($this->container['end_user_contact_phone'] === null) {
            $invalidProperties[] = "'end_user_contact_phone' can't be null";
        }
        if ((mb_strlen($this->container['end_user_contact_phone']) > 32)) {
            $invalidProperties[] = "invalid value for 'end_user_contact_phone', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['end_user_contact_email']) && (mb_strlen($this->container['end_user_contact_email']) > 255)) {
            $invalidProperties[] = "invalid value for 'end_user_contact_email', the character length must be smaller than or equal to 255.";
        }

        if (!is_null($this->container['end_user_contact_notes']) && (mb_strlen($this->container['end_user_contact_notes']) > 1000)) {
            $invalidProperties[] = "invalid value for 'end_user_contact_notes', the character length must be smaller than or equal to 1000.";
        }

        if (!is_null($this->container['site_access_building_name']) && (mb_strlen($this->container['site_access_building_name']) > 100)) {
            $invalidProperties[] = "invalid value for 'site_access_building_name', the character length must be smaller than or equal to 100.";
        }

        $allowedValues = $this->getSiteAccessBuildingTypeAllowableValues();
        if (!is_null($this->container['site_access_building_type']) && !in_array($this->container['site_access_building_type'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'site_access_building_type', must be one of '%s'",
                $this->container['site_access_building_type'],
                implode("', '", $allowedValues)
            );
        }

        if (!is_null($this->container['site_access_instructions']) && (mb_strlen($this->container['site_access_instructions']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_instructions', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['site_access_induction']) && (mb_strlen($this->container['site_access_induction']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_induction', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['site_access_accreditation']) && (mb_strlen($this->container['site_access_accreditation']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_accreditation', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['site_access_considerations']) && (mb_strlen($this->container['site_access_considerations']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_considerations', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['site_access_operational_hours']) && (mb_strlen($this->container['site_access_operational_hours']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_operational_hours', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['site_access_hazards']) && (mb_strlen($this->container['site_access_hazards']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_hazards', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['site_access_conditions']) && (mb_strlen($this->container['site_access_conditions']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_conditions', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['site_access_notes']) && (mb_strlen($this->container['site_access_notes']) > 500)) {
            $invalidProperties[] = "invalid value for 'site_access_notes', the character length must be smaller than or equal to 500.";
        }

        if (!is_null($this->container['related_order_id']) && (mb_strlen($this->container['related_order_id']) > 32)) {
            $invalidProperties[] = "invalid value for 'related_order_id', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['related_product']) && (mb_strlen($this->container['related_product']) > 32)) {
            $invalidProperties[] = "invalid value for 'related_product', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['copper_pair_id']) && (mb_strlen($this->container['copper_pair_id']) > 32)) {
            $invalidProperties[] = "invalid value for 'copper_pair_id', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['end_user']) && (mb_strlen($this->container['end_user']) > 64)) {
            $invalidProperties[] = "invalid value for 'end_user', the character length must be smaller than or equal to 64.";
        }

        if (!is_null($this->container['trouble_ticket_id']) && (mb_strlen($this->container['trouble_ticket_id']) > 32)) {
            $invalidProperties[] = "invalid value for 'trouble_ticket_id', the character length must be smaller than or equal to 32.";
        }

        if (!is_null($this->container['incident_id']) && (mb_strlen($this->container['incident_id']) > 32)) {
            $invalidProperties[] = "invalid value for 'incident_id', the character length must be smaller than or equal to 32.";
        }

        $allowedValues = $this->getAlternativeTechnologyAllowableValues();
        if (!is_null($this->container['alternative_technology']) && !in_array($this->container['alternative_technology'], $allowedValues, true)) {
            $invalidProperties[] = sprintf(
                "invalid value '%s' for 'alternative_technology', must be one of '%s'",
                $this->container['alternative_technology'],
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
     * @param string $loc_id NBN Location ID of the premises that this appointment is for.
     *
     * @return self
     */
    public function setLocId($loc_id)
    {
        if (is_null($loc_id)) {
            throw new \InvalidArgumentException('non-nullable loc_id cannot be null');
        }
        $this->container['loc_id'] = $loc_id;

        return $this;
    }

    /**
     * Gets demand_type
     *
     * @return string
     */
    public function getDemandType()
    {
        return $this->container['demand_type'];
    }

    /**
     * Sets demand_type
     *
     * @param string $demand_type The Demand Type of the appointment.  - \"Standard Install\" - for most Activation appointments  - \"Non EU Jumper Only\" - for NCAS SC12 Active lines where `installOptionCentralSplitter` = \"false\"  - \"Additional Install\" - for installing Central Splitter at connected premises  - \"Service Restoration\" - for restoring an affected active service  - \"Install - Deinstall\" - for combined installation and de-installation work at the premises  - \"Non Standard Install\" - for installations requiring non-standard work
     *
     * @return self
     */
    public function setDemandType($demand_type)
    {
        if (is_null($demand_type)) {
            throw new \InvalidArgumentException('non-nullable demand_type cannot be null');
        }
        $allowedValues = $this->getDemandTypeAllowableValues();
        if (!in_array($demand_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'demand_type', must be one of '%s'",
                    $demand_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['demand_type'] = $demand_type;

        return $this;
    }

    /**
     * Gets service_restoration_sla
     *
     * @return string|null
     */
    public function getServiceRestorationSla()
    {
        return $this->container['service_restoration_sla'];
    }

    /**
     * Sets service_restoration_sla
     *
     * @param string|null $service_restoration_sla (Conditional) Product Instance Service Restoration SLA. Do not specify for Activation appointments for new Orders.
     *
     * @return self
     */
    public function setServiceRestorationSla($service_restoration_sla)
    {
        if (is_null($service_restoration_sla)) {
            throw new \InvalidArgumentException('non-nullable service_restoration_sla cannot be null');
        }
        $allowedValues = $this->getServiceRestorationSlaAllowableValues();
        if (!in_array($service_restoration_sla, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'service_restoration_sla', must be one of '%s'",
                    $service_restoration_sla,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['service_restoration_sla'] = $service_restoration_sla;

        return $this;
    }

    /**
     * Gets appointment_slot_type
     *
     * @return string|null
     */
    public function getAppointmentSlotType()
    {
        return $this->container['appointment_slot_type'];
    }

    /**
     * Sets appointment_slot_type
     *
     * @param string|null $appointment_slot_type (Conditional) Type of Appointment Time Slot. This value will be auto-detected for standard 'AM' or 'PM' timeslots, but this field must be specified for 'AHA' and 'CA' timeslots. It is recommended to always set this value. 'AM' (morning), 'PM' (afternoon), 'AHA' (after hours), 'CA' (customer appointment) After Hours Appointment (AHA) windows are:  • 5pm - 9pm (Monday to Friday)  • 8am - 12pm (Saturday & Sunday)  • 1pm - 5pm (Saturday & Sunday) AHA will apply to the following demand types:  • Standard Installation  • Non-Standard Installation
     *
     * @return self
     */
    public function setAppointmentSlotType($appointment_slot_type)
    {
        if (is_null($appointment_slot_type)) {
            throw new \InvalidArgumentException('non-nullable appointment_slot_type cannot be null');
        }
        $allowedValues = $this->getAppointmentSlotTypeAllowableValues();
        if (!in_array($appointment_slot_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'appointment_slot_type', must be one of '%s'",
                    $appointment_slot_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['appointment_slot_type'] = $appointment_slot_type;

        return $this;
    }

    /**
     * Gets appointment_sla
     *
     * @return string|null
     */
    public function getAppointmentSla()
    {
        return $this->container['appointment_sla'];
    }

    /**
     * Sets appointment_sla
     *
     * @param string|null $appointment_sla (Conditional) The Appointment Service Level Agreement (SLA). Field is required for Demand Type \"Standard Install\". Do not specify for Demand Type \"Service Restoration\".
     *
     * @return self
     */
    public function setAppointmentSla($appointment_sla)
    {
        if (is_null($appointment_sla)) {
            throw new \InvalidArgumentException('non-nullable appointment_sla cannot be null');
        }
        $allowedValues = $this->getAppointmentSlaAllowableValues();
        if (!in_array($appointment_sla, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'appointment_sla', must be one of '%s'",
                    $appointment_sla,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['appointment_sla'] = $appointment_sla;

        return $this;
    }

    /**
     * Gets start_date_time
     *
     * @return \DateTime|null
     */
    public function getStartDateTime()
    {
        return $this->container['start_date_time'];
    }

    /**
     * Sets start_date_time
     *
     * @param \DateTime|null $start_date_time (Conditional) Set the start time of a new timeslot. Must be a future time, but only up to 90 days. Use /appointments/query/new to find available slots. Example: \"2020-07-27T06:52:52+10:00\"
     *
     * @return self
     */
    public function setStartDateTime($start_date_time)
    {
        if (is_null($start_date_time)) {
            throw new \InvalidArgumentException('non-nullable start_date_time cannot be null');
        }
        $this->container['start_date_time'] = $start_date_time;

        return $this;
    }

    /**
     * Gets end_date_time
     *
     * @return \DateTime|null
     */
    public function getEndDateTime()
    {
        return $this->container['end_date_time'];
    }

    /**
     * Sets end_date_time
     *
     * @param \DateTime|null $end_date_time (Optional) Set the end time of a new timeslot. Must be a future time but only up to 90 days, and must be after the `startDateTime`. Use /appointments/query/new to find available slots. Example: \"2020-07-27T06:52:52+10:00\"
     *
     * @return self
     */
    public function setEndDateTime($end_date_time)
    {
        if (is_null($end_date_time)) {
            throw new \InvalidArgumentException('non-nullable end_date_time cannot be null');
        }
        $this->container['end_date_time'] = $end_date_time;

        return $this;
    }

    /**
     * Gets within_time_window
     *
     * @return bool|null
     */
    public function getWithinTimeWindow()
    {
        return $this->container['within_time_window'];
    }

    /**
     * Sets within_time_window
     *
     * @param bool|null $within_time_window (Conditional) Appointment Slot, Within the Time Window.
     *
     * @return self
     */
    public function setWithinTimeWindow($within_time_window)
    {
        if (is_null($within_time_window)) {
            throw new \InvalidArgumentException('non-nullable within_time_window cannot be null');
        }
        $this->container['within_time_window'] = $within_time_window;

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
     * @param string|null $end_user_type (Optional) Type of End-User
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
     * Gets end_user_contact_type
     *
     * @return string
     */
    public function getEndUserContactType()
    {
        return $this->container['end_user_contact_type'];
    }

    /**
     * Sets end_user_contact_type
     *
     * @param string $end_user_contact_type End User, Type of Contact Person.
     *
     * @return self
     */
    public function setEndUserContactType($end_user_contact_type)
    {
        if (is_null($end_user_contact_type)) {
            throw new \InvalidArgumentException('non-nullable end_user_contact_type cannot be null');
        }
        $allowedValues = $this->getEndUserContactTypeAllowableValues();
        if (!in_array($end_user_contact_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'end_user_contact_type', must be one of '%s'",
                    $end_user_contact_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['end_user_contact_type'] = $end_user_contact_type;

        return $this;
    }

    /**
     * Gets end_user_contact_name
     *
     * @return string
     */
    public function getEndUserContactName()
    {
        return $this->container['end_user_contact_name'];
    }

    /**
     * Sets end_user_contact_name
     *
     * @param string $end_user_contact_name End User, Name of Contact Person.
     *
     * @return self
     */
    public function setEndUserContactName($end_user_contact_name)
    {
        if (is_null($end_user_contact_name)) {
            throw new \InvalidArgumentException('non-nullable end_user_contact_name cannot be null');
        }
        if ((mb_strlen($end_user_contact_name) > 255)) {
            throw new \InvalidArgumentException('invalid length for $end_user_contact_name when calling PostReserveAppointmentRequest., must be smaller than or equal to 255.');
        }

        $this->container['end_user_contact_name'] = $end_user_contact_name;

        return $this;
    }

    /**
     * Gets end_user_contact_phone
     *
     * @return string
     */
    public function getEndUserContactPhone()
    {
        return $this->container['end_user_contact_phone'];
    }

    /**
     * Sets end_user_contact_phone
     *
     * @param string $end_user_contact_phone End User, Contact Phone Number.
     *
     * @return self
     */
    public function setEndUserContactPhone($end_user_contact_phone)
    {
        if (is_null($end_user_contact_phone)) {
            throw new \InvalidArgumentException('non-nullable end_user_contact_phone cannot be null');
        }
        if ((mb_strlen($end_user_contact_phone) > 32)) {
            throw new \InvalidArgumentException('invalid length for $end_user_contact_phone when calling PostReserveAppointmentRequest., must be smaller than or equal to 32.');
        }

        $this->container['end_user_contact_phone'] = $end_user_contact_phone;

        return $this;
    }

    /**
     * Gets end_user_contact_email
     *
     * @return string|null
     */
    public function getEndUserContactEmail()
    {
        return $this->container['end_user_contact_email'];
    }

    /**
     * Sets end_user_contact_email
     *
     * @param string|null $end_user_contact_email (Optional) End User, Contact Email Address.
     *
     * @return self
     */
    public function setEndUserContactEmail($end_user_contact_email)
    {
        if (is_null($end_user_contact_email)) {
            throw new \InvalidArgumentException('non-nullable end_user_contact_email cannot be null');
        }
        if ((mb_strlen($end_user_contact_email) > 255)) {
            throw new \InvalidArgumentException('invalid length for $end_user_contact_email when calling PostReserveAppointmentRequest., must be smaller than or equal to 255.');
        }

        $this->container['end_user_contact_email'] = $end_user_contact_email;

        return $this;
    }

    /**
     * Gets end_user_contact_notes
     *
     * @return string|null
     */
    public function getEndUserContactNotes()
    {
        return $this->container['end_user_contact_notes'];
    }

    /**
     * Sets end_user_contact_notes
     *
     * @param string|null $end_user_contact_notes (Optional) End User, Notes.
     *
     * @return self
     */
    public function setEndUserContactNotes($end_user_contact_notes)
    {
        if (is_null($end_user_contact_notes)) {
            throw new \InvalidArgumentException('non-nullable end_user_contact_notes cannot be null');
        }
        if ((mb_strlen($end_user_contact_notes) > 1000)) {
            throw new \InvalidArgumentException('invalid length for $end_user_contact_notes when calling PostReserveAppointmentRequest., must be smaller than or equal to 1000.');
        }

        $this->container['end_user_contact_notes'] = $end_user_contact_notes;

        return $this;
    }

    /**
     * Gets site_access_building_name
     *
     * @return string|null
     */
    public function getSiteAccessBuildingName()
    {
        return $this->container['site_access_building_name'];
    }

    /**
     * Sets site_access_building_name
     *
     * @param string|null $site_access_building_name (Optional) Site Access Details: Building Name. Max length: 100
     *
     * @return self
     */
    public function setSiteAccessBuildingName($site_access_building_name)
    {
        if (is_null($site_access_building_name)) {
            throw new \InvalidArgumentException('non-nullable site_access_building_name cannot be null');
        }
        if ((mb_strlen($site_access_building_name) > 100)) {
            throw new \InvalidArgumentException('invalid length for $site_access_building_name when calling PostReserveAppointmentRequest., must be smaller than or equal to 100.');
        }

        $this->container['site_access_building_name'] = $site_access_building_name;

        return $this;
    }

    /**
     * Gets site_access_building_type
     *
     * @return string|null
     */
    public function getSiteAccessBuildingType()
    {
        return $this->container['site_access_building_type'];
    }

    /**
     * Sets site_access_building_type
     *
     * @param string|null $site_access_building_type (Optional) Site Access Details: Building Type.
     *
     * @return self
     */
    public function setSiteAccessBuildingType($site_access_building_type)
    {
        if (is_null($site_access_building_type)) {
            throw new \InvalidArgumentException('non-nullable site_access_building_type cannot be null');
        }
        $allowedValues = $this->getSiteAccessBuildingTypeAllowableValues();
        if (!in_array($site_access_building_type, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'site_access_building_type', must be one of '%s'",
                    $site_access_building_type,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['site_access_building_type'] = $site_access_building_type;

        return $this;
    }

    /**
     * Gets site_access_instructions
     *
     * @return string|null
     */
    public function getSiteAccessInstructions()
    {
        return $this->container['site_access_instructions'];
    }

    /**
     * Sets site_access_instructions
     *
     * @param string|null $site_access_instructions (Optional) Site Access Details: Access Instructions. Max length: 500
     *
     * @return self
     */
    public function setSiteAccessInstructions($site_access_instructions)
    {
        if (is_null($site_access_instructions)) {
            throw new \InvalidArgumentException('non-nullable site_access_instructions cannot be null');
        }
        if ((mb_strlen($site_access_instructions) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_instructions when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_instructions'] = $site_access_instructions;

        return $this;
    }

    /**
     * Gets site_access_induction
     *
     * @return string|null
     */
    public function getSiteAccessInduction()
    {
        return $this->container['site_access_induction'];
    }

    /**
     * Sets site_access_induction
     *
     * @param string|null $site_access_induction (Conditional) Site Access Details: Induction Requirements. Mandatory if End User Type is \"Business\". Max length: 500
     *
     * @return self
     */
    public function setSiteAccessInduction($site_access_induction)
    {
        if (is_null($site_access_induction)) {
            throw new \InvalidArgumentException('non-nullable site_access_induction cannot be null');
        }
        if ((mb_strlen($site_access_induction) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_induction when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_induction'] = $site_access_induction;

        return $this;
    }

    /**
     * Gets site_access_accreditation
     *
     * @return string|null
     */
    public function getSiteAccessAccreditation()
    {
        return $this->container['site_access_accreditation'];
    }

    /**
     * Sets site_access_accreditation
     *
     * @param string|null $site_access_accreditation (Conditional) Site Access Details: Accreditation Requirements. Mandatory if End User Type is \"Business\". Allowed values: ('None', 'Unknown', 'Current White Card', 'Elevated Work Platform Rescue', 'Industry Recognised CPR Training Course', 'nbn Safety And Awareness', 'Permit', 'Police Check Required', 'Power Awareness', 'Remove non friable Asbestos', 'Security Clearance', 'Site Induction Essential', 'Site Access Clearance', 'Supervised Asbestos Removal', 'Working At Heights', 'Working With Children', 'Other') If the value \"Other\" is selected, additional free text can be added to the string.  e.g., \"Other Your Details Here\". Max length: 500
     *
     * @return self
     */
    public function setSiteAccessAccreditation($site_access_accreditation)
    {
        if (is_null($site_access_accreditation)) {
            throw new \InvalidArgumentException('non-nullable site_access_accreditation cannot be null');
        }
        if ((mb_strlen($site_access_accreditation) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_accreditation when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_accreditation'] = $site_access_accreditation;

        return $this;
    }

    /**
     * Gets site_access_considerations
     *
     * @return string|null
     */
    public function getSiteAccessConsiderations()
    {
        return $this->container['site_access_considerations'];
    }

    /**
     * Sets site_access_considerations
     *
     * @param string|null $site_access_considerations (Optional) Site Access Details: Site Considerations. Any (comma separated) combination of \"Heritage\", \"Environmental\", \"Cultural\" and additional clarifying free text.
     *
     * @return self
     */
    public function setSiteAccessConsiderations($site_access_considerations)
    {
        if (is_null($site_access_considerations)) {
            throw new \InvalidArgumentException('non-nullable site_access_considerations cannot be null');
        }
        if ((mb_strlen($site_access_considerations) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_considerations when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_considerations'] = $site_access_considerations;

        return $this;
    }

    /**
     * Gets site_access_operational_hours
     *
     * @return string|null
     */
    public function getSiteAccessOperationalHours()
    {
        return $this->container['site_access_operational_hours'];
    }

    /**
     * Sets site_access_operational_hours
     *
     * @param string|null $site_access_operational_hours (Conditional) Site Access Details: Operational hours of the site. Mandatory if End User Type is \"Business\"
     *
     * @return self
     */
    public function setSiteAccessOperationalHours($site_access_operational_hours)
    {
        if (is_null($site_access_operational_hours)) {
            throw new \InvalidArgumentException('non-nullable site_access_operational_hours cannot be null');
        }
        if ((mb_strlen($site_access_operational_hours) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_operational_hours when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_operational_hours'] = $site_access_operational_hours;

        return $this;
    }

    /**
     * Gets site_access_hazards
     *
     * @return string|null
     */
    public function getSiteAccessHazards()
    {
        return $this->container['site_access_hazards'];
    }

    /**
     * Sets site_access_hazards
     *
     * @param string|null $site_access_hazards (Optional) Site Access Details: Information relating to any hazards nbn technicians will need to be aware of.
     *
     * @return self
     */
    public function setSiteAccessHazards($site_access_hazards)
    {
        if (is_null($site_access_hazards)) {
            throw new \InvalidArgumentException('non-nullable site_access_hazards cannot be null');
        }
        if ((mb_strlen($site_access_hazards) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_hazards when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_hazards'] = $site_access_hazards;

        return $this;
    }

    /**
     * Gets site_access_conditions
     *
     * @return string|null
     */
    public function getSiteAccessConditions()
    {
        return $this->container['site_access_conditions'];
    }

    /**
     * Sets site_access_conditions
     *
     * @param string|null $site_access_conditions (Optional) Site Access Details: Special conditions that nbn technicians will need to be aware of.
     *
     * @return self
     */
    public function setSiteAccessConditions($site_access_conditions)
    {
        if (is_null($site_access_conditions)) {
            throw new \InvalidArgumentException('non-nullable site_access_conditions cannot be null');
        }
        if ((mb_strlen($site_access_conditions) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_conditions when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_conditions'] = $site_access_conditions;

        return $this;
    }

    /**
     * Gets site_access_notes
     *
     * @return string|null
     */
    public function getSiteAccessNotes()
    {
        return $this->container['site_access_notes'];
    }

    /**
     * Sets site_access_notes
     *
     * @param string|null $site_access_notes (Optional) Site Access Details: Any other information for the nbn technicians.  Max length: 500
     *
     * @return self
     */
    public function setSiteAccessNotes($site_access_notes)
    {
        if (is_null($site_access_notes)) {
            throw new \InvalidArgumentException('non-nullable site_access_notes cannot be null');
        }
        if ((mb_strlen($site_access_notes) > 500)) {
            throw new \InvalidArgumentException('invalid length for $site_access_notes when calling PostReserveAppointmentRequest., must be smaller than or equal to 500.');
        }

        $this->container['site_access_notes'] = $site_access_notes;

        return $this;
    }

    /**
     * Gets related_order_id
     *
     * @return string|null
     */
    public function getRelatedOrderId()
    {
        return $this->container['related_order_id'];
    }

    /**
     * Sets related_order_id
     *
     * @param string|null $related_order_id (Optional) Related Entities, NBN Product Order ID This will relate a Product Order to this Appointment record. IMPORTANT! This will not attach the Appointment to a related Connect Order. You must call the `/orders/add_appointment` API with the resulting APT # to attach to an order.
     *
     * @return self
     */
    public function setRelatedOrderId($related_order_id)
    {
        if (is_null($related_order_id)) {
            throw new \InvalidArgumentException('non-nullable related_order_id cannot be null');
        }
        if ((mb_strlen($related_order_id) > 32)) {
            throw new \InvalidArgumentException('invalid length for $related_order_id when calling PostReserveAppointmentRequest., must be smaller than or equal to 32.');
        }

        $this->container['related_order_id'] = $related_order_id;

        return $this;
    }

    /**
     * Gets related_product
     *
     * @return string|null
     */
    public function getRelatedProduct()
    {
        return $this->container['related_product'];
    }

    /**
     * Sets related_product
     *
     * @param string|null $related_product (Optional) Related Entities, NBN Product ID
     *
     * @return self
     */
    public function setRelatedProduct($related_product)
    {
        if (is_null($related_product)) {
            throw new \InvalidArgumentException('non-nullable related_product cannot be null');
        }
        if ((mb_strlen($related_product) > 32)) {
            throw new \InvalidArgumentException('invalid length for $related_product when calling PostReserveAppointmentRequest., must be smaller than or equal to 32.');
        }

        $this->container['related_product'] = $related_product;

        return $this;
    }

    /**
     * Gets copper_pair_id
     *
     * @return string|null
     */
    public function getCopperPairId()
    {
        return $this->container['copper_pair_id'];
    }

    /**
     * Sets copper_pair_id
     *
     * @param string|null $copper_pair_id (Optional) Related Entities, NBN Copper Pair ID
     *
     * @return self
     */
    public function setCopperPairId($copper_pair_id)
    {
        if (is_null($copper_pair_id)) {
            throw new \InvalidArgumentException('non-nullable copper_pair_id cannot be null');
        }
        if ((mb_strlen($copper_pair_id) > 32)) {
            throw new \InvalidArgumentException('invalid length for $copper_pair_id when calling PostReserveAppointmentRequest., must be smaller than or equal to 32.');
        }

        $this->container['copper_pair_id'] = $copper_pair_id;

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
     * @param bool|null $priority_assist (Optional) Priority Assist required or not. Priority timeslots will only be returned if \"true\" and the Appointment SLA value is \"Standard\". Only specify \"true\" where the end user is eligible for a Downstream Priority Assistance Service in accordance with NBN Co's Wholesale Broadband Agreement. \"true\" is accepted only for a customer on our Priority Assistance register (the end user named in `endUser`, or when none is named the account or your own end user); otherwise the reservation is refused with a 422 on `priorityAssist` before the carrier is asked.
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
     * @param string|null $end_user (Optional) Ours, not the carrier's: the number of the end user the appointment is for, as `POST /data/orders` takes it. With `priorityAssist` true it is that end user's registration that counts; an end user that is not this account's is refused with a 422 on `priorityAssist`. Left out, the account's own registration counts, or the one on your own end user (made when you register yourself); another end user's counts only when named.
     *
     * @return self
     */
    public function setEndUser($end_user)
    {
        if (is_null($end_user)) {
            throw new \InvalidArgumentException('non-nullable end_user cannot be null');
        }
        if ((mb_strlen($end_user) > 64)) {
            throw new \InvalidArgumentException('invalid length for $end_user when calling PostReserveAppointmentRequest., must be smaller than or equal to 64.');
        }

        $this->container['end_user'] = $end_user;

        return $this;
    }

    /**
     * Gets trouble_ticket_id
     *
     * @return string|null
     */
    public function getTroubleTicketId()
    {
        return $this->container['trouble_ticket_id'];
    }

    /**
     * Sets trouble_ticket_id
     *
     * @param string|null $trouble_ticket_id (Optional) ID of an NBN Trouble Ticket relevant to this appointment. e.g., \"INC190100002402\"
     *
     * @return self
     */
    public function setTroubleTicketId($trouble_ticket_id)
    {
        if (is_null($trouble_ticket_id)) {
            throw new \InvalidArgumentException('non-nullable trouble_ticket_id cannot be null');
        }
        if ((mb_strlen($trouble_ticket_id) > 32)) {
            throw new \InvalidArgumentException('invalid length for $trouble_ticket_id when calling PostReserveAppointmentRequest., must be smaller than or equal to 32.');
        }

        $this->container['trouble_ticket_id'] = $trouble_ticket_id;

        return $this;
    }

    /**
     * Gets incident_id
     *
     * @return string|null
     */
    public function getIncidentId()
    {
        return $this->container['incident_id'];
    }

    /**
     * Sets incident_id
     *
     * @param string|null $incident_id (Optional) ID of an NBN Incident report
     *
     * @return self
     */
    public function setIncidentId($incident_id)
    {
        if (is_null($incident_id)) {
            throw new \InvalidArgumentException('non-nullable incident_id cannot be null');
        }
        if ((mb_strlen($incident_id) > 32)) {
            throw new \InvalidArgumentException('invalid length for $incident_id when calling PostReserveAppointmentRequest., must be smaller than or equal to 32.');
        }

        $this->container['incident_id'] = $incident_id;

        return $this;
    }

    /**
     * Gets alternative_technology
     *
     * @return string|null
     */
    public function getAlternativeTechnology()
    {
        return $this->container['alternative_technology'];
    }

    /**
     * Sets alternative_technology
     *
     * @param string|null $alternative_technology (Conditional) For FTTN/C Fibre Uplift appointments only. Specify as \"Fibre\" if uplifting a FTTN or FTTC location to Fibre.
     *
     * @return self
     */
    public function setAlternativeTechnology($alternative_technology)
    {
        if (is_null($alternative_technology)) {
            throw new \InvalidArgumentException('non-nullable alternative_technology cannot be null');
        }
        $allowedValues = $this->getAlternativeTechnologyAllowableValues();
        if (!in_array($alternative_technology, $allowedValues, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid value '%s' for 'alternative_technology', must be one of '%s'",
                    $alternative_technology,
                    implode("', '", $allowedValues)
                )
            );
        }
        $this->container['alternative_technology'] = $alternative_technology;

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


