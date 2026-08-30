import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/habits.dart';
import '../../core/theme/design_tokens.dart';
import '../../core/theme/habit_palette.dart';
import '../../core/utils/date_format.dart';
import '../../models/journal.dart';
import '../../providers/service_providers.dart';
import '../../widgets/app_card.dart';
import '../../widgets/state_views.dart';

class JournalDetailScreen extends ConsumerStatefulWidget {
  const JournalDetailScreen({required this.id, super.key});

  final int id;

  @override
  ConsumerState<JournalDetailScreen> createState() =>
      _JournalDetailScreenState();
}

class _JournalDetailScreenState extends ConsumerState<JournalDetailScreen> {
  Journal? _journal;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final journal = await ref
          .read(journalServiceProvider)
          .fetchDetail(widget.id);
      if (mounted) {
        setState(() {
          _journal = journal;
          _loading = false;
        });
      }
    } on Object catch (error) {
      if (mounted) {
        setState(() {
          _error = error.toString();
          _loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Detail Jurnal')),
      body: _buildBody(context),
    );
  }

  Widget _buildBody(BuildContext context) {
    if (_loading) {
      return StateViews.loading(message: 'Memuat detail…');
    }
    if (_error != null || _journal == null) {
      return StateViews.error(context, _error ?? 'Data tidak tersedia', _load);
    }

    final journal = _journal!;
    final date = DateTime.tryParse(journal.date) ?? DateTime.now();

    return ListView(
      padding: const EdgeInsets.fromLTRB(
        AppSpacing.xl,
        AppSpacing.lg,
        AppSpacing.xl,
        AppSpacing.xxl,
      ),
      children: [
        AppCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                AppDateFormat.hariTanggal(date),
                style: Theme.of(context).textTheme.titleLarge
                    ?.copyWith(fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 10),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _Badge(
                    label: journal.isSubmitted ? 'Terkirim' : 'Draft',
                    active: journal.isSubmitted,
                  ),
                  _Badge(
                    label: '${journal.completedCount} kebiasaan',
                    active: journal.isFullyCompleted,
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: AppSpacing.lg),
        const SectionTitle('Rincian kebiasaan'),
        const SizedBox(height: AppSpacing.md),
        ...Habits.all.map((habit) {
          final done = _value(journal, habit.key);
          final time = habit.key == 'bangun_pagi'
              ? journal.bangunPagiTime
              : habit.key == 'tidur_cepat'
              ? journal.tidurNote
              : null;
          final note = _note(journal, habit.key);
          return Padding(
            padding: const EdgeInsets.only(bottom: AppSpacing.sm),
            child: _HabitRow(habit: habit, done: done, time: time, note: note),
          );
        }),
        if (journal.olahragaPhotoUrl != null ||
            journal.makanPhotoUrl != null) ...[
          const SizedBox(height: AppSpacing.lg),
          const SectionTitle('Dokumentasi'),
          const SizedBox(height: AppSpacing.md),
          Row(
            children: [
              if (journal.olahragaPhotoUrl != null)
                Expanded(
                  child: _PhotoTile(
                    label: 'Olahraga',
                    url: journal.olahragaPhotoUrl!,
                  ),
                ),
              if (journal.olahragaPhotoUrl != null &&
                  journal.makanPhotoUrl != null)
                const SizedBox(width: AppSpacing.md),
              if (journal.makanPhotoUrl != null)
                Expanded(
                  child: _PhotoTile(
                    label: 'Makan sehat',
                    url: journal.makanPhotoUrl!,
                  ),
                ),
            ],
          ),
        ],
      ],
    );
  }

  bool _value(Journal journal, String key) => switch (key) {
    'bangun_pagi' => journal.bangunPagi,
    'beribadah' => journal.beribadah,
    'berolahraga' => journal.berolahraga,
    'makan_sehat' => journal.makanSehat,
    'gemar_belajar' => journal.gemarBelajar,
    'bermasyarakat' => journal.bermasyarakat,
    'tidur_cepat' => journal.tidurCepat,
    _ => false,
  };

  String? _note(Journal journal, String key) => switch (key) {
    'berolahraga' => journal.olahragaNote,
    'makan_sehat' => journal.makanNote,
    'gemar_belajar' => journal.belajarNote,
    'bermasyarakat' => journal.masyarakatNote,
    'tidur_cepat' => journal.tidurNote,
    _ => null,
  };
}

class _HabitRow extends StatelessWidget {
  const _HabitRow({
    required this.habit,
    required this.done,
    this.time,
    this.note,
  });

  final HabitMeta habit;
  final bool done;
  final String? time;
  final String? note;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final tone = HabitPalettes.byKey[habit.key]!.resolve(
      isDark ? Brightness.dark : Brightness.light,
    );

    return Container(
      decoration: BoxDecoration(
        color: isDark
            ? const Color(0xFF1E293B).withValues(alpha: 0.6)
            : Colors.white.withValues(alpha: 0.9),
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(
          color: isDark
              ? const Color(0xFF334155).withValues(alpha: 0.5)
              : const Color(0xFFE2E8F0),
        ),
      ),
      padding: const EdgeInsets.all(AppSpacing.md),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: tone.iconBg,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(habit.icon, color: tone.iconText, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  habit.label,
                  style: theme.textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
                ),
                if (time case final t? when t.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    t,
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
                if (note case final n? when n.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(n, style: theme.textTheme.bodySmall),
                ],
              ],
            ),
          ),
          const SizedBox(width: 8),
          Icon(
            done ? Icons.check_circle_rounded : Icons.cancel_rounded,
            color: done ? AppColors.primary500 : Colors.grey.shade400,
            size: 22,
          ),
        ],
      ),
    );
  }
}

class _Badge extends StatelessWidget {
  const _Badge({required this.label, required this.active});

  final String label;
  final bool active;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: active ? colors.primaryContainer : colors.surfaceContainer,
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelMedium?.copyWith(
          color: active ? colors.onPrimaryContainer : colors.onSurfaceVariant,
        ),
      ),
    );
  }
}

class _PhotoTile extends StatelessWidget {
  const _PhotoTile({required this.label, required this.url});

  final String label;
  final String url;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        AspectRatio(
          aspectRatio: 4 / 3,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(AppRadius.md),
            child: Image.network(url, fit: BoxFit.cover),
          ),
        ),
        const SizedBox(height: 6),
        Text(label, style: Theme.of(context).textTheme.bodySmall),
      ],
    );
  }
}
