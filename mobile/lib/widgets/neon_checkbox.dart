import 'package:flutter/material.dart';
import 'package:lucide_flutter/lucide_flutter.dart';

import '../core/theme/design_tokens.dart';
import '../core/feedback/reduced_motion.dart';

/// Tappable checkbox matching the PWA `NeonCheckbox.jsx` (rounded box, 2px
/// border, emerald tint when checked, animated check). Includes the
/// `cubic-bezier(0.175, 0.885, 0.32, 1.275)` pop animation matching the
/// PWA `.animate-check-pop` on check events.
class NeonCheckbox extends StatefulWidget {
  const NeonCheckbox({
    required this.value,
    required this.onChanged,
    this.size = 36,
    this.enabled = true,
    super.key,
  });

  final bool value;
  final ValueChanged<bool>? onChanged;
  final double size;
  final bool enabled;

  @override
  State<NeonCheckbox> createState() => _NeonCheckboxState();
}

class _NeonCheckboxState extends State<NeonCheckbox>
    with SingleTickerProviderStateMixin {
  late final AnimationController _checkPopController;
  late final Animation<double> _popAnimation;

  @override
  void initState() {
    super.initState();
    _checkPopController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );
    _popAnimation =
        TweenSequence<double>([
          TweenSequenceItem(
            tween: Tween<double>(begin: 1.0, end: 1.18),
            weight: 50,
          ),
          TweenSequenceItem(
            tween: Tween<double>(begin: 1.18, end: 1.0),
            weight: 50,
          ),
        ]).animate(
          CurvedAnimation(parent: _checkPopController, curve: Curves.easeOut),
        );
  }

  @override
  void didUpdateWidget(covariant NeonCheckbox oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.value != oldWidget.value && widget.value) {
      _checkPopController.forward().then((_) {
        _checkPopController.reset();
      });
    }
  }

  @override
  void dispose() {
    _checkPopController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final reducedMotion = useReducedMotion(context);

    final borderColor = widget.value
        ? AppColors.primary500
        : (isDark ? const Color(0xFF334155) : const Color(0xFFCBD5E1));
    final bg = widget.value
        ? AppColors.primary500.withValues(alpha: isDark ? 0.15 : 0.06)
        : (isDark ? const Color(0xFF0F172A) : Colors.white);

    return GestureDetector(
      onTap: widget.enabled && widget.onChanged != null
          ? () {
              widget.onChanged!(!widget.value);
            }
          : null,
      child: AnimatedBuilder(
        animation: _checkPopController,
        builder: (context, child) {
          return Transform.scale(
            scale: reducedMotion ? 1.0 : _popAnimation.value,
            child: child,
          );
        },
        child: AnimatedContainer(
          duration: reducedMotion
              ? Duration.zero
              : const Duration(milliseconds: 300),
          curve: Curves.easeOutCubic,
          width: widget.size,
          height: widget.size,
          decoration: BoxDecoration(
            color: bg,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: borderColor, width: 2),
            boxShadow: widget.value
                ? <BoxShadow>[
                    BoxShadow(
                      color: AppColors.primary500.withValues(alpha: 0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : null,
          ),
          child: AnimatedScale(
            duration: reducedMotion
                ? Duration.zero
                : const Duration(milliseconds: 250),
            curve: Curves.easeOutBack,
            scale: widget.value ? 1 : 0,
            child: Icon(
              LucideIcons.check,
              size: widget.size * 0.6,
              color: AppColors.primary500,
            ),
          ),
        ),
      ),
    );
  }
}
