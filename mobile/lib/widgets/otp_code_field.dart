import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../core/theme/design_tokens.dart';

class OtpCodeField extends StatefulWidget {
  const OtpCodeField({
    required this.controller,
    required this.focusNode,
    required this.enabled,
    required this.onChanged,
    required this.onSubmitted,
    this.hasError = false,
    super.key,
  });

  final TextEditingController controller;
  final FocusNode focusNode;
  final bool enabled;
  final ValueChanged<String> onChanged;
  final ValueChanged<String> onSubmitted;
  final bool hasError;

  @override
  State<OtpCodeField> createState() => _OtpCodeFieldState();
}

class _OtpCodeFieldState extends State<OtpCodeField> {
  @override
  void initState() {
    super.initState();
    widget.controller.addListener(_refresh);
    widget.focusNode.addListener(_refresh);
  }

  @override
  void didUpdateWidget(covariant OtpCodeField oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.controller != widget.controller) {
      oldWidget.controller.removeListener(_refresh);
      widget.controller.addListener(_refresh);
    }
    if (oldWidget.focusNode != widget.focusNode) {
      oldWidget.focusNode.removeListener(_refresh);
      widget.focusNode.addListener(_refresh);
    }
  }

  @override
  void dispose() {
    widget.controller.removeListener(_refresh);
    widget.focusNode.removeListener(_refresh);
    super.dispose();
  }

  void _refresh() {
    if (mounted) {
      setState(() {});
    }
  }

  @override
  Widget build(BuildContext context) {
    final code = widget.controller.text;
    final colors = Theme.of(context).colorScheme;

    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Semantics(
      label: 'Kode OTP enam digit',
      value: '${code.length} dari 6 digit terisi',
      textField: true,
      child: SizedBox(
        height: 62,
        child: Stack(
          children: [
            ExcludeSemantics(
              child: Row(
                children: List<Widget>.generate(6, (index) {
                  final filled = index < code.length;
                  final isCurrent =
                      widget.focusNode.hasFocus &&
                      code.length < 6 &&
                      index == code.length;

                  final borderColor = switch ((
                    widget.hasError,
                    isCurrent,
                    filled,
                  )) {
                    (true, _, _) => AppColors.error,
                    (_, true, _) || (_, _, true) => AppColors.primary600,
                    _ =>
                      isDark
                          ? const Color(0xFF334155)
                          : const Color(0xFFE2E8F0),
                  };

                  final fillColor = switch ((widget.hasError, filled, isDark)) {
                    (true, _, _) => AppColors.error.withValues(alpha: 0.06),
                    (_, true, true) => AppColors.primary500.withValues(
                      alpha: 0.20,
                    ),
                    (_, true, false) => AppColors.primary500.withValues(
                      alpha: 0.12,
                    ),
                    (_, _, true) => const Color(0xFF0F172A),
                    (_, _, false) => Colors.white,
                  };

                  return Expanded(
                    child: Padding(
                      padding: EdgeInsets.only(right: index == 5 ? 0 : 8),
                      child: AnimatedContainer(
                        duration: MediaQuery.disableAnimationsOf(context)
                            ? Duration.zero
                            : const Duration(milliseconds: 160),
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: fillColor,
                          borderRadius: BorderRadius.circular(AppRadius.sm),
                          border: Border.all(
                            color: borderColor,
                            width: isCurrent || widget.hasError || filled
                                ? 2
                                : 1,
                          ),
                          boxShadow: isCurrent
                              ? AppShadows.glow(context)
                              : null,
                        ),
                        child: Text(
                          index < code.length ? code[index] : '',
                          style: Theme.of(context).textTheme.headlineSmall
                              ?.copyWith(
                                color: widget.hasError
                                    ? AppColors.error
                                    : colors.onSurface,
                                fontWeight: FontWeight.w700,
                              ),
                        ),
                      ),
                    ),
                  );
                }),
              ),
            ),
            Positioned.fill(
              child: TextField(
                controller: widget.controller,
                focusNode: widget.focusNode,
                enabled: widget.enabled,
                keyboardType: TextInputType.number,
                textInputAction: TextInputAction.done,
                autofillHints: const <String>[AutofillHints.oneTimeCode],
                inputFormatters: <TextInputFormatter>[
                  FilteringTextInputFormatter.digitsOnly,
                  LengthLimitingTextInputFormatter(6),
                ],
                maxLength: 6,
                enableInteractiveSelection: false,
                showCursor: false,
                style: const TextStyle(color: Colors.transparent),
                cursorColor: Colors.transparent,
                decoration: const InputDecoration(
                  counterText: '',
                  filled: false,
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  contentPadding: EdgeInsets.zero,
                ),
                onChanged: widget.onChanged,
                onSubmitted: widget.onSubmitted,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
