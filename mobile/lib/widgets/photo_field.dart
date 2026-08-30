import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../core/theme/design_tokens.dart';

class PhotoField extends StatelessWidget {
  const PhotoField({
    required this.label,
    required this.onPicked,
    this.file,
    this.url,
    super.key,
  });

  final String label;
  final ValueChanged<String> onPicked;
  final File? file;
  final String? url;

  Future<void> _pick(BuildContext context) async {
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 1280,
      maxHeight: 1280,
      imageQuality: 80,
    );
    if (image != null && context.mounted) {
      onPicked(image.path);
    }
  }

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    final ImageProvider? image = file != null
        ? FileImage(file!)
        : (url != null && url!.isNotEmpty ? NetworkImage(url!) : null);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: AppSpacing.sm),
        InkWell(
          borderRadius: BorderRadius.circular(AppRadius.md),
          onTap: () => _pick(context),
          child: AspectRatio(
            aspectRatio: 16 / 9,
            child: Container(
              decoration: BoxDecoration(
                color: colors.surfaceContainerLow,
                borderRadius: BorderRadius.circular(AppRadius.md),
                border: Border.all(color: colors.outline),
                image: image == null
                    ? null
                    : DecorationImage(image: image, fit: BoxFit.cover),
              ),
              child: image == null
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.add_a_photo_outlined,
                            color: colors.onSurfaceVariant,
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Tambahkan foto',
                            style: Theme.of(context).textTheme.bodySmall
                                ?.copyWith(color: colors.onSurfaceVariant),
                          ),
                        ],
                      ),
                    )
                  : null,
            ),
          ),
        ),
        if (file != null || (url != null && url!.isNotEmpty))
          Padding(
            padding: const EdgeInsets.only(top: 6),
            child: TextButton.icon(
              onPressed: () => _pick(context),
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('Ganti foto'),
            ),
          ),
      ],
    );
  }
}
