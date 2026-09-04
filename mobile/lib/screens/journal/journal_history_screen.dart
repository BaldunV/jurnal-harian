import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../../core/constants/habits.dart';
import '../../core/theme/design_tokens.dart';
import '../../core/utils/date_format.dart';
import '../../models/journal.dart';
import '../../providers/journal_controller.dart';
import '../../widgets/app_card.dart';
import '../../widgets/screen_app_bar.dart';
import '../../widgets/state_views.dart';
import 'journal_detail_screen.dart';

class JournalHistoryScreen extends ConsumerStatefulWidget {
  const JournalHistoryScreen({super.key});

  @override
  ConsumerState<JournalHistoryScreen> createState() =>
      _JournalHistoryScreenState();
}

class _JournalHistoryScreenState extends ConsumerState<JournalHistoryScreen> {
  late DateTime _focused;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _focused = DateTime(now.year, now.month, 1);
  }

  Map<DateTime, Journal> _indexByDate(List<Journal> journals) {
    final map = <DateTime, Journal>{};
    for (final journal in journals) {
      final date = DateTime.tryParse(journal.date);
      if (date != null) {
        map[DateTime(date.year, date.month, date.day)] = journal;
      }
    }
    return map;
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(journalControllerProvider);

    return Scaffold(
      appBar: const ScreenAppBar(
        title: 'Riwayat jurnal',
        subtitle: 'Lihat perjalanan kebiasaanmu',
        icon: LucideIcons.calendarDays,
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(journalControllerProvider.notifier).load(),
        child: _buildBody(context, ref, state),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, JournalState state) {
    if (state.isLoadingHistory && state.history.isEmpty) {
      return StateViews.loading(message: 'Memuat riwayat…');
    }
    if (state.error != null && state.history.isEmpty) {
      return StateViews.error(
        context,
        state.error!,
        () => ref.read(journalControllerProvider.notifier).load(),
      );
    }
    if (state.history.isEmpty) {
      return StateViews.empty(
        context,
        'Belum ada jurnal tercatat. Mulai isi jurnal harianmu.',
      );
    }

    final byDate = _indexByDate(state.history);
    final monthJournals = byDate.entries
        .where(
          (entry) =>
              entry.key.year == _focused.year &&
              entry.key.month == _focused.month,
        )
        .length;

    return ListView(
      padding: const EdgeInsets.fromLTRB(20, AppSpacing.lg, 20, AppSpacing.xxl),
      children: [
        _MonthHeader(
          focused: _focused,
          count: monthJournals,
          onPrev: () => setState(
            () => _focused = DateTime(_focused.year, _focused.month - 1, 1),
          ),
          onNext: () => setState(
            () => _focused = DateTime(_focused.year, _focused.month + 1, 1),
          ),
        ),
        const SizedBox(height: AppSpacing.md),
        _MonthCalendar(
          focused: _focused,
          byDate: byDate,
          onTapDay: (journal) {
            if (journal != null) {
              Navigator.of(context).push(
                MaterialPageRoute<dynamic>(
                  builder: (_) => JournalDetailScreen(id: journal.id),
                ),
              );
            }
          },
        ),
        const SizedBox(height: AppSpacing.lg),
        const SectionTitle('Catatan terbaru'),
        const SizedBox(height: AppSpacing.md),
        ...state.history.map((journal) => _HistoryItem(journal: journal)),
        if (state.hasMore)
          Padding(
            padding: const EdgeInsets.only(top: AppSpacing.md),
            child: Center(
              child: state.isLoadingHistory
                  ? const CircularProgressIndicator()
                  : TextButton(
                      onPressed: () => ref
                          .read(journalControllerProvider.notifier)
                          .loadMore(),
                      child: const Text('Muat lebih banyak'),
                    ),
            ),
          ),
      ],
    );
  }
}

class _MonthHeader extends StatelessWidget {
  const _MonthHeader({
    required this.focused,
    required this.count,
    required this.onPrev,
    required this.onNext,
  });

  final DateTime focused;
  final int count;
  final VoidCallback onPrev;
  final VoidCallback onNext;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return AppCard(
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppDateFormat.bulanTahun(focused),
                  style: Theme.of(context).textTheme.titleMedium
                      ?.copyWith(fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 2),
                Text(
                  '$count jurnal tercatat',
                  style: Theme.of(context).textTheme.bodySmall
                      ?.copyWith(color: colors.onSurfaceVariant),
                ),
              ],
            ),
          ),
          IconButton.filledTonal(
            onPressed: onPrev,
            tooltip: 'Bulan sebelumnya',
            icon: const Icon(LucideIcons.chevronLeft),
          ),
          const SizedBox(width: AppSpacing.xs),
          IconButton.filledTonal(
            onPressed: onNext,
            tooltip: 'Bulan berikutnya',
            icon: const Icon(LucideIcons.chevronRight),
          ),
        ],
      ),
    );
  }
}

class _MonthCalendar extends StatelessWidget {
  const _MonthCalendar({
    required this.focused,
    required this.byDate,
    required this.onTapDay,
  });

