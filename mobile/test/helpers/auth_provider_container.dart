import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:riverpod/misc.dart' show Override;
import 'package:jurnal_siswa/providers/api_client_provider.dart';
import 'package:jurnal_siswa/providers/auth_invalidation_provider.dart';
import 'package:jurnal_siswa/providers/core_providers.dart';

import 'fake_http_client_adapter.dart';
import 'memory_token_storage.dart';

ProviderContainer createAuthProviderContainer({
  required FakeHttpClientAdapter adapter,
  required MemoryTokenStorage tokenStorage,
  List<Override> additionalOverrides = const <Override>[],
}) {
  return ProviderContainer(
    overrides: [
      tokenStorageProvider.overrideWithValue(tokenStorage),
      apiClientProvider.overrideWith((ref) {
        final client = createTestApiClient(
          adapter: adapter,
          tokenStorage: tokenStorage,
          onUnauthorized: () async {
            ref.read(authInvalidationProvider.notifier).markUnauthorized();
          },
        );
        ref.onDispose(client.close);
        return client;
      }),
      ...additionalOverrides,
    ],
  );
}
