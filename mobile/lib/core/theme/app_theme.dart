import 'package:flutter/material.dart';

import 'design_tokens.dart';
import 'habit_ribbon_colors.dart';

abstract final class AppTheme {
  AppTheme._();

  static ThemeData get light => _build(lightScheme(), _lightRibbon);

  static ThemeData get dark => _build(darkScheme(), _darkRibbon);

  static ColorScheme lightScheme() => const ColorScheme(
    brightness: Brightness.light,
    primary: AppColors.primary600,
    onPrimary: Colors.white,
    primaryContainer: AppColors.primary50,
    onPrimaryContainer: AppColors.primary800,
    secondary: AppColors.teal,
    onSecondary: Colors.white,
    secondaryContainer: Color(0xFFCCFBF1),
    onSecondaryContainer: Color(0xFF134E4A),
    tertiary: AppColors.amber,
    onTertiary: Color(0xFF442700),
    tertiaryContainer: Color(0xFFFEF3C7),
    onTertiaryContainer: Color(0xFF3F2D00),
    error: AppColors.error,
    onError: Colors.white,
    errorContainer: Color(0xFFFECDD3),
    onErrorContainer: Color(0xFF7F1029),
    surface: AppColors.surface50,
    onSurface: AppColors.ink900,
    onSurfaceVariant: AppColors.ink600,
    outline: AppColors.border,
    outlineVariant: AppColors.border,
    surfaceContainerLowest: Colors.white,
    surfaceContainerLow: Colors.white,
    surfaceContainer: AppColors.surface100,
    surfaceContainerHigh: Color(0xFFEFF3F8),
    surfaceContainerHighest: AppColors.surface200,
    shadow: AppColors.ink900,
    scrim: AppColors.ink900,
    inversePrimary: AppColors.primary200,
  );

  static ColorScheme darkScheme() => const ColorScheme(
    brightness: Brightness.dark,
    primary: AppColors.primary300,
    onPrimary: AppColors.primary800,
    primaryContainer: Color(0xFF065F46),
    onPrimaryContainer: AppColors.primary50,
    secondary: Color(0xFF5EEAD4),
    onSecondary: Color(0xFF003D36),
    secondaryContainer: Color(0xFF134E4A),
    onSecondaryContainer: Color(0xFFA7F3D0),
    tertiary: Color(0xFFFCD34D),
    onTertiary: Color(0xFF3F2D00),
    tertiaryContainer: Color(0xFF5C4300),
    onTertiaryContainer: Color(0xFFFDE68A),
    error: Color(0xFFFE8398),
    onError: Color(0xFF4A0014),
    errorContainer: Color(0xFF7F1029),
    onErrorContainer: Color(0xFFFECDD3),
    surface: Color(0xFF0B1220),
    onSurface: Color(0xFFE2E8F0),
    onSurfaceVariant: AppColors.ink400,
    outline: Color(0xFF334155),
    outlineVariant: Color(0xFF273448),
    surfaceContainerLowest: Color(0xFF070D18),
    surfaceContainerLow: Color(0xFF0F172A),
    surfaceContainer: Color(0xFF162032),
    surfaceContainerHigh: Color(0xFF1E293B),
    surfaceContainerHighest: Color(0xFF273448),
    shadow: Colors.black,
    scrim: Colors.black,
    inversePrimary: AppColors.primary600,
  );

  static ThemeData _build(ColorScheme colors, HabitRibbonColors ribbon) {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: colors,
      scaffoldBackgroundColor: colors.surface,
      fontFamily: AppTypography.bodyFont,
      visualDensity: VisualDensity.standard,
      extensions: <ThemeExtension<dynamic>>[ribbon],
    );

    final textTheme = base.textTheme.copyWith(
      displaySmall: base.textTheme.displaySmall?.copyWith(
        fontFamily: AppTypography.displayFont,
        color: colors.onSurface,
        fontWeight: FontWeight.w800,
        letterSpacing: -0.6,
        height: 1.08,
      ),
      headlineSmall: base.textTheme.headlineSmall?.copyWith(
        color: colors.onSurface,
        fontWeight: FontWeight.w700,
        letterSpacing: -0.4,
      ),
      titleLarge: base.textTheme.titleLarge?.copyWith(
        color: colors.onSurface,
        fontWeight: FontWeight.w800,
        letterSpacing: -0.3,
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
        scrolledUnderElevation: 0,
        surfaceTintColor: Colors.transparent,
        centerTitle: false,
        titleTextStyle: textTheme.titleLarge,
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        color: colors.surfaceContainerLowest,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.lg),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(48, 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          textStyle: textTheme.labelLarge,
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(48, 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppRadius.md),
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
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: BorderSide(color: colors.outline),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: BorderSide(color: colors.primary, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: BorderSide(color: colors.error, width: 2),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: BorderSide(color: colors.error, width: 2),
        ),
        labelStyle: TextStyle(color: colors.onSurfaceVariant),
        floatingLabelStyle: TextStyle(
          color: colors.primary,
          fontWeight: FontWeight.w700,
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        height: 72,
        elevation: 0,
        backgroundColor: colors.surfaceContainerLowest,
        indicatorColor: colors.primaryContainer,
        indicatorShape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
        ),
        iconTheme: WidgetStateProperty.resolveWith<IconThemeData?>((
          Set<WidgetState> states,
        ) {
          final selected = states.contains(WidgetState.selected);
          return IconThemeData(
            size: selected ? 24 : 22,
            color: selected ? colors.primary : colors.onSurfaceVariant,
          );
        }),
        labelTextStyle: WidgetStateProperty.resolveWith<TextStyle?>((
          Set<WidgetState> states,
        ) {
          final selected = states.contains(WidgetState.selected);
          return textTheme.labelSmall?.copyWith(
            color: selected ? colors.primary : colors.onSurfaceVariant,
            fontWeight: selected ? FontWeight.w800 : FontWeight.w600,
          );
        }),
      ),
      navigationRailTheme: NavigationRailThemeData(
        backgroundColor: colors.surfaceContainerLowest,
        indicatorColor: colors.primaryContainer,
        useIndicator: true,
      ),
      dividerTheme: DividerThemeData(
        color: colors.outlineVariant,
        thickness: 1,
        space: 1,
      ),
      dialogTheme: DialogThemeData(
        elevation: 0,
        backgroundColor: colors.surfaceContainerLowest,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.xl),
        ),
      ),
      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: colors.surfaceContainerLowest,
        modalBackgroundColor: colors.surfaceContainerLowest,
        surfaceTintColor: Colors.transparent,
        showDragHandle: true,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(
            top: Radius.circular(AppRadius.xl),
          ),
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: colors.inverseSurface,
        contentTextStyle: textTheme.bodyMedium?.copyWith(
          color: colors.onInverseSurface,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
        ),
      ),
      chipTheme: ChipThemeData(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.sm),
        ),
        labelStyle: textTheme.labelMedium,
      ),
    );
  }

  static const _lightRibbon = HabitRibbonColors(
    active: AppColors.primary600,
    inactive: AppColors.surface100,
    marker: AppColors.amber,
  );

  static const _darkRibbon = HabitRibbonColors(
    active: AppColors.primary300,
    inactive: Color(0xFF162032),
    marker: AppColors.amber,
  );
}
