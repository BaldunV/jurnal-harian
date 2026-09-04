import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';

/// Compact page header for the authenticated tabs.
///
/// The title and supporting label remain visible on phones without consuming
/// the amount of space used by a large sliver app bar.
class ScreenAppBar extends StatelessWidget implements PreferredSizeWidget {
  const ScreenAppBar({
    required this.title,
    required this.subtitle,
    required this.icon,
    this.actions,
    super.key,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final List<Widget>? actions;

  @override
  Size get preferredSize => const Size.fromHeight(76);

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;

    return AppBar(
      toolbarHeight: preferredSize.height,
      titleSpacing: 20,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(title),
          const SizedBox(height: 2),
          Text(
            subtitle,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: colors.onSurfaceVariant,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
      actions:
          actions ??
          <Widget>[
            Padding(
              padding: const EdgeInsets.only(right: 20),
              child: Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: colors.primaryContainer,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: Icon(icon, color: colors.primary, size: 21),
              ),
            ),
          ],
    );
  }
}
