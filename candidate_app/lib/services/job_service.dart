import '../models/job.dart';
import 'api_client.dart';

class JobService {
  const JobService(this.api);
  final ApiClient api;

  Future<List<Job>> getJobs({String keyword = ''}) async {
    final response = await api.get(api.isAuthenticated ? '/candidate/jobs' : '/jobs',
        query: {'paginate': '20', if (keyword.isNotEmpty) 'keyword': keyword});
    dynamic payload = response['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    if (payload is Map && payload['data'] != null) payload = payload['data'];
    return (payload as List? ?? const [])
        .whereType<Map>()
        .map((item) => Job.fromJson(item.cast<String, dynamic>()))
        .toList();
  }

  Future<Map<String, dynamic>> details(String slug) async {
    final response = await api.get(
        api.isAuthenticated ? '/candidate/jobs/$slug' : '/jobs/$slug');
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
    return payload is Map && (payload['status'] == true || payload['status'] == 1);
  }
}

