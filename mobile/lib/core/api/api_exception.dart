import 'dart:io';

import 'package:dio/dio.dart';

enum ApiFailureKind {
  unauthenticated,
  forbidden,
  notFound,
  conflict,
  validation,
  rateLimited,
  server,
  timeout,
  offline,
  unknown,
}

class ApiException implements Exception {
  const ApiException({
    required this.kind,
    required this.message,
    this.statusCode,
    this.code,
    this.errors = const <String, List<String>>{},
  });

  factory ApiException.fromDio(DioException exception) {
    if (const {
      DioExceptionType.connectionTimeout,
      DioExceptionType.sendTimeout,
      DioExceptionType.receiveTimeout,
    }.contains(exception.type)) {
      return const ApiException(
        kind: ApiFailureKind.timeout,
        message: 'Koneksi terlalu lama. Periksa jaringan lalu coba lagi.',
      );
    }

    if (exception.type == DioExceptionType.connectionError ||
        exception.error is SocketException) {
      return const ApiException(
        kind: ApiFailureKind.offline,
        message: 'Tidak dapat terhubung. Periksa koneksi internetmu.',
      );
    }

    final statusCode = exception.response?.statusCode;
    final payload = _asStringMap(exception.response?.data);
    final rawMessage = payload?['message'];
    final rawCode = payload?['code'];

    return ApiException(
      kind: _kindForStatus(statusCode),
      message: rawMessage is String ? rawMessage : _fallbackMessage(statusCode),
      statusCode: statusCode,
      code: rawCode is String ? rawCode : null,
      errors: _parseErrors(payload?['errors']),
    );
  }

  final ApiFailureKind kind;
  final String message;
  final int? statusCode;
  final String? code;
  final Map<String, List<String>> errors;

  bool get requiresAuthentication => kind == ApiFailureKind.unauthenticated;

  static ApiFailureKind _kindForStatus(int? statusCode) {
    if (statusCode != null && statusCode >= 500) {
      return ApiFailureKind.server;
    }

    return switch (statusCode) {
      401 => ApiFailureKind.unauthenticated,
      403 => ApiFailureKind.forbidden,
      404 => ApiFailureKind.notFound,
      409 => ApiFailureKind.conflict,
      422 => ApiFailureKind.validation,
      429 => ApiFailureKind.rateLimited,
      _ => ApiFailureKind.unknown,
    };
  }

  static String _fallbackMessage(int? statusCode) {
    if (statusCode != null && statusCode >= 500) {
      return 'Layanan sedang bermasalah. Coba lagi beberapa saat.';
    }

    return switch (statusCode) {
      401 => 'Sesi sudah berakhir. Silakan masuk kembali.',
      403 => 'Akunmu tidak memiliki izin untuk tindakan ini.',
      404 => 'Data yang diminta tidak ditemukan.',
      409 => 'Data tidak dapat diubah pada kondisi saat ini.',
      422 => 'Periksa kembali data yang kamu isi.',
      429 => 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.',
      _ => 'Permintaan belum dapat diproses.',
    };
  }

  static Map<String, Object?>? _asStringMap(Object? value) {
    if (value is! Map<Object?, Object?>) {
      return null;
    }

    return value.map((key, item) => MapEntry(key.toString(), item));
  }

  static Map<String, List<String>> _parseErrors(Object? value) {
    final raw = _asStringMap(value);
    if (raw == null) {
      return const <String, List<String>>{};
    }

    return raw.map((field, messages) {
      final normalized = switch (messages) {
        List<Object?> values => values.map((item) => item.toString()).toList(),
        null => <String>[],
        _ => <String>[messages.toString()],
      };

      return MapEntry(field, normalized);
    });
  }

  @override
  String toString() => 'ApiException($kind, $statusCode, $code, $message)';
}
