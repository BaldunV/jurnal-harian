import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';

/// Frosted white card matching the PWA `.modern-card` surface.
class AppCard extends StatelessWidget {
  const AppCard({
    required this.child,
    this.padding = const EdgeInsets.all(AppSpacing.lg),
    this.color,
    this.elevation = true,
    this.radius = AppRadius.xl,
    super.key,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final Color? color;
  final bool elevation;
  final double radius;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        color:
            color ??
            (isDark
                ? const Color(0xFF1E293B).withValues(alpha: 0.75)
                : Colors.white.withValues(alpha: 0.92)),
        borderRadius: BorderRadius.circular(radius),
        boxShadow: elevation ? AppShadows.card(context) : null,
        border: Border.all(
          color: isDark
              ? const Color(0xFF334155).withValues(alpha: 0.5)
              : const Color(0xFFE2E8F0).withValues(alpha: 0.8),
        ),
      ),
      padding: padding,
      child: child,
    );
  }
}

/// Glassy container used for the hero / progress banner (`.glass-panel`).
class GlassCard extends StatelessWidget {
  const GlassCard({
    required this.child,
    this.gradient = AppGradients.hero,
    this.padding = const EdgeInsets.all(AppSpacing.xl),
    this.radius = AppRadius.lg,
    super.key,
  });

  final Widget child;
  final Gradient gradient;
  final EdgeInsetsGeometry padding;
  final double radius;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: gradient,
        borderRadius: BorderRadius.circular(radius),
        boxShadow: AppShadows.glow(context),
      ),
      padding: padding,
      child: child,
    );
  }
}

/// Icon chip matching `.modern-icon-container` (emerald→teal tinted).
class ModernIconContainer extends StatelessWidget {
  const ModernIconContainer({
    required this.icon,
    this.size = 48,
    this.iconColor,
    this.gradient = AppGradients.primary,
    super.key,
  });

  final IconData icon;
  final double size;
  final Color? iconColor;
  final Gradient gradient;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        gradient: gradient,
        borderRadius: BorderRadius.circular(size * 0.30),
        border: Border.all(
          color: AppColors.primary500.withValues(alpha: isDark ? 0.3 : 0.2),
        ),
      ),
      child: Icon(icon, size: size * 0.5, color: iconColor ?? Colors.white),
    );
  }
}

/// Uppercase emerald kicker with a leading accent bar (`.student-section-kicker`).
class SectionKicker extends StatelessWidget {
  const SectionKicker(this.label, {this.color, super.key});

  final String label;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final accent =
        color ?? (isDark ? AppColors.primary300 : AppColors.primary700);
    return Row(
      children: [
        Container(
          width: 24,
          height: 3,
          decoration: BoxDecoration(
            color: AppColors.primary500,
            borderRadius: BorderRadius.circular(9999),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          label.toUpperCase(),
          style: theme.textTheme.labelSmall?.copyWith(
            color: accent,
            fontWeight: FontWeight.w800,
            letterSpacing: 1.2,
          ),
        ),
      ],
    );
  }
}

/// Pill badge matching `.modern-badge` (gradient emerald tint, uppercase).
class Pill extends StatelessWidget {
  const Pill(this.label, {this.icon, this.color, super.key});

  final String label;
  final IconData? icon;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final fg = color ?? (isDark ? AppColors.primary300 : AppColors.primary700);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: <Color>[
            AppColors.primary500.withValues(alpha: 0.15),
            AppColors.teal.withValues(alpha: 0.12),
          ],
        ),
        borderRadius: BorderRadius.circular(9999),
        border: Border.all(
          color: AppColors.primary500.withValues(alpha: isDark ? 0.3 : 0.2),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 13, color: fg),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: theme.textTheme.labelSmall?.copyWith(
              color: fg,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.4,
            ),
          ),
        ],
      ),
    );
  }
}

class SectionTitle extends StatelessWidget {
  const SectionTitle(this.title, {this.subtitle, super.key});

  final String title;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    final text = Theme.of(context).textTheme;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: text.titleMedium),
        if (subtitle != null) ...[
          const SizedBox(height: 4),
          Text(
            subtitle!,
            style: text.bodyMedium?.copyWith(
              color: Theme.of(context).colorScheme.onSurfaceVariant,
            ),
          ),
        ],
      ],
    );
  }
}
