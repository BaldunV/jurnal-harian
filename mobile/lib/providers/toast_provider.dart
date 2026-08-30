import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/legacy.dart' show ChangeNotifierProvider;

import '../widgets/toast.dart';

class ToastItem {
  ToastItem({
    required this.message,
    this.type = ToastType.success,
    this.duration = 4000,
  });

  final String message;
  final ToastType type;
  final int duration;
}

class ToastNotifier extends ChangeNotifier {
  final List<ToastItem> _items = <ToastItem>[];

  List<ToastItem> get items => List.unmodifiable(_items);

  void showToast({
    required String message,
    ToastType type = ToastType.success,
    int duration = 4000,
  }) {
    final item = ToastItem(message: message, type: type, duration: duration);
    _items.add(item);
    notifyListeners();
    Timer(Duration(milliseconds: duration + 500), () {
      final index = _items.indexOf(item);
      if (index != -1) {
        _items.removeAt(index);
        notifyListeners();
      }
    });
  }
}

final toastProvider = ChangeNotifierProvider<ToastNotifier>((ref) {
  return ToastNotifier();
});
