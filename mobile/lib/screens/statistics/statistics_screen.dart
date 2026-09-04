import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../../core/constants/habits.dart';
import '../../core/theme/design_tokens.dart';
import '../../core/theme/habit_palette.dart';
import '../../models/statistics.dart';
import '../../providers/statistics_controller.dart';
import '../../widgets/app_card.dart';
import '../../widgets/progress_ring.dart';
import '../../widgets/screen_app_bar.dart';
import '../../widgets/state_views.dart';

class StatisticsScreen extends ConsumerStatefulWidget {
  const StatisticsScreen({super.key});

  @override
  ConsumerState<StatisticsScreen> createState() => _StatisticsScreenState();
}

class _StatisticsScreenState extends ConsumerState<StatisticsScreen> {
  @override
  Widget build(BuildContext context) {
    final state = ref.watch(statisticsControllerProvider);

    return Scaffold(
      appBar: const ScreenAppBar(
        title: 'Statistik',
        subtitle: 'Pantau perkembangan kebiasaanmu',
        icon: LucideIcons.pieChart,
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(statisticsControllerProvider.notifier).load(),
        child: ListView(
          padding: const EdgeInsets.fromLTRB(
            20,
            AppSpacing.lg,
            20,
            AppSpacing.xxl,
          ),
          children: [
            _PeriodToggle(
              period: state.period,
              onChanged: (value) => ref
                  .read(statisticsControllerProvider.notifier)
                  .setPeriod(value),
            ),
            const SizedBox(height: AppSpacing.lg),
            if (state.isLoading && state.data == null)
              StateViews.loading(message: 'Memuat statistik…')
            else if (state.error != null && state.data == null)
              StateViews.error(
                context,
                state.error!,
                () => ref.read(statisticsControllerProvider.notifier).load(),
              )
            else if (state.data != null)
              _StatisticsContent(data: state.data!),
          ],
        ),
      ),
    );
  }
}

class _PeriodToggle extends StatelessWidget {
  const _PeriodToggle({required this.period, required this.onChanged});

  final String period;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final isWeek = period == 'week';
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: colors.surfaceContainer,
        borderRadius: BorderRadius.circular(9999),
      ),
      child: Row(
        children: [
          _ToggleOption(
            label: '7 Hari',
            active: isWeek,
            onTap: () => onChanged('week'),
          ),
          _ToggleOption(
            label: '30 Hari',
            active: !isWeek,
            onTap: () => onChanged('month'),
          ),
        ],
      ),
    );
  }
}

class _ToggleOption extends StatelessWidget {
  const _ToggleOption({
    required this.label,
    required this.active,
    required this.onTap,
  });

  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Expanded(
      child: Semantics(
        button: true,
        selected: active,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(AppRadius.pill),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 180),
            curve: Curves.easeOut,
            padding: const EdgeInsets.symmetric(vertical: 10),
            decoration: BoxDecoration(
              color: active ? colors.primary : Colors.transparent,
              borderRadius: BorderRadius.circular(AppRadius.pill),
            ),
            alignment: Alignment.center,
            child: Text(
              label,
              style: TextStyle(
                fontWeight: FontWeight.w700,
                color: active ? colors.onPrimary : colors.onSurfaceVariant,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class HabitTrendChart extends StatelessWidget {
  const HabitTrendChart({super.key, required this.trendData});

  final List<TrendData> trendData;

  @override
  Widget build(BuildContext context) {
    if (trendData.isEmpty) {
      return const SizedBox(
        height: 220,
        child: Center(child: Text('Belum ada data tren')),
      );
    }
    final barData = trendData
        .map(
          (entry) => BarChartGroupData(
            x: trendData.indexOf(entry),
            barRods: [
              BarChartRodData(
                toY: entry.completed.toDouble(),
                color: AppColors.primary500,
                width: 14,
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(4),
                ),
              ),
            ],
          ),
        )
        .toList();
    final maxCompleted = trendData
        .map((entry) => entry.completed)
        .fold<int>(0, (a, b) => a > b ? a : b);
    return SizedBox(
      height: 220,
      child: BarChart(
        BarChartData(
          barGroups: barData,
          maxY: maxCompleted.toDouble(),
          titlesData: FlTitlesData(
            bottomTitles: AxisTitles(
              sideTitles: SideTitles(
                showTitles: true,
                getTitlesWidget: (value, meta) {
                  final index = value.toInt();
                  if (index >= 0 && index < trendData.length) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Text(
                        trendData[index].date,
                        style: const TextStyle(fontSize: 9),
                      ),
                    );
                  }
                  return const Text('');
                },
              ),
            ),
            leftTitles: const AxisTitles(
              sideTitles: SideTitles(showTitles: false),
            ),
          ),
          gridData: const FlGridData(show: false),
          borderData: FlBorderData(show: false),
          barTouchData: BarTouchData(
            touchTooltipData: BarTouchTooltipData(
              getTooltipItem: (group, groupIndex, rod, rodIndex) {
                return BarTooltipItem(
                  '${rod.toY}',
                  const TextStyle(color: Colors.white),
                );
              },
            ),
          ),
        ),
        duration: const Duration(milliseconds: 400),
      ),
    );
  }
}

class _StatisticsContent extends ConsumerWidget {
  const _StatisticsContent({required this.data});

