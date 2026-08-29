import 'api_client.dart';
import 'package:http_parser/http_parser.dart';

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

  Future<Map<String, dynamic>> uploadResume({
    required String name,
    required String fileName,
    required List<int> fileBytes,
  }) async {
    final response = await api.postMultipartBytes(
      '/candidate/upload-resume',
      fields: {'name': name},
      fileField: 'resume_file',
      bytes: fileBytes,
      filename: fileName,
      contentType: _resumeContentType(fileName),
    );
    return _nestedData(response);
  }

  Future<Map<String, dynamic>> replaceResume({
    required int id,
    required String name,
    required String fileName,
    required List<int> fileBytes,
  }) async {
    final response = await api.postMultipartBytes(
      '/candidate/update-resume/$id',
      fields: {'name': name},
      fileField: 'resume_file',
      bytes: fileBytes,
      filename: fileName,
      contentType: _resumeContentType(fileName),
    );
    return _nestedData(response);
  }

  Future<Map<String, dynamic>> renameResume({
    required int id,
    required String name,
  }) async {
    final response = await api.post(
      '/candidate/update-resume/$id',
      body: {'name': name},
    );
    return _nestedData(response);
  }

  Future<String> deleteResume(int id) async {
    final response = await api.delete('/candidate/delete-resume/$id');
    dynamic payload = response['data'];
    return payload is Map && payload['message'] != null
        ? '${payload['message']}'
        : 'Resume deleted successfully.';
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

  Map<String, dynamic> _nestedData(Map<String, dynamic> response) {
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    return (payload as Map? ?? const {}).cast<String, dynamic>();
  }

  MediaType _resumeContentType(String fileName) {
    final extension = fileName.split('.').last.toLowerCase();
    return switch (extension) {
      'pdf' => MediaType('application', 'pdf'),
      'doc' => MediaType('application', 'msword'),
      'docx' => MediaType('application',
          'vnd.openxmlformats-officedocument.wordprocessingml.document'),
      _ => MediaType('application', 'octet-stream'),
    };
  }
}
