import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/auth_flow_provider.dart';
import 'login_screen.dart';
import 'otp_screen.dart';

class AuthFlowScreen extends ConsumerWidget {
  const AuthFlowScreen({this.sessionNotice, super.key});

  final String? sessionNotice;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(authFlowControllerProvider);

    return switch (state.step) {
      AuthStep.login => LoginScreen(sessionNotice: sessionNotice),
      AuthStep.otp => const OtpScreen(),
    };
  }
}
