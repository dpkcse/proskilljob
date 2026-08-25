import 'api_client.dart';

class AuthService {
  const AuthService(this.api);
  final ApiClient api;

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await api.post('/login', body: {
      'email': email,
      'password': password,
    });
    final payload = response['data'] as Map<String, dynamic>? ?? response;
    final nested = payload['data'] as Map<String, dynamic>? ?? payload;
    final token = '${nested['token'] ?? ''}';
    if (token.isEmpty) throw const ApiException('লগইন টোকেন পাওয়া যায়নি');
    await api.saveToken(token);
    return (nested['user'] as Map?)?.cast<String, dynamic>() ?? {};
  }

  Future<void> register(String name, String email, String password) async {
    await api.post('/register', body: {
      'name': name,
      'email': email,
      'role': 'candidate',
      'password': password,
      'password_confirmation': password,
    });
  }
}
