import 'api_client.dart';
import './api_exception.dart';

/// Extracts the `data` payload from a success envelope, mirroring the auth
/// service behaviour. Throws [ApiException] when the envelope is malformed.
JsonMap extractData(JsonMap response) {
  final data = response['data'];
  if (response['success'] != true || data is! Map<Object?, Object?>) {
    throw const ApiException(
      kind: ApiFailureKind.server,
      message: 'Respons layanan tidak dapat dibaca.',
    );
  }

  return data.map((key, value) => MapEntry(key.toString(), value));
}