  final Statistics data;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final colors = Theme.of(context).colorScheme;
    final habits = data.habitStatistics;

    HabitStatistic? lowest;
    for (final habit in habits) {
      if (lowest == null || habit.percentage < lowest.percentage) {
        lowest = habit;
      }
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (lowest != null && lowest.percentage < 100)
          _AlertCard(lowest: lowest),
        if (lowest != null && lowest.percentage < 100)
          const SizedBox(height: AppSpacing.lg),
        AppCard(
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Konsistensi ${data.periodDays} hari',
                      style: Theme.of(context).textTheme.titleMedium
                          ?.copyWith(fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 12),
                    _Metric(
                      icon: LucideIcons.flame,
                      label: 'Streak aktif',
                      value: '${data.currentStreak} hari',
                    ),
                    const SizedBox(height: 8),
                    _Metric(
                      icon: LucideIcons.calendarCheck,
                      label: 'Hari penuh',
                      value: '${data.completedDays}/${data.recordedDays}',
                    ),
                  ],
                ),
              ),
              ProgressRing(
                value: data.recordedDays == 0 ? 0 : data.percentage / 100,
                label: '${data.percentage}%',
                sublabel: 'rata-rata',
                size: 116,
                strokeWidth: 11,
              ),
            ],
          ),
        ),
        const SizedBox(height: AppSpacing.lg),
        if (data.trendData.isNotEmpty) ...[
          const SectionTitle('Tren harian'),
          const SizedBox(height: AppSpacing.md),
          AppCard(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: HabitTrendChart(trendData: data.trendData),
          ),
        ],
        const SizedBox(height: AppSpacing.lg),
        const SectionTitle('Ketaatan per kebiasaan'),
        const SizedBox(height: AppSpacing.md),
        AppCard(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Column(
            children: habits.map((habit) {
              final meta = Habits.byKey(habit.key);
              final tone = HabitPalettes.byKey[habit.key]?.resolve(
                Theme.of(context).brightness,
              );
              return Padding(
                padding: const EdgeInsets.only(bottom: AppSpacing.md),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          meta.icon,
                          size: 20,
                          color: tone?.iconText ?? colors.primary,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            habit.name,
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                        ),
                        Text(
                          '${habit.percentage}%',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    LinearProgressIndicator(
                      value: habit.percentage / 100,
                      minHeight: 8,
                      borderRadius: BorderRadius.circular(8),
                      color: tone?.iconText ?? colors.primary,
                      backgroundColor: colors.surfaceContainer,
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }
}

class SectionTitle extends StatelessWidget {
  const SectionTitle(this.title, {super.key});
  final String title;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Text(
      title,
      style: Theme.of(context).textTheme.titleMedium
          ?.copyWith(fontWeight: FontWeight.w800, color: colors.primary),
    );
  }
}

class _AlertCard extends StatelessWidget {
  const _AlertCard({required this.lowest});

  final HabitStatistic lowest;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Container(
      decoration: BoxDecoration(
        color: colors.tertiaryContainer,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: colors.tertiary.withValues(alpha: 0.45)),
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Row(
        children: [
          Icon(LucideIcons.lightbulb, color: colors.onTertiaryContainer),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Kebiasaan paling sering terlewat',
                  style: TextStyle(
                    fontWeight: FontWeight.w800,
                    color: colors.onTertiaryContainer,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${lowest.name} hanya ${lowest.percentage}% di periode ini. '
                  'Coba jadwalkan di waktu yang konsisten setiap hari!',
                  style: TextStyle(
                    fontSize: 12,
                    color: colors.onTertiaryContainer,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Metric extends StatelessWidget {
  const _Metric({required this.icon, required this.label, required this.value});

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Row(
      children: [
        Icon(icon, size: 18, color: colors.primary),
        const SizedBox(width: 8),
        Text(label, style: Theme.of(context).textTheme.bodyMedium),
        const Spacer(),
        Text(
          value,
          style: Theme.of(context).textTheme.titleMedium
              ?.copyWith(color: colors.onSurface),
        ),
      ],
    );
  }
}
