import 'dart:async';
import 'dart:typed_data';

import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';
import 'auth_token_interceptor.dart';

typedef JsonMap = Map<String, Object?>;

class ApiClient {
  ApiClient({
    required AppConfig config,
    required TokenStorage tokenStorage,
    FutureOr<void> Function()? onUnauthorized,
    Dio? dio,
  }) : _dio =
           dio ??
           Dio(
             BaseOptions(
               baseUrl: config.apiBaseUrl,
               connectTimeout: const Duration(seconds: 15),
               receiveTimeout: const Duration(seconds: 20),
               sendTimeout: const Duration(seconds: 20),
               headers: const <String, Object>{
                 'Accept': 'application/json',
                 'Content-Type': 'application/json',
               },
             ),
           ) {
    _dio.interceptors.add(
      AuthTokenInterceptor(
        tokenStorage: tokenStorage,
        onUnauthorized: onUnauthorized,
      ),
    );
  }

  final Dio _dio;

  Future<JsonMap> get(String path, {Map<String, Object?>? queryParameters}) {
    return _request(
      () => _dio.get<Object?>(path, queryParameters: queryParameters),
    );
  }

  Future<JsonMap> post(String path, {Object? data}) {
    return _request(() => _dio.post<Object?>(path, data: data));
  }

  Future<JsonMap> put(String path, {Object? data}) {
    return _request(() => _dio.put<Object?>(path, data: data));
  }

  /// Uploads a file via multipart/form-data and parses the JSON envelope.
  Future<JsonMap> uploadFile(
    String path,
    String fileField,
    String filePath, {
    String? filename,
    Map<String, Object?>? extra,
  }) {
    return _request(() async {
      final formData = FormData.fromMap(<String, Object?>{
        fileField: await MultipartFile.fromFile(filePath, filename: filename),
        ...?extra,
      });
      return _dio.post<Object?>(path, data: formData);
    });
  }

  /// Downloads raw bytes (e.g. a journal/profile photo) using an absolute URL.
  /// Throws [ApiException] on a non-success status.
  Future<Uint8List> getBytes(String url) async {
    try {
      final response = await _dio.get<Uint8List>(
        url,
        options: Options(responseType: ResponseType.bytes),
      );
      final payload = response.data;
      if (payload == null) {
        throw const ApiException(
          kind: ApiFailureKind.server,
          message: 'Berkas tidak dapat diunduh.',
        );
      }
      return payload;
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    } on ApiException {
      rethrow;
    } on Object {
      throw const ApiException(
        kind: ApiFailureKind.unknown,
        message: 'Berkas tidak dapat diunduh.',
      );
    }
  }

  Future<JsonMap> _request(Future<Response<Object?>> Function() send) async {
    try {
      final response = await send();
      final payload = response.data;
      if (payload is! Map<Object?, Object?>) {
        throw const ApiException(
          kind: ApiFailureKind.server,
          message: 'Respons layanan tidak dapat dibaca.',
        );
      }

      return payload.map((key, value) => MapEntry(key.toString(), value));
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  void close() => _dio.close(force: true);
}
