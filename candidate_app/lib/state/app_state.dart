import 'package:flutter/foundation.dart';
import '../models/job.dart';
import '../models/job_category.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../services/candidate_service.dart';
import '../services/job_service.dart';

class AppState extends ChangeNotifier {
  AppState(this.api)
      : auth = AuthService(api),
        candidateApi = CandidateService(api),
        jobsApi = JobService(api);
  final ApiClient api;
  final AuthService auth;
  final CandidateService candidateApi;
  final JobService jobsApi;
  List<Job> jobs = const [];
  List<JobCategory> categories = const [];
  int? selectedCategoryId;
  Map<String, dynamic> currentUser = const {};
  Map<String, dynamic> dashboard = const {};
  List<Map<String, dynamic>> resumes = const [];
  bool profileBusy = false;
  String? profileError;
  bool busy = false;
  String? error;

  bool get loggedIn => api.isAuthenticated;

  Future<void> initialize() async {
    await api.restoreToken();
    try {
      categories = await jobsApi.getCategories();
    } catch (_) {
      categories = const [];
    }
    if (loggedIn) {
      await loadProfile();
    }
    await loadJobs();
  }

  Future<void> loadJobs({String keyword = '', int? categoryId}) async {
    busy = true;
    error = null;
    notifyListeners();
    try {
      jobs = await jobsApi.getJobs(
        keyword: keyword,
        categoryId: categoryId ?? selectedCategoryId,
      );
    } catch (e) {
      error = e.toString();
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  Future<void> selectCategory(int? categoryId, {String keyword = ''}) async {
    selectedCategoryId = categoryId;
    notifyListeners();
    await loadJobs(keyword: keyword, categoryId: categoryId);
  }

  Future<void> login(String email, String password) async {
    currentUser = await auth.login(email, password);
    await loadProfile();
    await loadJobs();
  }

  Future<void> loadProfile() async {
    if (!loggedIn) return;
    profileBusy = true;
    profileError = null;
    notifyListeners();
    try {
      final results = await Future.wait<dynamic>([
        candidateApi.getProfile(),
        candidateApi.getDashboard(),
        candidateApi.getResumes(),
      ]);
      currentUser = results[0] as Map<String, dynamic>;
      dashboard = results[1] as Map<String, dynamic>;
      resumes = results[2] as List<Map<String, dynamic>>;
    } on ApiException catch (e) {
      profileError = e.message;
      if (e.statusCode == 401) {
        await api.clearToken();
        currentUser = const {};
        dashboard = const {};
        resumes = const [];
      }
    } catch (e) {
      profileError = e.toString();
    } finally {
      profileBusy = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await api.clearToken();
    currentUser = const {};
    dashboard = const {};
    resumes = const [];
    profileError = null;
    await loadJobs();
  }
}
