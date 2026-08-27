import 'dart:collection';
import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:jurnal_siswa/core/api/api_client.dart';
import 'package:jurnal_siswa/core/config/app_config.dart';
import 'package:jurnal_siswa/core/storage/token_storage.dart';

class FakeHttpClientAdapter implements HttpClientAdapter {
  final Queue<_QueuedResponse> _responses = Queue<_QueuedResponse>();
  final List<RequestOptions> requests = <RequestOptions>[];

  void enqueueJson(Map<String, Object?> body, {int statusCode = 200}) {
    _responses.add(_QueuedResponse.json(body, statusCode));
  }

  void enqueueConnectionError() {
    _responses.add(const _QueuedResponse.connectionError());
  }

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async {
    requests.add(options);
    if (_responses.isEmpty) {
      throw StateError('No fake response queued for ${options.path}.');
    }

    final response = _responses.removeFirst();
    if (response.isConnectionError) {
      throw DioException(
        requestOptions: options,
        type: DioExceptionType.connectionError,
        error: const SocketExceptionForTest(),
      );
    }

    return ResponseBody.fromString(
      jsonEncode(response.body),
      response.statusCode,
      headers: <String, List<String>>{
        Headers.contentTypeHeader: <String>[Headers.jsonContentType],
      },
    );
  }

  @override
  void close({bool force = false}) {}
}

class SocketExceptionForTest implements Exception {
  const SocketExceptionForTest();
}

class _QueuedResponse {
  const _QueuedResponse.json(this.body, this.statusCode)
    : isConnectionError = false;

  const _QueuedResponse.connectionError()
    : body = const <String, Object?>{},
      statusCode = 0,
      isConnectionError = true;

  final Map<String, Object?> body;
  final int statusCode;
  final bool isConnectionError;
}

ApiClient createTestApiClient({
  required FakeHttpClientAdapter adapter,
  required TokenStorage tokenStorage,
  Future<void> Function()? onUnauthorized,
}) {
  final dio = Dio(
    BaseOptions(
      baseUrl: 'https://api.example.sch.id/api/',
      headers: const <String, Object>{
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  )..httpClientAdapter = adapter;

  return ApiClient(
    config: AppConfig(apiBaseUrl: 'https://api.example.sch.id/api'),
    tokenStorage: tokenStorage,
    onUnauthorized: onUnauthorized,
    dio: dio,
  );
}
