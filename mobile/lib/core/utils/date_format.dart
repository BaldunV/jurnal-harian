/// Indonesian date/time formatting without requiring intl locale data.
abstract final class AppDateFormat {
  AppDateFormat._();

  static const List<String> _days = <String>[
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu',
  ];

  static const List<String> _months = <String>[
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ];

  static String hariTanggal(DateTime date) =>
      '${_days[date.weekday - 1]}, ${date.day} ${_months[date.month - 1]} ${date.year}';

  static String tanggal(DateTime date) =>
      '${date.day} ${_months[date.month - 1]} ${date.year}';

  static String bulanTahun(DateTime date) =>
      '${_months[date.month - 1]} ${date.year}';

  /// Human, relative day label: Hari ini, Kemarin, or the full date.
  static String relatif(DateTime date, {DateTime? now}) {
    final reference = now ?? DateTime.now();
    final today = DateTime(reference.year, reference.month, reference.day);
    final target = DateTime(date.year, date.month, date.day);
    final difference = today.difference(target).inDays;

    if (difference == 0) {
      return 'Hari ini';
    }
    if (difference == 1) {
      return 'Kemarin';
    }

    return tanggal(date);
  }

  static String jamMenit(String? value) {
    if (value == null || value.isEmpty) {
      return '—';
    }
    return value;
  }
}
