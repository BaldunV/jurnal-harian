import '../core/api/api_client.dart';
import '../core/api/api_exception.dart';
import '../core/api/envelope.dart';
import '../models/student.dart';

class ProfileService {
  ProfileService(this._apiClient);

  final ApiClient _apiClient;

  Future<Student> update({
    required String name,
    required String worshipType,
  }) async {
    final response = await _apiClient.put(
      'me/profile',
      data: <String, Object?>{'name': name, 'worship_type': worshipType},
    );
    return Student.fromJson(extractData(response));
  }

  Future<String> uploadPhoto(String filePath) async {
    final response = await _apiClient.uploadFile(
      'me/profile/photo',
      'photo',
      filePath,
    );
    final data = extractData(response);
    for (final key in const <String>['url', 'photo_url', 'profile_photo_url']) {
      final value = data[key];
      if (value is String && value.isNotEmpty) {
        return value;
      }
    }
    throw const ApiException(
      kind: ApiFailureKind.server,
      message: 'Unggahan foto tidak mengembalikan tautan.',
    );
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    await _apiClient.post(
      'me/change-password',
      data: <String, Object?>{
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': newPassword,
      },
    );
  }
}
