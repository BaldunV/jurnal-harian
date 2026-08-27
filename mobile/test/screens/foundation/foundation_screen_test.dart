import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/core/theme/app_theme.dart';
import 'package:jurnal_siswa/screens/foundation/foundation_screen.dart';
import 'package:jurnal_siswa/widgets/habit_ribbon.dart';

void main() {
  testWidgets('shows the signed-out product foundation', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: const FoundationScreen(state: FoundationState.signedOut),
      ),
    );

    expect(find.text('SMK BPPI'), findsOneWidget);
    expect(find.text('JURNAL 7 KEBIASAAN'), findsOneWidget);
    expect(find.text('Satu hari, tujuh kebiasaan baik.'), findsOneWidget);
    expect(find.byType(HabitRibbon), findsOneWidget);
    expect(find.bySemanticsLabel('Kemajuan tujuh kebiasaan'), findsOneWidget);
  });

  testWidgets('offers a recovery action after a storage failure', (
    tester,
  ) async {
    var retries = 0;
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.light,
        home: FoundationScreen(
          state: FoundationState.storageError,
          onRetry: () => retries += 1,
        ),
      ),
    );

    final retryButton = find.text('Coba lagi');
    await tester.ensureVisible(retryButton);
    await tester.tap(retryButton);

    expect(retries, 1);
  });
}
