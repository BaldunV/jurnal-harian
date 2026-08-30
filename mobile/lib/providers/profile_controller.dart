import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/api/api_exception.dart';
import '../models/student.dart';
import '../services/profile_service.dart';
import '../providers/session_provider.dart';
import 'service_providers.dart';

enum ProfileOperationStatus {
  idle,
  saving,
  uploading,
  changingPassword,
  success,
  error,
}

class ProfileOperation {
  const ProfileOperation(this.status, [this.message]);

  final ProfileOperationStatus status;
  final String? message;

  bool get isBusy =>
      status == ProfileOperationStatus.saving ||
      status == ProfileOperationStatus.uploading ||
      status == ProfileOperationStatus.changingPassword;
}

class ProfileState {
  const ProfileState({
    this.operation = const ProfileOperation(ProfileOperationStatus.idle),
  });

  final ProfileOperation operation;

  ProfileState copyWith({ProfileOperation? operation}) {
    return ProfileState(operation: operation ?? this.operation);
  }
}

final profileControllerProvider =
    NotifierProvider<ProfileController, ProfileState>(ProfileController.new);

class ProfileController extends Notifier<ProfileState> {
  @override
  ProfileState build() => const ProfileState();

  ProfileService get _service => ref.read(profileServiceProvider);

  Future<Student> updateProfile({
    required String name,
    required String worshipType,
  }) async {
    state = state.copyWith(
      operation: const ProfileOperation(ProfileOperationStatus.saving),
    );
    try {
      final student = await _service.update(
        name: name,
        worshipType: worshipType,
      );
      ref.read(sessionControllerProvider.notifier).updateStudent(student);
      state = state.copyWith(
        operation: const ProfileOperation(ProfileOperationStatus.success),
      );
      return student;
    } on ApiException catch (error) {
      state = state.copyWith(
        operation: ProfileOperation(
          ProfileOperationStatus.error,
          error.message,
        ),
      );
      rethrow;
    } on Object {
      state = state.copyWith(
        operation: const ProfileOperation(
          ProfileOperationStatus.error,
          'Profil belum diperbarui.',
        ),
      );
      rethrow;
    }
  }

  Future<String> uploadPhoto(String filePath) async {
    state = state.copyWith(
      operation: const ProfileOperation(ProfileOperationStatus.uploading),
    );
    try {
      final url = await _service.uploadPhoto(filePath);
      state = state.copyWith(
        operation: const ProfileOperation(ProfileOperationStatus.success),
      );
      return url;
    } on ApiException catch (error) {
      state = state.copyWith(
        operation: ProfileOperation(
          ProfileOperationStatus.error,
          error.message,
        ),
      );
      rethrow;
    } on Object {
      state = state.copyWith(
        operation: const ProfileOperation(
          ProfileOperationStatus.error,
          'Foto profil belum terunggah.',
        ),
      );
      rethrow;
    }
  }

  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    state = state.copyWith(
      operation: const ProfileOperation(
        ProfileOperationStatus.changingPassword,
      ),
    );
    try {
      await _service.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
      );
      state = state.copyWith(
        operation: const ProfileOperation(ProfileOperationStatus.success),
      );
    } on ApiException catch (error) {
      state = state.copyWith(
        operation: ProfileOperation(
          ProfileOperationStatus.error,
          error.message,
        ),
      );
      rethrow;
    } on Object {
      state = state.copyWith(
        operation: const ProfileOperation(
          ProfileOperationStatus.error,
          'Password belum diubah.',
        ),
      );
      rethrow;
    }
  }

  void resetOperation() {
    state = state.copyWith(
      operation: const ProfileOperation(ProfileOperationStatus.idle),
    );
  }
}
