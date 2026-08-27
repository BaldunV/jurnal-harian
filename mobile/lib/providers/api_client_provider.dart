import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_client.dart';
import 'auth_invalidation_provider.dart';
import 'core_providers.dart';

final apiClientProvider = Provider<ApiClient>((ref) {
  final client = ApiClient(
    config: ref.watch(appConfigProvider),
    tokenStorage: ref.watch(tokenStorageProvider),
    onUnauthorized: () =>
        ref.read(authInvalidationProvider.notifier).markUnauthorized(),
  );
  ref.onDispose(client.close);

  return client;
});
