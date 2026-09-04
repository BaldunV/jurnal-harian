import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../../core/theme/design_tokens.dart';
import '../../providers/auth_flow_provider.dart';
import '../../providers/interaction_feedback_provider.dart';
import '../../providers/session_provider.dart';
import '../../widgets/auth_scaffold.dart';
import '../../widgets/buttons.dart';
import '../../widgets/status_message.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({this.sessionNotice, super.key});

  final String? sessionNotice;

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nisController = TextEditingController();
  final _passwordController = TextEditingController();
  final _nisFocusNode = FocusNode();
  final _passwordFocusNode = FocusNode();
  bool _obscurePassword = true;
  bool _submitted = false;
  Map<String, List<String>> _serverErrors = const <String, List<String>>{};

  @override
  void dispose() {
    _passwordController.clear();
    _nisController.dispose();
    _passwordController.dispose();
    _nisFocusNode.dispose();
    _passwordFocusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authFlowControllerProvider);

    return AuthScaffold(
      title: 'Selamat datang',
      description: 'Masuk untuk melanjutkan kebiasaan baikmu hari ini.',
      background: const _LoginBackdrop(),
      child: AutofillGroup(
        child: Form(
          key: _formKey,
          autovalidateMode: _submitted
              ? AutovalidateMode.onUserInteraction
              : AutovalidateMode.disabled,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (widget.sessionNotice case final notice?) ...[
                StatusMessage(
                  message: notice,
                  tone: StatusMessageTone.warning,
                  onDismiss: () => ref
                      .read(sessionControllerProvider.notifier)
                      .dismissNotice(),
                ),
                const SizedBox(height: 18),
              ],
              if (auth.error case final error?) ...[
                StatusMessage(
                  message: error.message,
                  tone: StatusMessageTone.error,
                ),
                const SizedBox(height: 18),
              ],
              TextFormField(
                key: const Key('login_nis_field'),
                controller: _nisController,
                focusNode: _nisFocusNode,
                enabled: !auth.isSubmitting,
                autofillHints: const <String>[AutofillHints.username],
                textCapitalization: TextCapitalization.characters,
                textInputAction: TextInputAction.next,
                inputFormatters: <TextInputFormatter>[
                  LengthLimitingTextInputFormatter(255),
                ],
                decoration: const InputDecoration(
                  labelText: 'NIS',
                  hintText: 'Masukkan NIS',
                  prefixIcon: Icon(LucideIcons.badgeCheck),
                ),
                validator: _validateNis,
                onChanged: _onFieldChanged,
                onFieldSubmitted: (_) => _passwordFocusNode.requestFocus(),
              ),
              const SizedBox(height: 16),
              TextFormField(
                key: const Key('login_password_field'),
                controller: _passwordController,
                focusNode: _passwordFocusNode,
                enabled: !auth.isSubmitting,
                obscureText: _obscurePassword,
                autofillHints: const <String>[AutofillHints.password],
                textInputAction: TextInputAction.done,
                inputFormatters: <TextInputFormatter>[
                  LengthLimitingTextInputFormatter(255),
                ],
                decoration: InputDecoration(
                  labelText: 'Password',
                  hintText: 'Masukkan password',
                  prefixIcon: const Icon(LucideIcons.lock),
                  suffixIcon: IconButton(
                    onPressed: auth.isSubmitting
                        ? null
                        : () => setState(
                            () => _obscurePassword = !_obscurePassword,
                          ),
                    tooltip: _obscurePassword
                        ? 'Tampilkan password'
                        : 'Sembunyikan password',
                    icon: Icon(
                      _obscurePassword ? LucideIcons.eye : LucideIcons.eyeOff,
                    ),
                  ),
                ),
                validator: _validatePassword,
                onChanged: _onFieldChanged,
                onFieldSubmitted: (_) => _submit(),
              ),
              const SizedBox(height: 24),
              GradientButton(
                key: const Key('login_submit_button'),
                label: 'Lanjutkan',
                enabled: !auth.isSubmitting,
                isLoading: auth.isSubmitting,
                onPressed: _submit,
              ),
              const SizedBox(height: 22),
              Container(
                padding: const EdgeInsets.all(AppSpacing.md),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.primaryContainer
                      .withValues(alpha: 0.55),
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      LucideIcons.shieldCheck,
                      size: 20,
                      color: Theme.of(context).colorScheme.primary,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Akses khusus siswa. Password tidak disimpan di perangkat.',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Theme.of(context)
                              .colorScheme
                              .onPrimaryContainer,
                          height: 1.45,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),
              Center(
                child: Text(
                  'SMK BPPI  •  Jurnal 7 Kebiasaan',
                  style: Theme.of(context).textTheme.labelSmall?.copyWith(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String? _validateNis(String? value) {
    final normalized = value?.trim() ?? '';
    if (normalized.isEmpty) {
      return 'NIS wajib diisi.';
    }
    if (normalized.length > 255) {
      return 'NIS terlalu panjang.';
    }

    return _firstError('nis');
  }

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password wajib diisi.';
    }
    if (value.length > 255) {
      return 'Password terlalu panjang.';
    }

    return _firstError('password');
  }

  String? _firstError(String field) {
    final errors = _serverErrors[field];
    return errors == null || errors.isEmpty ? null : errors.first;
  }

  void _onFieldChanged(String value) {
    if (_serverErrors.isNotEmpty) {
      setState(() => _serverErrors = const <String, List<String>>{});
    }
    if (ref.read(authFlowControllerProvider).error != null) {
      ref.read(authFlowControllerProvider.notifier).dismissError();
    }
  }

  Future<void> _submit() async {
    unawaited(ref.read(interactionFeedbackProvider).buttonPress());
    setState(() => _submitted = true);
    if (!_formKey.currentState!.validate()) {
      return;
    }

    FocusManager.instance.primaryFocus?.unfocus();
    final succeeded = await ref
        .read(authFlowControllerProvider.notifier)
        .login(nis: _nisController.text, password: _passwordController.text);

    if (!succeeded && mounted) {
      setState(() {
        _serverErrors =
            ref.read(authFlowControllerProvider).error?.errors ??
            const <String, List<String>>{};
      });
      _formKey.currentState?.validate();
    }
  }
}

class _LoginBackdrop extends StatelessWidget {
  const _LoginBackdrop();

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Stack(
      fit: StackFit.expand,
      children: [
        DecoratedBox(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: <Color>[
                colors.surface,
                colors.primaryContainer.withValues(alpha: isDark ? 0.34 : 0.7),
                colors.surface,
              ],
            ),
          ),
        ),
        Positioned(
          top: -80,
          right: -70,
          child: _GlowOrb(
            size: 230,
            color: AppColors.teal.withValues(alpha: isDark ? 0.12 : 0.10),
          ),
        ),
        Positioned(
          bottom: -110,
          left: -90,
          child: _GlowOrb(
            size: 280,
            color: AppColors.primary500.withValues(alpha: isDark ? 0.13 : 0.09),
          ),
        ),
      ],
    );
  }
}

class _GlowOrb extends StatelessWidget {
  const _GlowOrb({required this.size, required this.color});

  final double size;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(color: color, shape: BoxShape.circle),
      ),
    );
  }
}
