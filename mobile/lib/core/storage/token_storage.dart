import 'package:flutter_secure_storage/flutter_secure_storage.dart';

abstract interface class TokenStorage {
  Future<String?> readToken();

  Future<void> writeToken(String token);

  Future<void> clearToken();
}

class SecureTokenStorage implements TokenStorage {
  SecureTokenStorage({FlutterSecureStorage? storage})
    : _storage =
          storage ??
          const FlutterSecureStorage(
            aOptions: AndroidOptions(
              storageNamespace: 'jurnal_smk_bppi',
              preferencesKeyPrefix: 'jurnal_siswa',
            ),
          );

  static const _tokenKey = 'sanctum_token';

  final FlutterSecureStorage _storage;

  @override
  Future<String?> readToken() => _storage.read(key: _tokenKey);

  @override
  Future<void> writeToken(String token) {
    final normalized = token.trim();
    if (normalized.isEmpty) {
      throw ArgumentError.value(token, 'token', 'Token tidak boleh kosong.');
    }

    return _storage.write(key: _tokenKey, value: normalized);
  }

  @override
  Future<void> clearToken() => _storage.delete(key: _tokenKey);
}
