import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_client.dart';
import 'core_providers.dart';
import 'session_provider.dart';

final apiClientProvider = Provider<ApiClient>((ref) {
  final client = ApiClient(
    config: ref.watch(appConfigProvider),
    tokenStorage: ref.watch(tokenStorageProvider),
    onUnauthorized: () =>
        ref.read(sessionControllerProvider.notifier).invalidateSession(),
  );
  ref.onDispose(client.close);

  return client;
});
