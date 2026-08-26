import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';

class ApiException implements Exception {
  const ApiException(this.message, [this.statusCode]);
  final String message;
  final int? statusCode;
  @override
  String toString() => message;
}

class ApiClient {
  ApiClient({http.Client? client}) : _client = client ?? http.Client();
  final http.Client _client;
  String? _token;

  Future<void> restoreToken() async {
    _token = (await SharedPreferences.getInstance()).getString('auth_token');
  }

  Future<void> saveToken(String token) async {
    _token = token;
    await (await SharedPreferences.getInstance())
        .setString('auth_token', token);
  }

  Future<void> clearToken() async {
    _token = null;
    await (await SharedPreferences.getInstance()).remove('auth_token');
  }

  bool get isAuthenticated => _token?.isNotEmpty == true;

  Future<Map<String, dynamic>> get(String path,
      {Map<String, String>? query}) async {
    final uri = Uri.parse('${AppConfig.apiBaseUrl}$path')
        .replace(queryParameters: query);
    return _decode(await _client.get(uri, headers: _headers));
  }

  Future<Map<String, dynamic>> post(String path,
      {Map<String, dynamic>? body}) async {
    final uri = Uri.parse('${AppConfig.apiBaseUrl}$path');
    return _decode(await _client.post(uri,
        headers: _headers, body: jsonEncode(body ?? {})));
  }

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Map<String, dynamic> _decode(http.Response response) {
    final dynamic decoded =
        response.body.isEmpty ? {} : jsonDecode(response.body);
    final data =
        decoded is Map<String, dynamic> ? decoded : <String, dynamic>{};
    if (response.statusCode >= 200 && response.statusCode < 300) return data;
    final errors = data['errors'];
    final firstError =
        errors is Map && errors.isNotEmpty ? errors.values.first : null;
    final validationMessage = firstError is List && firstError.isNotEmpty
        ? '${firstError.first}'
        : firstError?.toString();
    final nestedData = data['data'];
    final nestedMessage =
        nestedData is Map ? nestedData['message'] ?? nestedData['error'] : null;
    final message = validationMessage ??
        data['message'] ??
        data['error'] ??
        nestedMessage ??
        'অনুরোধটি সম্পন্ন হয়নি';
    throw ApiException('$message', response.statusCode);
  }
}
