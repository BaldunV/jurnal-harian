import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/theme/app_theme.dart';
import 'providers/session_provider.dart';
import 'screens/auth/auth_flow_screen.dart';
import 'screens/auth/session_recovery_screen.dart';
import 'screens/foundation/foundation_screen.dart';
import 'screens/home/app_shell.dart';

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
      data: (state) => switch (state) {
        SignedOutSession(:final notice) => AuthFlowScreen(
          sessionNotice: notice,
        ),
        AuthenticatedSession(:final student) => AppShell(student: student),
      },
      error: (error, stackTrace) => SessionRecoveryScreen(error: error),
      loading: () => const FoundationScreen(state: FoundationState.loading),
    );
  }
}
