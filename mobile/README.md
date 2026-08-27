# Jurnal SMK BPPI

Android Flutter client for the SMK BPPI seven-habit student journal. Laravel is
the source of truth for authentication, dates, journals, media, and profile
data.

## Requirements

- Flutter 3.47.2 or another compatible stable release
- Android SDK 37 for compilation
- A running Laravel API

## API Configuration

The emulator development default is `http://10.0.2.2:8000/api`. Override it at
build or run time with a Dart define:

```shell
flutter run --dart-define=API_BASE_URL=https://example.sch.id/api
```

Debug builds allow cleartext HTTP for local development. Release builds use the
normal Android policy and require HTTPS.

## Verification

```shell
flutter pub get
dart format .
flutter analyze
flutter test
flutter build apk --debug
```
