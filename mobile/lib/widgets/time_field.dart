import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';
import '../core/utils/date_format.dart';

class TimeField extends StatelessWidget {
  const TimeField({
    required this.label,
    this.value,
    required this.onChanged,
    super.key,
  });

  final String label;
  final String? value;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return InkWell(
      borderRadius: BorderRadius.circular(AppRadius.md),
      onTap: () async {
        final initial = _parse(value);
        final picked = await showTimePicker(
          context: context,
          initialTime: initial ?? TimeOfDay.now(),
          builder: (context, child) => Theme(
            data: Theme.of(context).copyWith(colorScheme: colors),
            child: child!,
          ),
        );
        if (picked != null) {
          onChanged(
            '${picked.hour.toString().padLeft(2, '0')}:'
            '${picked.minute.toString().padLeft(2, '0')}',
          );
        }
      },
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: label,
          prefixIcon: const Icon(Icons.access_time_rounded),
        ),
        child: Text(
          value == null || value!.isEmpty
              ? 'Atur waktu'
              : AppDateFormat.jamMenit(value),
        ),
      ),
    );
  }

  TimeOfDay? _parse(String? value) {
    if (value == null || value.isEmpty) {
      return null;
    }
    final parts = value.split(':');
    if (parts.length != 2) {
      return null;
    }
    return TimeOfDay(
      hour: int.tryParse(parts[0]) ?? 0,
      minute: int.tryParse(parts[1]) ?? 0,
    );
  }
}
