import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';

/// Progress ring matching `ProgressRing.jsx` (SVG, single colour that shifts by
/// completion: emerald ≥100%, teal ≥70%, amber ≥40%, red below). The arc
/// animates smoothly toward [value] whenever it changes, matching the PWA
/// count-up transition.
class ProgressRing extends StatefulWidget {
  const ProgressRing({
    required this.value,
    required this.label,
    this.sublabel,
    this.size = 132,
    this.strokeWidth = 12,
    super.key,
  });

  /// Fraction completed, 0..1.
  final double value;
  final String label;
  final String? sublabel;
  final double size;
  final double strokeWidth;

  static Color colorFor(double percent) {
    if (percent >= 100) return AppColors.primary500;
    if (percent >= 70) return AppColors.teal;
    if (percent >= 40) return AppColors.amber;
    return AppColors.rose;
  }

  @override
  State<ProgressRing> createState() => _ProgressRingState();
}

class _ProgressRingState extends State<ProgressRing>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _curve;
  double _from = 0;
  double _to = 0;

  @override
  void initState() {
    super.initState();
    _from = _to = widget.value.clamp(0.0, 1.0);
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    )..value = 1;
    _curve = CurvedAnimation(parent: _controller, curve: Curves.easeOutCubic);
    _controller.addListener(() => setState(() {}));
  }

  @override
  void didUpdateWidget(covariant ProgressRing oldWidget) {
    super.didUpdateWidget(oldWidget);
    final target = widget.value.clamp(0.0, 1.0);
    if ((target - _to).abs() > 1e-4) {
      _from = _to;
      _to = target;
      _controller
        ..value = 0
        ..animateTo(1, curve: Curves.easeOutCubic);
    }
  }

  @override
  void dispose() {
    _controller
      ..removeListener(() {})
      ..dispose();
    super.dispose();
  }

  double get _animatedValue {
    final value = widget.value.clamp(0.0, 1.0);
    // Keep the live target in sync if the parent changes the value without a
    // discrete animation step (e.g. identical rebuilds).
    if ((value - _to).abs() <= 1e-4) {
      return _from + (_curve.value * (_to - _from));
    }
    return value;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final value = _animatedValue;
    final percent = (value.clamp(0.0, 1.0) * 100).toDouble();
    final ringColor = ProgressRing.colorFor(percent);
    final trackColor = isDark
        ? const Color(0xFF334155)
        : const Color(0xFFE2E8F0);

    return SizedBox(
      width: widget.size,
      height: widget.size,
      child: Stack(
        alignment: Alignment.center,
        children: [
          CustomPaint(
            size: Size(widget.size, widget.size),
            painter: _RingPainter(
              progress: value.clamp(0.0, 1.0),
              strokeWidth: widget.strokeWidth,
              trackColor: trackColor,
              ringColor: ringColor,
            ),
          ),
          Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                widget.label,
                style: theme.textTheme.headlineSmall?.copyWith(
                  fontFamily: AppTypography.displayFont,
                  color: ringColor,
                  fontWeight: FontWeight.w800,
                  height: 1,
                ),
              ),
              if (widget.sublabel != null)
                Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(
                    widget.sublabel!,
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _RingPainter extends CustomPainter {
  _RingPainter({
    required this.progress,
    required this.strokeWidth,
    required this.trackColor,
    required this.ringColor,
  });

  final double progress;
  final double strokeWidth;
  final Color trackColor;
  final Color ringColor;

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = (size.width - strokeWidth) / 2;
    const start = -1.5708; // -90deg
    final sweep = 6.2832 * progress;

    final track = Paint()
      ..color = trackColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.round;

    final fg = Paint()
      ..color = ringColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.round;

    canvas.drawCircle(center, radius, track);
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      start,
      sweep,
      false,
      fg,
    );

    // Soft glow matching `drop-shadow` on the PWA ring.
    final glow = Paint()
      ..color = ringColor.withValues(alpha: 0.35)
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.round
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 6);
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      start,
      sweep,
      false,
      glow,
    );
  }

  @override
  bool shouldRepaint(covariant _RingPainter old) =>
      old.progress != progress ||
      old.ringColor != ringColor ||
      old.trackColor != trackColor;
}
