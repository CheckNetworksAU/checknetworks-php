<?php
/**
 * GetSearch200ResponseInner
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
 * GetSearch200ResponseInner Class Doc Comment
 *
 * @category Class
 * @package  CheckNetworks\Api\Generated
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class GetSearch200ResponseInner implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
     * The original name of the model.
     *
     * @var string
     */
    protected static $openAPIModelName = 'get_search_200_response_inner';

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $openAPITypes = [
        'id' => 'string',
        'href' => 'string',
        'formatted_address' => 'string',
        'lot_number' => 'string',
        'plan_number' => 'string',
        'road_number1' => 'string',
        'road_number2' => 'string',
        'road_name' => 'string',
        'road_type_code' => 'string',
        'road_suffix_code' => 'string',
        'locality_name' => 'string',
        'postcode' => 'string',
        'state_territory_code' => 'string',
        'gnaf_id' => 'string',
        'country' => 'string',
        'secondary_complex_name' => 'string',
        'unit_type_code' => 'string',
        'unit_number' => 'string',
        'level_type_code' => 'string',
        'level_number' => 'string',
        'address_site_name' => 'string',
        'complex_road_number1' => 'string',
        'complex_road_number2' => 'string',
        'complex_road_name' => 'string',
        'complex_road_type_code' => 'string',
        'complex_road_suffix_code' => 'string',
        'location_descriptor' => 'string',
        'geographic_datum' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'rank' => 'float'
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
        'href' => null,
        'formatted_address' => null,
        'lot_number' => null,
        'plan_number' => null,
        'road_number1' => null,
        'road_number2' => null,
        'road_name' => null,
        'road_type_code' => null,
        'road_suffix_code' => null,
        'locality_name' => null,
        'postcode' => null,
        'state_territory_code' => null,
        'gnaf_id' => null,
        'country' => null,
        'secondary_complex_name' => null,
        'unit_type_code' => null,
        'unit_number' => null,
        'level_type_code' => null,
        'level_number' => null,
        'address_site_name' => null,
        'complex_road_number1' => null,
        'complex_road_number2' => null,
        'complex_road_name' => null,
        'complex_road_type_code' => null,
        'complex_road_suffix_code' => null,
        'location_descriptor' => null,
        'geographic_datum' => null,
        'latitude' => null,
        'longitude' => null,
        'rank' => null
    ];

    /**
     * Array of nullable properties. Used for (de)serialization
     *
     * @var boolean[]
     */
    protected static array $openAPINullables = [
        'id' => false,
        'href' => false,
        'formatted_address' => false,
        'lot_number' => false,
        'plan_number' => false,
        'road_number1' => false,
        'road_number2' => false,
        'road_name' => false,
        'road_type_code' => false,
        'road_suffix_code' => false,
        'locality_name' => false,
        'postcode' => false,
        'state_territory_code' => false,
        'gnaf_id' => false,
        'country' => false,
        'secondary_complex_name' => false,
        'unit_type_code' => false,
        'unit_number' => false,
        'level_type_code' => false,
        'level_number' => false,
        'address_site_name' => false,
        'complex_road_number1' => false,
        'complex_road_number2' => false,
        'complex_road_name' => false,
        'complex_road_type_code' => false,
        'complex_road_suffix_code' => false,
        'location_descriptor' => false,
        'geographic_datum' => false,
        'latitude' => false,
        'longitude' => false,
        'rank' => false
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
        'formatted_address' => 'formattedAddress',
        'lot_number' => 'lotNumber',
        'plan_number' => 'planNumber',
        'road_number1' => 'roadNumber1',
        'road_number2' => 'roadNumber2',
        'road_name' => 'roadName',
        'road_type_code' => 'roadTypeCode',
        'road_suffix_code' => 'roadSuffixCode',
        'locality_name' => 'localityName',
        'postcode' => 'postcode',
        'state_territory_code' => 'stateTerritoryCode',
        'gnaf_id' => 'gnafId',
        'country' => 'country',
        'secondary_complex_name' => 'secondaryComplexName',
        'unit_type_code' => 'unitTypeCode',
        'unit_number' => 'unitNumber',
        'level_type_code' => 'levelTypeCode',
        'level_number' => 'levelNumber',
        'address_site_name' => 'addressSiteName',
        'complex_road_number1' => 'complexRoadNumber1',
        'complex_road_number2' => 'complexRoadNumber2',
        'complex_road_name' => 'complexRoadName',
        'complex_road_type_code' => 'complexRoadTypeCode',
        'complex_road_suffix_code' => 'complexRoadSuffixCode',
        'location_descriptor' => 'locationDescriptor',
        'geographic_datum' => 'geographicDatum',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'rank' => 'rank'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'id' => 'setId',
        'href' => 'setHref',
        'formatted_address' => 'setFormattedAddress',
        'lot_number' => 'setLotNumber',
        'plan_number' => 'setPlanNumber',
        'road_number1' => 'setRoadNumber1',
        'road_number2' => 'setRoadNumber2',
        'road_name' => 'setRoadName',
        'road_type_code' => 'setRoadTypeCode',
        'road_suffix_code' => 'setRoadSuffixCode',
        'locality_name' => 'setLocalityName',
        'postcode' => 'setPostcode',
        'state_territory_code' => 'setStateTerritoryCode',
        'gnaf_id' => 'setGnafId',
        'country' => 'setCountry',
        'secondary_complex_name' => 'setSecondaryComplexName',
        'unit_type_code' => 'setUnitTypeCode',
        'unit_number' => 'setUnitNumber',
        'level_type_code' => 'setLevelTypeCode',
        'level_number' => 'setLevelNumber',
        'address_site_name' => 'setAddressSiteName',
        'complex_road_number1' => 'setComplexRoadNumber1',
        'complex_road_number2' => 'setComplexRoadNumber2',
        'complex_road_name' => 'setComplexRoadName',
        'complex_road_type_code' => 'setComplexRoadTypeCode',
        'complex_road_suffix_code' => 'setComplexRoadSuffixCode',
        'location_descriptor' => 'setLocationDescriptor',
        'geographic_datum' => 'setGeographicDatum',
        'latitude' => 'setLatitude',
        'longitude' => 'setLongitude',
        'rank' => 'setRank'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'id' => 'getId',
        'href' => 'getHref',
        'formatted_address' => 'getFormattedAddress',
        'lot_number' => 'getLotNumber',
        'plan_number' => 'getPlanNumber',
        'road_number1' => 'getRoadNumber1',
        'road_number2' => 'getRoadNumber2',
        'road_name' => 'getRoadName',
        'road_type_code' => 'getRoadTypeCode',
        'road_suffix_code' => 'getRoadSuffixCode',
        'locality_name' => 'getLocalityName',
        'postcode' => 'getPostcode',
        'state_territory_code' => 'getStateTerritoryCode',
        'gnaf_id' => 'getGnafId',
        'country' => 'getCountry',
        'secondary_complex_name' => 'getSecondaryComplexName',
        'unit_type_code' => 'getUnitTypeCode',
        'unit_number' => 'getUnitNumber',
        'level_type_code' => 'getLevelTypeCode',
        'level_number' => 'getLevelNumber',
        'address_site_name' => 'getAddressSiteName',
        'complex_road_number1' => 'getComplexRoadNumber1',
        'complex_road_number2' => 'getComplexRoadNumber2',
        'complex_road_name' => 'getComplexRoadName',
        'complex_road_type_code' => 'getComplexRoadTypeCode',
        'complex_road_suffix_code' => 'getComplexRoadSuffixCode',
        'location_descriptor' => 'getLocationDescriptor',
        'geographic_datum' => 'getGeographicDatum',
        'latitude' => 'getLatitude',
        'longitude' => 'getLongitude',
        'rank' => 'getRank'
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
        $this->setIfExists('id', $data ?? [], null);
        $this->setIfExists('href', $data ?? [], null);
        $this->setIfExists('formatted_address', $data ?? [], null);
        $this->setIfExists('lot_number', $data ?? [], null);
        $this->setIfExists('plan_number', $data ?? [], null);
        $this->setIfExists('road_number1', $data ?? [], null);
        $this->setIfExists('road_number2', $data ?? [], null);
        $this->setIfExists('road_name', $data ?? [], null);
        $this->setIfExists('road_type_code', $data ?? [], null);
        $this->setIfExists('road_suffix_code', $data ?? [], null);
        $this->setIfExists('locality_name', $data ?? [], null);
        $this->setIfExists('postcode', $data ?? [], null);
        $this->setIfExists('state_territory_code', $data ?? [], null);
        $this->setIfExists('gnaf_id', $data ?? [], null);
        $this->setIfExists('country', $data ?? [], null);
        $this->setIfExists('secondary_complex_name', $data ?? [], null);
        $this->setIfExists('unit_type_code', $data ?? [], null);
        $this->setIfExists('unit_number', $data ?? [], null);
        $this->setIfExists('level_type_code', $data ?? [], null);
        $this->setIfExists('level_number', $data ?? [], null);
        $this->setIfExists('address_site_name', $data ?? [], null);
        $this->setIfExists('complex_road_number1', $data ?? [], null);
        $this->setIfExists('complex_road_number2', $data ?? [], null);
        $this->setIfExists('complex_road_name', $data ?? [], null);
        $this->setIfExists('complex_road_type_code', $data ?? [], null);
        $this->setIfExists('complex_road_suffix_code', $data ?? [], null);
        $this->setIfExists('location_descriptor', $data ?? [], null);
        $this->setIfExists('geographic_datum', $data ?? [], null);
        $this->setIfExists('latitude', $data ?? [], null);
        $this->setIfExists('longitude', $data ?? [], null);
        $this->setIfExists('rank', $data ?? [], null);
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
     * @param string|null $id NBN Location ID
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
     * @param string|null $href Link to this address, `/nbn/address/{locId}`, on this API. Present only when the carrier sent a link.
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
     * Gets formatted_address
     *
     * @return string|null
     */
    public function getFormattedAddress()
    {
        return $this->container['formatted_address'];
    }

    /**
     * Sets formatted_address
     *
     * @param string|null $formatted_address Human-readable single-line form of the address.
     *
     * @return self
     */
    public function setFormattedAddress($formatted_address)
    {
        if (is_null($formatted_address)) {
            throw new \InvalidArgumentException('non-nullable formatted_address cannot be null');
        }
        $this->container['formatted_address'] = $formatted_address;

        return $this;
    }

    /**
     * Gets lot_number
     *
     * @return string|null
     */
    public function getLotNumber()
    {
        return $this->container['lot_number'];
    }

    /**
     * Sets lot_number
     *
     * @param string|null $lot_number The Lot number is a reference number allocated to a property for subdivision administration purposes prior to road numbering.  Usage: Where an official Road number has not been assigned, this field may be used for postal purposes as well as the physical identification of an address site.
     *
     * @return self
     */
    public function setLotNumber($lot_number)
    {
        if (is_null($lot_number)) {
            throw new \InvalidArgumentException('non-nullable lot_number cannot be null');
        }
        $this->container['lot_number'] = $lot_number;

        return $this;
    }

    /**
     * Gets plan_number
     *
     * @return string|null
     */
    public function getPlanNumber()
    {
        return $this->container['plan_number'];
    }

    /**
     * Sets plan_number
     *
     * @param string|null $plan_number The deposited plan number allocated to a property for subdivision administration purposes prior to road numbering in addition to the Lot number.
     *
     * @return self
     */
    public function setPlanNumber($plan_number)
    {
        if (is_null($plan_number)) {
            throw new \InvalidArgumentException('non-nullable plan_number cannot be null');
        }
        $this->container['plan_number'] = $plan_number;

        return $this;
    }

    /**
     * Gets road_number1
     *
     * @return string|null
     */
    public function getRoadNumber1()
    {
        return $this->container['road_number1'];
    }

    /**
     * Sets road_number1
     *
     * @param string|null $road_number1 Identifies the number of the address in the road or thoroughfare and for a ranged address is the start number. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix.
     *
     * @return self
     */
    public function setRoadNumber1($road_number1)
    {
        if (is_null($road_number1)) {
            throw new \InvalidArgumentException('non-nullable road_number1 cannot be null');
        }
        $this->container['road_number1'] = $road_number1;

        return $this;
    }

    /**
     * Gets road_number2
     *
     * @return string|null
     */
    public function getRoadNumber2()
    {
        return $this->container['road_number2'];
    }

    /**
     * Sets road_number2
     *
     * @param string|null $road_number2 Identifies the last number for a ranged address in the road or thoroughfare. The data element may include an alphabetic prefix. The data element may include an alphabetic suffix. This data element is used for ranged street numbers otherwise it is left blank.
     *
     * @return self
     */
    public function setRoadNumber2($road_number2)
    {
        if (is_null($road_number2)) {
            throw new \InvalidArgumentException('non-nullable road_number2 cannot be null');
        }
        $this->container['road_number2'] = $road_number2;

        return $this;
    }

    /**
     * Gets road_name
     *
     * @return string|null
     */
    public function getRoadName()
    {
        return $this->container['road_name'];
    }

    /**
     * Sets road_name
     *
     * @param string|null $road_name The name of the road/thoroughfare applicable to the address site or complex. Only the road name should be stored in this data element.
     *
     * @return self
     */
    public function setRoadName($road_name)
    {
        if (is_null($road_name)) {
            throw new \InvalidArgumentException('non-nullable road_name cannot be null');
        }
        $this->container['road_name'] = $road_name;

        return $this;
    }

    /**
     * Gets road_type_code
     *
     * @return string|null
     */
    public function getRoadTypeCode()
    {
        return $this->container['road_type_code'];
    }

    /**
     * Sets road_type_code
     *
     * @param string|null $road_type_code The abbreviation code used to distinguish the type of road/thoroughfare applicable to the address site/complex.
     *
     * @return self
     */
    public function setRoadTypeCode($road_type_code)
    {
        if (is_null($road_type_code)) {
            throw new \InvalidArgumentException('non-nullable road_type_code cannot be null');
        }
        $this->container['road_type_code'] = $road_type_code;

        return $this;
    }

    /**
     * Gets road_suffix_code
     *
     * @return string|null
     */
    public function getRoadSuffixCode()
    {
        return $this->container['road_suffix_code'];
    }

    /**
     * Sets road_suffix_code
     *
     * @param string|null $road_suffix_code The abbreviation code used to represent the suffix of road/thoroughfare applicable to the address site/complex.
     *
     * @return self
     */
    public function setRoadSuffixCode($road_suffix_code)
    {
        if (is_null($road_suffix_code)) {
            throw new \InvalidArgumentException('non-nullable road_suffix_code cannot be null');
        }
        $this->container['road_suffix_code'] = $road_suffix_code;

        return $this;
    }

    /**
     * Gets locality_name
     *
     * @return string|null
     */
    public function getLocalityName()
    {
        return $this->container['locality_name'];
    }

    /**
     * Sets locality_name
     *
     * @param string|null $locality_name The name of the locality/suburb of the address.
     *
     * @return self
     */
    public function setLocalityName($locality_name)
    {
        if (is_null($locality_name)) {
            throw new \InvalidArgumentException('non-nullable locality_name cannot be null');
        }
        $this->container['locality_name'] = $locality_name;

        return $this;
    }

    /**
     * Gets postcode
     *
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->container['postcode'];
    }

    /**
     * Sets postcode
     *
     * @param string|null $postcode The Australian numeric descriptor for a postal delivery area, aligned with locality, suburb or place  *Mandatory if stateTerritoryCode not provided
     *
     * @return self
     */
    public function setPostcode($postcode)
    {
        if (is_null($postcode)) {
            throw new \InvalidArgumentException('non-nullable postcode cannot be null');
        }
        $this->container['postcode'] = $postcode;

        return $this;
    }

    /**
     * Gets state_territory_code
     *
     * @return string|null
     */
    public function getStateTerritoryCode()
    {
        return $this->container['state_territory_code'];
    }

    /**
     * Sets state_territory_code
     *
     * @param string|null $state_territory_code The State or Territory code of the address.  *Mandatory if postcode not provided
     *
     * @return self
     */
    public function setStateTerritoryCode($state_territory_code)
    {
        if (is_null($state_territory_code)) {
            throw new \InvalidArgumentException('non-nullable state_territory_code cannot be null');
        }
        $this->container['state_territory_code'] = $state_territory_code;

        return $this;
    }

    /**
     * Gets gnaf_id
     *
     * @return string|null
     */
    public function getGnafId()
    {
        return $this->container['gnaf_id'];
    }

    /**
     * Sets gnaf_id
     *
     * @param string|null $gnaf_id Geocoded National Address File (GNAF) ID
     *
     * @return self
     */
    public function setGnafId($gnaf_id)
    {
        if (is_null($gnaf_id)) {
            throw new \InvalidArgumentException('non-nullable gnaf_id cannot be null');
        }
        $this->container['gnaf_id'] = $gnaf_id;

        return $this;
    }

    /**
     * Gets country
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->container['country'];
    }

    /**
     * Sets country
     *
     * @param string|null $country Country of the address. Always \"Australia\".
     *
     * @return self
     */
    public function setCountry($country)
    {
        if (is_null($country)) {
            throw new \InvalidArgumentException('non-nullable country cannot be null');
        }
        $this->container['country'] = $country;

        return $this;
    }

    /**
     * Gets secondary_complex_name
     *
     * @return string|null
     */
    public function getSecondaryComplexName()
    {
        return $this->container['secondary_complex_name'];
    }

    /**
     * Sets secondary_complex_name
     *
     * @param string|null $secondary_complex_name Name associated with a building or area within a complex site. This data element may represent the name given to an entire building within an address site that has its own separate address.
     *
     * @return self
     */
    public function setSecondaryComplexName($secondary_complex_name)
    {
        if (is_null($secondary_complex_name)) {
            throw new \InvalidArgumentException('non-nullable secondary_complex_name cannot be null');
        }
        $this->container['secondary_complex_name'] = $secondary_complex_name;

        return $this;
    }

    /**
     * Gets unit_type_code
     *
     * @return string|null
     */
    public function getUnitTypeCode()
    {
        return $this->container['unit_type_code'];
    }

    /**
     * Sets unit_type_code
     *
     * @param string|null $unit_type_code Sub dwelling unit type code. Within a complex, an abbreviation used to distinguish the type of an address found within a building / sub-complex or marina.
     *
     * @return self
     */
    public function setUnitTypeCode($unit_type_code)
    {
        if (is_null($unit_type_code)) {
            throw new \InvalidArgumentException('non-nullable unit_type_code cannot be null');
        }
        $this->container['unit_type_code'] = $unit_type_code;

        return $this;
    }

    /**
     * Gets unit_number
     *
     * @return string|null
     */
    public function getUnitNumber()
    {
        return $this->container['unit_number'];
    }

    /**
     * Sets unit_number
     *
     * @param string|null $unit_number Sub-dwelling unit number. Within a complex, a number used to distinguish an address found within a building / sub-complex or marina. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix.
     *
     * @return self
     */
    public function setUnitNumber($unit_number)
    {
        if (is_null($unit_number)) {
            throw new \InvalidArgumentException('non-nullable unit_number cannot be null');
        }
        $this->container['unit_number'] = $unit_number;

        return $this;
    }

    /**
     * Gets level_type_code
     *
     * @return string|null
     */
    public function getLevelTypeCode()
    {
        return $this->container['level_type_code'];
    }

    /**
     * Sets level_type_code
     *
     * @param string|null $level_type_code Within a complex, an abbreviation used to distinguish the floor or level of a multi-storey building/sub-complex.
     *
     * @return self
     */
    public function setLevelTypeCode($level_type_code)
    {
        if (is_null($level_type_code)) {
            throw new \InvalidArgumentException('non-nullable level_type_code cannot be null');
        }
        $this->container['level_type_code'] = $level_type_code;

        return $this;
    }

    /**
     * Gets level_number
     *
     * @return string|null
     */
    public function getLevelNumber()
    {
        return $this->container['level_number'];
    }

    /**
     * Sets level_number
     *
     * @param string|null $level_number Within a complex, the number of the floor or level of a multi-storey building/ sub-complex. The data element may include a leading alphabetic prefix. The data element may include a trailing alphabetic suffix.
     *
     * @return self
     */
    public function setLevelNumber($level_number)
    {
        if (is_null($level_number)) {
            throw new \InvalidArgumentException('non-nullable level_number cannot be null');
        }
        $this->container['level_number'] = $level_number;

        return $this;
    }

    /**
     * Gets address_site_name
     *
     * @return string|null
     */
    public function getAddressSiteName()
    {
        return $this->container['address_site_name'];
    }

    /**
     * Sets address_site_name
     *
     * @param string|null $address_site_name Synonyms: Building property name, Primary Complex name  The official place name or culturally accepted common usage name for an address site, including the name of a building, homestead, building complex, agricultural property, park or unbounded address site. This data element represents the name given to an entire building or address site
     *
     * @return self
     */
    public function setAddressSiteName($address_site_name)
    {
        if (is_null($address_site_name)) {
            throw new \InvalidArgumentException('non-nullable address_site_name cannot be null');
        }
        $this->container['address_site_name'] = $address_site_name;

        return $this;
    }

    /**
     * Gets complex_road_number1
     *
     * @return string|null
     */
    public function getComplexRoadNumber1()
    {
        return $this->container['complex_road_number1'];
    }

    /**
     * Sets complex_road_number1
     *
     * @param string|null $complex_road_number1 Within a complex, identifies the number of the address in the road or thoroughfare and for a ranged address is the start number.
     *
     * @return self
     */
    public function setComplexRoadNumber1($complex_road_number1)
    {
        if (is_null($complex_road_number1)) {
            throw new \InvalidArgumentException('non-nullable complex_road_number1 cannot be null');
        }
        $this->container['complex_road_number1'] = $complex_road_number1;

        return $this;
    }

    /**
     * Gets complex_road_number2
     *
     * @return string|null
     */
    public function getComplexRoadNumber2()
    {
        return $this->container['complex_road_number2'];
    }

    /**
     * Sets complex_road_number2
     *
     * @param string|null $complex_road_number2 Within a complex, identifies the last number for a ranged address in the road or thoroughfare.
     *
     * @return self
     */
    public function setComplexRoadNumber2($complex_road_number2)
    {
        if (is_null($complex_road_number2)) {
            throw new \InvalidArgumentException('non-nullable complex_road_number2 cannot be null');
        }
        $this->container['complex_road_number2'] = $complex_road_number2;

        return $this;
    }

    /**
     * Gets complex_road_name
     *
     * @return string|null
     */
    public function getComplexRoadName()
    {
        return $this->container['complex_road_name'];
    }

    /**
     * Sets complex_road_name
     *
     * @param string|null $complex_road_name Within a complex, the name of the road /thoroughfare of the address. Within a Road name it is possible to find what appears to be a Road type (eg. The Boulevard). It is also possible to have a null Road type.
     *
     * @return self
     */
    public function setComplexRoadName($complex_road_name)
    {
        if (is_null($complex_road_name)) {
            throw new \InvalidArgumentException('non-nullable complex_road_name cannot be null');
        }
        $this->container['complex_road_name'] = $complex_road_name;

        return $this;
    }

    /**
     * Gets complex_road_type_code
     *
     * @return string|null
     */
    public function getComplexRoadTypeCode()
    {
        return $this->container['complex_road_type_code'];
    }

    /**
     * Sets complex_road_type_code
     *
     * @param string|null $complex_road_type_code Within a complex, an abbreviation used to distinguish the type of road / thoroughfare of the address.
     *
     * @return self
     */
    public function setComplexRoadTypeCode($complex_road_type_code)
    {
        if (is_null($complex_road_type_code)) {
            throw new \InvalidArgumentException('non-nullable complex_road_type_code cannot be null');
        }
        $this->container['complex_road_type_code'] = $complex_road_type_code;

        return $this;
    }

    /**
     * Gets complex_road_suffix_code
     *
     * @return string|null
     */
    public function getComplexRoadSuffixCode()
    {
        return $this->container['complex_road_suffix_code'];
    }

    /**
     * Sets complex_road_suffix_code
     *
     * @param string|null $complex_road_suffix_code Within a complex, an abbreviation representing the suffix of the road/ thoroughfare of the address.
     *
     * @return self
     */
    public function setComplexRoadSuffixCode($complex_road_suffix_code)
    {
        if (is_null($complex_road_suffix_code)) {
            throw new \InvalidArgumentException('non-nullable complex_road_suffix_code cannot be null');
        }
        $this->container['complex_road_suffix_code'] = $complex_road_suffix_code;

        return $this;
    }

    /**
     * Gets location_descriptor
     *
     * @return string|null
     */
    public function getLocationDescriptor()
    {
        return $this->container['location_descriptor'];
    }

    /**
     * Sets location_descriptor
     *
     * @param string|null $location_descriptor Additional descriptor that further identifies the location.
     *
     * @return self
     */
    public function setLocationDescriptor($location_descriptor)
    {
        if (is_null($location_descriptor)) {
            throw new \InvalidArgumentException('non-nullable location_descriptor cannot be null');
        }
        $this->container['location_descriptor'] = $location_descriptor;

        return $this;
    }

    /**
     * Gets geographic_datum
     *
     * @return string|null
     */
    public function getGeographicDatum()
    {
        return $this->container['geographic_datum'];
    }

    /**
     * Sets geographic_datum
     *
     * @param string|null $geographic_datum Geographic datum of the address.
     *
     * @return self
     */
    public function setGeographicDatum($geographic_datum)
    {
        if (is_null($geographic_datum)) {
            throw new \InvalidArgumentException('non-nullable geographic_datum cannot be null');
        }
        $this->container['geographic_datum'] = $geographic_datum;

        return $this;
    }

    /**
     * Gets latitude
     *
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->container['latitude'];
    }

    /**
     * Sets latitude
     *
     * @param string|null $latitude Geographic latitude coordinate of the address, as a decimal string.
     *
     * @return self
     */
    public function setLatitude($latitude)
    {
        if (is_null($latitude)) {
            throw new \InvalidArgumentException('non-nullable latitude cannot be null');
        }
        $this->container['latitude'] = $latitude;

        return $this;
    }

    /**
     * Gets longitude
     *
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->container['longitude'];
    }

    /**
     * Sets longitude
     *
     * @param string|null $longitude Geographic longitude coordinate of the address, as a decimal string.
     *
     * @return self
     */
    public function setLongitude($longitude)
    {
        if (is_null($longitude)) {
            throw new \InvalidArgumentException('non-nullable longitude cannot be null');
        }
        $this->container['longitude'] = $longitude;

        return $this;
    }

    /**
     * Gets rank
     *
     * @return float|null
     */
    public function getRank()
    {
        return $this->container['rank'];
    }

    /**
     * Sets rank
     *
     * @param float|null $rank How well this result matches the initial search, as a number between 0 and 100. A higher rank represents a better match. Only present when the `rankResults` parameter is true.
     *
     * @return self
     */
    public function setRank($rank)
    {
        if (is_null($rank)) {
            throw new \InvalidArgumentException('non-nullable rank cannot be null');
        }
        $this->container['rank'] = $rank;

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


