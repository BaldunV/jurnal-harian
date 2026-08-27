import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/providers/core_providers.dart';
import 'package:jurnal_siswa/providers/session_provider.dart';

import '../helpers/memory_token_storage.dart';

void main() {
  test('restores and clears a stored session', () async {
    final storage = MemoryTokenStorage('existing-token');
    final container = ProviderContainer(
      overrides: [tokenStorageProvider.overrideWithValue(storage)],
    );
    addTearDown(container.dispose);

    expect(
      await container.read(sessionControllerProvider.future),
      SessionStatus.signedIn,
    );

    await container.read(sessionControllerProvider.notifier).signOut();

    expect(storage.token, isNull);
    expect(
      container.read(sessionControllerProvider),
      const AsyncData<SessionStatus>(SessionStatus.signedOut),
    );
  });

  test('stores a token only when establishing a session', () async {
    final storage = MemoryTokenStorage();
    final container = ProviderContainer(
      overrides: [tokenStorageProvider.overrideWithValue(storage)],
    );
    addTearDown(container.dispose);
    await container.read(sessionControllerProvider.future);

    await container
        .read(sessionControllerProvider.notifier)
        .establishSession('new-token');

    expect(storage.token, 'new-token');
    expect(
      container.read(sessionControllerProvider),
      const AsyncData<SessionStatus>(SessionStatus.signedIn),
    );
  });
}
