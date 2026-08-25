import 'package:flutter/foundation.dart';
import '../models/job.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../services/job_service.dart';

class AppState extends ChangeNotifier {
  AppState(this.api)
      : auth = AuthService(api),
        jobsApi = JobService(api);
  final ApiClient api;
  final AuthService auth;
  final JobService jobsApi;
  List<Job> jobs = const [];
  bool busy = false;
  String? error;

  bool get loggedIn => api.isAuthenticated;

  Future<void> initialize() async {
    await api.restoreToken();
    await loadJobs();
  }

  Future<void> loadJobs({String keyword = ''}) async {
    busy = true;
    error = null;
    notifyListeners();
    try {
      jobs = await jobsApi.getJobs(keyword: keyword);
    } catch (e) {
      error = e.toString();
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  Future<void> login(String email, String password) async {
    await auth.login(email, password);
    await loadJobs();
  }

  Future<void> logout() async {
    await api.clearToken();
    await loadJobs();
  }
}
