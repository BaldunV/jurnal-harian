import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/models/auth_models.dart';

import '../helpers/auth_test_data.dart';

void main() {
  test('parses a valid bearer token result', () {
    final result = AuthTokenResult.fromJson(
      tokenEnvelope()['data']! as Map<String, Object?>,
    );

    expect(result.token, '12|plain-token');
    expect(result.issuedUser.nis, '20260012');
    expect(result.expiresAt, DateTime.parse('2026-09-27T10:00:00+07:00'));
  });

  test('rejects a response without a bearer token type', () {
    final data = tokenEnvelope()['data']! as Map<String, Object?>;
    data['token_type'] = 'Basic';

    expect(() => AuthTokenResult.fromJson(data), throwsFormatException);
  });
}
