import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class MobileApi {
  MobileApi._();
  static final MobileApi instance = MobileApi._();
  static const _storage = FlutterSecureStorage();
  static const baseUrl = String.fromEnvironment(
    'RESQLINK_API_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );
  String? _token;
  bool mustChangePassword = false;
  String? get token => _token;

  Future<String?> restoreToken() async {
    _token = await _storage.read(key: 'access_token');
    mustChangePassword = await _storage.read(key: 'must_change_password') == 'true';
    if (_token != null) {
      try {
        final response = await http.get(Uri.parse('$baseUrl/user'), headers: _headers());
        final result = _decode(response);
        mustChangePassword = (result['user'] as Map)['must_change_password'] == true;
        await _storage.write(key: 'must_change_password', value: mustChangePassword.toString());
      } catch (_) {
        await logout();
      }
    }
    return _token;
  }

  Future<Map<String, dynamic>> login(String login, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Accept': 'application/json'},
      body: {'login': login, 'password': password, 'device_name': 'resqlink-volunteer-app'},
    );
    final data = _decode(response);
    _token = data['token'] as String?;
    mustChangePassword = data['must_change_password'] == true;
    if (_token != null) await _storage.write(key: 'access_token', value: _token);
    await _storage.write(key: 'must_change_password', value: mustChangePassword.toString());
    return data;
  }

  Future<void> changePassword(String current, String password, String confirmation) async {
    final response = await http.put(
      Uri.parse('$baseUrl/password/change'),
      headers: _headers(),
      body: {'current_password': current, 'password': password, 'password_confirmation': confirmation},
    );
    _decode(response);
    mustChangePassword = false;
    await _storage.write(key: 'must_change_password', value: 'false');
  }

  Future<Map<String, dynamic>> submitIncident(Map<String, String> fields, Uint8List? photo) async {
    final request = http.MultipartRequest('POST', Uri.parse('$baseUrl/incidents'));
    request.headers.addAll(_headers());
    request.fields.addAll(fields);
    if (photo != null) {
      request.files.add(http.MultipartFile.fromBytes('photo', photo, filename: 'incident-photo.jpg'));
    }
    final response = await http.Response.fromStream(await request.send());
    return _decode(response);
  }

  Future<void> logout() async {
    if (_token != null) {
      try { await http.post(Uri.parse('$baseUrl/logout'), headers: _headers()); } catch (_) {}
    }
    _token = null;
    await _storage.delete(key: 'access_token');
    await _storage.delete(key: 'must_change_password');
  }

  Map<String, String> _headers() => {
    'Accept': 'application/json',
    if (_token != null) 'Authorization': 'Bearer $_token',
  };

  Map<String, dynamic> _decode(http.Response response) {
    final dynamic decoded = jsonDecode(response.body);
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final errors = decoded is Map ? decoded['errors'] as Map? : null;
      final values = errors?.values;
      final first = values == null || values.isEmpty ? null : values.first;
      final message = first is List && first.isNotEmpty ? first.first : decoded['message'];
      throw Exception(message?.toString() ?? 'Request failed (${response.statusCode}).');
    }
    return Map<String, dynamic>.from(decoded as Map);
  }
}
