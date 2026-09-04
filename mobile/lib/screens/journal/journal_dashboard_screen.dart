import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../../core/constants/habits.dart';
import '../../core/theme/design_tokens.dart';
import '../../core/utils/date_format.dart';
import '../../models/journal.dart';
import '../../models/student.dart';
import '../../providers/journal_controller.dart';
import '../../screens/home/app_shell.dart';
import '../../widgets/app_card.dart';
import '../../widgets/buttons.dart';
import '../../widgets/habit_card.dart';
import '../../widgets/heatmap.dart';
import '../../widgets/state_views.dart';
import '../journal/journal_detail_screen.dart';
import '../journal/journal_form_screen.dart';

class JournalDashboardScreen extends ConsumerStatefulWidget {
  const JournalDashboardScreen({required this.student, super.key});

  final Student student;

  @override
  ConsumerState<JournalDashboardScreen> createState() =>
      _JournalDashboardScreenState();
}

class _JournalDashboardScreenState
    extends ConsumerState<JournalDashboardScreen> {
  @override
  Widget build(BuildContext context) {
    final state = ref.watch(journalControllerProvider);
    final today = state.today;
    final journal = today?.journal;
    final firstName = widget.student.name.trim().split(RegExp(r'\s+')).first;
    final completed = journal?.completed ?? 0;
    final isLocked = journal?.isSubmitted ?? false;
    final streak = _computeStreak(state.history);
    final dateLabel = today != null && today.date.isNotEmpty
        ? AppDateFormat.hariTanggal(_parseDate(today.date))
        : 'Memuat tanggal sekolah…';

    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: Align(
          alignment: Alignment.topCenter,
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 920),
            child: RefreshIndicator(
              onRefresh: () =>
                  ref.read(journalControllerProvider.notifier).load(),
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
                children: [
                  _HeroBanner(
                    firstName: firstName,
                    className: widget.student.className,
                    dateLabel: dateLabel,
                    streak: streak,
                  ),
                  const SizedBox(height: AppSpacing.lg),
                  if (state.isLoadingToday && journal == null)
                    StateViews.loading(message: 'Memuat jurnal hari ini…')
                  else if (state.error != null && journal == null)
                    StateViews.error(
                      context,
                      state.error!,
                      () => ref.read(journalControllerProvider.notifier).load(),
                    )
                  else ...[
                    _ProgressCard(
                      completed: completed,
                      total: Habits.total,
                      isSubmitted: journal?.isSubmitted ?? false,
                    ),
                    const SizedBox(height: AppSpacing.md),
                    GradientButton(
                      key: const Key('open_journal_form_button'),
                      label: journal == null
                          ? 'Mulai isi jurnal hari ini'
                          : isLocked
                          ? 'Lihat jurnal hari ini'
                          : 'Lanjutkan jurnal hari ini',
                      icon: journal == null
                          ? LucideIcons.plus
                          : isLocked
                          ? LucideIcons.eye
                          : LucideIcons.pencil,
                      onPressed: () => _openTodayJournal(journal),
                    ),
                  ],
                  const SizedBox(height: AppSpacing.lg),
                  _QuickStats(
                    streak: streak,
                    history: state.history,
                    percent: journal != null
                        ? (completed / Habits.total * 100).round()
                        : 0,
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  const SectionKicker('Tujuh Kebiasaan'),
                  const SizedBox(height: AppSpacing.md),
                  if (journal != null)
                    ...Habits.all.map(
                      (habit) => Padding(
                        padding: const EdgeInsets.only(bottom: AppSpacing.md),
                        child: HabitCheckCard(
                          habit: habit,
                          done: _habitValue(journal, habit.key),
                          time: _habitTime(journal, habit.key),
                          note: _habitNote(journal, habit.key),
                          enabled: !isLocked,
                          onTap: () => _openTodayJournal(journal),
                          onChanged: (_) => _openForm(journal),
                        ),
                      ),
                    )
                  else
                    AppCard(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        child: Column(
                          children: [
                            Icon(
                              LucideIcons.listChecks,
                              size: 40,
                              color: Theme.of(context).colorScheme.primary
                                  .withValues(alpha: 0.6),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Belum ada catatan hari ini. Mulai jurnal dan tandai kebiasaan yang sudah kamu lakukan.',
                              textAlign: TextAlign.center,
                              style: Theme.of(context).textTheme.bodyMedium,
                            ),
                          ],
                        ),
                      ),
                    ),
                  const SizedBox(height: AppSpacing.xl),
                  const SectionKicker('Konsistensi 7 Hari'),
                  const SizedBox(height: AppSpacing.md),
                  AppCard(
                    child: WeekHeatmap(
                      cells: _heatCells(state.history),
                      onTap: _onHeatCellTap,
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xl),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const SectionKicker('Riwayat Terisi'),
                      TextButton(
                        onPressed: _openHistory,
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 6,
                          ),
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text('Lihat semua'),
                            SizedBox(width: 4),
                            Icon(LucideIcons.arrowRight, size: 15),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  _RecentHistory(history: state.history),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _openTodayJournal(Journal? journal) {
    if (journal == null) {
      _openForm(null);
      return;
    }

    if (journal.isSubmitted && journal.id != 0) {
      Navigator.of(context).push(
        MaterialPageRoute<dynamic>(
          builder: (_) => JournalDetailScreen(id: journal.id),
        ),
      );
      return;
    }

    _openForm(journal);
  }

  void _openForm(Journal? journal) {
    Navigator.of(context).push(
      MaterialPageRoute<dynamic>(
        builder: (_) =>
            JournalFormScreen(student: widget.student, existing: journal),
      ),
    );
  }

  void _openHistory() {
    ref.read(shellTabProvider.notifier).setTab(1);
  }

  bool _habitValue(Journal journal, String key) => switch (key) {
    'bangun_pagi' => journal.bangunPagi,
    'beribadah' => journal.beribadah,
    'berolahraga' => journal.berolahraga,
    'makan_sehat' => journal.makanSehat,
    'gemar_belajar' => journal.gemarBelajar,
    'bermasyarakat' => journal.bermasyarakat,
    'tidur_cepat' => journal.tidurCepat,
    _ => false,
  };

  String? _habitTime(Journal journal, String key) => switch (key) {
    'bangun_pagi' => journal.bangunPagiTime,
    'tidur_cepat' => journal.tidurCepatTime,
    _ => null,
  };

  String? _habitNote(Journal journal, String key) => switch (key) {
    'berolahraga' => journal.olahragaNote,
    'makan_sehat' => journal.makanNote,
    'gemar_belajar' => journal.belajarNote,
    'bermasyarakat' => journal.masyarakatNote,
    'tidur_cepat' => journal.tidurNote,
    _ => null,
  };

  DateTime _parseDate(String value) {
    final parsed = DateTime.tryParse(value);
    return parsed ?? DateTime.now();
  }

  void _onHeatCellTap(int index) {
    final cell = _heatCells(ref.read(journalControllerProvider).history)[index];
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) => _CalendarDetail(cell: cell),
    );
  }
}

int _computeStreak(List<Journal> history) {
  if (history.isEmpty) return 0;
  final byDate = <String, Journal>{for (final j in history) j.date: j};
  var streak = 0;
  var day = DateTime.now();
  // Allow today to be unrecorded without breaking the streak.
  for (var i = 0; i < 365; i++) {
    final key = _dateKey(day);
    final journal = byDate[key];
    if (journal != null) {
      if (journal.isFullyCompleted) {
        streak++;
      } else {
        break;
      }
    }
    day = day.subtract(const Duration(days: 1));
  }
  return streak;
}

String _dateKey(DateTime d) =>
    '${d.year.toString().padLeft(4, '0')}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

List<HeatCell> _heatCells(List<Journal> history) {
  final byDate = <String, Journal>{for (final j in history) j.date: j};
  final labels = <String>['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
  final cells = <HeatCell>[];
  final today = DateTime.now();
  for (var i = 6; i >= 0; i--) {
    final day = today.subtract(Duration(days: i));
    final journal = byDate[_dateKey(day)];
    final state = journal == null
        ? 'none'
        : journal.isFullyCompleted
        ? 'full'
        : journal.completedCount > 0
        ? 'partial'
        : 'empty';
    cells.add(
      HeatCell(label: labels[day.weekday - 1], state: state, isToday: i == 0),
    );
  }
  return cells;
}

class _HeroBanner extends StatelessWidget {
  const _HeroBanner({
    required this.firstName,
    required this.className,
    required this.dateLabel,
    required this.streak,
  });

  final String firstName;
  final String className;
  final String dateLabel;
  final int streak;

  @override
  Widget build(BuildContext context) {
    return Container(
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        gradient: AppGradients.morningMesh,
        borderRadius: BorderRadius.circular(AppRadius.xl),
        border: Border.all(color: Colors.white.withValues(alpha: 0.14)),
        boxShadow: <BoxShadow>[
          BoxShadow(
            color: AppColors.primary700.withValues(alpha: 0.25),
            blurRadius: 28,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            top: -54,
            right: -24,
            child: Container(
              width: 150,
              height: 150,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.07),
                shape: BoxShape.circle,
              ),
            ),
          ),
          Positioned(
            bottom: -56,
            left: -36,
            child: Container(
              width: 130,
              height: 130,
              decoration: BoxDecoration(
                color: AppColors.amber.withValues(alpha: 0.09),
                shape: BoxShape.circle,
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(AppSpacing.xl),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 46,
                      height: 46,
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        boxShadow: <BoxShadow>[
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.12),
                            blurRadius: 14,
                            offset: const Offset(0, 6),
                          ),
                        ],
                      ),
                      child: Image.asset(
                        'assets/images/logo.png',
                        semanticLabel: 'Logo SMK BPPI',
                      ),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'JURNAL 7 KEBIASAAN',
                            style: Theme.of(context).textTheme.labelSmall
                                ?.copyWith(
                                  color: Colors.white.withValues(alpha: 0.74),
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.2,
                                ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'SMK BPPI',
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w800,
                                ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.xl),
                Text(
                  'Halo, $firstName 👋',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontFamily: AppTypography.displayFont,
                    color: Colors.white,
                    fontSize: 30,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: AppSpacing.xs),
                Text(
                  'Satu langkah kecil untuk kebiasaan yang lebih baik.',
                  style: Theme.of(context).textTheme.bodyMedium
                      ?.copyWith(color: Colors.white.withValues(alpha: 0.82)),
                ),
                const SizedBox(height: AppSpacing.lg),
                Wrap(
                  spacing: AppSpacing.sm,
                  runSpacing: AppSpacing.sm,
                  children: [
                    _HeroPill(icon: LucideIcons.calendarDays, label: dateLabel),
                    _HeroPill(
                      icon: LucideIcons.flame,
                      label: '$streak hari beruntun',
                      emphasized: streak > 0,
                    ),
                    if (className.isNotEmpty)
                      _HeroPill(icon: LucideIcons.school, label: className),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroPill extends StatelessWidget {
  const _HeroPill({
    required this.icon,
    required this.label,
    this.emphasized = false,
  });

  final IconData icon;
  final String label;
  final bool emphasized;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: emphasized
            ? AppColors.amber.withValues(alpha: 0.20)
            : Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(AppRadius.pill),
        border: Border.all(
          color: emphasized
              ? AppColors.amber.withValues(alpha: 0.32)
              : Colors.white.withValues(alpha: 0.13),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 14,
            color: emphasized ? const Color(0xFFFDE68A) : Colors.white,
          ),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              label,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.labelSmall
                  ?.copyWith(color: Colors.white, fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }
}

class _ProgressCard extends StatelessWidget {
  const _ProgressCard({
    required this.completed,
    required this.total,
    required this.isSubmitted,
  });

  final int completed;
  final int total;
  final bool isSubmitted;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final percent = total == 0
        ? 0.0
        : (completed / total).clamp(0.0, 1.0).toDouble();
    final isComplete = percent >= 1;
    final statusLabel = isSubmitted
        ? 'Terkunci'
        : isComplete
        ? 'Sempurna!'
        : 'Belum dikirim';

    return AppCard(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  gradient: AppGradients.primary,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: const Icon(
                  LucideIcons.listChecks,
                  color: Colors.white,
                  size: 22,
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Progres hari ini',
                      style: Theme.of(context).textTheme.titleMedium
                          ?.copyWith(fontWeight: FontWeight.w800),
                    ),
                    Text(
                      isComplete
                          ? 'Semua kebiasaan sudah tercatat.'
                          : '${total - completed} kebiasaan lagi untuk selesai.',
                      style: Theme.of(context).textTheme.bodySmall,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const SizedBox(width: AppSpacing.sm),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: isComplete || isSubmitted
                      ? colors.primaryContainer
                      : colors.secondaryContainer,
                  borderRadius: BorderRadius.circular(AppRadius.pill),
                ),
                child: Text(
                  statusLabel,
                  style: Theme.of(context).textTheme.labelSmall?.copyWith(
                    color: isSubmitted
                        ? colors.onPrimaryContainer
                        : isComplete
                        ? colors.onPrimaryContainer
                        : colors.onSecondaryContainer,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.lg),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '$completed',
                style: Theme.of(context).textTheme.displaySmall?.copyWith(
                  color: colors.onSurface,
                  fontSize: 38,
                  height: 1,
                ),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(left: 5, bottom: 2),
                  child: Text(
                    'dari $total kebiasaan',
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ),
              ),
              const SizedBox(width: AppSpacing.sm),
              Text(
                '${(percent * 100).round()}%',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  color: colors.primary,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          Semantics(
            label:
                '$completed dari $total kebiasaan selesai, ${(percent * 100).round()} persen',
            child: _HorizontalProgress(value: percent),
          ),
        ],
      ),
    );
  }
}

/// Horizontal progress bar: solid emerald fill on a subtle slate track.
class _HorizontalProgress extends StatelessWidget {
  const _HorizontalProgress({required this.value});

  final double value;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        return SizedBox(
          height: 8,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: Stack(
              children: [
                Container(
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.surfaceContainer,
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(
                      color: Theme.of(context)
                          .colorScheme
                          .surfaceContainerHighest,
                      width: 0.5,
                    ),
                  ),
                ),
                AnimatedContainer(
                  duration: const Duration(milliseconds: 700),
                  curve: Curves.easeOut,
                  height: 8,
                  width: constraints.maxWidth * value,
                  decoration: BoxDecoration(
                    gradient: value > 0 ? AppGradients.primary : null,
                    color: value > 0
                        ? null
                        : Theme.of(context).colorScheme.surfaceContainer,
                    borderRadius: BorderRadius.circular(999),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _QuickStats extends StatelessWidget {
  const _QuickStats({
    required this.streak,
    required this.history,
    required this.percent,
  });

  final int streak;
  final List<Journal> history;
  final int percent;

  @override
  Widget build(BuildContext context) {
    final days = history.where((j) => j.completedCount > 0).length;
    return Row(
      children: [
        Expanded(
          child: _SummaryCard(
            icon: LucideIcons.zap,
            value: '$streak',
            label: 'Streak',
            color: AppColors.amber,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _SummaryCard(
            icon: LucideIcons.calendarDays,
            value: '$days',
            label: 'Hari isi',
            color: AppColors.teal,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _SummaryCard(
            icon: LucideIcons.circleGauge,
            value: '$percent%',
            label: 'Hari ini',
            color: AppColors.primary500,
          ),
        ),
      ],
    );
  }
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({
    required this.icon,
    required this.value,
    required this.label,
    required this.color,
  });

  final IconData icon;
  final String value;
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Container(
      height: 104,
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: colors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(color: colors.outlineVariant),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.13),
              borderRadius: BorderRadius.circular(AppRadius.sm),
            ),
            child: Icon(icon, size: 17, color: color),
          ),
          const SizedBox(height: 6),
          FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(
              value,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: colors.onSurface,
              ),
            ),
          ),
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: colors.onSurfaceVariant,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _RecentHistory extends StatelessWidget {
  const _RecentHistory({required this.history});

  final List<Journal> history;

  @override
  Widget build(BuildContext context) {
    final recent = history.take(1).toList();
    if (recent.isEmpty) {
      return AppCard(
        child: Text(
          'Belum ada riwayat jurnal yang tercatat.',
          style: Theme.of(context).textTheme.bodyMedium,
        ),
      );
    }
    return Column(
      children: recent.map((journal) {
        final tone = journal.isFullyCompleted
            ? AppColors.primary500
            : journal.completedCount > 0
            ? AppColors.amber
            : AppColors.ink400;
        return Padding(
          padding: const EdgeInsets.only(bottom: AppSpacing.md),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              borderRadius: BorderRadius.circular(AppRadius.lg),
              onTap: () {
                if (journal.id != 0) {
                  Navigator.of(context).push(
                    MaterialPageRoute<dynamic>(
                      builder: (_) => JournalDetailScreen(id: journal.id),
                    ),
                  );
                }
              },
              child: AppCard(
                elevation: false,
                padding: const EdgeInsets.all(AppSpacing.md),
                child: Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: tone.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        journal.isFullyCompleted
                            ? LucideIcons.checkCircle
                            : LucideIcons.calendarDays,
                        color: tone,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            journal.date.isNotEmpty
                                ? AppDateFormat.hariTanggal(
                                    DateTime.tryParse(journal.date) ??
                                        DateTime.now(),
                                  )
                                : 'Jurnal',
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            journal.isFullyCompleted
                                ? '7/7 kebiasaan lengkap'
                                : '${journal.completedCount}/7 kebiasaan',
                            style: Theme.of(context).textTheme.bodySmall
                                ?.copyWith(
                                  color: Theme.of(context)
                                      .colorScheme
                                      .onSurfaceVariant,
                                ),
                          ),
                        ],
                      ),
                    ),
                    Icon(
                      LucideIcons.chevronRight,
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      }).toList(),
    );
  }
}

class _CalendarDetail extends StatefulWidget {
  const _CalendarDetail({required this.cell});

  final HeatCell cell;

  @override
  State<_CalendarDetail> createState() => _CalendarDetailState();
}

class _CalendarDetailState extends State<_CalendarDetail>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );
    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return SlideTransition(
      position: Tween<Offset>(
        begin: const Offset(0, 1),
        end: Offset.zero,
      ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut)),
      child: FadeTransition(
        opacity: _controller,
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.lg),
          decoration: BoxDecoration(
            color: theme.colorScheme.surface,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            boxShadow: AppShadows.card(context),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.ink200,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Text(
                'Detail: ',
                style: theme.textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: widget.cell.state == 'full'
                      ? AppColors.primary500.withValues(alpha: 0.15)
                      : widget.cell.state == 'partial'
                      ? AppColors.amber.withValues(alpha: 0.15)
                      : AppColors.ink200.withValues(alpha: 0.3),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Text(
                  widget.cell.state == 'full'
                      ? '7/7 Lengkap'
                      : widget.cell.state == 'partial'
                      ? 'Sebagian'
                      : 'Belum Isi',
                  style: TextStyle(
                    fontWeight: FontWeight.w800,
                    color: widget.cell.state == 'full'
                        ? AppColors.primary600
                        : widget.cell.state == 'partial'
                        ? AppColors.amber600
                        : AppColors.ink400,
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
            ],
          ),
        ),
      ),
    );
  }
}
