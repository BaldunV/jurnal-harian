import 'package:flutter_test/flutter_test.dart';
import 'package:jurnal_siswa/models/journal.dart';
import 'package:jurnal_siswa/models/journal_list.dart';
import 'package:jurnal_siswa/models/statistics.dart';

const _journalJson = <String, Object?>{
  'id': 1,
  'date': '2026-08-28',
  'bangun_pagi': true,
  'bangun_pagi_time': '05:30',
  'beribadah': true,
  'ibadah_details': <String, Object?>{'sholat': true, 'mengaji': false},
  'berolahraga': true,
  'olahraga_note': 'lari pagi',
  'olahraga_photo_url': null,
  'makan_sehat': false,
  'makan_note': null,
  'makan_photo_url': null,
  'gemar_belajar': true,
  'belajar_note': 'baca buku',
  'bermasyarakat': false,
  'masyarakat_note': null,
  'tidur_cepat': true,
  'tidur_note': '21:00',
  'completed_count': 5,
  'is_fully_completed': false,
  'is_submitted': true,
};

void main() {
  group('Journal', () {
    test('parses the Laravel JournalResource shape', () {
      final journal = Journal.fromJson(_journalJson);
      expect(journal.id, 1);
      expect(journal.bangunPagi, isTrue);
      expect(journal.bangunPagiTime, '05:30');
      expect(journal.beribadah, isTrue);
      expect(journal.ibadahDetails['sholat'], isTrue);
      expect(journal.berolahraga, isTrue);
      expect(journal.olahragaNote, 'lari pagi');
      expect(journal.makanSehat, isFalse);
      expect(journal.gemarBelajar, isTrue);
      expect(journal.tidurCepat, isTrue);
      expect(journal.tidurNote, '21:00');
      expect(journal.completedCount, 5);
      expect(journal.isSubmitted, isTrue);
      expect(journal.completed, 5);
    });

    test('treats missing booleans as false', () {
      final journal = Journal.fromJson(<String, Object?>{
        'id': 2,
        'date': '2026-08-29',
      });
      expect(journal.bangunPagi, isFalse);
      expect(journal.completed, 0);
      expect(journal.olahragaPhotoUrl, isNull);
    });

    test('draft round-trips through toJson', () {
      final draft = Journal.fromJson(_journalJson).toDraft();
      final json = draft.toJson();
      expect(json['bangun_pagi'], isTrue);
      expect(json['bangun_pagi_time'], '05:30');
      expect(json['ibadah_details'], isA<Map<String, Object?>>());
      expect(json['olahraga_note'], 'lari pagi');
      expect(json['tidur_note'], '21:00');
    });
  });

  group('JournalToday', () {
    test('parses a present journal', () {
      final today = JournalToday.fromJson(<String, Object?>{
        'date': '2026-08-28',
        'journal': _journalJson,
      });
      expect(today.date, '2026-08-28');
      expect(today.journal, isNotNull);
      expect(today.journal!.id, 1);
    });

    test('handles a null journal', () {
      final today = JournalToday.fromJson(<String, Object?>{
        'date': '2026-08-28',
        'journal': null,
      });
      expect(today.journal, isNull);
    });
  });

  group('JournalPage', () {
    test('reads meta pagination', () {
      final page = JournalPage.fromJson(<String, Object?>{
        'data': <Object?>[_journalJson, _journalJson],
        'meta': <String, Object?>{
          'current_page': 1,
          'last_page': 3,
          'total': 25,
        },
      });
      expect(page.items.length, 2);
      expect(page.currentPage, 1);
      expect(page.lastPage, 3);
      expect(page.hasMore, isTrue);
    });
  });

  group('Statistics', () {
    test('parses the JournalStatisticsService shape', () {
      final stats = Statistics.fromJson(<String, Object?>{
        'period': 'week',
        'today_progress': 3,
        'today_total': 7,
        'percentage': 42,
        'current_streak': 2,
        'completed_days': 3,
        'recorded_days': 5,
        'period_days': 7,
        'habit_statistics': <Object?>[
          <String, Object?>{
            'key': 'bangun_pagi',
            'name': 'Bangun Pagi',
            'icon': 'sunrise',
            'count': 5,
            'percentage': 71,
          },
        ],
      });
      expect(stats.period, 'week');
      expect(stats.percentage, 42);
      expect(stats.currentStreak, 2);
      expect(stats.habitStatistics.length, 1);
      expect(stats.habitStatistics.first.name, 'Bangun Pagi');
      expect(stats.habitStatistics.first.percentage, 71);
    });
  });
}
