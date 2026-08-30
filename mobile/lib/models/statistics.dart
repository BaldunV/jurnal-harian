import 'package:flutter/material.dart';

import 'package:jurnal_siswa/core/constants/habits.dart';

class TrendData {
  const TrendData({required this.date, required this.completed});

  factory TrendData.fromJson(Map<String, Object?> json) {
    return TrendData(
      date: (json['date'] as String?) ?? '',
      completed: json['completed'] is num
          ? (json['completed'] as num).toInt()
          : 0,
    );
  }

  final String date;
  final int completed;
}

class HabitStatistic {
  const HabitStatistic({
    required this.key,
    required this.name,
    required this.icon,
    required this.count,
    required this.percentage,
  });

  factory HabitStatistic.fromJson(Map<String, Object?> json) {
    final key = (json['key'] as String?) ?? '';
    return HabitStatistic(
      key: key,
      name: (json['name'] as String?) ?? Habits.byKey(key).label,
      icon: Habits.byKey(key).icon,
      count: json['count'] is num ? (json['count'] as num).toInt() : 0,
      percentage: json['percentage'] is num
          ? (json['percentage'] as num).toInt()
          : 0,
    );
  }

  final String key;
  final String name;
  final IconData icon;
  final int count;
  final int percentage;
}

class Statistics {
  const Statistics({
    required this.period,
    required this.todayProgress,
    required this.todayTotal,
    required this.percentage,
    required this.currentStreak,
    required this.completedDays,
    required this.recordedDays,
    required this.periodDays,
    required this.habitStatistics,
    required this.trendData,
  });

  factory Statistics.fromJson(Map<String, Object?> json) {
    final rawHabits = json['habit_statistics'];
    final habitStatistics = rawHabits is List
        ? rawHabits
              .whereType<Map<Object?, Object?>>()
              .map(
                (item) => HabitStatistic.fromJson(
                  item.map((key, value) => MapEntry(key.toString(), value)),
                ),
              )
              .toList()
        : <HabitStatistic>[];

    final rawTrend = json['trend_data'];
    final trendData = rawTrend is List
        ? rawTrend
              .whereType<Map<Object?, Object?>>()
              .map(
                (item) => TrendData.fromJson(
                  item.map((key, value) => MapEntry(key.toString(), value)),
                ),
              )
              .toList()
        : <TrendData>[];

    return Statistics(
      period: (json['period'] as String?) ?? 'week',
      todayProgress: json['today_progress'] is num
          ? (json['today_progress'] as num).toInt()
          : 0,
      todayTotal: json['today_total'] is num
          ? (json['today_total'] as num).toInt()
          : 7,
      percentage: json['percentage'] is num
          ? (json['percentage'] as num).toInt()
          : 0,
      currentStreak: json['current_streak'] is num
          ? (json['current_streak'] as num).toInt()
          : 0,
      completedDays: json['completed_days'] is num
          ? (json['completed_days'] as num).toInt()
          : 0,
      recordedDays: json['recorded_days'] is num
          ? (json['recorded_days'] as num).toInt()
          : 0,
      periodDays: json['period_days'] is num
          ? (json['period_days'] as num).toInt()
          : 7,
      habitStatistics: habitStatistics,
      trendData: trendData,
    );
  }

  final String period;
  final int todayProgress;
  final int todayTotal;
  final int percentage;
  final int currentStreak;
  final int completedDays;
  final int recordedDays;
  final int periodDays;
  final List<HabitStatistic> habitStatistics;
  final List<TrendData> trendData;
}
