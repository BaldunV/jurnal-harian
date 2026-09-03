bool _asBool(Object? value) {
  if (value is bool) {
    return value;
  }
  if (value is num) {
    return value != 0;
  }
  if (value is String) {
    return value == '1' || value.toLowerCase() == 'true';
  }
  return false;
}

int _asInt(Object? value, [int fallback = 0]) {
  if (value is num) {
    return value.toInt();
  }
  if (value is String) {
    return int.tryParse(value) ?? fallback;
  }
  return fallback;
}

Map<String, bool> _asHabitMap(Object? value) {
  if (value is! Map) {
    return const <String, bool>{};
  }
  return value.map((key, item) => MapEntry(key.toString(), _asBool(item)));
}

class Journal {
  const Journal({
    required this.id,
    required this.date,
    required this.bangunPagi,
    this.bangunPagiTime,
    required this.beribadah,
    this.ibadahDetails = const <String, bool>{},
    required this.berolahraga,
    this.olahragaNote,
    this.olahragaPhotoUrl,
    required this.makanSehat,
    this.makanNote,
    this.makanPhotoUrl,
    required this.gemarBelajar,
    this.belajarNote,
    this.belajarPhotoUrl,
    required this.bermasyarakat,
    this.masyarakatNote,
    this.masyarakatPhotoUrl,
    required this.tidurCepat,
    this.tidurNote,
    required this.completedCount,
    required this.isFullyCompleted,
    required this.isSubmitted,
  });

  factory Journal.fromJson(Map<String, Object?> json) {
    return Journal(
      id: _asInt(json['id']),
      date: (json['date'] as String?) ?? '',
      bangunPagi: _asBool(json['bangun_pagi']),
      bangunPagiTime: json['bangun_pagi_time'] as String?,
      beribadah: _asBool(json['beribadah']),
      ibadahDetails: _asHabitMap(json['ibadah_details']),
      berolahraga: _asBool(json['berolahraga']),
      olahragaNote: json['olahraga_note'] as String?,
      olahragaPhotoUrl: json['olahraga_photo_url'] as String?,
      makanSehat: _asBool(json['makan_sehat']),
      makanNote: json['makan_note'] as String?,
      makanPhotoUrl: json['makan_photo_url'] as String?,
      gemarBelajar: _asBool(json['gemar_belajar']),
      belajarNote: json['belajar_note'] as String?,
      belajarPhotoUrl: json['belajar_photo_url'] as String?,
      bermasyarakat: _asBool(json['bermasyarakat']),
      masyarakatNote: json['masyarakat_note'] as String?,
      masyarakatPhotoUrl: json['masyarakat_photo_url'] as String?,
      tidurCepat: _asBool(json['tidur_cepat']),
      tidurNote: json['tidur_note'] as String?,
      completedCount: _asInt(json['completed_count']),
      isFullyCompleted: _asBool(json['is_fully_completed']),
      isSubmitted: _asBool(json['is_submitted']),
    );
  }

  final int id;
  final String date;
  final bool bangunPagi;
  final String? bangunPagiTime;
  final bool beribadah;
  final Map<String, bool> ibadahDetails;
  final bool berolahraga;
  final String? olahragaNote;
  final String? olahragaPhotoUrl;
  final bool makanSehat;
  final String? makanNote;
  final String? makanPhotoUrl;
  final bool gemarBelajar;
  final String? belajarNote;
  final String? belajarPhotoUrl;
  final bool bermasyarakat;
  final String? masyarakatNote;
  final String? masyarakatPhotoUrl;
  final bool tidurCepat;
  final String? tidurNote;

  /// Bed-time (`HH:mm`). The backend stores it in the legacy `tidur_note`
  /// column, so this is an alias that names the value for what it is.
  String? get tidurCepatTime => tidurNote;

  final int completedCount;
  final bool isFullyCompleted;
  final bool isSubmitted;

  int get completed => <bool>[
    bangunPagi,
    beribadah,
    berolahraga,
    makanSehat,
    gemarBelajar,
    bermasyarakat,
    tidurCepat,
  ].where((completed) => completed).length;

  JournalDraft toDraft() => JournalDraft(
    bangunPagi: bangunPagi,
    bangunPagiTime: bangunPagiTime,
    beribadah: beribadah,
    ibadahDetails: ibadahDetails,
    berolahraga: berolahraga,
    olahragaNote: olahragaNote,
    makanSehat: makanSehat,
    makanNote: makanNote,
    gemarBelajar: gemarBelajar,
    belajarNote: belajarNote,
    bermasyarakat: bermasyarakat,
    masyarakatNote: masyarakatNote,
    tidurCepat: tidurCepat,
    tidurNote: tidurNote,
  );
}

