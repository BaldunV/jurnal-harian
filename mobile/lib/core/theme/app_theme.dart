import 'package:flutter/material.dart';

import 'habit_ribbon_colors.dart';

abstract final class AppTheme {
  static const indonesiaRed = Color(0xFFC1121F);
  static const deepNavy = Color(0xFF102A43);
  static const warmWhite = Color(0xFFFFF9F2);
  static const schoolGold = Color(0xFFD8A51D);

  static ThemeData get light {
    final colors = ColorScheme.fromSeed(
      seedColor: indonesiaRed,
      brightness: Brightness.light,
      primary: indonesiaRed,
      secondary: schoolGold,
      surface: warmWhite,
      contrastLevel: 0.1,
    );

    return _theme(
      colors,
      const HabitRibbonColors(
        active: indonesiaRed,
        inactive: Color(0xFFE8E0D8),
        marker: schoolGold,
      ),
    );
  }

  static ThemeData get dark {
    final colors = ColorScheme.fromSeed(
      seedColor: indonesiaRed,
      brightness: Brightness.dark,
      primary: Color(0xFFFFB3AE),
      secondary: Color(0xFFF3CD69),
      surface: Color(0xFF0D1B2A),
      contrastLevel: 0.1,
    );

    return _theme(
      colors,
      const HabitRibbonColors(
        active: Color(0xFFFFB3AE),
        inactive: Color(0xFF33485C),
        marker: Color(0xFFF3CD69),
      ),
    );
  }

  static ThemeData _theme(ColorScheme colors, HabitRibbonColors ribbonColors) {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: colors,
      scaffoldBackgroundColor: colors.surface,
      visualDensity: VisualDensity.standard,
      extensions: <ThemeExtension<dynamic>>[ribbonColors],
    );
    final textTheme = base.textTheme.copyWith(
      displaySmall: base.textTheme.displaySmall?.copyWith(
        color: colors.onSurface,
        fontWeight: FontWeight.w800,
        letterSpacing: -1.1,
        height: 1.08,
      ),
      headlineSmall: base.textTheme.headlineSmall?.copyWith(
        color: colors.onSurface,
        fontWeight: FontWeight.w700,
        letterSpacing: -0.4,
      ),
      titleMedium: base.textTheme.titleMedium?.copyWith(
        color: colors.onSurface,
        fontWeight: FontWeight.w700,
      ),
      bodyLarge: base.textTheme.bodyLarge?.copyWith(
        color: colors.onSurfaceVariant,
        height: 1.5,
      ),
      bodyMedium: base.textTheme.bodyMedium?.copyWith(
        color: colors.onSurfaceVariant,
        height: 1.45,
      ),
      labelLarge: base.textTheme.labelLarge?.copyWith(
        fontWeight: FontWeight.w700,
      ),
    );

    return base.copyWith(
      textTheme: textTheme,
      appBarTheme: AppBarTheme(
        backgroundColor: colors.surface,
        foregroundColor: colors.onSurface,
        elevation: 0,
        scrolledUnderElevation: 1,
        centerTitle: false,
        titleTextStyle: textTheme.titleLarge?.copyWith(
          color: colors.onSurface,
          fontWeight: FontWeight.w700,
        ),
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        color: colors.surfaceContainerLow,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(48, 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          textStyle: textTheme.labelLarge,
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(48, 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          textStyle: textTheme.labelLarge,
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: colors.surfaceContainerLow,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 16,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: colors.outlineVariant),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: colors.primary, width: 2),
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 72,
        backgroundColor: colors.surfaceContainer,
        indicatorColor: colors.primaryContainer,
        labelTextStyle: WidgetStatePropertyAll(textTheme.labelMedium),
      ),
    );
  }
}
