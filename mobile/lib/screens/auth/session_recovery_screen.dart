import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/api/api_exception.dart';
import '../../providers/session_provider.dart';
import '../../widgets/auth_scaffold.dart';
import '../../widgets/status_message.dart';

class SessionRecoveryScreen extends ConsumerStatefulWidget {
  const SessionRecoveryScreen({required this.error, super.key});

  final Object error;

  @override
  ConsumerState<SessionRecoveryScreen> createState() =>
      _SessionRecoveryScreenState();
}

class _SessionRecoveryScreenState extends ConsumerState<SessionRecoveryScreen> {
  bool _isWorking = false;

  @override
  Widget build(BuildContext context) {
    final content = _contentFor(widget.error);

    return AuthScaffold(
      title: content.title,
      description: content.description,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          StatusMessage(
            message: content.detail,
            tone: StatusMessageTone.warning,
          ),
          const SizedBox(height: 24),
          FilledButton.icon(
            key: const Key('session_retry_button'),
            onPressed: _isWorking ? null : _retry,
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Coba lagi'),
          ),
          const SizedBox(height: 10),
          TextButton(
            key: const Key('session_local_logout_button'),
            onPressed: _isWorking ? null : _discardSession,
            child: const Text('Keluar dari perangkat ini'),
          ),
        ],
      ),
    );
  }

  Future<void> _retry() async {
    setState(() => _isWorking = true);
    await ref.read(sessionControllerProvider.notifier).retryRestoration();
    if (mounted) {
      setState(() => _isWorking = false);
    }
  }

  Future<void> _discardSession() async {
    setState(() => _isWorking = true);
    try {
      await ref.read(sessionControllerProvider.notifier).discardLocalSession();
    } on Object {
      if (mounted) {
        setState(() => _isWorking = false);
      }
    }
  }
}

class _RecoveryContent {
  const _RecoveryContent({
    required this.title,
    required this.description,
    required this.detail,
  });

  final String title;
  final String description;
  final String detail;
}

_RecoveryContent _contentFor(Object error) {
  if (error is ApiException) {
    return switch (error.kind) {
      ApiFailureKind.offline => const _RecoveryContent(
        title: 'Jaringan belum tersedia',
        description: 'Sesi amanmu tetap tersimpan. Sambungkan perangkat ke internet lalu coba lagi.',
        detail: 'Kami belum dapat memvalidasi sesi ke sistem sekolah.',
      ),
      ApiFailureKind.timeout => const _RecoveryContent(
        title: 'Koneksi terlalu lama',
        description: 'Sesi amanmu tidak dihapus. Coba kembali saat koneksi lebih stabil.',
        detail: 'Sistem sekolah belum memberikan respons tepat waktu.',
      ),
      _ => const _RecoveryContent(
        title: 'Sesi belum dapat diperiksa',
        description: 'Sesi amanmu tetap tersimpan sampai sistem sekolah dapat dihubungi kembali.',
        detail: 'Layanan sedang bermasalah. Coba lagi beberapa saat.',
      ),
    };
  }

  return const _RecoveryContent(
    title: 'Penyimpanan aman belum siap',
    description: 'Perangkat belum dapat membaca sesi lokal. Tidak ada data yang dikirim.',
    detail: 'Coba lagi atau keluarkan akun dari perangkat ini.',
  );
}
