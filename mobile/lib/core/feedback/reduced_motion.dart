import 'package:flutter/material.dart';

bool get reducedMotionEnabled =>
    WidgetsBinding.instance.accessibilityFeatures.reduceMotion;

class ReducedMotion extends StatelessWidget {
  const ReducedMotion({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    if (!reducedMotionEnabled) return child;
    return _ReducedMotionScope(child: child);
  }
}

class _ReducedMotionScope extends InheritedWidget {
  const _ReducedMotionScope({required super.child});

  @override
  bool updateShouldNotify(_ReducedMotionScope oldWidget) => false;
}

bool useReducedMotion(BuildContext context) {
  if (!WidgetsBinding.instance.accessibilityFeatures.reduceMotion) return false;
  return true;
}

class AnimatedContainerWithReducedMotion extends StatelessWidget {
  const AnimatedContainerWithReducedMotion({
    super.key,
    required this.duration,
    required this.curve,
    required this.width,
    required this.height,
    required this.decoration,
    required this.child,
    this.onEnd,
  });

  final Duration duration;
  final Curve curve;
  final double width;
  final double height;
  final BoxDecoration decoration;
  final Widget child;
  final VoidCallback? onEnd;

  @override
  Widget build(BuildContext context) {
    if (useReducedMotion(context)) {
      return Container(
        width: width,
        height: height,
        decoration: decoration,
        child: child,
      );
    }
    return AnimatedContainer(
      duration: duration,
      curve: curve,
      width: width,
      height: height,
      decoration: decoration,
      onEnd: onEnd,
      child: child,
    );
  }
}
