import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/core/api/api_exception.dart';

void main() {
  test('maps Laravel validation errors', () {
    final request = RequestOptions(path: '/api/auth/login');
    final exception = DioException(
      requestOptions: request,
      response: Response<Object?>(
        requestOptions: request,
        statusCode: 422,
        data: const <String, Object?>{
          'success': false,
          'message': 'Data tidak valid.',
          'errors': <String, Object?>{
            'nis': <String>['NIS wajib diisi.'],
          },
        },
      ),
      type: DioExceptionType.badResponse,
    );

    final result = ApiException.fromDio(exception);

    expect(result.kind, ApiFailureKind.validation);
    expect(result.statusCode, 422);
    expect(result.message, 'Data tidak valid.');
    expect(result.errors['nis'], <String>['NIS wajib diisi.']);
  });

  test('maps a stable authentication code', () {
    final request = RequestOptions(path: '/api/auth/login');
    final exception = DioException(
      requestOptions: request,
      response: Response<Object?>(
        requestOptions: request,
        statusCode: 401,
        data: const <String, Object?>{
          'message': 'Sesi sudah berakhir.',
          'code': 'token_expired',
        },
      ),
      type: DioExceptionType.badResponse,
    );

    final result = ApiException.fromDio(exception);

    expect(result.kind, ApiFailureKind.unauthenticated);
    expect(result.requiresAuthentication, isTrue);
    expect(result.code, 'token_expired');
  });

  test('distinguishes timeout and offline failures', () {
    final request = RequestOptions(path: '/api/me');

    final timeout = ApiException.fromDio(
      DioException(
        requestOptions: request,
        type: DioExceptionType.connectionTimeout,
      ),
    );
    final offline = ApiException.fromDio(
      DioException(
        requestOptions: request,
        error: const SocketException('offline'),
        type: DioExceptionType.connectionError,
      ),
    );

    expect(timeout.kind, ApiFailureKind.timeout);
    expect(offline.kind, ApiFailureKind.offline);
  });
}
