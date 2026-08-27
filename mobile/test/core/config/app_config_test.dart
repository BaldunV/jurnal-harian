import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/core/config/app_config.dart';

void main() {
  test('uses the Android emulator API URL by default', () {
    final config = AppConfig.fromEnvironment();

    expect(config.apiBaseUrl, 'http://10.0.2.2:8000/api/');
    expect(config.environmentLabel, '10.0.2.2:8000');
  });

  test('normalizes an injected API URL', () {
    final config = AppConfig(apiBaseUrl: ' https://api.example.sch.id/api/// ');

    expect(config.apiBaseUrl, 'https://api.example.sch.id/api/');
  });

  test('rejects a URL without an HTTP scheme', () {
    expect(
      () => AppConfig(apiBaseUrl: 'api.example.sch.id/api'),
      throwsArgumentError,
    );
  });
}
