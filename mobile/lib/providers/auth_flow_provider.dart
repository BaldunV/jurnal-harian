import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_exception.dart';
import '../models/auth_models.dart';
import 'auth_service_provider.dart';
import 'session_provider.dart';

enum AuthStep { login, otp }

class AuthFlowState {
  const AuthFlowState({
    required this.step,
    this.challenge,
    this.isSubmitting = false,
    this.isResending = false,
    this.error,
    this.feedback,
  });

  const AuthFlowState.login({this.isSubmitting = false, this.error})
    : step = AuthStep.login,
      challenge = null,
      isResending = false,
      feedback = null;

  const AuthFlowState.otp({
    required OtpChallenge this.challenge,
    this.isSubmitting = false,
    this.isResending = false,
    this.error,
    this.feedback,
  }) : step = AuthStep.otp;

  final AuthStep step;
  final OtpChallenge? challenge;
  final bool isSubmitting;
  final bool isResending;
  final ApiException? error;
  final String? feedback;

  bool get challengeEnded =>
      const {'otp_expired', 'otp_attempts_exhausted'}.contains(error?.code);

  AuthFlowState copyWith({
    OtpChallenge? challenge,
    bool? isSubmitting,
    bool? isResending,
    ApiException? error,
    bool clearError = false,
    String? feedback,
    bool clearFeedback = false,
  }) {
    return AuthFlowState(
      step: step,
      challenge: challenge ?? this.challenge,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      isResending: isResending ?? this.isResending,
      error: clearError ? null : error ?? this.error,
      feedback: clearFeedback ? null : feedback ?? this.feedback,
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
      final challenge = await ref
          .read(authServiceProvider)
          .login(nis: nis, password: password);
      state = AuthFlowState.otp(challenge: challenge);
      return true;
    } on Object catch (error) {
      state = AuthFlowState.login(error: _publicError(error));
      return false;
    }
  }

  Future<bool> verifyOtp(String code) async {
    final challenge = state.challenge;
    if (challenge == null || state.isSubmitting || state.challengeEnded) {
      return false;
    }

    state = state.copyWith(
      isSubmitting: true,
      clearError: true,
      clearFeedback: true,
    );

    late AuthTokenResult result;
    try {
      result = await ref
          .read(authServiceProvider)
          .verifyOtp(challengeId: challenge.challengeId, code: code);
    } on Object catch (error) {
      state = state.copyWith(isSubmitting: false, error: _publicError(error));
      return false;
    }

    state = const AuthFlowState.login();
    try {
      await ref
          .read(sessionControllerProvider.notifier)
          .establishSession(result.token);
      return true;
    } on Object {
      return false;
    }
  }

  Future<bool> resendOtp() async {
    final challenge = state.challenge;
    if (challenge == null ||
        state.isResending ||
        state.isSubmitting ||
        state.challengeEnded ||
        !challenge.canResendAt(DateTime.now())) {
      return false;
    }

    state = state.copyWith(
      isResending: true,
      clearError: true,
      clearFeedback: true,
    );

    try {
      final updated = await ref.read(authServiceProvider).resendOtp(challenge);
      state = state.copyWith(
        challenge: updated,
        isResending: false,
        feedback: 'Kode OTP baru telah dikirim.',
      );
      return true;
    } on Object catch (error) {
      state = state.copyWith(isResending: false, error: _publicError(error));
      return false;
    }
  }

  void dismissError() {
    if (state.error != null) {
      state = state.copyWith(clearError: true);
    }
  }

  void resetToLogin() {
    if (!state.isSubmitting) {
      state = const AuthFlowState.login();
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