  final DateTime focused;
  final Map<DateTime, Journal> byDate;
  final ValueChanged<Journal?> onTapDay;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final today = DateTime.now();
    final firstWeekday = DateTime(focused.year, focused.month, 1).weekday % 7;
    final daysInMonth = DateUtils.getDaysInMonth(focused.year, focused.month);

    final cells = <Widget>[];
    for (var i = 0; i < firstWeekday; i++) {
      cells.add(const SizedBox.shrink());
    }
    for (var day = 1; day <= daysInMonth; day++) {
      final date = DateTime(focused.year, focused.month, day);
      final journal = byDate[date];
      final isToday =
          date.year == today.year &&
          date.month == today.month &&
          date.day == today.day;
      cells.add(
        _DayCell(
          day: day,
          journal: journal,
          isToday: isToday,
          dimmed: date.isAfter(today),
          onTap: () => onTapDay(journal),
        ),
      );
    }

    return AppCard(
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: const [
              _WeekdayLabel('Min'),
              _WeekdayLabel('Sen'),
              _WeekdayLabel('Sel'),
              _WeekdayLabel('Rab'),
              _WeekdayLabel('Kam'),
              _WeekdayLabel('Jum'),
              _WeekdayLabel('Sab'),
            ],
          ),
          const SizedBox(height: 8),
          GridView.count(
            crossAxisCount: 7,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 8,
            crossAxisSpacing: 8,
            childAspectRatio: 1,
            children: cells,
          ),
          const SizedBox(height: 12),
          Wrap(
            alignment: WrapAlignment.center,
            spacing: 16,
            runSpacing: 8,
            children: [
              _Legend(color: AppColors.primary500, label: 'Lengkap'),
              _Legend(color: AppColors.amber, label: 'Sebagian'),
              _Legend(color: colors.surfaceContainerHighest, label: 'Kosong'),
            ],
          ),
        ],
      ),
    );
  }
}

class _WeekdayLabel extends StatelessWidget {
  const _WeekdayLabel(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: Theme.of(context).textTheme.labelSmall?.copyWith(
        color: Theme.of(context).colorScheme.onSurfaceVariant,
        fontWeight: FontWeight.w700,
      ),
    );
  }
}

class _DayCell extends StatelessWidget {
  const _DayCell({
    required this.day,
    required this.journal,
    required this.isToday,
    required this.dimmed,
    required this.onTap,
  });

  final int day;
  final Journal? journal;
  final bool isToday;
  final bool dimmed;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    Color? background;
    Color? foreground;
    if (journal != null) {
      if (journal!.isFullyCompleted) {
        background = AppColors.primary500;
        foreground = Colors.white;
      } else {
        background = AppColors.amber;
        foreground = Colors.white;
      }
    } else if (dimmed) {
      background = colors.surfaceContainerLow;
      foreground = colors.onSurfaceVariant.withValues(alpha: 0.4);
    } else {
      background = colors.surfaceContainerHighest;
      foreground = colors.onSurfaceVariant;
    }

    final child = Container(
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(10),
        border: isToday
            ? Border.all(color: AppColors.primary500, width: 2)
            : null,
      ),
      child: Center(
        child: Text(
          '$day',
          style: TextStyle(
            color: foreground,
            fontWeight: FontWeight.w700,
            fontSize: 13,
          ),
        ),
      ),
    );

    return journal != null
        ? InkWell(
            onTap: onTap,
            borderRadius: BorderRadius.circular(10),
            child: child,
          )
        : child;
  }
}

class _Legend extends StatelessWidget {
  const _Legend({required this.color, required this.label});

  final Color color;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(4),
          ),
        ),
        const SizedBox(width: 6),
        Text(label, style: Theme.of(context).textTheme.labelSmall),
      ],
    );
  }
}

class _HistoryItem extends StatelessWidget {
  const _HistoryItem({required this.journal});

  final Journal journal;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final tone = journal.isFullyCompleted
        ? AppColors.primary500
        : journal.completed > 0
        ? AppColors.amber
        : colors.onSurfaceVariant;
    final date = DateTime.tryParse(journal.date) ?? DateTime.now();

    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.md),
      child: AppCard(
        padding: const EdgeInsets.all(AppSpacing.md),
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.lg),
          onTap: () => Navigator.of(context).push(
            MaterialPageRoute<dynamic>(
              builder: (_) => JournalDetailScreen(id: journal.id),
            ),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      AppDateFormat.hariTanggal(date),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${journal.completed} dari ${Habits.total} kebiasaan'
                      '${journal.isSubmitted ? '  •  Terkirim' : ''}',
                      style: Theme.of(context).textTheme.bodyMedium
                          ?.copyWith(color: colors.onSurfaceVariant),
                    ),
                  ],
                ),
              ),
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: tone.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Icon(
                  journal.isFullyCompleted
                      ? LucideIcons.trophy
                      : LucideIcons.listChecks,
                  color: tone,
                  size: 22,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
