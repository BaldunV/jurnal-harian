import 'package:flutter/material.dart';

/// Design tokens mirrored from the mobile web/PWA stylesheet so the Flutter
/// app shares the same emerald/slate identity, spacing, radii, elevation and
/// gradient language defined in `resources/css/app.css` and `app/DESAIN.md`.
abstract final class AppColors {
  AppColors._();

  // Primary (emerald)
  static const primary50 = Color(0xFFECFDF5);
  static const primary100 = Color(0xFFD1FAE5);
  static const primary200 = Color(0xFFA7F3D0);
  static const primary300 = Color(0xFF6EE7B7);
  static const primary400 = Color(0xFF34D399);
  static const primary500 = Color(0xFF10B981);
  static const primary600 = Color(0xFF059669);
  static const primary700 = Color(0xFF047857);
  static const primary800 = Color(0xFF065F46);
  static const primary900 = Color(0xFF064E3B);

  // Ink / slate text + surfaces
  static const ink200 = Color(0xFFE2E8F0);
  static const ink300 = Color(0xFFCBD5E1);
  static const ink400 = Color(0xFF94A3B8);
  static const ink500 = Color(0xFF64748B);
  static const ink600 = Color(0xFF475569);
  static const ink700 = Color(0xFF334155);
  static const ink900 = Color(0xFF0F172A);

  static const surface0 = Color(0xFF070D18);
  static const surface50 = Color(0xFFF8FAFC);
  static const surface100 = Color(0xFFF1F5F9);
  static const surface200 = Color(0xFFE2E8F0);

  // Accents
  static const amber = Color(0xFFF59E0B);
  static const amber600 = Color(0xFFD97706);
  static const teal = Color(0xFF14B8A6);
  static const error = Color(0xFFE11D48);
  static const rose = Color(0xFFF43F5E);

  // Category accents (PWA HabitCard per-habit palette)
  static const sky600 = Color(0xFF0284C7);
  static const sky500 = Color(0xFF0EA5E9);
  static const violet600 = Color(0xFF7C3AED);
  static const violet500 = Color(0xFF8B5CF6);
  static const blue600 = Color(0xFF2563EB);
  static const blue500 = Color(0xFF3B82F6);
  static const indigo600 = Color(0xFF4F46E5);
  static const indigo500 = Color(0xFF6366F1);

  static const border = Color(0xFFE2E8F0);
  static const card = Colors.white;
}

/// Shared linear gradients from the PWA design system.
abstract final class AppGradients {
  AppGradients._();

  /// `modern-btn-gradient` / `btn-save-glow` pill gradient.
  static const Gradient primary = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: <Color>[AppColors.primary700, AppColors.primary500, AppColors.teal],
  );

  /// `student-progress-card` / hero emerald→teal gradient.
  static const Gradient hero = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: <Color>[AppColors.primary700, AppColors.primary600, AppColors.teal],
  );

  /// `gradient-border-hero` border gradient (emerald→teal).
  static const Gradient heroBorder = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: <Color>[AppColors.primary700, AppColors.primary500, AppColors.teal],
  );

  /// Soft morning mesh used behind the dashboard hero (PWA `.student-parallax-layer--back`).
  static const Gradient morningMesh = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: <Color>[Color(0xFF064E3B), AppColors.primary700, Color(0xFF0F766E)],
  );

  /// Amber CTA gradient (Bangun Pagi time preset).
  static const Gradient amberCta = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: <Color>[Color(0xFFF59E0B), Color(0xFFD97706)],
  );
}

abstract final class AppSpacing {
  AppSpacing._();

  static const double xs = 4;
  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 24;
  static const double xxl = 32;

  static const EdgeInsets screenPadding = EdgeInsets.fromLTRB(24, 24, 24, 32);
}

abstract final class AppRadius {
  AppRadius._();

  static const double xs = 8;
  static const double sm = 12;
  static const double md = 16;
  static const double lg = 20;
  static const double xl = 24;
  static const double xxl = 32;
  static const double pill = 9999;
}

abstract final class AppShadows {
  AppShadows._();

  /// PWA `--shadow-card`: soft dual-layer elevation.
  static List<BoxShadow> card(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return <BoxShadow>[
      BoxShadow(
        color: scheme.shadow.withValues(alpha: 0.04),
        blurRadius: 2,
        offset: const Offset(0, 1),
      ),
      BoxShadow(
        color: scheme.shadow.withValues(alpha: 0.06),
        blurRadius: 24,
        offset: const Offset(0, 8),
      ),
    ];
  }

  /// `modern-btn-gradient` glow used on primary CTAs.
  static List<BoxShadow> glow(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return <BoxShadow>[
      BoxShadow(
        color: AppColors.primary500.withValues(alpha: 0.3),
        blurRadius: 15,
        offset: const Offset(0, 4),
      ),
      BoxShadow(
        color: scheme.shadow.withValues(alpha: 0.1),
        blurRadius: 5,
        offset: const Offset(0, 2),
      ),
    ];
  }
}

abstract final class AppTypography {
  AppTypography._();

  static const String bodyFont = 'PlusJakartaSans';
  static const String displayFont = 'Baloo2';
}
