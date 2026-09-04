import 'package:flutter/material.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../core/constants/habits.dart';
import '../core/theme/design_tokens.dart';
import '../core/theme/habit_palette.dart';
import 'neon_checkbox.dart';

/// Dashboard habit card matching `HabitCard.jsx`: category-tinted icon, title,
/// sub-pill, optional time and a neon toggle that lights up the card.
class HabitCheckCard extends StatelessWidget {
  const HabitCheckCard({
    required this.habit,
    required this.done,
    required this.onChanged,
    this.onTap,
    this.time,
    this.note,
    this.enabled = true,
    super.key,
  });

  final HabitMeta habit;
  final bool done;
  final ValueChanged<bool>? onChanged;
  final VoidCallback? onTap;
  final String? time;
  final String? note;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final tone = HabitPalettes.byKey[habit.key]!.resolve(
      isDark ? Brightness.dark : Brightness.light,
    );

    final cardBg = done ? tone.bg : null;
    final cardBorder = done
        ? tone.border
        : (isDark
              ? const Color(0xFF334155).withValues(alpha: 0.5)
              : const Color(0xFFE2E8F0).withValues(alpha: 0.8));

    return Semantics(
      button: onTap != null,
      label: '${habit.label}, ${done ? 'sudah dilakukan' : 'belum dilakukan'}',
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOutCubic,
          decoration: BoxDecoration(
            color:
                cardBg ??
                (isDark
                    ? const Color(0xFF1E293B).withValues(alpha: 0.75)
                    : Colors.white.withValues(alpha: 0.92)),
            borderRadius: BorderRadius.circular(AppRadius.lg),
            border: Border.all(color: cardBorder, width: 1.5),
            boxShadow: done
                ? <BoxShadow>[
                    BoxShadow(
                      color: tone.text.withValues(alpha: 0.15),
                      blurRadius: 18,
                      offset: const Offset(0, 6),
                    ),
                  ]
                : AppShadows.card(context),
          ),
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Row(
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
                    if (time != null) ...[
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Icon(
                            LucideIcons.clock,
                            size: 14,
                            color: theme.colorScheme.onSurfaceVariant,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            time!,
                            style: theme.textTheme.bodySmall?.copyWith(
                              color: theme.colorScheme.onSurfaceVariant,
                            ),
                          ),
                        ],
                      ),
                    ],
                    if (note != null && note!.isNotEmpty) ...[
                      const SizedBox(height: 6),
                      Text(
                        note!,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: theme.textTheme.bodySmall?.copyWith(
                          fontStyle: FontStyle.italic,
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              NeonCheckbox(value: done, onChanged: onChanged, enabled: enabled),
            ],
          ),
        ),
      ),
    );
  }
}
