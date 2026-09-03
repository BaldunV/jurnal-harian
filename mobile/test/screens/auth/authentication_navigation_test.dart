import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/app.dart';
import 'package:jurnal_siswa/screens/auth/login_screen.dart';

import 'package:jurnal_siswa/providers/journal_controller.dart';
import 'package:jurnal_siswa/providers/statistics_controller.dart';

import '../../helpers/auth_provider_container.dart';
import '../../helpers/auth_test_data.dart';
import '../../helpers/fake_http_client_adapter.dart';
import '../../helpers/memory_token_storage.dart';

void main() {
  testWidgets('empty login shows local validation without a request', (
    tester,
  ) async {
    final adapter = FakeHttpClientAdapter();
    await _pumpApp(tester, adapter: adapter, storage: MemoryTokenStorage());

    final submit = find.byKey(const Key('login_submit_button'));
    await tester.ensureVisible(submit);
    await tester.tap(submit);
    await tester.pump();

    expect(find.text('NIS wajib diisi.'), findsOneWidget);
    expect(find.text('Password wajib diisi.'), findsOneWidget);
    expect(adapter.requests, isEmpty);
  });

  testWidgets('invalid credentials stay on login with Laravel message', (
    tester,
  ) async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(
        errorEnvelope(
          message: 'NIS atau password yang dimasukkan salah.',
          code: 'invalid_credentials',
        ),
        statusCode: 401,
      );
    await _pumpApp(tester, adapter: adapter, storage: MemoryTokenStorage());

    await _fillAndSubmitLogin(tester);

    expect(find.byType(LoginScreen), findsOneWidget);
    expect(
      find.text('NIS atau password yang dimasukkan salah.'),
      findsOneWidget,
    );
  });

  testWidgets('Laravel validation errors are rendered in the login form', (
    tester,
  ) async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(
        errorEnvelope(
          message: 'Data tidak valid.',
          code: 'validation_error',
          errors: const <String, Object?>{
            'nis': <String>['Format NIS tidak valid.'],
          },
        ),
        statusCode: 422,
      );
    await _pumpApp(tester, adapter: adapter, storage: MemoryTokenStorage());

    await _fillAndSubmitLogin(tester);

    expect(find.text('Data tidak valid.'), findsOneWidget);
    expect(find.text('Format NIS tidak valid.'), findsOneWidget);
  });

  testWidgets('login success replaces auth screens with the app shell', (
    tester,
  ) async {
    final adapter = FakeHttpClientAdapter()
      ..enqueueJson(tokenEnvelope())
      ..enqueueJson(meEnvelope());
    final storage = MemoryTokenStorage();
    await _pumpApp(tester, adapter: adapter, storage: storage);
    await _fillAndSubmitLogin(tester);

    expect(storage.token, '12|plain-token');
    expect(find.byKey(const Key('authenticated_app_shell')), findsOneWidget);
    expect(find.byType(LoginScreen), findsNothing);
  });

  testWidgets('stored valid token opens authenticated navigation', (
    tester,
  ) async {
    final adapter = FakeHttpClientAdapter()..enqueueJson(meEnvelope());
    await _pumpApp(
      tester,
      adapter: adapter,
      storage: MemoryTokenStorage('stored-token'),
    );

    expect(find.byKey(const Key('authenticated_app_shell')), findsOneWidget);
    expect(find.text('Jurnal'), findsOneWidget);
    expect(find.text('Riwayat'), findsOneWidget);
    expect(find.text('Statistik'), findsOneWidget);
    expect(find.text('Profil'), findsOneWidget);
    expect(find.byType(LoginScreen), findsNothing);
  });

  testWidgets('missing token exposes only unauthenticated navigation', (
    tester,
  ) async {
    await _pumpApp(
      tester,
      adapter: FakeHttpClientAdapter(),
      storage: MemoryTokenStorage(),
    );

    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.byKey(const Key('authenticated_app_shell')), findsNothing);
  });
}

Future<void> _pumpApp(
  WidgetTester tester, {
  required FakeHttpClientAdapter adapter,
  required MemoryTokenStorage storage,
}) async {
  final container = createAuthProviderContainer(
    adapter: adapter,
    tokenStorage: storage,
    additionalOverrides: [
      journalControllerProvider.overrideWith(_TestJournalController.new),
      statisticsControllerProvider.overrideWith(_TestStatisticsController.new),
    ],
  );
  addTearDown(container.dispose);
  await tester.pumpWidget(
    UncontrolledProviderScope(container: container, child: const JurnalApp()),
  );
  await tester.pump();
  await tester.pump(const Duration(milliseconds: 800));
}

Future<void> _fillAndSubmitLogin(WidgetTester tester) async {
  await tester.enterText(find.byKey(const Key('login_nis_field')), '20260012');
  await tester.enterText(
    find.byKey(const Key('login_password_field')),
    'secret123',
  );
  final submit = find.byKey(const Key('login_submit_button'));
  await tester.ensureVisible(submit);
  await tester.tap(submit);
  await tester.pumpAndSettle();
}

class _TestJournalController extends JournalController {
  @override
  JournalState build() {
    return const JournalState();
  }
}

class _TestStatisticsController extends StatisticsController {
  @override
  StatisticsState build() {
    return const StatisticsState();
  }
}
