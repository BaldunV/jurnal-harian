import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';
import '../core/feedback/reduced_motion.dart';

class HeatCell {
  const HeatCell({
    required this.label,
    required this.state,
    this.isToday = false,
  });

  /// 'full' (7/7), 'partial' (1-6), 'empty' (0) or 'none' (no data).
  final String state;
  final String label;
  final bool isToday;
}

/// Mini 7-day consistency heatmap matching the PWA dashboard strip:
/// emerald = full, amber = partial, slate = empty, with a "Hari Ini" marker.
class WeekHeatmap extends StatefulWidget {
  const WeekHeatmap({required this.cells, super.key, this.onTap});

  final List<HeatCell> cells;
  final void Function(int index)? onTap;

  @override
  State<WeekHeatmap> createState() => _WeekHeatmapState();
}

class _WeekHeatmapState extends State<WeekHeatmap> {
  final Map<int, bool> _pressed = <int, bool>{};

  bool _isPressed(int index) => _pressed[index] ?? false;

  void _onTapDown(int index, TapDownDetails details) {
    setState(() => _pressed[index] = true);
  }

  void _onTapUp(int index, TapUpDetails details) {
    setState(() => _pressed[index] = false);
    widget.onTap?.call(index);
  }

  void _onTapCancel(int index) {
    setState(() => _pressed[index] = false);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final reducedMotion = useReducedMotion(context);

    Color fill(String state) => switch (state) {
      'full' => AppColors.primary500,
      'partial' => AppColors.amber,
      'empty' => isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
      _ => isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
    };

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (int i = 0; i < widget.cells.length; i++)
          Expanded(
            child: Center(
              child: _HeatCellWidget(
                cell: widget.cells[i],
                color: fill(widget.cells[i].state),
                index: i,
                pressed: _isPressed(i),
                reducedMotion: reducedMotion,
                onTapDown: (details) => _onTapDown(i, details),
                onTapUp: (details) => _onTapUp(i, details),
                onTapCancel: () => _onTapCancel(i),
              ),
            ),
          ),
      ],
    );
  }
}

class _HeatCellWidget extends StatelessWidget {
  const _HeatCellWidget({
    required this.cell,
    required this.color,
    required this.index,
    required this.pressed,
    required this.reducedMotion,
    required this.onTapDown,
    required this.onTapUp,
    required this.onTapCancel,
  });

  final HeatCell cell;
  final Color color;
  final int index;
  final bool pressed;
  final bool reducedMotion;
  final GestureTapDownCallback onTapDown;
  final GestureTapUpCallback onTapUp;
  final GestureTapCancelCallback onTapCancel;

  @override
  Widget build(BuildContext context) {
    final scale = pressed ? 0.95 : 1.0;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        SizedBox(
          height: 16,
          child: cell.isToday
              ? Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 5,
                    vertical: 1,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF505F76),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text(
                    'INI',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 8,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                )
              : null,
        ),
        const SizedBox(height: 2),
        Text(
          cell.label,
          maxLines: 1,
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 12,
            fontWeight: cell.isToday ? FontWeight.w700 : FontWeight.w500,
            color: cell.isToday
                ? const Color(0xFF131B2E)
                : const Color(0xFF505F76),
          ),
        ),
        const SizedBox(height: 8),
        GestureDetector(
          onTapDown: onTapDown,
          onTapUp: onTapUp,
          onTapCancel: onTapCancel,
          child: AnimatedContainer(
            duration: reducedMotion
                ? Duration.zero
                : const Duration(milliseconds: 150),
            curve: Curves.easeOut,
            width: 32,
            height: 32,
            transform: Matrix4.diagonal3Values(scale, scale, 1),
            transformAlignment: Alignment.center,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(11),
              boxShadow: cell.state == 'full' || cell.state == 'partial'
                  ? <BoxShadow>[
                      BoxShadow(
                        color: color.withValues(alpha: 0.4),
                        blurRadius: 10,
                        offset: const Offset(0, 3),
                      ),
                    ]
                  : null,
              border: cell.isToday
                  ? Border.all(color: AppColors.primary600, width: 2)
                  : null,
            ),
            child: cell.state == 'full' || cell.state == 'partial'
                ? Center(
                    child: Icon(
                      cell.state == 'full' ? Icons.check_rounded : Icons.circle,
                      size: cell.state == 'full' ? 16 : 8,
                      color: Colors.white,
                    ),
                  )
                : null,
          ),
        ),
      ],
    );
  }
}

/// Mini 7-day consistency heatmap matching the PWA dashboard strip:
/// emerald = full, amber = partial, slate = empty, with a "Hari Ini" marker.
class WeekHeatmapLegacy extends StatelessWidget {
  const WeekHeatmapLegacy({required this.cells, super.key});

  final List<HeatCell> cells;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    Color fill(String state) => switch (state) {
      'full' => AppColors.primary500,
      'partial' => AppColors.amber,
      'empty' => isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
      _ => isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
    };

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: cells.map((cell) {
        final color = fill(cell.state);
        return Column(
          children: [
            Stack(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: color,
                    borderRadius: BorderRadius.circular(11),
                    boxShadow: cell.state == 'full' || cell.state == 'partial'
                        ? <BoxShadow>[
                            BoxShadow(
                              color: color.withValues(alpha: 0.4),
                              blurRadius: 10,
                              offset: const Offset(0, 3),
                            ),
                          ]
                        : null,
                  ),
                  child: cell.state == 'full' || cell.state == 'partial'
                      ? Center(
                          child: Icon(
                            cell.state == 'full'
                                ? Icons.check_rounded
                                : Icons.circle,
                            size: cell.state == 'full' ? 18 : 9,
                            color: Colors.white,
                          ),
                        )
                      : null,
                ),
                if (cell.isToday)
                  Positioned(
                    top: -6,
                    left: 0,
                    right: 0,
                    child: Container(
                      alignment: Alignment.center,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 5,
                          vertical: 1,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.primary600,
                          borderRadius: BorderRadius.circular(9999),
                        ),
                        child: const Text(
                          'INI',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 7,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.3,
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              cell.label,
              style: theme.textTheme.labelSmall?.copyWith(
                color: cell.isToday
                    ? AppColors.primary600
                    : theme.colorScheme.onSurfaceVariant,
                fontWeight: cell.isToday ? FontWeight.w800 : FontWeight.w600,
              ),
            ),
          ],
        );
      }).toList(),
    );
  }
}
