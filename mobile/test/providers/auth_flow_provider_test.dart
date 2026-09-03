import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/providers/auth_flow_provider.dart';
import 'package:jurnal_siswa/providers/session_provider.dart';

import '../helpers/auth_provider_container.dart';
import '../helpers/auth_test_data.dart';
import '../helpers/fake_http_client_adapter.dart';
import '../helpers/memory_token_storage.dart';

void main() {
  test(
    'login stores the token, validates auth me, and authenticates directly',
    () async {
      final adapter = FakeHttpClientAdapter()
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

      expect(storage.token, '12|plain-token');
      expect(container.read(authFlowControllerProvider).isSubmitting, isFalse);
      expect(container.read(authFlowControllerProvider).error, isNull);
      expect(
        container.read(sessionControllerProvider).value,
        isA<AuthenticatedSession>(),
      );
      expect(adapter.requests.map((request) => request.path), <String>[
        'auth/login',
        'auth/me',
      ]);
    },
  );

  test('invalid credentials stay signed out with server feedback', () async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(
        errorEnvelope(
          message: 'NIS atau password yang dimasukkan salah.',
          code: 'invalid_credentials',
        ),
        statusCode: 401,
      );
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: MemoryTokenStorage(),
    );
    addTearDown(container.dispose);
    await container.read(sessionControllerProvider.future);

    final controller = container.read(authFlowControllerProvider.notifier);
    expect(await controller.login(nis: '20260012', password: 'wrong'), isFalse);

    final state = container.read(authFlowControllerProvider);
    expect(state.error?.code, 'invalid_credentials');
    expect(
      container.read(sessionControllerProvider).value,
      isA<SignedOutSession>(),
    );
  });

  test(
    'a failed /auth/me after login clears token and does not authenticate',
    () async {
      final adapter = FakeHttpClientAdapter()
        ..enqueueJson(tokenEnvelope())
        ..enqueueJson(
          errorEnvelope(
            message: 'Sesi sudah berakhir.',
            code: 'unauthenticated',
          ),
          statusCode: 401,
        );

      final storage = MemoryTokenStorage();

      final container = createAuthProviderContainer(
        adapter: adapter,
        tokenStorage: storage,
      );

      addTearDown(container.dispose);

      await container.read(sessionControllerProvider.future);

      final controller = container.read(authFlowControllerProvider.notifier);

      final ok = await controller.login(nis: '20260012', password: 'secret123');

      expect(ok, isFalse);

      expect(container.read(authFlowControllerProvider).isSubmitting, isFalse);

      expect(storage.token, isNull);

      expect(
        container.read(sessionControllerProvider).value,
        isA<SignedOutSession>(),
      );
    },
  );
}
