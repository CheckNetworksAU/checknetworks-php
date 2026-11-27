<?php
/**
 * WebhookEventType
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
use \CheckNetworks\ObjectSerializer;

/**
 * WebhookEventType Class Doc Comment
 *
 * @category Class
 * @description A subscribable webhook event type, or &#x60;*&#x60; for every event.  Declared in one place and referenced by both the subscription request and the subscription you get back, so anything you can be told about is something you can subscribe to. (They drifted apart once: five order and service events reached the response list while the request list still rejected them, which left a generated client unable to subscribe to events the same spec said existed.)  The three &#x60;transit.blackhole.*&#x60; types are not available yet: IP transit is not offered by API yet, so subscribing to one answers &#x60;422&#x60; and none is sent until it is. They are listed so a client built now knows them.  &#x60;ping&#x60; is deliberately absent — it is sent only by &#x60;POST /webhooks/{webhookId}/test&#x60;, regardless of what the subscription covers, so it is not something to subscribe to.
 * @package  CheckNetworks
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class WebhookEventType
{
    /**
     * Possible values of this enum
     */
    public const STAR = '*';

    public const TICKET_CREATED = 'ticket.created';

    public const TICKET_REPLIED = 'ticket.replied';

    public const TICKET_STATUS_CHANGED = 'ticket.statusChanged';

    public const TICKET_CSAT_SUBMITTED = 'ticket.csatSubmitted';

    public const TICKET_INFORMATION_REQUIRED = 'ticket.informationRequired';

    public const VOICE_CALL_COMPLETED = 'voice.call.completed';

    public const VOICE_VOICEMAIL_RECEIVED = 'voice.voicemail.received';

    public const VOICE_RECORDING_READY = 'voice.recording.ready';

    public const TRUNK_DOWN = 'trunk.down';

    public const TRUNK_UP = 'trunk.up';

    public const VOICE_HOLD_PLACED = 'voice.hold.placed';

    public const VOICE_HOLD_RELEASED = 'voice.hold.released';

    public const NUMBER_ASSIGNED = 'number.assigned';

    public const NUMBER_UNASSIGNED = 'number.unassigned';

    public const NUMBER_RELEASED = 'number.released';

    public const NUMBER_SUSPENDED = 'number.suspended';

    public const NUMBER_REACTIVATED = 'number.reactivated';

    public const IPND_RECORD_REJECTED = 'ipnd.recordRejected';

    public const TRUNK_PLAN_CHANGE_SCHEDULED = 'trunk.planChangeScheduled';

    public const TRUNK_PLAN_CHANGE_CANCELLED = 'trunk.planChangeCancelled';

    public const TRUNK_PLAN_CHANGED = 'trunk.planChanged';

    public const BOOKED_CHANGE_APPLIED = 'bookedChange.applied';

    public const BOOKED_CHANGE_CANCELLED = 'bookedChange.cancelled';

    public const BOOKED_CHANGE_FAILED = 'bookedChange.failed';

    public const BOOKED_CHANGE_RESCHEDULED = 'bookedChange.rescheduled';

    public const INVOICE_ISSUED = 'invoice.issued';

    public const INVOICE_PAID = 'invoice.paid';

    public const INVOICE_VOIDED = 'invoice.voided';

    public const PAYMENT_RECEIVED = 'payment.received';

    public const PAYMENT_SUCCEEDED = 'payment.succeeded';

    public const PAYMENT_FAILED = 'payment.failed';

    public const PAYMENT_REVERSED = 'payment.reversed';

    public const CREDIT_NOTE_ISSUED = 'creditNote.issued';

    public const DISPUTE_RAISED = 'dispute.raised';

    public const DISPUTE_DECIDED = 'dispute.decided';

    public const INVOICE_OVERDUE = 'invoice.overdue';

    public const PAYMENT_METHOD_EXPIRING = 'paymentMethod.expiring';

    public const CREDIT_THRESHOLD_REACHED = 'credit.thresholdReached';

    public const CREDIT_CALLS_BLOCKED = 'credit.callsBlocked';

    public const CREDIT_CALLS_RESTORED = 'credit.callsRestored';

    public const PRICE_RISE_NOTICED = 'priceRise.noticed';

    public const USAGE_THRESHOLD_REACHED = 'usage.thresholdReached';

    public const USAGE_ALERT_TRIGGERED = 'usageAlert.triggered';

    public const DATA_REALM_APPROVED = 'data.realm.approved';

    public const DATA_REALM_REJECTED = 'data.realm.rejected';

    public const DATA_RADIUS_SESSION_RETRIEVED = 'data.radius.sessionRetrieved';

    public const ORDER_STATUS_CHANGED = 'order.statusChanged';

    public const ORDER_DELAYED = 'order.delayed';

    public const ORDER_APPOINTMENT_REQUIRED = 'order.appointmentRequired';

    public const ORDER_NTD_FULL = 'order.ntdFull';

    public const PORT_ACTION_REQUIRED = 'port.actionRequired';

    public const SERVICE_PROVISIONED = 'service.provisioned';

    public const SERVICE_SUSPENDED = 'service.suspended';

    public const SERVICE_RESUMED = 'service.resumed';

    public const SERVICE_DISCONNECTED = 'service.disconnected';

    public const SERVICE_CEASE_SCHEDULED = 'service.ceaseScheduled';

    public const DIAGNOSTIC_COMPLETED = 'diagnostic.completed';

    public const SERVICE_HEALTH_REPORT_READY = 'serviceHealth.reportReady';

    public const ONT_UPGRADE_COMPLETED = 'ontUpgrade.completed';

    public const APPOINTMENT_BOOKED = 'appointment.booked';

    public const APPOINTMENT_RESCHEDULED = 'appointment.rescheduled';

    public const APPOINTMENT_CANCELLED = 'appointment.cancelled';

    public const OUTAGE_OPENED = 'outage.opened';

    public const OUTAGE_UPDATED = 'outage.updated';

    public const OUTAGE_CLOSED = 'outage.closed';

    public const DEGRADATION_OPENED = 'degradation.opened';

    public const DEGRADATION_UPDATED = 'degradation.updated';

    public const DEGRADATION_CLOSED = 'degradation.closed';

    public const QUOTE_COMPLETED = 'quote.completed';

    public const QUOTE_ACTION_REQUIRED = 'quote.actionRequired';

    public const QUOTE_CLOSED = 'quote.closed';

    public const TRANSIT_BLACKHOLE_PLACED = 'transit.blackhole.placed';

    public const TRANSIT_BLACKHOLE_RELEASED = 'transit.blackhole.released';

    public const TRANSIT_BLACKHOLE_EXPIRED = 'transit.blackhole.expired';

    public const END_USER_SPEND_THRESHOLD = 'endUser.spendThreshold';

    public const CALLER_ID_STATUS_CHANGED = 'callerId.statusChanged';

    public const END_USER_CREDIT_NOTE_ISSUED = 'endUser.creditNote.issued';

    public const END_USER_DISPUTE_RAISED = 'endUser.dispute.raised';

    public const END_USER_DISPUTE_DECIDED = 'endUser.dispute.decided';

    public const SMS_RECEIVED = 'sms.received';

    public const SMS_DELIVERED = 'sms.delivered';

    public const SMS_FAILED = 'sms.failed';

    public const FAX_RECEIVED = 'fax.received';

    public const FAX_SENT = 'fax.sent';

    public const FAX_FAILED = 'fax.failed';

    public const ACCOUNT_SUSPENDED = 'account.suspended';

    public const ACCOUNT_REINSTATED = 'account.reinstated';

    public const END_USER_CREATED = 'endUser.created';

    public const END_USER_UPDATED = 'endUser.updated';

    public const END_USER_SUSPENDED = 'endUser.suspended';

    public const END_USER_REACTIVATED = 'endUser.reactivated';

    public const WEBHOOK_ENDPOINT_DISABLED = 'webhook.endpointDisabled';

    public const DELEGATION_USED = 'delegation.used';

    public const EXPORT_READY = 'export.ready';

    public const EXPORT_FAILED = 'export.failed';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::STAR,
            self::TICKET_CREATED,
            self::TICKET_REPLIED,
            self::TICKET_STATUS_CHANGED,
            self::TICKET_CSAT_SUBMITTED,
            self::TICKET_INFORMATION_REQUIRED,
            self::VOICE_CALL_COMPLETED,
            self::VOICE_VOICEMAIL_RECEIVED,
            self::VOICE_RECORDING_READY,
            self::TRUNK_DOWN,
            self::TRUNK_UP,
            self::VOICE_HOLD_PLACED,
            self::VOICE_HOLD_RELEASED,
            self::NUMBER_ASSIGNED,
            self::NUMBER_UNASSIGNED,
            self::NUMBER_RELEASED,
            self::NUMBER_SUSPENDED,
            self::NUMBER_REACTIVATED,
            self::IPND_RECORD_REJECTED,
            self::TRUNK_PLAN_CHANGE_SCHEDULED,
            self::TRUNK_PLAN_CHANGE_CANCELLED,
            self::TRUNK_PLAN_CHANGED,
            self::BOOKED_CHANGE_APPLIED,
            self::BOOKED_CHANGE_CANCELLED,
            self::BOOKED_CHANGE_FAILED,
            self::BOOKED_CHANGE_RESCHEDULED,
            self::INVOICE_ISSUED,
            self::INVOICE_PAID,
            self::INVOICE_VOIDED,
            self::PAYMENT_RECEIVED,
            self::PAYMENT_SUCCEEDED,
            self::PAYMENT_FAILED,
            self::PAYMENT_REVERSED,
            self::CREDIT_NOTE_ISSUED,
            self::DISPUTE_RAISED,
            self::DISPUTE_DECIDED,
            self::INVOICE_OVERDUE,
            self::PAYMENT_METHOD_EXPIRING,
            self::CREDIT_THRESHOLD_REACHED,
            self::CREDIT_CALLS_BLOCKED,
            self::CREDIT_CALLS_RESTORED,
            self::PRICE_RISE_NOTICED,
            self::USAGE_THRESHOLD_REACHED,
            self::USAGE_ALERT_TRIGGERED,
            self::DATA_REALM_APPROVED,
            self::DATA_REALM_REJECTED,
            self::DATA_RADIUS_SESSION_RETRIEVED,
            self::ORDER_STATUS_CHANGED,
            self::ORDER_DELAYED,
            self::ORDER_APPOINTMENT_REQUIRED,
            self::ORDER_NTD_FULL,
            self::PORT_ACTION_REQUIRED,
            self::SERVICE_PROVISIONED,
            self::SERVICE_SUSPENDED,
            self::SERVICE_RESUMED,
            self::SERVICE_DISCONNECTED,
            self::SERVICE_CEASE_SCHEDULED,
            self::DIAGNOSTIC_COMPLETED,
            self::SERVICE_HEALTH_REPORT_READY,
            self::ONT_UPGRADE_COMPLETED,
            self::APPOINTMENT_BOOKED,
            self::APPOINTMENT_RESCHEDULED,
            self::APPOINTMENT_CANCELLED,
            self::OUTAGE_OPENED,
            self::OUTAGE_UPDATED,
            self::OUTAGE_CLOSED,
            self::DEGRADATION_OPENED,
            self::DEGRADATION_UPDATED,
            self::DEGRADATION_CLOSED,
            self::QUOTE_COMPLETED,
            self::QUOTE_ACTION_REQUIRED,
            self::QUOTE_CLOSED,
            self::TRANSIT_BLACKHOLE_PLACED,
            self::TRANSIT_BLACKHOLE_RELEASED,
            self::TRANSIT_BLACKHOLE_EXPIRED,
            self::END_USER_SPEND_THRESHOLD,
            self::CALLER_ID_STATUS_CHANGED,
            self::END_USER_CREDIT_NOTE_ISSUED,
            self::END_USER_DISPUTE_RAISED,
            self::END_USER_DISPUTE_DECIDED,
            self::SMS_RECEIVED,
            self::SMS_DELIVERED,
            self::SMS_FAILED,
            self::FAX_RECEIVED,
            self::FAX_SENT,
            self::FAX_FAILED,
            self::ACCOUNT_SUSPENDED,
            self::ACCOUNT_REINSTATED,
            self::END_USER_CREATED,
            self::END_USER_UPDATED,
            self::END_USER_SUSPENDED,
            self::END_USER_REACTIVATED,
            self::WEBHOOK_ENDPOINT_DISABLED,
            self::DELEGATION_USED,
            self::EXPORT_READY,
            self::EXPORT_FAILED
        ];
    }
}


