import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:lucide_flutter/lucide_flutter.dart';
import 'package:image_picker/image_picker.dart';

import '../../core/theme/design_tokens.dart';
import '../../models/student.dart';
import '../../providers/profile_controller.dart';
import '../../providers/session_provider.dart';
import '../../widgets/app_card.dart';
import '../../widgets/profile_avatar.dart';
import '../../widgets/status_message.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _nameController = TextEditingController();
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  String? _worshipType;
  bool _isLoggingOut = false;

  @override
  void dispose() {
    _nameController.dispose();
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionControllerProvider);
    final operation = ref.watch(profileControllerProvider).operation;

    final student = session.value is AuthenticatedSession
        ? (session.value as AuthenticatedSession).student
        : null;

    if (student == null) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(
          AppSpacing.xl,
          AppSpacing.lg,
          AppSpacing.xl,
          AppSpacing.xxl,
        ),
        children: [
          Center(
            child: Container(
              width: double.infinity,
              decoration: BoxDecoration(
                gradient: AppGradients.hero,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                boxShadow: AppShadows.glow(context),
              ),
              padding: const EdgeInsets.all(AppSpacing.xl),
              child: Column(
                children: [
                  ProfileAvatar(
                    name: student.name,
                    photoUrl: student.profilePhotoUrl,
                    radius: 44,
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Text(
                    student.name,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontFamily: AppTypography.displayFont,
                      color: Colors.white,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Text(
                    '${student.className}  •  NIS ${student.nis}',
                    style: Theme.of(context).textTheme.bodyMedium
                        ?.copyWith(color: Colors.white.withValues(alpha: 0.85)),
                  ),
                  TextButton.icon(
                    onPressed: operation.isBusy
                        ? null
                        : () => _pickPhoto(student),
                    style: TextButton.styleFrom(foregroundColor: Colors.white),
                    icon: operation.status == ProfileOperationStatus.uploading
                        ? const SizedBox.square(
                            dimension: 16,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(LucideIcons.camera, size: 18),
                    label: const Text('Ubah foto'),
                  ),
                ],
              ),
            ),
          ),
          if (operation.status == ProfileOperationStatus.error &&
              operation.message != null)
            Padding(
              padding: const EdgeInsets.only(bottom: AppSpacing.md),
              child: StatusMessage(
                tone: StatusMessageTone.error,
                message: operation.message!,
                onDismiss: () => ref
                    .read(profileControllerProvider.notifier)
                    .resetOperation(),
              ),
            ),
          if (operation.status == ProfileOperationStatus.success &&
              operation.message == null)
            Padding(
              padding: const EdgeInsets.only(bottom: AppSpacing.md),
              child: StatusMessage(
                tone: StatusMessageTone.success,
                message: 'Perubahan berhasil disimpan.',
                onDismiss: () => ref
                    .read(profileControllerProvider.notifier)
                    .resetOperation(),
              ),
            ),
          AppCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Data diri',
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                ),
                const SizedBox(height: 12),
                _Row(
                  icon: LucideIcons.contact,
                  label: 'NIS',
                  value: student.nis,
                ),
                const Divider(height: 1),
                _Row(
                  icon: LucideIcons.school,
                  label: 'Kelas',
                  value: student.className,
                ),
                const Divider(height: 1),
                _Row(
                  icon: LucideIcons.heartHandshake,
                  label: 'Jenis ibadah',
                  value: student.worshipType == 'muslim'
                      ? 'Muslim'
                      : 'Non-Muslim',
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.md),
          FilledButton.icon(
            key: const Key('edit_profile_button'),
            onPressed: operation.isBusy ? null : () => _editProfile(student),
            icon: const Icon(LucideIcons.userCog),
            label: const Text('Ubah nama & jenis ibadah'),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            key: const Key('change_password_button'),
            onPressed: operation.isBusy ? null : _changePassword,
            icon: const Icon(LucideIcons.lock),
            label: const Text('Ubah password'),
          ),
          const SizedBox(height: 24),
          OutlinedButton.icon(
            key: const Key('logout_button'),
            onPressed: _isLoggingOut ? null : _logout,
            style: OutlinedButton.styleFrom(
              foregroundColor: Theme.of(context).colorScheme.error,
              side: BorderSide(color: Theme.of(context).colorScheme.error),
            ),
            icon: _isLoggingOut
                ? const SizedBox.square(
                    dimension: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(LucideIcons.logOut),
            label: Text(_isLoggingOut ? 'Sedang keluar' : 'Keluar dari akun'),
          ),
        ],
      ),
    );
  }

  Future<void> _logout() async {
    setState(() => _isLoggingOut = true);
    try {
      await ref.read(sessionControllerProvider.notifier).logout();
    } on Object {
      if (mounted) {
        setState(() => _isLoggingOut = false);
      }
    }
  }

  Future<void> _pickPhoto(Student student) async {
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 640,
      maxHeight: 640,
      imageQuality: 82,
    );
    if (image == null || !mounted) {
      return;
    }
    try {
      final url = await ref
          .read(profileControllerProvider.notifier)
          .uploadPhoto(image.path);
      if (mounted) {
        ref
            .read(sessionControllerProvider.notifier)
            .updateStudent(student.copyWith(profilePhotoUrl: url));
        ref.read(profileControllerProvider.notifier).resetOperation();
      }
    } on Object {
      // Operation error already surfaced in the UI.
    }
  }

  Future<void> _editProfile(Student student) async {
    _nameController.text = student.name;
    _worshipType = student.worshipType;
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => _ProfileEditDialog(
        nameController: _nameController,
        worshipType: _worshipType!,
        onWorshipChanged: (value) => _worshipType = value,
      ),
    );
    if (result != true || !mounted) {
      return;
    }
    try {
      await ref
          .read(profileControllerProvider.notifier)
          .updateProfile(
            name: _nameController.text.trim(),
            worshipType: _worshipType!,
          );
      if (mounted) {
        ref.read(profileControllerProvider.notifier).resetOperation();
      }
    } on Object {
      // Operation error shown in UI.
    }
  }

  Future<void> _changePassword() async {
    _currentPasswordController.clear();
    _newPasswordController.clear();
    _confirmPasswordController.clear();
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => _PasswordDialog(
        current: _currentPasswordController,
        next: _newPasswordController,
        confirm: _confirmPasswordController,
      ),
    );
    if (result != true || !mounted) {
      return;
    }
    if (_newPasswordController.text != _confirmPasswordController.text) {
      return;
    }
    try {
      await ref
          .read(profileControllerProvider.notifier)
          .changePassword(
            currentPassword: _currentPasswordController.text,
            newPassword: _newPasswordController.text,
          );
      if (mounted) {
        ref.read(profileControllerProvider.notifier).resetOperation();
      }
    } on Object {
      // Operation error shown in UI.
    }
  }
}

