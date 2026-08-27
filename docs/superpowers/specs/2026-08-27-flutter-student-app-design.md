# Flutter Student App Design

Date: 2026-08-27
Status: Approved for implementation

## Goal

Add a production-oriented Flutter client for students without replacing the
existing Laravel web application. Laravel remains the source of truth for
authentication, authorization, OTP, journals, statistics, profiles, media,
and persistence.

```text
Flutter Student App
        |
        v
Laravel REST API
        |
        v
      MySQL

Laravel Web
|- Student (existing)
|- Teacher (existing)
`- Admin (existing)
```

## Constraints

- Keep all existing web routes and role behavior working.
- Add the Flutter project under `mobile/`.
- Do not accept a user ID, role, or journal date from the mobile client.
- Use Sanctum personal access tokens, not browser session cookies, for Flutter.
- Issue no token until a required OTP challenge succeeds.
- Students without a configured phone cannot use mobile login. The API returns
  `otp_setup_required`; an administrator must configure the account first.
- Use `Asia/Jakarta` as the journal business timezone.
- Keep the existing `journals` field names and unique `(user_id, date)` index.
- Avoid broad refactors that are unrelated to mobile correctness or security.

## Chosen Approach

Use targeted shared-domain extraction. New API controllers delegate journal,
statistics, OTP, and media behavior to focused services. Existing web and
Livewire mutation paths are moved onto the same journal/media services where
doing so prevents duplicated business rules. Views and route contracts remain
intact.

Rejected alternatives:

- Thin API controllers writing models directly would duplicate validation and
  preserve the existing race conditions.
- A complete domain rewrite would increase regression risk and conflict with
  the requirement to preserve the Laravel web application.

## Laravel Structure

The implementation will add or extend these boundaries:

```text
app/Http/Controllers/Api/
|- AuthController.php
|- JournalController.php
|- StatisticsController.php
`- ProfileController.php

app/Http/Requests/Api/
app/Http/Resources/
app/Services/
|- JournalService.php
|- JournalStatisticsService.php
`- MediaService.php

