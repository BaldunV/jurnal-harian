import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../core/theme/design_tokens.dart';

/// Pill CTA matching `.modern-btn-gradient` (emerald→teal gradient, glow).
class GradientButton extends StatelessWidget {
  const GradientButton({
    required this.label,
    required this.onPressed,
    this.icon,
    this.enabled = true,
    this.isLoading = false,
    this.fullWidth = true,
    this.padding = const EdgeInsets.symmetric(vertical: 15, horizontal: 24),
    this.gradient = AppGradients.primary,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final bool enabled;
  final bool isLoading;
  final bool fullWidth;
  final EdgeInsetsGeometry padding;
  final Gradient gradient;

  @override
  Widget build(BuildContext context) {
    final loading = isLoading;
    final child = Row(
      mainAxisSize: fullWidth ? MainAxisSize.max : MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (loading)
          const SizedBox.square(
            dimension: 18,
            child: CircularProgressIndicator(
              strokeWidth: 2.5,
              color: Colors.white,
            ),
          )
        else if (icon != null) ...[
          Icon(icon, size: 18, color: Colors.white),
          const SizedBox(width: 8),
        ],
        if (loading) const SizedBox(width: 10),
        if (!loading) ...[
          Text(
            label,
            style: const TextStyle(
              fontFamily: AppTypography.bodyFont,
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: Colors.white,
              letterSpacing: 0.2,
            ),
          ),
        ],
      ],
    );

    return Container(
      width: fullWidth ? double.infinity : null,
      decoration: BoxDecoration(
        gradient: enabled ? gradient : null,
        color: enabled ? null : Colors.grey.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(AppRadius.pill),
        boxShadow: enabled ? AppShadows.glow(context) : null,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(AppRadius.pill),
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.pill),
          onTap: (enabled && !isLoading)
              ? () {
                  HapticFeedback.lightImpact();
                  onPressed?.call();
                }
              : null,
          splashColor: Colors.white.withValues(alpha: 0.2),
          highlightColor: Colors.white.withValues(alpha: 0.1),
          child: Padding(padding: padding, child: child),
        ),
      ),
    );
  }
}

/// CTA matching `.btn-save-glow` with a shine sweep on press.
class SaveGlowButton extends StatefulWidget {
  const SaveGlowButton({
    required this.label,
    required this.onPressed,
    this.icon = Icons.save_outlined,
    this.enabled = true,
    this.isLoading = false,
    this.fullWidth = true,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final IconData icon;
  final bool enabled;
  final bool isLoading;
  final bool fullWidth;

  @override
  State<SaveGlowButton> createState() => _SaveGlowButtonState();
}

class _SaveGlowButtonState extends State<SaveGlowButton>
    with SingleTickerProviderStateMixin {
  late final AnimationController _shine = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 800),
  );
  final _tween = Tween<double>(begin: -1.4, end: 1.4);

  @override
  void dispose() {
    _shine.dispose();
    super.dispose();
  }

  void _trigger() {
    if (widget.enabled && !widget.isLoading && widget.onPressed != null) {
      HapticFeedback.lightImpact();
      _shine.forward(from: 0);
      widget.onPressed!();
    }
  }

  @override
  Widget build(BuildContext context) {
    final loading = widget.isLoading;
    final child = Row(
      mainAxisSize: widget.fullWidth ? MainAxisSize.max : MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (loading)
          const SizedBox.square(
            dimension: 18,
            child: CircularProgressIndicator(
              strokeWidth: 2.5,
              color: Colors.white,
            ),
          )
        else
          Icon(widget.icon, size: 18, color: Colors.white),
        const SizedBox(width: 8),
        Text(
          widget.label,
          style: const TextStyle(
            fontFamily: AppTypography.bodyFont,
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: Colors.white,
            letterSpacing: 0.2,
          ),
        ),
      ],
    );

    final box = Container(
      width: widget.fullWidth ? double.infinity : null,
      decoration: BoxDecoration(
        gradient: widget.enabled ? AppGradients.primary : null,
        color: widget.enabled ? null : Colors.grey.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(AppRadius.pill),
        boxShadow: widget.enabled ? AppShadows.glow(context) : null,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(AppRadius.pill),
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.pill),
          onTap: (widget.enabled && !loading) ? _trigger : null,
          splashColor: Colors.white.withValues(alpha: 0.2),
          highlightColor: Colors.white.withValues(alpha: 0.1),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 15, horizontal: 24),
            child: child,
          ),
        ),
      ),
    );

    return ClipRRect(
      borderRadius: BorderRadius.circular(AppRadius.pill),
      child: Stack(
        children: [
          box,
          Positioned.fill(
            child: IgnorePointer(
              child: AnimatedBuilder(
                animation: _shine,
                builder: (context, _) {
                  final value = _tween.evaluate(_shine);
                  return FractionallySizedBox(
                    widthFactor: 0.5,
                    alignment: Alignment(value, 0),
                    child: Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: <Color>[
                            Colors.white.withValues(alpha: 0),
                            Colors.white.withValues(alpha: 0.3),
                            Colors.white.withValues(alpha: 0),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}
