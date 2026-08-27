import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/core/api/api_client.dart';
import 'package:jurnal_siswa/core/api/api_exception.dart';
import 'package:jurnal_siswa/core/config/app_config.dart';

import '../../helpers/memory_token_storage.dart';

void main() {
  test('adds a bearer token and clears it after a 401 response', () async {
    final storage = MemoryTokenStorage('secret-token');
    final adapter = _UnauthorizedAdapter();
    final dio = Dio(BaseOptions(baseUrl: 'https://api.example.sch.id/api/'))
      ..httpClientAdapter = adapter;
    var unauthorizedCalls = 0;
    final client = ApiClient(
      config: AppConfig(apiBaseUrl: 'https://api.example.sch.id/api'),
      tokenStorage: storage,
      onUnauthorized: () async {
        unauthorizedCalls += 1;
      },
      dio: dio,
    );
    addTearDown(client.close);

    await expectLater(
      client.get('me'),
      throwsA(
        isA<ApiException>().having(
          (error) => error.kind,
          'kind',
          ApiFailureKind.unauthenticated,
        ),
      ),
    );

    expect(adapter.authorization, 'Bearer secret-token');
    expect(storage.token, isNull);
    expect(storage.clearCount, 1);
    expect(unauthorizedCalls, 1);
  });

  test('preserves the 401 when secure cleanup fails', () async {
    final storage = _FailingTokenStorage('stale-token');
    final dio = Dio(BaseOptions(baseUrl: 'https://api.example.sch.id/api/'))
      ..httpClientAdapter = _UnauthorizedAdapter();
    var unauthorizedCalls = 0;
    final client = ApiClient(
      config: AppConfig(apiBaseUrl: 'https://api.example.sch.id/api'),
      tokenStorage: storage,
      onUnauthorized: () => unauthorizedCalls += 1,
      dio: dio,
    );
    addTearDown(client.close);

    await expectLater(
      client.get('me'),
      throwsA(
        isA<ApiException>().having(
          (error) => error.kind,
          'kind',
          ApiFailureKind.unauthenticated,
        ),
      ),
    );

    expect(unauthorizedCalls, 1);
  });
}

class _FailingTokenStorage extends MemoryTokenStorage {
  _FailingTokenStorage(super.token);

  @override
  Future<void> clearToken() =>
      Future<void>.error(StateError('secure storage unavailable'));
}

class _UnauthorizedAdapter implements HttpClientAdapter {
  String? authorization;

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async {
    authorization = options.headers['Authorization'] as String?;

    return ResponseBody.fromString(
      '{"success":false,"message":"Sesi sudah berakhir."}',
      401,
      headers: <String, List<String>>{
        Headers.contentTypeHeader: <String>[Headers.jsonContentType],
      },
    );
  }

  @override
  void close({bool force = false}) {}
}