class JournalToday {
  const JournalToday({required this.date, this.journal});

  factory JournalToday.fromJson(Map<String, Object?> json) {
    final rawJournal = json['journal'];
    final journal = rawJournal is Map<Object?, Object?>
        ? Journal.fromJson(
            rawJournal.map((key, value) => MapEntry(key.toString(), value)),
          )
        : null;

    return JournalToday(
      date: (json['date'] as String?) ?? '',
      journal: journal,
    );
  }

  final String date;
  final Journal? journal;
}

class JournalDraft {
  const JournalDraft({
    this.bangunPagi = false,
    this.bangunPagiTime,
    this.beribadah = false,
    this.ibadahDetails = const <String, bool>{},
    this.berolahraga = false,
    this.olahragaNote,
    this.makanSehat = false,
    this.makanNote,
    this.gemarBelajar = false,
    this.belajarNote,
    this.bermasyarakat = false,
    this.masyarakatNote,
    this.tidurCepat = false,
    this.tidurNote,
  });

  final bool bangunPagi;
  final String? bangunPagiTime;
  final bool beribadah;
  final Map<String, bool> ibadahDetails;
  final bool berolahraga;
  final String? olahragaNote;
  final bool makanSehat;
  final String? makanNote;
  final bool gemarBelajar;
  final String? belajarNote;
  final bool bermasyarakat;
  final String? masyarakatNote;
  final bool tidurCepat;
  final String? tidurNote;

  int get completed => <bool>[
    bangunPagi,
    beribadah,
    berolahraga,
    makanSehat,
    gemarBelajar,
    bermasyarakat,
    tidurCepat,
  ].where((completed) => completed).length;

  JournalDraft copyWith({
    bool? bangunPagi,
    String? bangunPagiTime,
    bool clearBangunPagiTime = false,
    bool? beribadah,
    Map<String, bool>? ibadahDetails,
    bool? berolahraga,
    String? olahragaNote,
    bool clearOlahragaNote = false,
    bool? makanSehat,
    String? makanNote,
    bool clearMakanNote = false,
    bool? gemarBelajar,
    String? belajarNote,
    bool clearBelajarNote = false,
    bool? bermasyarakat,
    String? masyarakatNote,
    bool clearMasyarakatNote = false,
    bool? tidurCepat,
    String? tidurNote,
    bool clearTidurNote = false,
  }) {
    return JournalDraft(
      bangunPagi: bangunPagi ?? this.bangunPagi,
      bangunPagiTime: clearBangunPagiTime
          ? null
          : (bangunPagiTime ?? this.bangunPagiTime),
      beribadah: beribadah ?? this.beribadah,
      ibadahDetails: ibadahDetails ?? this.ibadahDetails,
      berolahraga: berolahraga ?? this.berolahraga,
      olahragaNote: clearOlahragaNote
          ? null
          : (olahragaNote ?? this.olahragaNote),
      makanSehat: makanSehat ?? this.makanSehat,
      makanNote: clearMakanNote ? null : (makanNote ?? this.makanNote),
      gemarBelajar: gemarBelajar ?? this.gemarBelajar,
      belajarNote: clearBelajarNote ? null : (belajarNote ?? this.belajarNote),
      bermasyarakat: bermasyarakat ?? this.bermasyarakat,
      masyarakatNote: clearMasyarakatNote
          ? null
          : (masyarakatNote ?? this.masyarakatNote),
      tidurCepat: tidurCepat ?? this.tidurCepat,
      tidurNote: clearTidurNote ? null : (tidurNote ?? this.tidurNote),
    );
  }

  Map<String, Object?> toJson() {
    return <String, Object?>{
      'bangun_pagi': bangunPagi,
      if (bangunPagiTime != null) 'bangun_pagi_time': bangunPagiTime,
      'ibadah_details': ibadahDetails,
      'berolahraga': berolahraga,
      'olahraga_note': olahragaNote ?? '',
      'makan_sehat': makanSehat,
      'makan_note': makanNote ?? '',
      'gemar_belajar': gemarBelajar,
      'belajar_note': belajarNote ?? '',
      'bermasyarakat': bermasyarakat,
      'masyarakat_note': masyarakatNote ?? '',
      'tidur_cepat': tidurCepat,
      if (tidurNote != null) 'tidur_note': tidurNote,
    };
  }
}
