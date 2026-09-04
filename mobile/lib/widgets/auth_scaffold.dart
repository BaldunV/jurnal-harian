import 'package:flutter/material.dart';

import '../core/theme/design_tokens.dart';
import 'app_card.dart';
import 'school_brand_header.dart';

class AuthScaffold extends StatelessWidget {
  const AuthScaffold({
    required this.title,
    required this.description,
    required this.child,
    this.onBack,
    this.background,
    super.key,
  });

  final String title;
  final String description;
  final Widget child;
  final VoidCallback? onBack;

  /// Optional decorative layer rendered behind the auth content.
  final Widget? background;

  @override
  Widget build(BuildContext context) {
    final body = LayoutBuilder(
      builder: (context, constraints) {
        final horizontalPadding = constraints.maxWidth < 380 ? 18.0 : 24.0;
        return SingleChildScrollView(
          keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
          padding: EdgeInsets.fromLTRB(
            horizontalPadding,
            18,
            horizontalPadding,
            32,
          ),
          child: ConstrainedBox(
            constraints: BoxConstraints(
              minHeight: (constraints.maxHeight - 50).clamp(0, double.infinity),
            ),
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 480),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    if (onBack != null) ...[
                      Align(
                        alignment: Alignment.centerLeft,
                        child: IconButton.filledTonal(
                          onPressed: onBack,
                          tooltip: 'Kembali ke halaman masuk',
                          icon: const Icon(Icons.arrow_back_rounded),
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],
                    const SchoolBrandHeader(),
                    const SizedBox(height: AppSpacing.xxl),
                    Text(
                      title,
                      style: Theme.of(context).textTheme.displaySmall,
                    ),
                    const SizedBox(height: AppSpacing.sm),
                    Text(
                      description,
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                    const SizedBox(height: AppSpacing.xl),
                    AppCard(
                      padding: EdgeInsets.all(
                        constraints.maxWidth < 380
                            ? AppSpacing.lg
                            : AppSpacing.xl,
                      ),
                      child: child,
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );

    if (background == null) {
      return Scaffold(body: SafeArea(child: body));
    }

    return Scaffold(
      body: Stack(
        fit: StackFit.expand,
        children: [
          Positioned.fill(child: background!),
          SafeArea(child: body),
        ],
      ),
    );
  }
}