class _Row extends StatelessWidget {
  const _Row({required this.icon, required this.label, required this.value});

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final colors = Theme.of(context).colorScheme;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        children: [
          Icon(icon, size: 18, color: colors.primary),
          const SizedBox(width: 10),
          SizedBox(
            width: 96,
            child: Text(label, style: Theme.of(context).textTheme.bodyMedium),
          ),
          Expanded(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: Theme.of(context).textTheme.titleMedium,
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfileEditDialog extends StatelessWidget {
  const _ProfileEditDialog({
    required this.nameController,
    required this.worshipType,
    required this.onWorshipChanged,
  });

  final TextEditingController nameController;
  final String worshipType;
  final ValueChanged<String> onWorshipChanged;

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Ubah profil'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          TextField(
            controller: nameController,
            decoration: const InputDecoration(labelText: 'Nama lengkap'),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: worshipType,
            decoration: const InputDecoration(labelText: 'Jenis ibadah'),
            items: const [
              DropdownMenuItem(value: 'muslim', child: Text('Muslim')),
              DropdownMenuItem(value: 'non_muslim', child: Text('Non-Muslim')),
            ],
            onChanged: (value) => onWorshipChanged(value ?? 'muslim'),
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: const Text('Batal'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(true),
          child: const Text('Simpan'),
        ),
      ],
    );
  }
}

class _PasswordDialog extends StatelessWidget {
  const _PasswordDialog({
    required this.current,
    required this.next,
    required this.confirm,
  });

  final TextEditingController current;
  final TextEditingController next;
  final TextEditingController confirm;

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Ubah password'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          TextField(
            controller: current,
            obscureText: true,
            decoration: const InputDecoration(labelText: 'Password saat ini'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: next,
            obscureText: true,
            decoration: const InputDecoration(labelText: 'Password baru'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: confirm,
            obscureText: true,
            decoration: const InputDecoration(
              labelText: 'Ulangi password baru',
            ),
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: const Text('Batal'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(true),
          child: const Text('Simpan'),
        ),
      ],
    );
  }
}
