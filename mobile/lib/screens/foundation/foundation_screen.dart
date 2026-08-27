import 'package:flutter/material.dart';

import '../../widgets/habit_ribbon.dart';

enum FoundationState { loading, signedOut, sessionReady, storageError }

class FoundationScreen extends StatelessWidget {
  const FoundationScreen({required this.state, this.onRetry, super.key});

  final FoundationState state;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    final content = _contentFor(state);
    final colors = Theme.of(context).colorScheme;

    return Scaffold(
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            return SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(24, 20, 24, 28),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight: (constraints.maxHeight - 48).clamp(
                    0,
                    double.infinity,
                  ),
                ),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 520),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        _BrandMark(colors: colors),
                        const SizedBox(height: 44),
                        Text(
                          'JURNAL 7 KEBIASAAN',
                          style: Theme.of(context).textTheme.labelLarge
                              ?.copyWith(
                                color: colors.primary,
                                letterSpacing: 1.4,
                              ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          content.title,
                          style: Theme.of(context).textTheme.displaySmall,
                        ),
                        const SizedBox(height: 14),
                        Text(
                          content.description,
                          style: Theme.of(context).textTheme.bodyLarge,
                        ),
                        const SizedBox(height: 32),
                        const HabitRibbon(completed: 0),
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Hari ini',
                              style: Theme.of(context).textTheme.labelMedium,
                            ),
                            Text(
                              '7 kebiasaan',
                              style: Theme.of(context).textTheme.labelMedium,
                            ),
                          ],
                        ),
                        const SizedBox(height: 32),
                        _StatusCard(content: content),
                        if (state == FoundationState.storageError) ...[
                          const SizedBox(height: 16),
                          FilledButton.icon(
                            onPressed: onRetry,
                            icon: const Icon(Icons.refresh_rounded),
                            label: const Text('Coba lagi'),
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

class _BrandMark extends StatelessWidget {
  const _BrandMark({required this.colors});

  final ColorScheme colors;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 64,
          height: 64,
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: colors.outlineVariant),
            boxShadow: [
              BoxShadow(
                color: colors.shadow.withValues(alpha: 0.08),
                blurRadius: 18,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Image.asset(
            'assets/images/logo.png',
            semanticLabel: 'Logo SMK BPPI',
          ),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('SMK BPPI', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 2),
              Text(
                'Baleendah, Jawa Barat',
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _StatusCard extends StatelessWidget {
  const _StatusCard({required this.content});

  final _FoundationContent content;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: colors.primaryContainer,
                borderRadius: BorderRadius.circular(14),
              ),
              child: stateIcon(content.icon, colors.onPrimaryContainer),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    content.statusTitle,
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    content.statusDescription,
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget stateIcon(IconData icon, Color color) {
    if (content.showProgress) {
      return Padding(
        padding: const EdgeInsets.all(12),
        child: CircularProgressIndicator(
          color: color,
          strokeWidth: 2.5,
          semanticsLabel: 'Memeriksa sesi',
        ),
      );
    }

    return Icon(icon, color: color);
  }
}

class _FoundationContent {
  const _FoundationContent({
    required this.title,
    required this.description,
    required this.statusTitle,
    required this.statusDescription,
    required this.icon,
    this.showProgress = false,
  });

  final String title;
  final String description;
  final String statusTitle;
  final String statusDescription;
  final IconData icon;
  final bool showProgress;
}

_FoundationContent _contentFor(FoundationState state) {
  return switch (state) {
    FoundationState.loading => const _FoundationContent(
      title: 'Menyiapkan jurnalmu.',
      description:
          'Kami sedang memeriksa sesi aman di perangkat ini sebelum memulai.',
      statusTitle: 'Memeriksa sesi',
      statusDescription: 'Proses ini hanya berlangsung sebentar.',
      icon: Icons.hourglass_top_rounded,
      showProgress: true,
    ),
    FoundationState.signedOut => const _FoundationContent(
      title: 'Satu hari, tujuh kebiasaan baik.',
      description: 'Catat kegiatan harianmu dengan akun sekolah dan konfirmasi OTP yang aman.',
      statusTitle: 'Fondasi aplikasi siap',
      statusDescription: 'Alur masuk dan jurnal siswa akan tersambung langsung ke sistem sekolah.',
      icon: Icons.shield_outlined,
    ),
    FoundationState.sessionReady => const _FoundationContent(
      title: 'Jurnalmu siap dilanjutkan.',
      description:
          'Sesi aman ditemukan. Data tetap mengikuti catatan resmi sekolah.',
      statusTitle: 'Sesi terlindungi',
      statusDescription:
          'Identitas dan tanggal jurnal ditentukan oleh sistem sekolah.',
      icon: Icons.verified_user_outlined,
    ),
    FoundationState.storageError => const _FoundationContent(
      title: 'Sesi belum dapat diperiksa.',
      description: 'Penyimpanan aman di perangkat tidak merespons. Coba periksa kembali.',
      statusTitle: 'Belum tersambung',
      statusDescription:
          'Tidak ada data yang dikirim selama pemeriksaan ini gagal.',
      icon: Icons.phonelink_erase_rounded,
    ),
  };
}
