import '../models/job.dart';
import '../models/job_category.dart';
import 'api_client.dart';

class JobService {
  const JobService(this.api);
  final ApiClient api;

  Future<List<Job>> getJobs({String keyword = '', int? categoryId}) async {
    final response = await api
        .get(api.isAuthenticated ? '/candidate/jobs' : '/jobs', query: {
      'paginate': '20',
      if (keyword.isNotEmpty) 'keyword': keyword,
      if (categoryId != null) 'category': '$categoryId',
    });
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    return (payload as List? ?? const [])
        .whereType<Map>()
        .map((item) => Job.fromJson(item.cast<String, dynamic>()))
        .toList();
  }

  Future<List<JobCategory>> getCategories() async {
    final response = await api.get('/categories');
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    return (payload as List? ?? const [])
        .whereType<Map>()
        .map((item) => JobCategory.fromJson(item.cast<String, dynamic>()))
        .where((category) => category.id > 0 && category.name.isNotEmpty)
        .toList();
  }

  Future<Map<String, dynamic>> details(String slug) async {
    final response = await api
        .get(api.isAuthenticated ? '/candidate/jobs/$slug' : '/jobs/$slug');
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    if (payload is Map && payload['job'] != null) payload = payload['job'];
    return (payload as Map).cast<String, dynamic>();
  }

  Future<bool> toggleBookmark(int jobId) async {
    if (!api.isAuthenticated) throw const ApiException('আগে লগইন করুন', 401);
    final response = await api.post('/candidate/jobs/$jobId/bookmark');
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    return payload is Map &&
        (payload['status'] == true || payload['status'] == 1);
  }

  Future<String> apply({
    required int jobId,
    required int resumeId,
    required String coverLetter,
  }) async {
    if (!api.isAuthenticated) throw const ApiException('আগে লগইন করুন', 401);
    final response = await api.post('/candidate/jobs/apply', body: {
      'job_id': jobId,
      'resume_id': resumeId,
      'cover_letter': coverLetter,
    });
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    if (payload is Map && payload['status'] == false) {
      throw ApiException('${payload['message'] ?? 'Application failed'}');
    }
    return payload is Map
        ? '${payload['message'] ?? 'Application submitted successfully!'}'
        : 'Application submitted successfully!';
  }
}
