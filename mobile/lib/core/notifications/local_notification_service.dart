import 'dart:io';

import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

class LocalNotificationService {
  LocalNotificationService._();

  static final LocalNotificationService instance = LocalNotificationService._();

  static const int _fillJournalId = 1001;
  static const int _submitReminderId = 1002;
  static const int _submitSuccessId = 1003;

  final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();

  static const AndroidNotificationDetails _androidDetails =
      AndroidNotificationDetails(
        'journal_reminders',
        'Pengingat Jurnal',
        channelDescription:
            'Pengingat untuk mengisi dan mengirim jurnal harian',
        importance: Importance.high,
        priority: Priority.high,

        playSound: false,
        enableVibration: true,
      );

  static const NotificationDetails _notificationDetails = NotificationDetails(
    android: _androidDetails,
  );

  Future<void> initialize() async {
    if (!Platform.isAndroid) {
      return;
    }

    tz.initializeTimeZones();
    tz.setLocalLocation(tz.getLocation('Asia/Jakarta'));

    const initializationSettings = InitializationSettings(
      android: AndroidInitializationSettings('@mipmap/ic_launcher'),
    );

    await _plugin.initialize(settings: initializationSettings);

    final androidPlugin = _plugin
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >();

    await androidPlugin?.requestNotificationsPermission();
  }

  Future<void> scheduleDailyJournalReminder() async {
    if (!Platform.isAndroid) {
      return;
    }

    await _plugin.zonedSchedule(
      id: _fillJournalId,
      title: 'Jangan lupa isi jurnal',
      body: 'Catat 7 kebiasaanmu hari ini.',
      scheduledDate: _nextTime(6, 0),
      notificationDetails: _notificationDetails,
      androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
      matchDateTimeComponents: DateTimeComponents.time,
      payload: 'journal_today',
    );
  }

  Future<void> scheduleSubmitReminder({bool startTomorrow = false}) async {
    if (!Platform.isAndroid) {
      return;
    }

    await _plugin.cancel(id: _submitReminderId);

    await _plugin.zonedSchedule(
      id: _submitReminderId,
      title: 'Jurnal belum dikirim',
      body: 'Pastikan jurnal hari ini sudah selesai dan dikirim.',
      scheduledDate: _nextTime(20, 0, forceTomorrow: startTomorrow),
      notificationDetails: _notificationDetails,
      androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
      matchDateTimeComponents: DateTimeComponents.time,
      payload: 'journal_submit',
    );
  }

  Future<void> configureForToday({required bool isSubmitted}) async {
    await scheduleDailyJournalReminder();

    await scheduleSubmitReminder(startTomorrow: isSubmitted);
  }

  Future<void> journalSubmitted() async {
    if (!Platform.isAndroid) {
      return;
    }

    // Batalkan pengingat "belum dikirim" hari ini.
    await _plugin.cancel(id: _submitReminderId);

    // Jadwalkan kembali untuk besok.
    await scheduleSubmitReminder(startTomorrow: true);

    // Tampilkan notifikasi sukses sekarang.
    await _plugin.show(
      id: _submitSuccessId,
      title: 'Jurnal berhasil dikirim',
      body: 'Jurnal hari ini sudah tersimpan dan terkunci.',
      notificationDetails: _notificationDetails,
      payload: 'journal_submitted',
    );
  }

  tz.TZDateTime _nextTime(int hour, int minute, {bool forceTomorrow = false}) {
    final now = tz.TZDateTime.now(tz.local);

    var scheduled = tz.TZDateTime(
      tz.local,
      now.year,
      now.month,
      now.day,
      hour,
      minute,
    );

    if (forceTomorrow || !scheduled.isAfter(now)) {
      scheduled = scheduled.add(const Duration(days: 1));
    }

    return scheduled;
  }
}
