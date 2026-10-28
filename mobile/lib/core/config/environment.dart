abstract final class Environment {
  static const apiBaseUrl = String.fromEnvironment('API_BASE_URL');
  static const mapStyleUrl = String.fromEnvironment('MAP_STYLE_URL');

  static bool get hasApiBaseUrl => apiBaseUrl.trim().isNotEmpty;

  static bool get hasMapStyleUrl => mapStyleUrl.trim().isNotEmpty;
}