routes/api.php
```

API Resources whitelist response fields. Form Requests own request validation.
Services own reusable mutation and calculation rules. Controllers translate
HTTP requests and responses only.

## JSON Contract

Successful responses use:

```json
{
  "success": true,
  "message": "Jurnal berhasil disimpan.",
  "data": {}
}
```

Errors use:

```json
{
  "success": false,
  "message": "Data tidak valid.",
  "errors": {}
}
```

Domain errors may include a stable `code`, such as `otp_required`,
`otp_setup_required`, `journal_exists`, or `journal_locked`. HTTP status codes
remain authoritative. Pagination metadata is returned beside `data` without
serializing Eloquent pagination internals.

## Authentication And OTP

Sanctum is installed and `User` receives `HasApiTokens`. The mobile API does
not enable SPA cookie authentication.

### Login

`POST /api/auth/login` accepts `nis`, `password`, and `device_name`.

1. Validate input and apply IP plus normalized-NIS throttling.
2. Verify credentials using the existing user provider.
3. Require the user role to be exactly `siswa`.
4. Return `otp_setup_required` without a token when no phone is configured.
5. Create an OTP challenge with purpose `mobile-login`.
6. Return HTTP 202 with an opaque `challenge_id`, masked phone, channel,
   expiration, resend time, and `otp_required=true`.

The endpoint never returns the OTP, OTP hash, user ID, phone number, password,
or remember token.

### OTP Verification

`POST /api/auth/otp/verify` accepts `challenge_id`, `code`, and `device_name`.

1. Lock the challenge while checking and consuming it.
2. Enforce expiration and the cumulative attempt limit.
3. Recheck that the user still exists and has role `siswa`.
4. Consume a valid challenge exactly once.
5. Create a Sanctum token with a bounded expiration and student ability.
6. Return the plaintext token once with the whitelisted user resource.

### OTP Resend

`POST /api/auth/otp/resend` accepts `challenge_id`. Resend uses challenge, IP,
and account throttles. It preserves cumulative verification attempts and the
original expiry window.

### Authenticated Session

- `GET /api/auth/me` returns the authenticated student resource.
- `POST /api/auth/logout` deletes only the current access token.
- All private routes use `auth:sanctum` and `role:siswa`.
- Browser trusted-device cookies are not read or written by the mobile flow.
- Existing web session login and trusted-device behavior remain available.

## Journal Rules

The authoritative date is always:

```php
now('Asia/Jakarta')->toDateString()
```

Mobile create and update requests do not contain a date. Server-owned fields
are rejected or ignored, including `id`, `user_id`, `date`, `beribadah`, photo
paths, progress fields, submission state, and timestamps.

Endpoints:

```text
GET  /api/me/journal/today
POST /api/me/journal
PUT  /api/me/journal
POST /api/me/journal/submit
GET  /api/me/journals
GET  /api/me/journals/{journal}
POST /api/me/journals/{journal}/photos/{type}
GET  /api/me/journals/{journal}/photos/{type}
```

`POST /api/me/journal` creates today's draft and returns 409 when it already
exists. `PUT /api/me/journal` updates today's draft and returns 404 when none
exists. Submit saves validated data and sets `is_submitted` in one transaction.
A submitted journal is immutable and returns `journal_locked` on mutation.

Every journal lookup is scoped through `$request->user()->journals()`. Route
model IDs cannot expose another student's journal.

The shared journal service:

- validates booleans, `H:i` times, notes up to database limits, and the worship
  detail shape for the user's worship type;
- derives `beribadah` on the server;
- normalizes wake and sleep values before progress calculation;
- locks existing rows during update or submit;
- preserves the database unique index as the final duplicate guard;
- recalculates `completed_count` and `is_fully_completed` before every save.

Existing database names remain unchanged. `tidur_note` continues to hold the
existing sleep-time value because introducing a renamed field is unnecessary
for the first mobile release.

## Media

Exercise and meal evidence are the only existing journal-photo types.
New files use private storage and paths containing the authenticated user,
journal, type, and a random identifier. Upload validation checks decoded image
content, an explicit JPEG/PNG/WebP MIME allowlist, extension, and a 5 MB limit.

Authorized media endpoints verify journal ownership before streaming bytes.
Existing persisted public paths remain readable through an authorized fallback;
new uploads never use the old shared date-only paths. Replacement stores the
new file before committing the new path, then removes the old file after a
successful update.

A nullable profile-photo path is added to `users`. Profile photos follow the
same private storage and authorization rules.

## Statistics

`GET /api/me/statistics?period=week|month` performs one bounded journal query.
It returns:

```text
today_progress
today_total
percentage
current_streak
completed_days
recorded_days
period_days
habit_statistics
```

The overall percentage is completed habit slots divided by recorded-day habit
slots, matching the existing student statistic semantics while using qualified
completion rules. An empty period returns zero rather than a fake denominator.
Current streak is calculated from one ordered date collection, not one query per
day. Habit statistics distinguish the seven server-qualified habits.

## Profile

Endpoints:

```text
GET  /api/me/profile
PUT  /api/me/profile
POST /api/me/profile/photo
GET  /api/me/profile/photo
POST /api/me/change-password
```

Students may update only `name` and `worship_type`. NIS, class, role, phone, OTP
channel, and administrative fields are read-only. Password changes require the
current password and the existing strong password policy. A successful password
change revokes trusted devices and all Sanctum tokens; Flutter clears local
credentials and returns to login.

## Web Security Compatibility

The service worker will cache only versioned static assets and the generic
offline page. It will not cache authenticated HTML, OTP pages, Livewire traffic,
or personal API responses. Activation removes legacy private caches.

Unsafe client rendering of journal notes will use text-safe DOM output rather
than inserting untrusted values into `innerHTML`. These targeted fixes are
included because the mobile work reuses the same private data and media.

## Flutter Architecture

Only the Android Flutter platform is required for this phase. Code is organized
under `mobile/lib`:

```text
core/api
core/config
core/storage
core/theme
models
services
providers
screens/auth
screens/home
screens/journal
screens/statistics
screens/profile
widgets
```

Dependencies are limited to stable releases of:

```text
dio
flutter_riverpod
flutter_secure_storage
image_picker
intl
```

Dio applies `Accept: application/json` and the Bearer token through an
interceptor. A 401 clears secure storage and invalidates authentication state.
Central error mapping differentiates 401, 403, 404, 409, 422, 429, server,
timeout, and offline failures.

Riverpod notifiers separate authenticated state, journal state, statistics,
and profile state from widgets. The main shell uses an `IndexedStack` so Home,
Jurnal, Statistik, and Profil retain state while switching tabs.

No request is marked successful until Laravel confirms it. The first release
does not implement offline journal synchronization.

## Flutter Visual Direction

The interface is an operational student tool: modern, calm, and educational,
not childish. The palette uses Indonesia red, warm white, dark navy, and a
small gold accent. The signature dashboard element is a seven-segment progress
ribbon that maps directly to the seven habits. Surrounding screens remain
quiet and scan-friendly.

Forms show inline validation and request errors. Buttons disable during
submission. OTP uses six visible digit fields, expiry countdown, resend state,
remaining-attempt feedback, and no production logging. Responsive layouts
support common Android phone widths and accessibility text scaling.

## Android Networking

The API base URL is configured once with `API_BASE_URL` and defaults to
`http://10.0.2.2:8000/api`. Android internet permission is present. Local HTTP
cleartext is enabled only by the debug manifest/network security configuration;
release builds do not permit insecure HTTP.

## Testing And Verification

Laravel tests cover successful and failed login, OTP setup requirement, valid,
invalid, expired and exhausted OTP, resend and rate limits, no token before OTP,
me, current-token logout, role denial, journal ownership, create, duplicate,
update, lock, server date, statistics, profile whitelisting, password revocation,
and media validation/authorization.

Existing web tests remain and must pass. Verification commands include:

```text
vendor/bin/pint
php artisan test
php artisan route:list
```

Flutter tests cover model parsing, API error parsing, login validation, auth
state, token clearing, and journal parsing. Verification commands include:

```text
flutter pub get
dart format .
flutter analyze
flutter test
flutter build apk --debug
```

The current machine has no Flutter SDK on PATH. The implementation will attempt
to install a stable SDK in an approved temporary location so analysis, tests,
and an actual APK build can still be performed.

## Delivery

Changes are grouped into logical commits for API infrastructure, journal and
profile APIs, Flutter foundation/features, tests, and documentation. No `.env`,
credential, API key, generated secure token, Flutter build output, or dependency
directory is committed.
