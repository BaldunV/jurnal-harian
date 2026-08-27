import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../providers/auth_flow_provider.dart';
import '../../providers/session_provider.dart';
import '../../widgets/auth_scaffold.dart';
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
      title: 'Masuk ke jurnalmu',
      description: 'Gunakan NIS dan password akun sekolah. Setelah datamu cocok, kami akan mengirim kode OTP.',
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
                  prefixIcon: Icon(Icons.badge_outlined),
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
                  prefixIcon: const Icon(Icons.lock_outline_rounded),
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
                      _obscurePassword
                          ? Icons.visibility_outlined
                          : Icons.visibility_off_outlined,
                    ),
                  ),
                ),
                validator: _validatePassword,
                onChanged: _onFieldChanged,
                onFieldSubmitted: (_) => _submit(),
              ),
              const SizedBox(height: 24),
              FilledButton(
                key: const Key('login_submit_button'),
                onPressed: auth.isSubmitting ? null : _submit,
                child: auth.isSubmitting
                    ? const SizedBox.square(
                        dimension: 22,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.5,
                          semanticsLabel: 'Sedang masuk',
                        ),
                      )
                    : const Text('Lanjutkan'),
              ),
              const SizedBox(height: 22),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(
                    Icons.verified_user_outlined,
                    size: 20,
                    color: Theme.of(context).colorScheme.primary,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Akses khusus siswa. Password tidak disimpan dan setiap login dikonfirmasi dengan OTP.',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ),
                ],
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
