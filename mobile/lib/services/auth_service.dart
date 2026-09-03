import '../core/api/api_client.dart';
import '../core/api/api_exception.dart';
import '../models/auth_models.dart';
import '../models/student.dart';

class AuthService {
  AuthService(this._apiClient, {this.deviceName = 'Jurnal SMK BPPI Android'});

  final ApiClient _apiClient;
  final String deviceName;

  Future<AuthTokenResult> login({
    required String nis,
    required String password,
  }) async {
    final response = await _apiClient.post(
      'auth/login',
      data: <String, Object?>{
        'nis': nis.trim(),
        'password': password,
        'device_name': deviceName,
      },
    );

    return _parse(response, AuthTokenResult.fromJson);
  }

  Future<Student> me() async {
    final response = await _apiClient.get('auth/me');
    return _parse(response, Student.fromJson);
  }

  Future<void> logout() async {
    final response = await _apiClient.post('auth/logout');
    _dataFrom(response);
  }

  T _parse<T>(JsonMap response, T Function(JsonMap data) parser) {
    try {
      return parser(_dataFrom(response));
    } on ApiException {
      rethrow;
    } on Object {
      throw const ApiException(
        kind: ApiFailureKind.server,
        message: 'Respons layanan tidak dapat dibaca.',
      );
    }
  }

  JsonMap _dataFrom(JsonMap response) {
    final data = response['data'];
    if (response['success'] != true || data is! Map<Object?, Object?>) {
      throw const ApiException(
        kind: ApiFailureKind.server,
        message: 'Respons layanan tidak dapat dibaca.',
      );
    }

    return data.map((key, value) => MapEntry(key.toString(), value));
  }
}
