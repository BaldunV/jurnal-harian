import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/theme/app_theme.dart';
import 'providers/session_provider.dart';
import 'screens/foundation/foundation_screen.dart';

class JurnalApp extends ConsumerWidget {
  const JurnalApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp(
      title: 'Jurnal SMK BPPI',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      darkTheme: AppTheme.dark,
      themeMode: ThemeMode.system,
      home: const _AppBootstrap(),
    );
  }
}

class _AppBootstrap extends ConsumerWidget {
  const _AppBootstrap();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(sessionControllerProvider);

    return session.when(
      data: (status) => FoundationScreen(
        state: status == SessionStatus.signedIn
            ? FoundationState.sessionReady
            : FoundationState.signedOut,
      ),
      error: (error, stackTrace) => FoundationScreen(
        state: FoundationState.storageError,
        onRetry: () => ref.invalidate(sessionControllerProvider),
      ),
      loading: () => const FoundationScreen(state: FoundationState.loading),
    );
  }
}
