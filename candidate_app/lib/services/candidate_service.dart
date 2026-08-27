import 'api_client.dart';

class CandidateService {
  const CandidateService(this.api);
  final ApiClient api;

  Future<Map<String, dynamic>> getProfile() async {
    final response = await api.get('/candidate');
    return _mapPayload(response);
  }

  Future<Map<String, dynamic>> getDashboard() async {
    final response = await api.get('/candidate/dashboard');
    return _mapPayload(response);
  }

  Future<List<Map<String, dynamic>>> getResumes() async {
    final response = await api.get('/candidate/resumes');
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    return (payload as List? ?? const [])
        .whereType<Map>()
        .map((item) => item.cast<String, dynamic>())
        .toList();
  }

  Future<Map<String, dynamic>> getSettings(String type) async {
    final response =
        await api.get('/candidate/settings', query: {'type': type});
    return _mapPayload(response);
  }

  Future<String> updateSettings(
      String type, Map<String, dynamic> values) async {
    final response = await api.post('/candidate/settings', body: {
      'type': type,
      ...values,
    });
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) {
      final message = payload['message'];
      if (message != null) return '$message';
      payload = payload['data'];
    }
    return payload is Map && payload['message'] != null
        ? '${payload['message']}'
        : 'Profile updated successfully!';
  }

  Future<String> updatePersonalWithPhoto(
      Map<String, dynamic> values, String photoPath) async {
    final response = await api.postMultipart('/candidate/settings',
        fields: {'type': 'personal', ...values},
        fileField: 'image',
        filePath: photoPath);
    dynamic payload = response['data'];
    return payload is Map && payload['message'] != null
        ? '${payload['message']}'
        : 'Profile updated successfully!';
  }

  Map<String, dynamic> _mapPayload(Map<String, dynamic> response) {
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    return (payload as Map? ?? const {}).cast<String, dynamic>();
  }
}
