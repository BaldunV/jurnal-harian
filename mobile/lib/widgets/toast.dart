import 'dart:async';

import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';

enum ToastType { success, error, info }

class ToastConfig {
  const ToastConfig({
    required this.icon,
    required this.bar,
    required this.text,
    required this.border,
  });

  final String icon;
  final Color bar;
  final Color text;
  final Color border;
}

const _toastConfigs = <ToastType, ToastConfig>{
  ToastType.success: ToastConfig(
    icon: '✓',
    bar: AppColors.primary500,
    text: AppColors.ink900,
    border: Color(0xFFD1FAE5),
  ),
  ToastType.error: ToastConfig(
    icon: '✕',
    bar: Color(0xFFE11D48),
    text: AppColors.ink900,
    border: Color(0xFFFECDD3),
  ),
  ToastType.info: ToastConfig(
    icon: 'i',
    bar: Color(0xFF3B82F6),
    text: AppColors.ink900,
    border: Color(0xFFBFDBFE),
  ),
};

class ToastNotification extends StatefulWidget {
  const ToastNotification({
    super.key,
    required this.message,
    this.type = ToastType.success,
    this.onClose,
    this.duration = 4000,
  });

  final String message;
  final ToastType type;
  final VoidCallback? onClose;
  final int duration;

  @override
  State<ToastNotification> createState() => _ToastNotificationState();
}

class _ToastNotificationState extends State<ToastNotification>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _scaleAnimation;
  late final Animation<double> _opacityAnimation;
  double _progress = 100.0;
  bool _visible = true;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 350),
    );
    _scaleAnimation = Tween<double>(
      begin: 0.94,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.elasticOut));
    _opacityAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut));
    _controller.forward();
    _progress = 100.0;
    _timer = Timer.periodic(const Duration(milliseconds: 50), (_) {
      if (!mounted || !_visible) return;
      setState(() {
        _progress = (_progress - (50.0 / widget.duration) * 100).clamp(
          0.0,
          100.0,
        );
        if (_progress <= 0) _dismiss();
      });
    });
  }

  void _dismiss() {
    if (!_visible) return;
    setState(() => _visible = false);
    _controller.reverse();
    _timer?.cancel();
    if (widget.onClose != null) {
      Future.delayed(const Duration(milliseconds: 300), widget.onClose);
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!_visible) return const SizedBox.shrink();
    final cfg = _toastConfigs[widget.type] ?? _toastConfigs[ToastType.success]!;
    return SlideTransition(
      position: Tween<Offset>(
        begin: const Offset(0, -1),
        end: Offset.zero,
      ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut)),
      child: ScaleTransition(
        scale: _scaleAnimation,
        child: FadeTransition(
          opacity: _opacityAnimation,
          child: Container(
            constraints: const BoxConstraints(minWidth: 280, maxWidth: 360),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border.all(color: cfg.border, width: 1.5),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: cfg.bar.withValues(alpha: 0.15),
                  blurRadius: 16,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: cfg.bar.withValues(alpha: 0.12),
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Text(
                      cfg.icon,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: cfg.bar,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    widget.message,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: cfg.text,
                      height: 1.3,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                GestureDetector(
                  onTap: _dismiss,
                  child: Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: AppColors.ink200.withValues(alpha: 0.4),
                      shape: BoxShape.circle,
                    ),
                    child: const Center(
                      child: Icon(
                        Icons.close,
                        size: 12,
                        color: AppColors.ink400,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
