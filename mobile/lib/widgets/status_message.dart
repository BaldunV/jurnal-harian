import 'package:flutter/material.dart';

enum StatusMessageTone { error, warning, success, info }

class StatusMessage extends StatelessWidget {
  const StatusMessage({
    required this.message,
    required this.tone,
    this.onDismiss,
    super.key,
  });

  final String message;
  final StatusMessageTone tone;
  final VoidCallback? onDismiss;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final (background, foreground, icon) = switch (tone) {
      StatusMessageTone.error => (
        colors.errorContainer,
        colors.onErrorContainer,
        Icons.error_outline_rounded,
      ),
      StatusMessageTone.warning => (
        colors.tertiaryContainer,
        colors.onTertiaryContainer,
        Icons.warning_amber_rounded,
      ),
      StatusMessageTone.success => (
        colors.secondaryContainer,
        colors.onSecondaryContainer,
        Icons.check_circle_outline_rounded,
      ),
      StatusMessageTone.info => (
        colors.primaryContainer,
        colors.onPrimaryContainer,
        Icons.info_outline_rounded,
      ),
    };

    return Semantics(
      container: true,
      liveRegion: true,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: background,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(14, 12, 8, 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(icon, color: foreground, size: 22),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  message,
                  style: Theme.of(context).textTheme.bodyMedium
                      ?.copyWith(color: foreground),
                ),
              ),
              if (onDismiss != null)
                IconButton(
                  onPressed: onDismiss,
                  color: foreground,
                  tooltip: 'Tutup pesan',
                  icon: const Icon(Icons.close_rounded),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
