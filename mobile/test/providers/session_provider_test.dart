import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/core/api/api_exception.dart';
import 'package:jurnal_siswa/providers/session_provider.dart';

import '../helpers/auth_provider_container.dart';
import '../helpers/auth_test_data.dart';
import '../helpers/fake_http_client_adapter.dart';
import '../helpers/memory_token_storage.dart';

void main() {
  test('starts signed out when no secure token exists', () async {
    final adapter = FakeHttpClientAdapter();
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: MemoryTokenStorage(),
    );
    addTearDown(container.dispose);

    final session = await container.read(sessionControllerProvider.future);

    expect(session, isA<SignedOutSession>());
    expect(adapter.requests, isEmpty);
  });

  test(
    'restores a student by validating the stored token with auth me',
    () async {
      final adapter = FakeHttpClientAdapter()..enqueueJson(meEnvelope());
      final storage = MemoryTokenStorage('stored-token');
      final container = createAuthProviderContainer(
        adapter: adapter,
        tokenStorage: storage,
      );
      addTearDown(container.dispose);

      final session = await container.read(sessionControllerProvider.future);

      expect(session, isA<AuthenticatedSession>());
      expect((session as AuthenticatedSession).student.name, 'Nadia Putri');
      expect(adapter.requests.single.path, 'auth/me');
      expect(
        adapter.requests.single.headers['Authorization'],
        'Bearer stored-token',
      );
    },
  );

  test('keeps the token after a temporary restoration failure', () async {
    final adapter = FakeHttpClientAdapter()..enqueueConnectionError();
    final storage = MemoryTokenStorage('stored-token');
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: storage,
    );
    addTearDown(container.dispose);

    await expectLater(
      container.read(sessionControllerProvider.future),
      throwsA(
        isA<ApiException>().having(
          (error) => error.kind,
          'kind',
          ApiFailureKind.offline,
        ),
      ),
    );

    expect(storage.token, 'stored-token');
    expect(
      container.read(sessionControllerProvider),
      isA<AsyncError<SessionState>>(),
    );

    adapter.enqueueJson(meEnvelope());
    await container.read(sessionControllerProvider.notifier).retryRestoration();

    expect(
      container.read(sessionControllerProvider).value,
      isA<AuthenticatedSession>(),
    );
  });

  test('clears an invalid token after auth me returns 401', () async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(
        errorEnvelope(
          message: 'Token tidak valid atau sudah kedaluwarsa.',
          code: 'unauthenticated',
        ),
        statusCode: 401,
      );
    final storage = MemoryTokenStorage('invalid-token');
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: storage,
    );
    addTearDown(container.dispose);

    final session = await container.read(sessionControllerProvider.future);

    expect(session, isA<SignedOutSession>());
    expect(storage.token, isNull);
    expect(storage.clearCount, 1);
  });

  test('persists a verified token before validating auth me', () async {
    final adapter = FakeHttpClientAdapter()..enqueueJson(meEnvelope());
    final storage = MemoryTokenStorage();
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: storage,
    );
    addTearDown(container.dispose);
    await container.read(sessionControllerProvider.future);

    await container
        .read(sessionControllerProvider.notifier)
        .establishSession('verified-token');

    expect(storage.token, 'verified-token');
    expect(
      container.read(sessionControllerProvider).value,
      isA<AuthenticatedSession>(),
    );
    expect(
      adapter.requests.single.headers['Authorization'],
      'Bearer verified-token',
    );
  });

  test('logs out through Laravel and clears the local token', () async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(meEnvelope())
      ..enqueueJson(successEnvelope(message: 'Logout berhasil.'));
    final storage = MemoryTokenStorage('active-token');
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: storage,
    );
    addTearDown(container.dispose);
    await container.read(sessionControllerProvider.future);

    await container.read(sessionControllerProvider.notifier).logout();

    final session = container.read(sessionControllerProvider).value;
    expect(session, isA<SignedOutSession>());
    expect((session as SignedOutSession).notice, isNull);
    expect(storage.token, isNull);
    expect(adapter.requests.last.path, 'auth/logout');
    expect(
      adapter.requests.last.headers['Authorization'],
      'Bearer active-token',
    );
  });

  test('allows local logout when server revocation is unreachable', () async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(meEnvelope())
      ..enqueueConnectionError();
    final storage = MemoryTokenStorage('active-token');
    final container = createAuthProviderContainer(
      adapter: adapter,
      tokenStorage: storage,
    );
    addTearDown(container.dispose);
    await container.read(sessionControllerProvider.future);

    await container.read(sessionControllerProvider.notifier).logout();

    final session = container.read(sessionControllerProvider).value;
    expect(session, isA<SignedOutSession>());
    expect((session as SignedOutSession).notice, contains('token di server'));
    expect(storage.token, isNull);
  });
}
