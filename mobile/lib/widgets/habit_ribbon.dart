import 'package:flutter/material.dart';

import '../core/theme/habit_ribbon_colors.dart';

class HabitRibbon extends StatelessWidget {
  const HabitRibbon({required this.completed, super.key})
    : assert(completed >= 0 && completed <= habitCount);

  static const habitCount = 7;

  final int completed;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).extension<HabitRibbonColors>()!;

    return Semantics(
      label: 'Kemajuan tujuh kebiasaan',
      value: '$completed dari $habitCount selesai',
      child: ExcludeSemantics(
        child: Row(
          children: List<Widget>.generate(habitCount, (index) {
            final isCompleted = index < completed;

            return Expanded(
              child: Padding(
                padding: EdgeInsets.only(
                  right: index == habitCount - 1 ? 0 : 4,
                ),
                child: Container(
                  height: 12,
                  decoration: BoxDecoration(
                    color: isCompleted ? colors.active : colors.inactive,
                    borderRadius: BorderRadius.horizontal(
                      left: index == 0
                          ? const Radius.circular(20)
                          : const Radius.circular(3),
                      right: index == habitCount - 1
                          ? const Radius.circular(20)
                          : const Radius.circular(3),
                    ),
                    border: isCompleted
                        ? Border(
                            bottom: BorderSide(color: colors.marker, width: 2),
                          )
                        : null,
                  ),
                ),
              ),
            );
          }),
        ),
      ),
    );
  }
}
