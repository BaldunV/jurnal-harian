import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../../core/constants/habits.dart';
import '../../core/theme/design_tokens.dart';
import '../../models/journal.dart';
import '../../models/student.dart';
import '../../providers/interaction_feedback_provider.dart';
import '../../providers/journal_controller.dart';
import '../../widgets/buttons.dart';
import '../../widgets/habit_form_card.dart';
import '../../widgets/photo_field.dart';
import '../../widgets/status_message.dart';

class JournalFormScreen extends ConsumerStatefulWidget {
  const JournalFormScreen({required this.student, this.existing, super.key});

  final Student student;
  final Journal? existing;

  @override
  ConsumerState<JournalFormScreen> createState() => _JournalFormScreenState();
}

class _JournalFormScreenState extends ConsumerState<JournalFormScreen> {
  late JournalDraft _draft;
  String? _olahragaPhoto;
  String? _makanPhoto;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _draft = widget.existing?.toDraft() ?? const JournalDraft();
  }

  Future<void> _persist({required bool submit}) async {
    if (_busy) {
      return;
    }
    await ref.read(interactionFeedbackProvider).buttonPress();
    setState(() => _busy = true);
    final notifier = ref.read(journalControllerProvider.notifier);
    try {
      final saved = await notifier.saveDraft(
        _draft,
        existingId: widget.existing?.id,
      );
      if (_olahragaPhoto != null) {
        await notifier.uploadPhoto(saved.id, 'olahraga', _olahragaPhoto!);
      }
      if (_makanPhoto != null) {
        await notifier.uploadPhoto(saved.id, 'makan', _makanPhoto!);
      }
      if (submit) {
        await notifier.submit(_draft, existingId: saved.id);
        if (mounted) {
          await ref.read(interactionFeedbackProvider).success();
        }
      }
      await notifier.load();
      if (mounted) {
        Navigator.of(context).pop(true);
      }
    } on Object {
      if (mounted) {
        setState(() => _busy = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final operation = ref.watch(journalControllerProvider).operation;
    final busy = _busy || operation.isBusy;
    final locked = widget.existing?.isSubmitted ?? false;

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.existing != null ? 'Perbarui jurnal' : 'Isi jurnal'),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(
          AppSpacing.xl,
          AppSpacing.lg,
          AppSpacing.xl,
          AppSpacing.xxl,
        ),
        children: [
          if (operation.status == JournalOperationStatus.error &&
              operation.message != null)
            Padding(
              padding: const EdgeInsets.only(bottom: AppSpacing.md),
              child: StatusMessage(
                tone: StatusMessageTone.error,
                message: operation.message!,
              ),
            ),
          if (locked) _lockBanner(),
          if (locked) const SizedBox(height: AppSpacing.lg),
          Text(
            'Lengkapi ketujuh kebiasaan untuk hari ini.',
            style: Theme.of(context).textTheme.bodyLarge,
          ),
          const SizedBox(height: AppSpacing.md),
          _progressChip(),
          const SizedBox(height: AppSpacing.lg),
          ..._habitCards(context, locked),
          const SizedBox(height: AppSpacing.xl),
          if (locked)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 15, horizontal: 24),
              decoration: BoxDecoration(
                color: Colors.grey.withValues(alpha: 0.4),
                borderRadius: BorderRadius.circular(AppRadius.pill),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(LucideIcons.lock, size: 18, color: Colors.white),
                  const SizedBox(width: 8),
                  const Text(
                    'Jurnal Selesai & Terkunci Permanen',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            )
          else ...[
            SaveGlowButton(
              key: const Key('submit_journal_button'),
              label: 'Simpan Jurnal Hari Ini',
              enabled: !busy,
              onPressed: () => _persist(submit: true),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                key: const Key('save_draft_button'),
                onPressed: busy ? null : () => _persist(submit: false),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  side: BorderSide(
                    color: AppColors.primary500.withValues(alpha: 0.5),
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                ),
                child: const Text('Simpan sebagai draft'),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _lockBanner() {
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: <Color>[Color(0xFF0F172A), Color(0xFF1E293B)],
        ),
        borderRadius: BorderRadius.circular(AppRadius.lg),
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Row(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: AppColors.amber.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              LucideIcons.lock,
              color: AppColors.amber,
              size: 24,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Jurnal Telah Terkunci',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Tidak dapat diubah demi menjaga kejujuran dan kedisiplinan.',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.7),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _progressChip() {
    final colors = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: colors.surfaceContainer,
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      child: Row(
        children: [
          Icon(LucideIcons.listChecks, color: colors.primary, size: 20),
          const SizedBox(width: 10),
          Text(
            '${_draft.completed} dari ${Habits.total} kebiasaan diisi',
            style: Theme.of(context).textTheme.titleMedium,
          ),
        ],
      ),
    );
  }

  List<Widget> _habitCards(BuildContext context, bool locked) {
    final tiles = <Widget>[];
    final descriptions = <String, String>{
      'bangun_pagi': 'Memulai hari lebih awal untuk melatih kedisiplinan dan kesiapan mental.',
      'beribadah': 'Membentuk fondasi spiritual, kejujuran, serta rasa syukur kepada Tuhan.',
      'berolahraga': 'Menjaga kebugaran tubuh dan kesehatan mental agar lebih fokus belajar.',
      'makan_sehat': 'Memenuhi kebutuhan nutrisi seimbang untuk mendukung pertumbuhan otak dan tubuh.',
      'gemar_belajar':
          'Menumbuhkan rasa ingin tahu serta semangat membaca sepanjang hayat.',
      'bermasyarakat': 'Mengasah rasa empati, toleransi, dan kemampuan kerja sama dengan lingkungan sekitar.',
      'tidur_cepat': 'Memastikan istirahat cukup untuk memulihkan tenaga (disarankan sebelum 22.00).',
    };

    void add(HabitMeta habit) {
      tiles.add(
        Padding(
          padding: const EdgeInsets.only(bottom: AppSpacing.md),
          child: HabitFormCard(
            habit: habit,
            value: _habitValue(habit.key),
            description: descriptions[habit.key],
            locked: locked,
            onChanged: locked ? null : (value) => _setHabit(habit.key, value),
            time: habit.key == 'bangun_pagi'
                ? _draft.bangunPagiTime
                : _draft.tidurNote,
            timePresets: habit.key == 'bangun_pagi'
                ? const [
                    '04:00',
                    '04:30',
                    '05:00',
                    '05:30',
                    '06:00',
                    '06:30',
                    '07:00',
                  ]
                : habit.key == 'tidur_cepat'
                ? const ['20:00', '20:30', '21:00', '21:30', '22:00']
                : const <String>[],
            onTimeChanged: locked
                ? null
                : (value) {
                    ref.read(interactionFeedbackProvider).buttonPress();
                    _setTime(habit.key, value);
                  },
            note: _habitNote(habit.key),
            noteHint: habit.noteHint,
            onNoteChanged: locked
                ? null
                : (value) => _setNote(habit.key, value),
            extra: habit.key == 'beribadah' ? _worshipDetails(context) : null,
            photo: habit.key == 'berolahraga' || habit.key == 'makan_sehat'
                ? _photoField(habit.key)
                : null,
          ),
        ),
      );
    }

    for (final habit in Habits.all) {
      add(habit);
    }
    return tiles;
  }

  Widget _photoField(String key) {
    final isOlahraga = key == 'berolahraga';
    return PhotoField(
      label: isOlahraga ? 'Foto olahraga' : 'Foto makan sehat',
      file: isOlahraga
          ? (_olahragaPhoto == null ? null : File(_olahragaPhoto!))
          : (_makanPhoto == null ? null : File(_makanPhoto!)),
      url: isOlahraga
          ? widget.existing?.olahragaPhotoUrl
          : widget.existing?.makanPhotoUrl,
      onPicked: (path) => setState(
        () => isOlahraga ? _olahragaPhoto = path : _makanPhoto = path,
      ),
    );
  }

  Widget _worshipDetails(BuildContext context) {
    final keys = Habits.worshipKeysFor(widget.student.worshipType);
    final details = _draft.ibadahDetails;
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Wrap(
        spacing: 8,
        runSpacing: 8,
        children: keys.map((key) {
          final active = details[key] ?? false;
          return FilterChip(
            label: Text(_prettify(key)),
            selected: active,
            onSelected: (widget.existing?.isSubmitted ?? false)
                ? null
                : (selected) {
                    if (selected) {
                      ref
                          .read(interactionFeedbackProvider)
                          .habitChecked('beribadah');
                    }
                    setState(() {
                      final next = Map<String, bool>.from(details);
                      next[key] = selected;
                      _draft = _draft.copyWith(ibadahDetails: next);
                    });
                  },
          );
        }).toList(),
      ),
    );
  }

  void _setHabit(String key, bool value) {
    if (value) {
      ref.read(interactionFeedbackProvider).habitChecked(key);
    }
    setState(() {
      switch (key) {
        case 'bangun_pagi':
          _draft = _draft.copyWith(
            bangunPagi: value,
            bangunPagiTime: value && _draft.bangunPagiTime == null
                ? Habits.bangunPagi.defaultTime
                : null,
            clearBangunPagiTime: !value,
          );
        case 'beribadah':
          _draft = _draft.copyWith(beribadah: value);
        case 'berolahraga':
          _draft = _draft.copyWith(berolahraga: value);
        case 'makan_sehat':
          _draft = _draft.copyWith(makanSehat: value);
        case 'gemar_belajar':
          _draft = _draft.copyWith(gemarBelajar: value);
        case 'bermasyarakat':
          _draft = _draft.copyWith(bermasyarakat: value);
        case 'tidur_cepat':
          _draft = _draft.copyWith(
            tidurCepat: value,
            tidurNote: value && _draft.tidurNote == null
                ? Habits.tidurCepat.defaultTime
                : null,
            clearTidurNote: !value,
          );
      }
    });
  }

  void _setTime(String key, String value) {
    setState(() {
      if (key == 'bangun_pagi') {
        _draft = _draft.copyWith(bangunPagiTime: value);
      } else if (key == 'tidur_cepat') {
        _draft = _draft.copyWith(tidurNote: value);
      }
    });
  }

  void _setNote(String key, String value) {
    setState(() {
      switch (key) {
        case 'berolahraga':
          _draft = _draft.copyWith(olahragaNote: value);
        case 'makan_sehat':
          _draft = _draft.copyWith(makanNote: value);
        case 'gemar_belajar':
          _draft = _draft.copyWith(belajarNote: value);
        case 'bermasyarakat':
          _draft = _draft.copyWith(masyarakatNote: value);
      }
    });
  }

  bool _habitValue(String key) => switch (key) {
    'bangun_pagi' => _draft.bangunPagi,
    'beribadah' => _draft.beribadah,
    'berolahraga' => _draft.berolahraga,
    'makan_sehat' => _draft.makanSehat,
    'gemar_belajar' => _draft.gemarBelajar,
    'bermasyarakat' => _draft.bermasyarakat,
    'tidur_cepat' => _draft.tidurCepat,
    _ => false,
  };

  String? _habitNote(String key) => switch (key) {
    'berolahraga' => _draft.olahragaNote,
    'makan_sehat' => _draft.makanNote,
    'gemar_belajar' => _draft.belajarNote,
    'bermasyarakat' => _draft.masyarakatNote,
    'tidur_cepat' => _draft.tidurNote,
    _ => null,
  };

  String _prettify(String key) {
    final spaced = key.replaceAll('_', ' ');
    return spaced[0].toUpperCase() + spaced.substring(1);
  }
}
