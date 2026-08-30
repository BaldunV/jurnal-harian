import 'package:flutter/services.dart';

class InteractionFeedback {
  Future<void> preload() async {}

  Future<void> buttonPress() async {
    await HapticFeedback.lightImpact();
  }

  Future<void> success() async {
    await HapticFeedback.mediumImpact();
  }

  Future<void> habitChecked(String key) async {
    await HapticFeedback.lightImpact();
  }

  void dispose() {}
}
