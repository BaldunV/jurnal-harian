import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_exception.dart';
import '../models/student.dart';
import 'auth_invalidation_provider.dart';
import 'auth_service_provider.dart';
import 'core_providers.dart';

sealed class SessionState {
  const SessionState();
}

final class SignedOutSession extends SessionState {
  const SignedOutSession({this.notice});

  final String? notice;
}

final class AuthenticatedSession extends SessionState {
  const AuthenticatedSession(this.student);

  final Student student;
}

final sessionControllerProvider =
    AsyncNotifierProvider<SessionController, SessionState>(
      SessionController.new,
      retry: _disableAutomaticRetry,
    );

Duration? _disableAutomaticRetry(int retryCount, Object error) => null;

class SessionController extends AsyncNotifier<SessionState> {
  static const _expiredNotice =
      'Sesi sudah berakhir. Silakan masuk kembali dengan akun sekolahmu.';
  static const _localLogoutNotice =
      'Kamu sudah keluar dari perangkat ini, tetapi token di server belum '
      'dapat dicabut. Token lama mungkin tetap aktif sampai kedaluwarsa.';

  @override
  Future<SessionState> build() async {
    ref.listen(authInvalidationProvider, (previous, next) {
      if (previous != next) {
        state = const AsyncData<SessionState>(
          SignedOutSession(notice: _expiredNotice),
        );
      }
    });

    return _restore();
  }

  Future<void> establishSession(String token) async {
    state = const AsyncLoading<SessionState>();

    try {
      await ref.read(tokenStorageProvider).writeToken(token);
      final student = await ref.read(authServiceProvider).me();
      state = AsyncData<SessionState>(AuthenticatedSession(student));
    } on ApiException catch (error, stackTrace) {
      if (error.requiresAuthentication) {
        state = const AsyncData<SessionState>(
          SignedOutSession(notice: _expiredNotice),
        );
      } else {
        state = AsyncError<SessionState>(error, stackTrace);
      }
      rethrow;
    } on Object catch (error, stackTrace) {
      state = AsyncError<SessionState>(error, stackTrace);
      rethrow;
    }
  }

  Future<void> retryRestoration() async {
    state = const AsyncLoading<SessionState>();
    state = await AsyncValue.guard(_restore);
  }

  Future<void> logout() async {
    ApiException? revocationFailure;

    try {
      await ref.read(authServiceProvider).logout();
    } on ApiException catch (error) {
      if (!error.requiresAuthentication) {
        revocationFailure = error;
      }
    }

    try {
      await ref.read(tokenStorageProvider).clearToken();
      state = AsyncData<SessionState>(
        SignedOutSession(
          notice: revocationFailure == null ? null : _localLogoutNotice,
        ),
      );
    } on Object catch (error, stackTrace) {
      state = AsyncError<SessionState>(error, stackTrace);
      rethrow;
    }
  }

  Future<void> discardLocalSession() async {
    try {
      await ref.read(tokenStorageProvider).clearToken();
      state = const AsyncData<SessionState>(SignedOutSession());
    } on Object catch (error, stackTrace) {
      state = AsyncError<SessionState>(error, stackTrace);
      rethrow;
    }
  }

  void dismissNotice() {
    final current = state.value;
    if (current is SignedOutSession && current.notice != null) {
      state = const AsyncData<SessionState>(SignedOutSession());
    }
  }

  Future<SessionState> _restore() async {
    final token = await ref.read(tokenStorageProvider).readToken();
    if (token == null || token.isEmpty) {
      return const SignedOutSession();
    }

    try {
      final student = await ref.read(authServiceProvider).me();
      return AuthenticatedSession(student);
    } on ApiException catch (error) {
      if (!error.requiresAuthentication) {
        rethrow;
      }

      return const SignedOutSession(notice: _expiredNotice);
    }
  }
}
