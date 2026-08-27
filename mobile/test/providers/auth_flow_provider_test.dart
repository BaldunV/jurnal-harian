import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/providers/auth_flow_provider.dart';
import 'package:jurnal_siswa/providers/session_provider.dart';

import '../helpers/auth_provider_container.dart';
import '../helpers/auth_test_data.dart';
import '../helpers/fake_http_client_adapter.dart';
import '../helpers/memory_token_storage.dart';

void main() {
  test(
    'OTP success stores the token, validates auth me, and authenticates',
    () async {
      final adapter = FakeHttpClientAdapter()
        ..enqueueJson(otpChallengeEnvelope(), statusCode: 202)
        ..enqueueJson(tokenEnvelope())
        ..enqueueJson(meEnvelope());
      final storage = MemoryTokenStorage();
      final container = createAuthProviderContainer(
        adapter: adapter,
        tokenStorage: storage,
      );
      addTearDown(container.dispose);
      await container.read(sessionControllerProvider.future);

      final controller = container.read(authFlowControllerProvider.notifier);
      expect(
        await controller.login(nis: '20260012', password: 'secret123'),
        isTrue,
      );
      expect(await controller.verifyOtp('123456'), isTrue);

      expect(storage.token, '12|plain-token');
      expect(container.read(authFlowControllerProvider).step, AuthStep.login);
      expect(
        container.read(sessionControllerProvider).value,
        isA<AuthenticatedSession>(),
      );
      expect(adapter.requests.map((request) => request.path), <String>[
        'auth/login',
        'auth/otp/verify',
        'auth/me',
      ]);
    },
  );

  test('invalid OTP stays on the challenge with server feedback', () async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(otpChallengeEnvelope(), statusCode: 202)
      ..enqueueJson(
        errorEnvelope(
          message: 'Kode OTP salah. Sisa percobaan: 4.',
          code: 'otp_invalid',
          errors: const <String, Object?>{
            'code': <String>['Kode OTP tidak valid.'],
          },
        ),
        statusCode: 422,
      );
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: MemoryTokenStorage(),
    );
    addTearDown(container.dispose);
    await container.read(sessionControllerProvider.future);

    final controller = container.read(authFlowControllerProvider.notifier);
    await controller.login(nis: '20260012', password: 'secret123');
    expect(await controller.verifyOtp('000000'), isFalse);

    final state = container.read(authFlowControllerProvider);
    expect(state.step, AuthStep.otp);
    expect(state.error?.code, 'otp_invalid');
    expect(state.challengeEnded, isFalse);
  });

  test(
    'attempt exhaustion ends the challenge and requires login again',
    () async {
      final adapter = FakeHttpClientAdapter()
        ..enqueueJson(otpChallengeEnvelope(), statusCode: 202)
        ..enqueueJson(
          errorEnvelope(
            message: 'Percobaan OTP habis. Silakan login kembali.',
            code: 'otp_attempts_exhausted',
          ),
          statusCode: 429,
        );
      final container = createAuthProviderContainer(
        adapter: adapter,
        tokenStorage: MemoryTokenStorage(),
      );
      addTearDown(container.dispose);
      await container.read(sessionControllerProvider.future);

      final controller = container.read(authFlowControllerProvider.notifier);
      await controller.login(nis: '20260012', password: 'secret123');
      await controller.verifyOtp('000000');

      expect(container.read(authFlowControllerProvider).challengeEnded, isTrue);
    },
  );

  test('resend updates the existing challenge after cooldown', () async {
    final now = DateTime.now().toUtc().millisecondsSinceEpoch ~/ 1000;
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(
        otpChallengeEnvelope(resendAfterSeconds: -1),
        statusCode: 202,
      )
      ..enqueueJson(<String, Object?>{
        'success': true,
        'message': 'Kode OTP baru telah dikirim.',
        'data': <String, Object?>{
          'channel': 'whatsapp',
          'sent_at': now,
          'resend_at': now + 60,
          'expires_at': now + 240,
        },
      });
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: MemoryTokenStorage(),
    );
    addTearDown(container.dispose);
    await container.read(sessionControllerProvider.future);

    final controller = container.read(authFlowControllerProvider.notifier);
    await controller.login(nis: '20260012', password: 'secret123');
    expect(await controller.resendOtp(), isTrue);

    final state = container.read(authFlowControllerProvider);
    expect(state.challenge?.challengeId, testChallengeId);
    expect(state.feedback, 'Kode OTP baru telah dikirim.');
  });
}
