import 'package:flutter_riverpod/flutter_riverpod.dart';

import './api_client_provider.dart';
import '../services/journal_service.dart';
import '../services/profile_service.dart';
import '../services/statistics_service.dart';

final journalServiceProvider = Provider<JournalService>(
  (ref) => JournalService(ref.watch(apiClientProvider)),
);

final statisticsServiceProvider = Provider<StatisticsService>(
  (ref) => StatisticsService(ref.watch(apiClientProvider)),
);

final profileServiceProvider = Provider<ProfileService>(
  (ref) => ProfileService(ref.watch(apiClientProvider)),
);
