import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/auth_flow_provider.dart';
import '../../widgets/auth_scaffold.dart';
import '../../widgets/otp_code_field.dart';
import '../../widgets/status_message.dart';

class OtpScreen extends ConsumerStatefulWidget {
  const OtpScreen({super.key});

  @override
  ConsumerState<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends ConsumerState<OtpScreen> {
  final _codeController = TextEditingController();
  final _codeFocusNode = FocusNode();
  Timer? _ticker;
  String? _localError;

  @override
  void initState() {
    super.initState();
    _ticker = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) {
        setState(() {});
      }
    });
  }

  @override
  void dispose() {
    _ticker?.cancel();
    _codeController.clear();
    _codeController.dispose();
    _codeFocusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authFlowControllerProvider);
    final challenge = auth.challenge!;
    final now = DateTime.now().toUtc();
    final isExpired = challenge.isExpiredAt(now);
    final canResend = challenge.canResendAt(now);
    final interactionLocked = auth.isSubmitting || auth.isResending;
    final codeError = _localError ?? _firstCodeError(auth);

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop && !interactionLocked) {
          _backToLogin();
        }
      },
      child: AuthScaffold(
        onBack: interactionLocked ? null : _backToLogin,
        title: 'Masukkan kode OTP',
        description:
            'Kami mengirim 6 digit melalui ${challenge.channelLabel} ke ${challenge.maskedPhone}.',
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (auth.error case final error?) ...[
              StatusMessage(
                message: error.message,
                tone: StatusMessageTone.error,
              ),
              const SizedBox(height: 18),
            ],
            if (auth.feedback case final feedback?) ...[
              StatusMessage(message: feedback, tone: StatusMessageTone.success),
              const SizedBox(height: 18),
            ],
            OtpCodeField(
              key: const Key('otp_code_field'),
              controller: _codeController,
              focusNode: _codeFocusNode,
              enabled: !interactionLocked && !auth.challengeEnded && !isExpired,
              hasError: codeError != null,
              onChanged: _onCodeChanged,
              onSubmitted: (_) => _verify(),
            ),
            if (codeError != null) ...[
              const SizedBox(height: 8),
              Text(
                codeError,
                style: Theme.of(context).textTheme.bodySmall
                    ?.copyWith(color: Theme.of(context).colorScheme.error),
              ),
            ],
            const SizedBox(height: 14),
            Row(
              children: [
                Icon(
                  Icons.schedule_rounded,
                  size: 19,
                  color: Theme.of(context).colorScheme.onSurfaceVariant,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    isExpired
                        ? 'Kode telah kedaluwarsa'
                        : 'Berlaku ${_clock(challenge.expiresAt, now)}',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
            FilledButton(
              key: const Key('otp_verify_button'),
              onPressed:
                  interactionLocked ||
                      auth.challengeEnded ||
                      isExpired ||
                      _codeController.text.length != 6
                  ? null
                  : _verify,
              child: auth.isSubmitting
                  ? const SizedBox.square(
                      dimension: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        semanticsLabel: 'Memverifikasi OTP',
                      ),
                    )
                  : const Text('Verifikasi OTP'),
            ),
            const SizedBox(height: 12),
            if (auth.challengeEnded || isExpired)
              OutlinedButton(
                onPressed: interactionLocked ? null : _backToLogin,
                child: const Text('Kembali ke halaman masuk'),
              )
            else
              TextButton.icon(
                key: const Key('otp_resend_button'),
                onPressed: canResend && !interactionLocked ? _resend : null,
                icon: auth.isResending
                    ? const SizedBox.square(
                        dimension: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.refresh_rounded),
                label: Text(
                  canResend
                      ? 'Kirim ulang kode'
                      : 'Kirim ulang dalam ${_clock(challenge.resendAt, now)}',
                ),
              ),
            const SizedBox(height: 16),
            Text(
              'Jangan berikan kode ini kepada siapa pun, termasuk pihak yang mengaku dari sekolah.',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ),
      ),
    );
  }

  String? _firstCodeError(AuthFlowState auth) {
    final errors = auth.error?.errors['code'];
    return errors == null || errors.isEmpty ? null : errors.first;
  }

  String _clock(DateTime target, DateTime now) {
    final seconds = math.max(0, target.difference(now).inSeconds);
    final minutes = seconds ~/ 60;
    final remainder = seconds % 60;
    return '$minutes:${remainder.toString().padLeft(2, '0')}';
  }

  void _onCodeChanged(String value) {
    setState(() => _localError = null);
    ref.read(authFlowControllerProvider.notifier).dismissError();
  }

  Future<void> _verify() async {
    if (_codeController.text.length != 6) {
      setState(() => _localError = 'Masukkan 6 digit kode OTP.');
      _codeFocusNode.requestFocus();
      return;
    }

    FocusManager.instance.primaryFocus?.unfocus();
    final succeeded = await ref
        .read(authFlowControllerProvider.notifier)
        .verifyOtp(_codeController.text);

    if (!succeeded && mounted) {
      final error = ref.read(authFlowControllerProvider).error;
      if (error?.code == 'otp_invalid') {
        _codeController.clear();
        _codeFocusNode.requestFocus();
      }
    }
  }

  Future<void> _resend() async {
    final succeeded = await ref
        .read(authFlowControllerProvider.notifier)
        .resendOtp();
    if (succeeded && mounted) {
      _codeController.clear();
      _codeFocusNode.requestFocus();
    }
  }

  void _backToLogin() {
    _codeController.clear();
    ref.read(authFlowControllerProvider.notifier).resetToLogin();
  }
}
