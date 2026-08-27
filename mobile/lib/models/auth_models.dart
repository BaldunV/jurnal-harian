import 'student.dart';

class OtpChallenge {
  const OtpChallenge({
    required this.challengeId,
    required this.channel,
    required this.channelLabel,
    required this.maskedPhone,
    required this.sentAt,
    required this.resendAt,
    required this.expiresAt,
  });

  factory OtpChallenge.fromLoginJson(Map<String, Object?> json) {
    if (json['otp_required'] != true) {
      throw const FormatException('OTP challenge expected.');
    }

    final challengeId = json['challenge_id']! as String;
    if (challengeId.length != 48) {
      throw const FormatException('Invalid OTP challenge identifier.');
    }

    return OtpChallenge(
      challengeId: challengeId,
      channel: json['channel']! as String,
      channelLabel: json['channel_label']! as String,
      maskedPhone: json['masked_phone']! as String,
      sentAt: _unixTime(json['sent_at']),
      resendAt: _unixTime(json['resend_at']),
      expiresAt: _unixTime(json['expires_at']),
    );
  }

  final String challengeId;
  final String channel;
  final String channelLabel;
  final String maskedPhone;
  final DateTime sentAt;
  final DateTime resendAt;
  final DateTime expiresAt;

  bool canResendAt(DateTime now) => !now.toUtc().isBefore(resendAt);

  bool isExpiredAt(DateTime now) => !now.toUtc().isBefore(expiresAt);

  OtpChallenge applyResendJson(Map<String, Object?> json) {
    final nextChannel = json['channel']! as String;

    return OtpChallenge(
      challengeId: challengeId,
      channel: nextChannel,
      channelLabel: switch (nextChannel) {
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        _ => channelLabel,
      },
      maskedPhone: maskedPhone,
      sentAt: _unixTime(json['sent_at']),
      resendAt: _unixTime(json['resend_at']),
      expiresAt: _unixTime(json['expires_at']),
    );
  }

  static DateTime _unixTime(Object? value) {
    final seconds = (value! as num).toInt();
    return DateTime.fromMillisecondsSinceEpoch(
      seconds * Duration.millisecondsPerSecond,
      isUtc: true,
    );
  }
}

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
