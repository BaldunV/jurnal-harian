import 'dart:async';

import 'package:dio/dio.dart';

import '../storage/token_storage.dart';

class AuthTokenInterceptor extends Interceptor {
  AuthTokenInterceptor({required this.tokenStorage, this.onUnauthorized});

  final TokenStorage tokenStorage;
  final FutureOr<void> Function()? onUnauthorized;
  Future<void>? _unauthorizedTask;

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    try {
      final token = await tokenStorage.readToken();
      if (token != null && token.isNotEmpty) {
        options.headers['Authorization'] = 'Bearer $token';
      }
      handler.next(options);
    } on Object catch (error, stackTrace) {
      handler.reject(
        DioException(
          requestOptions: options,
          error: error,
          stackTrace: stackTrace,
          type: DioExceptionType.unknown,
        ),
      );
    }
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401 &&
        err.requestOptions.headers.containsKey('Authorization')) {
      final task = _unauthorizedTask ??= _clearUnauthorizedSession();
      try {
        await task;
      } on Object {
        // Preserve the authoritative 401 even when local cleanup fails.
      } finally {
        if (identical(_unauthorizedTask, task)) {
          _unauthorizedTask = null;
        }
      }
    }

    handler.next(err);
  }

  Future<void> _clearUnauthorizedSession() async {
    try {
      await tokenStorage.clearToken();
    } finally {
      await onUnauthorized?.call();
    }
  }
}
