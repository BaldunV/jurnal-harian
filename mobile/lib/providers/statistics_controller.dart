import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_exception.dart';
import '../models/statistics.dart';
import '../services/statistics_service.dart';
import 'service_providers.dart';

class StatisticsState {
  const StatisticsState({
    this.period = 'week',
    this.data,
    this.isLoading = false,
    this.error,
  });

  final String period;
  final Statistics? data;
  final bool isLoading;
  final String? error;

  StatisticsState copyWith({
    String? period,
    Statistics? data,
    bool? isLoading,
    String? error,
    bool clearError = false,
  }) {
    return StatisticsState(
      period: period ?? this.period,
      data: data ?? this.data,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
    );
  }
}

final statisticsControllerProvider =
    NotifierProvider<StatisticsController, StatisticsState>(
      StatisticsController.new,
    );

class StatisticsController extends Notifier<StatisticsState> {
  @override
  StatisticsState build() {
    Future.microtask(load);
    return const StatisticsState();
  }

  StatisticsService get _service => ref.read(statisticsServiceProvider);

  Future<void> setPeriod(String period) async {
    if (state.period == period) {
      return;
    }
    state = state.copyWith(period: period);
    await load();
  }

  Future<void> load() async {
    final period = state.period;
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final data = await _service.fetch(period);
      state = state.copyWith(data: data, isLoading: false);
    } on ApiException catch (error) {
      state = state.copyWith(isLoading: false, error: error.message);
    } on Object {
      state = state.copyWith(
        isLoading: false,
        error: 'Statistik belum dapat dimuat.',
      );
    }
  }
}
