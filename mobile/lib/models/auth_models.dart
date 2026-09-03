import 'student.dart';

class AuthTokenResult {
  const AuthTokenResult({
    required this.token,
    required this.expiresAt,
    required this.issuedUser,
  });

  factory AuthTokenResult.fromJson(Map<String, Object?> json) {
    final token = json['token']! as String;
    if (token.isEmpty || json['token_type'] != 'Bearer') {
      throw const FormatException('Invalid bearer token response.');
    }

    final rawUser = json['user'];
    if (rawUser is! Map<Object?, Object?>) {
      throw const FormatException('Invalid student response.');
    }

    return AuthTokenResult(
      token: token,
      expiresAt: DateTime.parse(json['expires_at']! as String),
      issuedUser: Student.fromJson(
        rawUser.map((key, value) => MapEntry(key.toString(), value)),
      ),
    );
  }

  final String token;
  final DateTime expiresAt;
  final Student issuedUser;
}
