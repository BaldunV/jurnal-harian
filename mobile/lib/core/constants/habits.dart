import 'package:flutter/material.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

/// The seven habits, hardcoded to match the backend
/// [App\Services\JournalStatisticsService::HABITS] contract and the PWA
/// [HabitCard.jsx] presentation (icon, sub-label and category colour).
class HabitMeta {
  const HabitMeta({
    required this.key,
    required this.label,
    required this.icon,
    required this.pill,
    this.noteHint,
    this.defaultTime,
  });

  final String key;
  final String label;
  final IconData icon;

  /// Small category sub-label shown on the dashboard card (e.g. "Kedisiplinan").
  final String pill;

  /// Placeholder text for the optional note field on the journal form.
  final String? noteHint;

  /// Suggested default time for habits that carry a timestamp.
  final String? defaultTime;
}

abstract final class Habits {
  Habits._();

  static const int total = 7;

  static const bangunPagi = HabitMeta(
    key: 'bangun_pagi',
    label: 'Bangun Pagi',
    icon: LucideIcons.sunrise,
    pill: 'Kedisiplinan',
    defaultTime: '05:30',
  );

  static const beribadah = HabitMeta(
    key: 'beribadah',
    label: 'Beribadah',
    icon: LucideIcons.sparkles,
    pill: 'Spiritual',
  );

  static const berolahraga = HabitMeta(
    key: 'berolahraga',
    label: 'Berolahraga',
    icon: LucideIcons.footprints,
    pill: 'Kesehatan Fisik',
    noteHint: 'Opsional: Jenis olahraga (misal: Push up 20x / Jogging)',
  );

  static const makanSehat = HabitMeta(
    key: 'makan_sehat',
    label: 'Makan Sehat',
    icon: LucideIcons.salad,
    pill: 'Nutrisi Organik',
    noteHint: 'Opsional: Menu makan (misal: Nasi, Sayur bayam, Telur, Buah)',
  );

  static const gemarBelajar = HabitMeta(
    key: 'gemar_belajar',
    label: 'Gemar Belajar',
    icon: LucideIcons.bookOpen,
    pill: 'Literasi & Wawasan',
    noteHint: 'Opsional: Materi / Buku yang dipelajari',
  );

  static const bermasyarakat = HabitMeta(
    key: 'bermasyarakat',
    label: 'Bermasyarakat',
    icon: LucideIcons.users,
    pill: 'Empati & Kerjasama',
    noteHint: 'Opsional: Kegiatan sosial (misal: Kerja bakti / Bantu teman)',
  );

  static const tidurCepat = HabitMeta(
    key: 'tidur_cepat',
    label: 'Tidur Cepat',
    icon: LucideIcons.moonStar,
    pill: 'Istirahat Cukup',
    noteHint: 'Jam tidur',
    defaultTime: '21:00',
  );

  static const List<HabitMeta> all = <HabitMeta>[
    bangunPagi,
    beribadah,
    berolahraga,
    makanSehat,
    gemarBelajar,
    bermasyarakat,
    tidurCepat,
  ];

  static HabitMeta byKey(String key) =>
      all.firstWhere((habit) => habit.key == key);

  /// Worship detail keys sent to the API, keyed by the student's worship type.
  static const Map<String, List<String>> worshipDetailKeys =
      <String, List<String>>{
        'muslim': <String>['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'],
        'non_muslim': <String>['doa_pagi', 'kitab_meditasi', 'doa_malam'],
      };

  static List<String> worshipKeysFor(String worshipType) =>
      worshipDetailKeys[worshipType] ?? worshipDetailKeys['muslim']!;
}
