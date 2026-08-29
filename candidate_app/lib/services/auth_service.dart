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

  Future<Map<String, dynamic>> register(
      String name, String email, String password) async {
    final response = await api.post('/register', body: {
      'name': name,
      'email': email,
      'role': 'candidate',
      'password': password,
      'password_confirmation': password,
    });
    final payload = response['data'];
    return (payload is Map ? payload : response).cast<String, dynamic>();
  }

  Future<Map<String, dynamic>> verificationStatus(String email) async {
    final response = await api.get(
      '/email/verification-status',
      query: {'email': email},
    );
    final payload = response['data'];
    return (payload is Map ? payload : response).cast<String, dynamic>();
  }

  Future<String> resendVerification(String email) async {
    final response = await api.post(
      '/email/resend-verification',
      body: {'email': email},
    );
    return '${response['message'] ?? 'A new verification email has been sent.'}';
  }

  Future<String> requestPasswordReset(String email) async {
    final response = await api.post('/password/email', body: {'email': email});
    final payload = response['data'];
    return payload is Map && payload['message'] != null
        ? '${payload['message']}'
        : 'We sent a password reset code to your email.';
  }

  Future<String> resetPassword({
    required String email,
    required String code,
    required String password,
  }) async {
    final response = await api.post('/password/reset', body: {
      'email': email,
      'code': code,
      'password': password,
      'password_confirmation': password,
    });
    final payload = response['data'];
    return payload is Map && payload['message'] != null
        ? '${payload['message']}'
        : 'Password reset successfully.';
  }
}
