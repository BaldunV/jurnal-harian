import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/core/api/api_client.dart';
import 'package:jurnal_siswa/core/api/api_exception.dart';
import 'package:jurnal_siswa/services/auth_service.dart';

import '../helpers/auth_test_data.dart';
import '../helpers/fake_http_client_adapter.dart';
import '../helpers/memory_token_storage.dart';

void main() {
  late FakeHttpClientAdapter adapter;
  late ApiClient client;
  late AuthService service;

  setUp(() {
    adapter = FakeHttpClientAdapter();
    client = createTestApiClient(
      adapter: adapter,
      tokenStorage: MemoryTokenStorage(),
    );
    service = AuthService(client);
  });

  tearDown(() => client.close());

  test('sends the exact valid login request', () async {
    adapter.enqueueJson(tokenEnvelope());

    await service.login(nis: ' 20260012 ', password: 'secret123');

    final request = adapter.requests.single;
    expect(request.method, 'POST');
    expect(request.path, 'auth/login');
    expect(request.headers['Accept'], 'application/json');
    expect(request.data, <String, Object?>{
      'nis': '20260012',
      'password': 'secret123',
      'device_name': 'Jurnal SMK BPPI Android',
    });
  });

  test('parses the bearer token and student from the login response', () async {
    adapter.enqueueJson(tokenEnvelope());

    final result = await service.login(nis: '20260012', password: 'secret123');

    expect(result.token, '12|plain-token');
    expect(result.issuedUser.nis, '20260012');
  });

  test('maps invalid credentials without exposing a raw response', () async {
    adapter.enqueueJson(
      errorEnvelope(
        message: 'NIS atau password yang dimasukkan salah.',
        code: 'invalid_credentials',
      ),
      statusCode: 401,
    );

    await expectLater(
      service.login(nis: '20260012', password: 'wrong-password'),
      throwsA(
        isA<ApiException>()
            .having(
              (error) => error.kind,
              'kind',
              ApiFailureKind.unauthenticated,
            )
            .having((error) => error.code, 'code', 'invalid_credentials'),
      ),
    );
  });

  test('maps a malformed login response as a server error', () async {
    adapter.enqueueJson(successEnvelope());

    await expectLater(
      service.login(nis: '20260012', password: 'secret123'),
      throwsA(
        isA<ApiException>().having(
          (error) => error.kind,
          'kind',
          ApiFailureKind.server,
        ),
      ),
    );
  });

  test('maps API rate limiting', () async {
    adapter.enqueueJson(
      errorEnvelope(
        message: 'Terlalu banyak permintaan. Coba lagi beberapa saat.',
        code: 'rate_limited',
      ),
      statusCode: 429,
    );

    await expectLater(
      service.login(nis: '20260012', password: 'secret123'),
      throwsA(
        isA<ApiException>().having(
          (error) => error.kind,
          'kind',
          ApiFailureKind.rateLimited,
        ),
      ),
    );
  });
}
