# Product

<!-- impeccable:product-schema 1 -->

## Platform

android

## Users

SMK BPPI students use the app on their own Android phones to record and review their daily seven-habit journal. Mobile access is limited to student accounts configured by the school.

## Product Purpose

Jurnal SMK BPPI helps students record the seven daily habits, submit an immutable daily journal, review their history and qualified statistics, and maintain their profile. Success means the mobile experience stays consistent with the school's Laravel source of truth while making the daily task fast and clear on a phone.

## Positioning

The app is a school-account journal whose identity, authoritative Jakarta date, qualified progress, immutable submission, and evidence media are enforced by the same Laravel system used by staff and the existing web application.

## Operating Context

Students sign in with NIS and password, then use the app throughout the day to update one Jakarta-owned journal. Exercise and meal evidence can be captured from the phone. The first release requires a network connection and does not synchronize journals offline.

## Capabilities and Constraints

- Laravel remains authoritative for authentication, authorization, journals, statistics, profiles, media, and persistence.
- Flutter uses expiring Sanctum bearer tokens stored in platform-secure storage; browser sessions never authenticate the mobile API.
- The client never supplies a user ID, role, or journal date and never treats a request as successful before Laravel confirms it.
- Android is the only shipped platform in this phase.
- The API base URL is configured once through `API_BASE_URL`; local emulator development defaults to `http://10.0.2.2:8000/api`.
- Cleartext HTTP is allowed only in Android debug builds. Release builds require HTTPS.

## Brand Commitments

The product name is Jurnal SMK BPPI, with "Jurnal 7 Kebiasaan" as its student-facing descriptor. Indonesian is the interface language. The existing school mark at `public/images/logo.png` is the identity asset. The approved mobile character is modern, calm, educational, and never childish.

## Evidence on Hand

- Approved mobile architecture and behavior: `docs/superpowers/specs/2026-08-27-flutter-student-app-design.md`
- School logo: `public/images/logo.png`
- Existing PWA identity metadata and icons: `public/manifest.json` and `public/icons/`
- Verified API behavior: `tests/Feature/Api/`

## Product Principles

- Keep Laravel authoritative and expose no client-owned identity or date decisions.
- Make the daily seven-habit task legible before adding decoration.
- Treat authentication, private media, and immutable submissions as visible product guarantees.
- Preserve the existing web application while the Android client evolves independently.
- Explain failures with a concrete recovery action.

## Accessibility & Inclusion

Support Android text scaling, edge-to-edge safe areas, 48 dp touch targets, semantic labels, visible focus, dark theme, and reduced-motion preferences. Copy remains direct Indonesian suitable for secondary-school students.
