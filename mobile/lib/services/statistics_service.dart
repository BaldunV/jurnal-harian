import '../core/api/api_client.dart';
import '../core/api/envelope.dart';
import '../models/statistics.dart';

class StatisticsService {
  StatisticsService(this._apiClient);

  final ApiClient _apiClient;

  Future<Statistics> fetch(String period) async {
    final response = await _apiClient.get(
      'me/statistics',
      queryParameters: <String, Object?>{'period': period},
    );
    return Statistics.fromJson(extractData(response));
  }
}
