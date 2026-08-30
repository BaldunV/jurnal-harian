import 'package:flutter/material.dart';

import '../core/constants/habits.dart';
import '../core/theme/design_tokens.dart';
import '../core/theme/habit_palette.dart';
import 'neon_checkbox.dart';

/// Form card for a single habit, matching the PWA `journal-habit-card`
/// (category-tinted icon, sub-pill, neon toggle, optional time presets, note).
class HabitFormCard extends StatelessWidget {
  const HabitFormCard({
    required this.habit,
    required this.value,
    required this.onChanged,
    this.description,
    this.time,
    this.timePresets = const <String>[],
    this.onTimeChanged,
    this.note,
    this.noteHint,
    this.onNoteChanged,
    this.extra,
    this.photo,
    this.locked = false,
    super.key,
  });

  final HabitMeta habit;
  final bool value;
  final ValueChanged<bool>? onChanged;
  final String? description;
  final String? time;
  final List<String> timePresets;
  final ValueChanged<String>? onTimeChanged;
  final String? note;
  final String? noteHint;
  final ValueChanged<String>? onNoteChanged;
  final Widget? extra;
  final Widget? photo;
  final bool locked;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final tone = HabitPalettes.byKey[habit.key]!.resolve(
      isDark ? Brightness.dark : Brightness.light,
    );

    final enabled = !locked;

    return Container(
      decoration: BoxDecoration(
        color: isDark
            ? const Color(0xFF1E293B).withValues(alpha: 0.8)
            : Colors.white.withValues(alpha: 0.95),
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(
          color: value
              ? tone.border
              : (isDark
                    ? const Color(0xFF334155).withValues(alpha: 0.5)
                    : const Color(0xFFE2E8F0)),
          width: 1.5,
        ),
        boxShadow: AppShadows.card(context),
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: tone.iconBg,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(habit.icon, color: tone.iconText, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      habit.label,
                      style: theme.textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: tone.bg,
                        borderRadius: BorderRadius.circular(9999),
                      ),
                      child: Text(
                        habit.pill,
                        style: theme.textTheme.labelSmall?.copyWith(
                          color: tone.text,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              NeonCheckbox(
                value: value,
                enabled: enabled,
                onChanged: onChanged,
              ),
            ],
          ),
          if (description != null) ...[
            const SizedBox(height: 10),
            Text(
              description!,
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ],
          if (extra != null) ...[const SizedBox(height: 12), extra!],
          if (timePresets.isNotEmpty) ...[
            const SizedBox(height: 14),
            _TimePresets(
              presets: timePresets,
              selected: time,
              accent: tone.iconText,
              enabled: enabled,
              onChanged: onTimeChanged,
            ),
          ],
          if (noteHint != null) ...[
            const SizedBox(height: 14),
            TextField(
              controller: TextEditingController(
                text: note,
              )..selection = TextSelection.collapsed(offset: note?.length ?? 0),
              onChanged: onNoteChanged,
              enabled: enabled,
              maxLines: 1,
              style: theme.textTheme.bodyMedium,
              decoration: InputDecoration(
                hintText: noteHint,
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
              ),
            ),
          ],
          if (photo != null) ...[const SizedBox(height: 14), photo!],
        ],
      ),
    );
  }
}

class _TimePresets extends StatelessWidget {
  const _TimePresets({
    required this.presets,
    required this.selected,
    required this.accent,
    required this.enabled,
    required this.onChanged,
  });

  final List<String> presets;
  final String? selected;
  final Color accent;
  final bool enabled;
  final ValueChanged<String>? onChanged;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: presets.map((preset) {
        final active = selected == preset;
        return InkWell(
          onTap: enabled ? () => onChanged?.call(preset) : null,
          borderRadius: BorderRadius.circular(10),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: active ? accent : Colors.transparent,
              border: Border.all(
                color: active ? accent : AppColors.border,
                width: 1.5,
              ),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              preset,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: active ? Colors.white : Colors.grey.shade600,
              ),
            ),
          ),
        );
      }).toList(),
    );
  }
}
