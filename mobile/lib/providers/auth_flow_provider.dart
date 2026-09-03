import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_exception.dart';
import 'auth_service_provider.dart';
import 'session_provider.dart';

class AuthFlowState {
  const AuthFlowState({this.isSubmitting = false, this.error});

  const AuthFlowState.login({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiException? error;

  AuthFlowState copyWith({
    bool? isSubmitting,
    ApiException? error,
    bool clearError = false,
  }) {
    return AuthFlowState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final authFlowControllerProvider =
    NotifierProvider<AuthFlowController, AuthFlowState>(AuthFlowController.new);

class AuthFlowController extends Notifier<AuthFlowState> {
  @override
  AuthFlowState build() => const AuthFlowState.login();

  Future<bool> login({required String nis, required String password}) async {
    if (state.isSubmitting) {
      return false;
    }

    ref.read(sessionControllerProvider.notifier).dismissNotice();

    state = const AuthFlowState.login(isSubmitting: true);

    try {
      final result = await ref
          .read(authServiceProvider)
          .login(nis: nis, password: password);

      await ref
          .read(sessionControllerProvider.notifier)
          .establishSession(result.token);

      return true;
    } on Object catch (error) {
      state = AuthFlowState.login(error: _publicError(error));

      return false;
    } finally {
      state = state.copyWith(isSubmitting: false);
    }
  }

  void dismissError() {
    if (state.error != null) {
      state = state.copyWith(clearError: true);
    }
  }

  ApiException _publicError(Object error) {
    if (error is ApiException) {
      return error;
    }

    return const ApiException(
      kind: ApiFailureKind.unknown,
      message: 'Permintaan belum dapat diproses. Coba kembali.',
    );
  }
}
