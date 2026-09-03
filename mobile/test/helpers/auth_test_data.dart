Map<String, Object?> studentData({String nis = '20260012'}) {
  return <String, Object?>{
    'id': 12,
    'nis': nis,
    'name': 'Nadia Putri',
    'class': 'XI RPL 1',
    'role': 'siswa',
    'worship_type': 'islam',
    'profile_photo_url': null,
  };
}

Map<String, Object?> tokenEnvelope({String token = '12|plain-token'}) {
  return <String, Object?>{
    'success': true,
    'message': 'Login berhasil.',
    'data': <String, Object?>{
      'token': token,
      'token_type': 'Bearer',
      'expires_at': '2026-09-27T10:00:00+07:00',
      'user': studentData(),
    },
  };
}

Map<String, Object?> meEnvelope() {
  return <String, Object?>{
    'success': true,
    'message': 'Permintaan berhasil.',
    'data': studentData(),
  };
}

Map<String, Object?> successEnvelope({
  String message = 'Permintaan berhasil.',
}) {
  return <String, Object?>{
    'success': true,
    'message': message,
    'data': <String, Object?>{},
  };
}

Map<String, Object?> errorEnvelope({
  required String message,
  required String code,
  Map<String, Object?> errors = const <String, Object?>{},
}) {
  return <String, Object?>{
    'success': false,
    'message': message,
    'errors': errors,
    'code': code,
  };
}
