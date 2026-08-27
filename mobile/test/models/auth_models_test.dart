import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/models/auth_models.dart';

import '../helpers/auth_test_data.dart';

void main() {
  test('enforces resend cooldown from the server timestamp', () {
    final now = DateTime.now().toUtc();
    final challenge = OtpChallenge(
      challengeId: testChallengeId,
      channel: 'whatsapp',
      channelLabel: 'WhatsApp',
      maskedPhone: '081 **** 7890',
      sentAt: now,
      resendAt: now.add(const Duration(seconds: 30)),
      expiresAt: now.add(const Duration(minutes: 5)),
    );

    expect(challenge.canResendAt(now), isFalse);
    expect(challenge.canResendAt(now.add(const Duration(seconds: 30))), isTrue);
  });
}
