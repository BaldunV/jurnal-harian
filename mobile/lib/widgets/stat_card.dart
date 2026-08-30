import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';

/// Compact stat card matching the PWA dashboard quick-stats row and the
/// statistics breakdown cards (`stats-card` with a top accent bar).
class StatCard extends StatelessWidget {
  const StatCard({
    required this.icon,
    required this.value,
    required this.label,
    this.accent = AppColors.primary500,
    this.sublabel,
    super.key,
  });

  final IconData icon;
  final String value;
  final String label;
  final Color accent;
  final String? sublabel;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        color: isDark
            ? const Color(0xFF1E293B).withValues(alpha: 0.75)
            : Colors.white.withValues(alpha: 0.92),
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(
          color: isDark
              ? const Color(0xFF334155).withValues(alpha: 0.5)
              : const Color(0xFFE2E8F0).withValues(alpha: 0.8),
        ),
        boxShadow: AppShadows.card(context),
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      constraints: const BoxConstraints(minHeight: 188),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: accent.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: accent, size: 20),
          ),

          const SizedBox(height: 12),

          SizedBox(
            height: 30,
            child: Align(
              alignment: Alignment.topLeft,
              child: Text(
                value,
                maxLines: 1,
                style: theme.textTheme.headlineSmall?.copyWith(
                  fontFamily: AppTypography.displayFont,
                  fontWeight: FontWeight.w800,
                  height: 1,
                ),
              ),
            ),
          ),

          const SizedBox(height: 4),

          SizedBox(
            height: 40,
            child: Align(
              alignment: Alignment.topLeft,
              child: Text(
                label,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: theme.textTheme.labelMedium?.copyWith(
                  color: theme.colorScheme.onSurfaceVariant,
                  fontWeight: FontWeight.w700,
                  height: 1.25,
                ),
              ),
            ),
          ),

          const SizedBox(height: 2),

          SizedBox(
            height: 34,
            child: Align(
              alignment: Alignment.topLeft,
              child: sublabel != null
                  ? Text(
                      sublabel!,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: theme.textTheme.labelSmall?.copyWith(
                        color: theme.colorScheme.onSurfaceVariant.withValues(
                          alpha: 0.7,
                        ),
                        height: 1.25,
                      ),
                    )
                  : const SizedBox.shrink(),
            ),
          ),
        ],
      ),
    );
  }
}
