import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_exception.dart';
import '../core/notifications/local_notification_service.dart';
import '../models/journal.dart';
import '../models/journal_list.dart';
import '../services/journal_service.dart';
import 'service_providers.dart';

enum JournalOperationStatus { idle, saving, submitting, success, error }

class JournalOperation {
  const JournalOperation(this.status, [this.message]);

  final JournalOperationStatus status;
  final String? message;

  bool get isBusy =>
      status == JournalOperationStatus.saving ||
      status == JournalOperationStatus.submitting;
}

class JournalState {
  const JournalState({
    this.today,
    this.history = const <Journal>[],
    this.historyPage = 1,
    this.hasMore = false,
    this.isLoadingToday = false,
    this.isLoadingHistory = false,
    this.error,
    this.operation = const JournalOperation(JournalOperationStatus.idle),
  });

  final JournalToday? today;
  final List<Journal> history;
  final int historyPage;
  final bool hasMore;
  final bool isLoadingToday;
  final bool isLoadingHistory;
  final String? error;
  final JournalOperation operation;

  JournalState copyWith({
    JournalToday? today,
    bool clearToday = false,
    List<Journal>? history,
    int? historyPage,
    bool? hasMore,
    bool? isLoadingToday,
    bool? isLoadingHistory,
    String? error,
    bool clearError = false,
    JournalOperation? operation,
  }) {
    return JournalState(
      today: clearToday ? null : (today ?? this.today),
      history: history ?? this.history,
      historyPage: historyPage ?? this.historyPage,
      hasMore: hasMore ?? this.hasMore,
      isLoadingToday: isLoadingToday ?? this.isLoadingToday,
      isLoadingHistory: isLoadingHistory ?? this.isLoadingHistory,
      error: clearError ? null : (error ?? this.error),
      operation: operation ?? this.operation,
    );
  }
}

final journalControllerProvider =
    NotifierProvider<JournalController, JournalState>(JournalController.new);

class JournalController extends Notifier<JournalState> {
  @override
  JournalState build() {
    Future.microtask(load);
    return const JournalState();
  }

  JournalService get _service => ref.read(journalServiceProvider);

  Future<void> load() async {
    state = state.copyWith(
      isLoadingToday: true,
      isLoadingHistory: true,
      error: null,
      clearError: true,
    );
    try {
      final results = await Future.wait<dynamic>([
        _service.fetchToday(),
        _service.fetchJournals(page: 1),
      ]);
      final today = results[0] as JournalToday;
      final page = results[1] as JournalPage;
      state = state.copyWith(
        today: today,
        history: page.items,
        historyPage: page.currentPage,
        hasMore: page.hasMore,
        isLoadingToday: false,
        isLoadingHistory: false,
      );

      try {
        await LocalNotificationService.instance.configureForToday(
          isSubmitted: today.journal?.isSubmitted ?? false,
        );
      } on Object {
        // Gagal menjadwalkan notifikasi tidak boleh menggagalkan pemuatan jurnal.
      }
    } on ApiException catch (error) {
      state = state.copyWith(
        isLoadingToday: false,
        isLoadingHistory: false,
        error: error.message,
      );
    } on Object {
      state = state.copyWith(
        isLoadingToday: false,
        isLoadingHistory: false,
        error: 'Jurnal belum dapat dimuat.',
      );
    }
  }

  Future<void> loadMore() async {
    if (state.isLoadingHistory || !state.hasMore) {
      return;
    }
    state = state.copyWith(isLoadingHistory: true, clearError: true);
    try {
      final page = await _service.fetchJournals(page: state.historyPage + 1);
      state = state.copyWith(
        history: <Journal>[...state.history, ...page.items],
        historyPage: page.currentPage,
        hasMore: page.hasMore,
        isLoadingHistory: false,
      );
    } on ApiException catch (error) {
      state = state.copyWith(isLoadingHistory: false, error: error.message);
    } on Object {
      state = state.copyWith(
        isLoadingHistory: false,
        error: 'Halaman berikutnya belum dapat dimuat.',
      );
    }
  }

  Future<Journal> saveDraft(JournalDraft draft, {int? existingId}) async {
    state = state.copyWith(
      operation: const JournalOperation(JournalOperationStatus.saving),
    );
    try {
      final saved = existingId == null
          ? await _service.save(draft)
          : await _service.update(draft);
      state = state.copyWith(
        today: JournalToday(date: state.today?.date ?? '', journal: saved),
        operation: const JournalOperation(JournalOperationStatus.success),
      );
      return saved;
    } on ApiException catch (error) {
      state = state.copyWith(
        operation: JournalOperation(
          JournalOperationStatus.error,
          error.message,
        ),
      );
      rethrow;
    } on Object {
      state = state.copyWith(
        operation: const JournalOperation(
          JournalOperationStatus.error,
          'Catatan belum tersimpan.',
        ),
      );
      rethrow;
    }
  }

  Future<Journal> submit(JournalDraft draft, {int? existingId}) async {
    state = state.copyWith(
      operation: const JournalOperation(JournalOperationStatus.submitting),
    );
    try {
      if (existingId == null) {
        await _service.save(draft);
      } else {
        await _service.update(draft);
      }
      final submitted = await _service.submit(draft);
      state = state.copyWith(
        today: JournalToday(date: state.today?.date ?? '', journal: submitted),
        operation: const JournalOperation(JournalOperationStatus.success),
      );

      try {
        await LocalNotificationService.instance.journalSubmitted();
      } on Object {
        // Submit ke server sudah berhasil.
        // Abaikan kegagalan notifikasi.
      }

      return submitted;
    } on ApiException catch (error) {
      state = state.copyWith(
        operation: JournalOperation(
          JournalOperationStatus.error,
          error.message,
        ),
      );
      rethrow;
    } on Object {
      state = state.copyWith(
        operation: const JournalOperation(
          JournalOperationStatus.error,
          'Jurnal belum terkirim.',
        ),
      );
      rethrow;
    }
  }

  Future<String> uploadPhoto(
    int journalId,
    String type,
    String filePath,
  ) async {
    return _service.uploadPhoto(journalId, type, filePath);
  }
}
