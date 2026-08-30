import 'package:flutter/material.dart';

/// Per-habit category palette mirrored from `HabitCard.jsx` (emerald / sky /
/// violet / amber / blue / teal / indigo). Each tone carries a light and dark
/// variant so cards look identical to the PWA in both themes.
class HabitTone {
  const HabitTone({
    required this.bg,
    required this.border,
    required this.text,
    required this.iconBg,
    required this.iconText,
    required this.bgDark,
    required this.borderDark,
    required this.textDark,
    required this.iconBgDark,
    required this.iconTextDark,
  });

  final Color bg;
  final Color border;
  final Color text;
  final Color iconBg;
  final Color iconText;

  final Color bgDark;
  final Color borderDark;
  final Color textDark;
  final Color iconBgDark;
  final Color iconTextDark;

  ResolvedTone resolve(Brightness brightness) => brightness == Brightness.dark
      ? ResolvedTone(
          bg: bgDark,
          border: borderDark,
          text: textDark,
          iconBg: iconBgDark,
          iconText: iconTextDark,
        )
      : ResolvedTone(
          bg: bg,
          border: border,
          text: text,
          iconBg: iconBg,
          iconText: iconText,
        );
}

class ResolvedTone {
  const ResolvedTone({
    required this.bg,
    required this.border,
    required this.text,
    required this.iconBg,
    required this.iconText,
  });

  final Color bg;
  final Color border;
  final Color text;
  final Color iconBg;
  final Color iconText;
}

abstract final class HabitPalettes {
  HabitPalettes._();

  static const bangunPagi = HabitTone(
    bg: Color(0xFFECFDF5),
    border: Color(0xFFA7F3D0),
    text: Color(0xFF047857),
    iconBg: Color(0xFFD1FAE5),
    iconText: Color(0xFF059669),
    bgDark: Color(0xFF064E3B),
    borderDark: Color(0xFF065F46),
    textDark: Color(0xFF6EE7B7),
    iconBgDark: Color(0xFF065F46),
    iconTextDark: Color(0xFF6EE7B7),
  );

  static const beribadah = HabitTone(
    bg: Color(0xFFF0F9FF),
    border: Color(0xFFBAE6FD),
    text: Color(0xFF0369A1),
    iconBg: Color(0xFFE0F2FE),
    iconText: Color(0xFF0284C7),
    bgDark: Color(0xFF0C2A44),
    borderDark: Color(0xFF0C4A6E),
    textDark: Color(0xFF7DD3FC),
    iconBgDark: Color(0xFF0C4A6E),
    iconTextDark: Color(0xFF7DD3FC),
  );

  static const berolahraga = HabitTone(
    bg: Color(0xFFF5F3FF),
    border: Color(0xFFDDD6FE),
    text: Color(0xFF6D28D9),
    iconBg: Color(0xFFEDE9FE),
    iconText: Color(0xFF7C3AED),
    bgDark: Color(0xFF2E1065),
    borderDark: Color(0xFF4C1D95),
    textDark: Color(0xFFC4B5FD),
    iconBgDark: Color(0xFF4C1D95),
    iconTextDark: Color(0xFFC4B5FD),
  );

  static const makanSehat = HabitTone(
    bg: Color(0xFFFEFCE8),
    border: Color(0xFFFDE68A),
    text: Color(0xFFB45309),
    iconBg: Color(0xFFFEF3C7),
    iconText: Color(0xFFD97706),
    bgDark: Color(0xFF451A03),
    borderDark: Color(0xFF78350F),
    textDark: Color(0xFFFCD34D),
    iconBgDark: Color(0xFF78350F),
    iconTextDark: Color(0xFFFCD34D),
  );

  static const gemarBelajar = HabitTone(
    bg: Color(0xFFEFF6FF),
    border: Color(0xFFBFDBFE),
    text: Color(0xFF1D4ED8),
    iconBg: Color(0xFFDBEAFE),
    iconText: Color(0xFF2563EB),
    bgDark: Color(0xFF172554),
    borderDark: Color(0xFF1E3A8A),
    textDark: Color(0xFF93C5FD),
    iconBgDark: Color(0xFF1E3A8A),
    iconTextDark: Color(0xFF93C5FD),
  );

  static const bermasyarakat = HabitTone(
    bg: Color(0xFFF0FDFA),
    border: Color(0xFF99F6E4),
    text: Color(0xFF0F766E),
    iconBg: Color(0xFFCCFBF1),
    iconText: Color(0xFF0D9488),
    bgDark: Color(0xFF042F2E),
    borderDark: Color(0xFF115E59),
    textDark: Color(0xFF5EEAD4),
    iconBgDark: Color(0xFF115E59),
    iconTextDark: Color(0xFF5EEAD4),
  );

  static const tidurCepat = HabitTone(
    bg: Color(0xFFEEF2FF),
    border: Color(0xFFC7D2FE),
    text: Color(0xFF4338CA),
    iconBg: Color(0xFFE0E7FF),
    iconText: Color(0xFF4F46E5),
    bgDark: Color(0xFF1E1B4B),
    borderDark: Color(0xFF3730A3),
    textDark: Color(0xFFA5B4FC),
    iconBgDark: Color(0xFF3730A3),
    iconTextDark: Color(0xFFA5B4FC),
  );

  static const Map<String, HabitTone> byKey = <String, HabitTone>{
    'bangun_pagi': bangunPagi,
    'beribadah': beribadah,
    'berolahraga': berolahraga,
    'makan_sehat': makanSehat,
    'gemar_belajar': gemarBelajar,
    'bermasyarakat': bermasyarakat,
    'tidur_cepat': tidurCepat,
  };
}
