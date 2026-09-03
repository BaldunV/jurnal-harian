import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/core/constants/habits.dart';
import 'package:jurnal_siswa/models/journal.dart';
import 'package:jurnal_siswa/models/statistics.dart';
import 'package:jurnal_siswa/models/student.dart';
import 'package:jurnal_siswa/providers/journal_controller.dart';
import 'package:jurnal_siswa/providers/statistics_controller.dart';
import 'package:jurnal_siswa/screens/journal/journal_dashboard_screen.dart';
import 'package:jurnal_siswa/screens/statistics/statistics_screen.dart';
import 'package:jurnal_siswa/widgets/buttons.dart';
import 'package:jurnal_siswa/widgets/habit_card.dart';
import 'package:jurnal_siswa/widgets/habit_form_card.dart';
import 'package:jurnal_siswa/widgets/neon_checkbox.dart';

const _student = Student(
  id: 1,
  nis: '20260012',
  name: 'Budi',
  className: 'XII RPL',
  role: 'student',
  worshipType: 'muslim',
);

/// Fully-completed journal; [isSubmitted] toggles the locked status chip.
Journal _journal({bool isSubmitted = true}) => Journal(
  id: 10,
  date: '2026-08-29',
  bangunPagi: true,
  beribadah: true,
  berolahraga: true,
  makanSehat: true,
  gemarBelajar: true,
  bermasyarakat: true,
  tidurCepat: true,
  completedCount: 7,
  isFullyCompleted: true,
  isSubmitted: isSubmitted,
);

class _FakeJournalController extends JournalController {
  _FakeJournalController({this.isSubmitted = true});

  final bool isSubmitted;

  @override
  JournalState build() => JournalState(
    today: JournalToday(
      date: '2026-08-29',
      journal: _journal(isSubmitted: isSubmitted),
    ),
  );

  @override
  Future<void> load() async {}
}

class _FakeStatisticsController extends StatisticsController {
  @override
  StatisticsState build() => StatisticsState(data: _sampleStatistics());

  static Statistics _sampleStatistics() =>
      Statistics.fromJson(<String, Object?>{
        'period': 'week',
        'today_progress': 5,
        'today_total': 7,
        'percentage': 71,
        'current_streak': 4,
        'completed_days': 5,
        'recorded_days': 7,
        'period_days': 7,
        'habit_statistics': <Object?>[
          {
            'key': 'bangun_pagi',
            'name': 'Bangun Pagi',
            'count': 6,
            'percentage': 85,
          },
          {
            'key': 'beribadah',
            'name': 'Beribadah',
            'count': 7,
            'percentage': 100,
          },
          {
            'key': 'berolahraga',
            'name': 'Berolahraga',
            'count': 4,
            'percentage': 57,
          },
          {
            'key': 'makan_sehat',
            'name': 'Makan Sehat',
            'count': 5,
            'percentage': 71,
          },
          {
            'key': 'gemar_belajar',
            'name': 'Gemar Belajar',
            'count': 3,
            'percentage': 43,
          },
          {
            'key': 'bermasyarakat',
            'name': 'Bermasyarakat',
            'count': 5,
            'percentage': 71,
          },
          {
            'key': 'tidur_cepat',
            'name': 'Tidur Cepat',
            'count': 6,
            'percentage': 85,
          },
        ],
      });
}

