class AppConfig {
  AppConfig({required String apiBaseUrl}) : apiBaseUrl = _normalize(apiBaseUrl);

  factory AppConfig.fromEnvironment() {
    return AppConfig(
      apiBaseUrl: const String.fromEnvironment(
        'API_BASE_URL',
        defaultValue: defaultApiBaseUrl,
      ),
    );
  }

  static const defaultApiBaseUrl = 'http://192.168.101.4:8000/api';

  final String apiBaseUrl;

  Uri get apiUri => Uri.parse(apiBaseUrl);

  String get environmentLabel {
    final port = apiUri.hasPort ? ':${apiUri.port}' : '';
    return '${apiUri.host}$port';
  }

  static String _normalize(String value) {
    final trimmed = value.trim().replaceAll(RegExp(r'/+$'), '');
    final uri = Uri.tryParse(trimmed);

    if (uri == null ||
        !uri.hasScheme ||
        !uri.hasAuthority ||
        !const {'http', 'https'}.contains(uri.scheme)) {
      throw ArgumentError.value(value, 'apiBaseUrl', 'URL API tidak valid.');
    }

    return '$trimmed/';
  }
}


