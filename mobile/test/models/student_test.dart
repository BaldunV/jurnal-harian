import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/models/student.dart';

void main() {
  test('parses the whitelisted student resource', () {
    final student = Student.fromJson(const <String, Object?>{
      'id': 12,
      'nis': '20260012',
      'name': 'Nadia Putri',
      'class': 'XI RPL 1',
      'role': 'siswa',
      'worship_type': 'islam',
      'profile_photo_url': 'https://api.example.sch.id/api/me/profile/photo',
    });

    expect(student.id, 12);
    expect(student.nis, '20260012');
    expect(student.name, 'Nadia Putri');
    expect(student.className, 'XI RPL 1');
    expect(student.role, 'siswa');
    expect(student.worshipType, 'islam');
    expect(
      student.profilePhotoUrl,
      'https://api.example.sch.id/api/me/profile/photo',
    );
  });
}
