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
  String? _belajarPhoto;
  String? _masyarakatPhoto;
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
      if (_belajarPhoto != null) {
        await notifier.uploadPhoto(saved.id, 'belajar', _belajarPhoto!);
      }
      if (_masyarakatPhoto != null) {
        await notifier.uploadPhoto(saved.id, 'masyarakat', _masyarakatPhoto!);
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
    final keyboardOpen = MediaQuery.of(context).viewInsets.bottom > 0;

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.existing != null ? 'Perbarui jurnal' : 'Isi jurnal'),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(
          20,
          AppSpacing.lg,
          20,
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
        ],
      ),
      bottomNavigationBar: keyboardOpen
          ? null
          : _FormActionBar(
              locked: locked,
              busy: busy,
              onSaveDraft: () => _persist(submit: false),
              onSubmit: _confirmAndSubmit,
            ),
    );
  }

  Future<void> _confirmAndSubmit() async {
    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      builder: (context) => SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.tertiaryContainer,
                  borderRadius: BorderRadius.circular(AppRadius.lg),
                ),
                child: Icon(
                  LucideIcons.lockKeyhole,
                  color: Theme.of(context).colorScheme.onTertiaryContainer,
                ),
              ),
              const SizedBox(height: AppSpacing.lg),
              Text(
                'Kirim jurnal sekarang?',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: AppSpacing.sm),
              Text(
                'Periksa kembali isianmu. Setelah dikirim, jurnal hari ini akan terkunci dan tidak dapat diubah.',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodyMedium,
              ),
              const SizedBox(height: AppSpacing.xl),
              SaveGlowButton(
                label: 'Ya, kirim dan kunci',
                icon: LucideIcons.send,
                onPressed: () => Navigator.of(context).pop(true),
              ),
              const SizedBox(height: AppSpacing.sm),
              SizedBox(
                width: double.infinity,
                child: TextButton(
                  onPressed: () => Navigator.of(context).pop(false),
                  child: const Text('Periksa lagi'),
                ),
              ),
            ],
          ),
        ),
      ),
    );

    if (confirmed == true && mounted) {
      await _persist(submit: true);
    }
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
    final progress = (_draft.completed / Habits.total)
        .clamp(0.0, 1.0)
        .toDouble();
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: colors.surfaceContainerLowest,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: colors.outlineVariant),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: colors.primaryContainer,
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Icon(
                  LucideIcons.listChecks,
                  color: colors.primary,
                  size: 19,
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Text(
                  '${_draft.completed} dari ${Habits.total} kebiasaan diisi',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
              ),
              Text(
                '${(progress * 100).round()}%',
                style: Theme.of(context).textTheme.labelLarge
                    ?.copyWith(color: colors.primary),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          ClipRRect(
            borderRadius: BorderRadius.circular(AppRadius.pill),
            child: LinearProgressIndicator(
              value: progress,
              minHeight: 7,
              color: colors.primary,
              backgroundColor: colors.surfaceContainerHighest,
            ),
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
            photo:
                habit.key == 'berolahraga' ||
                    habit.key == 'makan_sehat' ||
                    habit.key == 'gemar_belajar' ||
                    habit.key == 'bermasyarakat'
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
    final label = switch (key) {
      'berolahraga' => 'Foto olahraga',
      'makan_sehat' => 'Foto makan sehat',
      'gemar_belajar' => 'Foto gemar belajar',
      'bermasyarakat' => 'Foto bermasyarakat',
      _ => 'Foto dokumentasi',
    };

    final path = switch (key) {
      'berolahraga' => _olahragaPhoto,
      'makan_sehat' => _makanPhoto,
      'gemar_belajar' => _belajarPhoto,
      'bermasyarakat' => _masyarakatPhoto,
      _ => null,
    };

    final url = switch (key) {
      'berolahraga' => widget.existing?.olahragaPhotoUrl,
      'makan_sehat' => widget.existing?.makanPhotoUrl,
      'gemar_belajar' => widget.existing?.belajarPhotoUrl,
      'bermasyarakat' => widget.existing?.masyarakatPhotoUrl,
      _ => null,
    };

    return PhotoField(
      label: label,
      file: path == null ? null : File(path),
      url: url,
      onPicked: (pickedPath) {
        setState(() {
          if (key == 'berolahraga') {
            _olahragaPhoto = pickedPath;
          } else if (key == 'makan_sehat') {
            _makanPhoto = pickedPath;
          } else if (key == 'gemar_belajar') {
            _belajarPhoto = pickedPath;
          } else if (key == 'bermasyarakat') {
            _masyarakatPhoto = pickedPath;
          }
        });
      },
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

class _FormActionBar extends StatelessWidget {
  const _FormActionBar({
    required this.locked,
    required this.busy,
    required this.onSaveDraft,
    required this.onSubmit,
  });

  final bool locked;
  final bool busy;
  final VoidCallback onSaveDraft;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;

    return Material(
      color: colors.surfaceContainerLowest,
      elevation: 12,
      shadowColor: colors.shadow.withValues(alpha: 0.14),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 14),
          child: locked
              ? Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(
                    vertical: 14,
                    horizontal: 18,
                  ),
                  decoration: BoxDecoration(
                    color: colors.surfaceContainer,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        LucideIcons.lock,
                        size: 18,
                        color: colors.onSurfaceVariant,
                      ),
                      const SizedBox(width: 8),
                      Flexible(
                        child: Text(
                          'Jurnal sudah dikirim dan terkunci',
                          textAlign: TextAlign.center,
                          style: Theme.of(context).textTheme.labelLarge,
                        ),
                      ),
                    ],
                  ),
                )
              : Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    SaveGlowButton(
                      key: const Key('submit_journal_button'),
                      label: 'Kirim dan kunci jurnal',
                      icon: LucideIcons.send,
                      enabled: !busy,
                      isLoading: busy,
                      onPressed: onSubmit,
                    ),
                    const SizedBox(height: AppSpacing.sm),
                    SizedBox(
                      width: double.infinity,
                      child: TextButton.icon(
                        key: const Key('save_draft_button'),
                        onPressed: busy ? null : onSaveDraft,
                        icon: const Icon(LucideIcons.save, size: 18),
                        label: const Text('Simpan sebagai draft'),
                      ),
                    ),
                  ],
                ),
        ),
      ),
    );
  }
}
