import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'login_screen.dart';

class AuthFlowScreen extends ConsumerWidget {
  const AuthFlowScreen({this.sessionNotice, super.key});

  final String? sessionNotice;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return LoginScreen(sessionNotice: sessionNotice);
  }
}
