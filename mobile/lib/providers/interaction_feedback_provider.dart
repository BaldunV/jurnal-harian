import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/feedback/interaction_feedback.dart';

/// Shared, lazily-created interaction feedback (haptic). Disposed with the
/// provider container.
///
/// The initial preload is awaited so the first tap is instant.
final interactionFeedbackProvider = Provider<InteractionFeedback>((ref) {
  final feedback = InteractionFeedback();
  unawaited(feedback.preload());
  ref.onDispose(feedback.dispose);
  return feedback;
});
