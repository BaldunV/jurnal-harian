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
    adapter.enqueueJson(otpChallengeEnvelope(), statusCode: 202);

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

  test('parses the OTP-required challenge response', () async {
    adapter.enqueueJson(otpChallengeEnvelope(), statusCode: 202);

    final challenge = await service.login(
      nis: '20260012',
      password: 'secret123',
    );

    expect(challenge.challengeId, testChallengeId);
    expect(challenge.channel, 'whatsapp');
    expect(challenge.channelLabel, 'WhatsApp');
    expect(challenge.maskedPhone, '081 **** 7890');
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

  test('parses OTP success and sends no invented verify fields', () async {
    adapter.enqueueJson(tokenEnvelope());

    final result = await service.verifyOtp(
      challengeId: testChallengeId,
      code: '123456',
    );

    expect(result.token, '12|plain-token');
    expect(result.issuedUser.nis, '20260012');
    expect(adapter.requests.single.data, <String, Object?>{
      'challenge_id': testChallengeId,
      'code': '123456',
    });
  });

  test('maps an invalid OTP with field feedback', () async {
    adapter.enqueueJson(
      errorEnvelope(
        message: 'Kode OTP salah. Sisa percobaan: 4.',
        code: 'otp_invalid',
        errors: const <String, Object?>{
          'code': <String>['Kode OTP tidak valid.'],
        },
      ),
      statusCode: 422,
    );

    await expectLater(
      service.verifyOtp(challengeId: testChallengeId, code: '000000'),
      throwsA(
        isA<ApiException>()
            .having((error) => error.kind, 'kind', ApiFailureKind.validation)
            .having((error) => error.code, 'code', 'otp_invalid')
            .having((error) => error.errors['code'], 'code errors', <String>[
              'Kode OTP tidak valid.',
            ]),
      ),
    );
  });

  test('maps an expired OTP as gone', () async {
    adapter.enqueueJson(
      errorEnvelope(
        message: 'Kode OTP sudah kedaluwarsa. Silakan login kembali.',
        code: 'otp_expired',
      ),
      statusCode: 410,
    );

    await expectLater(
      service.verifyOtp(challengeId: testChallengeId, code: '123456'),
      throwsA(
        isA<ApiException>()
            .having((error) => error.kind, 'kind', ApiFailureKind.gone)
            .having((error) => error.code, 'code', 'otp_expired'),
      ),
    );
  });

  test('resends with the same challenge and applies new timestamps', () async {
    adapter.enqueueJson(
      otpChallengeEnvelope(resendAfterSeconds: 0),
      statusCode: 202,
    );
    final challenge = await service.login(
      nis: '20260012',
      password: 'secret123',
    );
    final now = DateTime.now().toUtc().millisecondsSinceEpoch ~/ 1000;
    adapter.enqueueJson(<String, Object?>{
      'success': true,
      'message': 'Kode OTP baru telah dikirim.',
      'data': <String, Object?>{
        'channel': 'whatsapp',
        'sent_at': now,
        'resend_at': now + 60,
        'expires_at': now + 240,
      },
    });

    final updated = await service.resendOtp(challenge);

    expect(updated.challengeId, challenge.challengeId);
    expect(updated.resendAt.isAfter(challenge.resendAt), isTrue);
    expect(adapter.requests.last.data, <String, Object?>{
      'challenge_id': testChallengeId,
    });
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
