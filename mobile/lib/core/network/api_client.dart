import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import '../errors/api_exception.dart';
import '../storage/token_storage.dart';

class ApiClient {
  ApiClient({
    required String baseUrl,
    required this.tokenStorage,
    http.Client? httpClient,
    this.timeout = const Duration(seconds: 20),
  }) : _baseUri = _parseBaseUrl(baseUrl),
       _httpClient = httpClient ?? http.Client();

  final Uri _baseUri;
  final TokenStorage tokenStorage;
  final http.Client _httpClient;
  final Duration timeout;

  Future<Map<String, dynamic>> get(
    String path, {
    bool authenticated = false,
  }) {
    return _send('GET', path, authenticated: authenticated);
  }

  Future<Map<String, dynamic>> post(
    String path, {
    Map<String, dynamic>? body,
    bool authenticated = false,
  }) {
    return _send(
      'POST',
      path,
      body: body,
      authenticated: authenticated,
    );
  }

  Future<Map<String, dynamic>> _send(
    String method,
    String path, {
    Map<String, dynamic>? body,
    required bool authenticated,
  }) async {
    final request = http.Request(method, _resolve(path));
    request.headers['Accept'] = 'application/json';

    if (body != null) {
      request.headers['Content-Type'] = 'application/json';
      request.body = jsonEncode(body);
    }

    if (authenticated) {
      final token = await tokenStorage.readToken();
      if (token == null || token.isEmpty) {
        throw const ApiException(
          message: 'Your session is unavailable. Please sign in again.',
          statusCode: 401,
        );
      }
      request.headers['Authorization'] = 'Bearer $token';
    }

    try {
      final streamedResponse = await _httpClient.send(request).timeout(timeout);
      final response = await http.Response.fromStream(streamedResponse);
      return _decode(response);
    } on TimeoutException {
      throw const ApiException(
        message: 'The request timed out. Check your connection and try again.',
      );
    } on http.ClientException {
      throw const ApiException(
        message: 'Unable to reach ResQLink. Check your connection and try again.',
      );
    }
  }

  Map<String, dynamic> _decode(http.Response response) {
    final decoded = _decodeBody(response.body);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    throw ApiException(
      message: decoded['message'] as String? ?? 'The request could not be completed.',
      statusCode: response.statusCode,
      fieldErrors: _parseFieldErrors(decoded['errors']),
    );
  }

  Map<String, dynamic> _decodeBody(String body) {
    if (body.trim().isEmpty) {
      return {};
    }

    try {
      final decoded = jsonDecode(body);
      return decoded is Map<String, dynamic> ? decoded : {};
    } on FormatException {
      throw const ApiException(
        message: 'ResQLink returned an unreadable response.',
      );
    }
  }

  Map<String, List<String>> _parseFieldErrors(Object? errors) {
    if (errors is! Map) {
      return {};
    }

    return errors.map((key, value) {
      final messages = value is List
          ? value.map((message) => message.toString()).toList()
          : <String>[value.toString()];
      return MapEntry(key.toString(), messages);
    });
  }

  Uri _resolve(String path) => _baseUri.resolve(path.replaceFirst(RegExp(r'^/'), ''));

  static Uri _parseBaseUrl(String baseUrl) {
    final normalized = baseUrl.endsWith('/') ? baseUrl : '$baseUrl/';
    final uri = Uri.tryParse(normalized);
    if (baseUrl.trim().isEmpty || uri == null || !uri.hasScheme || !uri.hasAuthority) {
      throw ArgumentError.value(baseUrl, 'baseUrl', 'Must be an absolute URL.');
    }
    return uri;
  }
}