void main() {
  testWidgets('dashboard progress card never overflows at phone widths', (
    tester,
  ) async {
    final container = ProviderContainer(
      overrides: [
        journalControllerProvider.overrideWith(() => _FakeJournalController()),
      ],
    );
    addTearDown(container.dispose);

    for (final width in const <double>[360, 390, 412]) {
      await tester.pumpWidget(
        Directionality(
          textDirection: TextDirection.ltr,
          child: SizedBox(
            width: width,
            child: UncontrolledProviderScope(
              container: container,
              child: const MaterialApp(
                home: JournalDashboardScreen(student: _student),
              ),
            ),
          ),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 800));

      expect(
        tester.takeException(),
        isNull,
        reason: 'RenderFlex overflow at width $width',
      );
      // The submitted-journal status chip is the widest text in the card.
      expect(find.text('Terkunci'), findsOneWidget);
      expect(find.text('dari 7 kebiasaan'), findsOneWidget);
      // Horizontal progress bar renders.
      expect(find.byType(Row), findsAtLeast(1));
    }
  });

  testWidgets(
    'dashboard shows Sempurna badge at 100% without a percent label below',
    (tester) async {
      final container = ProviderContainer(
        overrides: [
          journalControllerProvider.overrideWith(
            () => _FakeJournalController(isSubmitted: false),
          ),
        ],
      );
      addTearDown(container.dispose);

      await tester.pumpWidget(
        Directionality(
          textDirection: TextDirection.ltr,
          child: SizedBox(
            width: 360,
            child: UncontrolledProviderScope(
              container: container,
              child: const MaterialApp(
                home: JournalDashboardScreen(student: _student),
              ),
            ),
          ),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 800));

      expect(find.text('Sempurna!'), findsOneWidget);
      expect(find.text('Terkunci'), findsNothing);
      // No numeric percent label below the bar (the badge replaces it).
      expect(find.textContaining('% Selesai'), findsNothing);
    },
  );

  testWidgets('GradientButton shows spinner while loading', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: GradientButton(
            label: 'Submit',
            isLoading: true,
            onPressed: () {},
          ),
        ),
      ),
    );
    await tester.pump();

    expect(find.byType(CircularProgressIndicator), findsOneWidget);
    // Spinner replaces the label text when loading.
    expect(find.text('Submit'), findsNothing);
  });

  testWidgets('SaveGlowButton tap is not intercepted by shine layer', (
    tester,
  ) async {
    var tapped = false;
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SaveGlowButton(label: 'Simpan', onPressed: () => tapped = true),
        ),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('Simpan'));
    await tester.pumpAndSettle();
    expect(tapped, isTrue);
  });

  testWidgets('habit card checkbox reflects completed state and toggles', (
    tester,
  ) async {
    var toggled = false;
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: HabitCheckCard(
            habit: Habits.bangunPagi,
            done: true,
            onChanged: (value) => toggled = value,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Bangun Pagi'), findsOneWidget);
    expect(tester.takeException(), isNull);

    // Tapping the neon checkbox reports the toggled (false) value.
    await tester.tap(find.byType(NeonCheckbox));
    await tester.pumpAndSettle();
    expect(toggled, isFalse);
  });

  testWidgets('habit form checkbox toggling does not break journal state', (
    tester,
  ) async {
    var lastValue = true;
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: HabitFormCard(
            habit: Habits.berolahraga,
            value: true,
            onChanged: (value) => lastValue = value,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);

    await tester.tap(find.byType(NeonCheckbox));
    await tester.pumpAndSettle();
    expect(lastValue, isFalse);
  });

  testWidgets('statistics screen renders without clipping at 360px', (
    tester,
  ) async {
    final container = ProviderContainer(
      overrides: [
        statisticsControllerProvider.overrideWith(
          () => _FakeStatisticsController(),
        ),
      ],
    );
    addTearDown(container.dispose);

    await tester.pumpWidget(
      Directionality(
        textDirection: TextDirection.ltr,
        child: SizedBox(
          width: 360,
          child: UncontrolledProviderScope(
            container: container,
            child: MaterialApp(home: const StatisticsScreen()),
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(tester.takeException(), isNull);
    expect(find.text('Ketaatan per kebiasaan'), findsOneWidget);
    // All seven habit rows render.
    expect(find.text('Berolahraga'), findsOneWidget);
    expect(find.text('Tidur Cepat'), findsOneWidget);
  });
}
