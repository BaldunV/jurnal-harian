import 'package:flutter/material.dart';

@immutable
class HabitRibbonColors extends ThemeExtension<HabitRibbonColors> {
  const HabitRibbonColors({
    required this.active,
    required this.inactive,
    required this.marker,
  });

  final Color active;
  final Color inactive;
  final Color marker;

  @override
  HabitRibbonColors copyWith({Color? active, Color? inactive, Color? marker}) {
    return HabitRibbonColors(
      active: active ?? this.active,
      inactive: inactive ?? this.inactive,
      marker: marker ?? this.marker,
    );
  }

  @override
  HabitRibbonColors lerp(
    covariant ThemeExtension<HabitRibbonColors>? other,
    double t,
  ) {
    if (other is! HabitRibbonColors) {
      return this;
    }

    return HabitRibbonColors(
      active: Color.lerp(active, other.active, t)!,
      inactive: Color.lerp(inactive, other.inactive, t)!,
      marker: Color.lerp(marker, other.marker, t)!,
    );
  }
}
