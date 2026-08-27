import 'package:flutter_riverpod/flutter_riverpod.dart';

final authInvalidationProvider =
    NotifierProvider<AuthInvalidationController, int>(
      AuthInvalidationController.new,
    );

class AuthInvalidationController extends Notifier<int> {
  @override
  int build() => 0;

  void markUnauthorized() => state += 1;
}
