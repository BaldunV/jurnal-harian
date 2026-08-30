import '../core/api/api_client.dart';
import '../core/api/api_exception.dart';
import '../core/api/envelope.dart';
import '../models/journal.dart';
import '../models/journal_list.dart';

class JournalService {
  JournalService(this._apiClient);

  final ApiClient _apiClient;

  Future<JournalToday> fetchToday() async {
    final response = await _apiClient.get('me/journal/today');
    return JournalToday.fromJson(extractData(response));
  }

  Future<JournalPage> fetchJournals({int page = 1}) async {
    final response = await _apiClient.get(
      'me/journals',
      queryParameters: <String, Object?>{'page': page},
    );

    if (response['success'] != true) {
      throw const ApiException(
        kind: ApiFailureKind.server,
        message: 'Respons layanan tidak dapat dibaca.',
      );
    }

    return JournalPage.fromJson(response);
  }

  Future<Journal> fetchDetail(int id) async {
    final response = await _apiClient.get('me/journals/$id');
    return Journal.fromJson(extractData(response));
  }

  Future<Journal> save(JournalDraft draft) async {
    final response = await _apiClient.post('me/journal', data: draft.toJson());
    return Journal.fromJson(extractData(response));
  }

  Future<Journal> update(JournalDraft draft) async {
    final response = await _apiClient.put('me/journal', data: draft.toJson());
    return Journal.fromJson(extractData(response));
  }

  Future<Journal> submit(JournalDraft draft) async {
    final response = await _apiClient.post(
      'me/journal/submit',
      data: draft.toJson(),
    );
    return Journal.fromJson(extractData(response));
  }

  /// Uploads a photo for a habit and returns the resulting public URL.
  Future<String> uploadPhoto(
    int journalId,
    String type,
    String filePath,
  ) async {
    final response = await _apiClient.uploadFile(
      'me/journals/$journalId/photos/$type',
      'photo',
      filePath,
    );
    final url = _photoUrl(extractData(response));
    if (url.isEmpty) {
      throw const ApiException(
        kind: ApiFailureKind.server,
        message: 'Unggahan foto tidak mengembalikan tautan.',
      );
    }
    return url;
  }

  static String _photoUrl(JsonMap data) {
    for (final key in const <String>[
      'url',
      'photo_url',
      'olahraga_photo_url',
      'makan_photo_url',
    ]) {
      final value = data[key];
      if (value is String && value.isNotEmpty) {
        return value;
      }
    }
    return '';
  }
}
