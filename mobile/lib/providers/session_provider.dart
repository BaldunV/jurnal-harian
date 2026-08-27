import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core_providers.dart';

enum SessionStatus { signedOut, signedIn }

final sessionControllerProvider =
    AsyncNotifierProvider<SessionController, SessionStatus>(
      SessionController.new,
    );

class SessionController extends AsyncNotifier<SessionStatus> {
  @override
  Future<SessionStatus> build() async {
    final token = await ref.watch(tokenStorageProvider).readToken();

    return token == null || token.isEmpty
        ? SessionStatus.signedOut
        : SessionStatus.signedIn;
  }

  Future<void> establishSession(String token) async {
    state = const AsyncLoading<SessionStatus>();
    state = await AsyncValue.guard(() async {
      await ref.read(tokenStorageProvider).writeToken(token);
      return SessionStatus.signedIn;
    });
  }

  Future<void> signOut() async {
    state = const AsyncLoading<SessionStatus>();
    state = await AsyncValue.guard(() async {
      await ref.read(tokenStorageProvider).clearToken();
      return SessionStatus.signedOut;
    });
  }

  void invalidateSession() {
    state = const AsyncData<SessionStatus>(SessionStatus.signedOut);
  }
}
