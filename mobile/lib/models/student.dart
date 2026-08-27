class Student {
  const Student({
    required this.id,
    required this.nis,
    required this.name,
    required this.className,
    required this.role,
    required this.worshipType,
    this.profilePhotoUrl,
  });

  factory Student.fromJson(Map<String, Object?> json) {
    return Student(
      id: (json['id'] as num).toInt(),
      nis: json['nis']! as String,
      name: json['name']! as String,
      className: json['class']! as String,
      role: json['role']! as String,
      worshipType: json['worship_type']! as String,
      profilePhotoUrl: json['profile_photo_url'] as String?,
    );
  }

  final int id;
  final String nis;
  final String name;
  final String className;
  final String role;
  final String worshipType;
  final String? profilePhotoUrl;
}
