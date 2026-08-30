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
  List<Job> savedJobs = const [];
  List<Map<String, dynamic>> notifications = const [];
  List<JobCategory> categories = const [];
  int? selectedCategoryId;
  List<String> jobTypes = const [];
  List<String> experiences = const [];
  String selectedJobType = '';
  String selectedExperience = '';
  String selectedSort = '';
  bool remoteOnly = false;
  int? minSalary;
  int? maxSalary;
  Map<String, dynamic> currentUser = const {};
  Map<String, dynamic> dashboard = const {};
  List<Map<String, dynamic>> resumes = const [];
  bool profileBusy = false;
  String? profileError;
  bool busy = false;
  bool savedJobsBusy = false;
  bool notificationsBusy = false;
  String? error;

  bool get loggedIn => api.isAuthenticated;

  Future<void> initialize() async {
    await api.restoreToken();
    await _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    try {
      final options = await Future.wait<dynamic>([
        jobsApi.getCategories(),
        jobsApi.getFilterOptions('/job-types'),
        jobsApi.getFilterOptions('/experiences'),
      ]);
      categories = options[0] as List<JobCategory>;
      jobTypes = options[1] as List<String>;
      experiences = options[2] as List<String>;
    } catch (_) {
      categories = const [];
    }
    notifyListeners();
    await Future.wait<void>([
      loadJobs(),
      if (loggedIn) loadProfile(),
    ]);
  }

  Future<void> loadJobs({String keyword = '', int? categoryId}) async {
    busy = true;
    error = null;
    notifyListeners();
    try {
      jobs = await jobsApi.getJobs(
        keyword: keyword,
        categoryId: categoryId ?? selectedCategoryId,
        jobType: selectedJobType,
        experience: selectedExperience,
        sortBy: selectedSort,
        remoteOnly: remoteOnly,
        minSalary: minSalary,
        maxSalary: maxSalary,
      );
    } catch (e) {
      error = e.toString();
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  int get activeFilterCount =>
      (selectedJobType.isNotEmpty ? 1 : 0) +
      (selectedExperience.isNotEmpty ? 1 : 0) +
      (selectedSort.isNotEmpty ? 1 : 0) +
      (remoteOnly ? 1 : 0) +
      (minSalary != null || maxSalary != null ? 1 : 0);

  Future<void> applyJobFilters({
    required String jobType,
    required String experience,
    required String sortBy,
    required bool remote,
    int? salaryMin,
    int? salaryMax,
    String keyword = '',
  }) async {
    selectedJobType = jobType;
    selectedExperience = experience;
    selectedSort = sortBy;
    remoteOnly = remote;
    minSalary = salaryMin;
    maxSalary = salaryMax;
    await loadJobs(keyword: keyword);
  }

  Future<void> clearJobFilters({String keyword = ''}) => applyJobFilters(
        jobType: '',
        experience: '',
        sortBy: '',
        remote: false,
        keyword: keyword,
      );

  Future<void> selectCategory(int? categoryId, {String keyword = ''}) async {
    selectedCategoryId = categoryId;
    notifyListeners();
    await loadJobs(keyword: keyword, categoryId: categoryId);
  }

  Future<bool> toggleSaved(Job job) async {
    var jobId = job.id;
    if (jobId <= 0 && job.slug.isNotEmpty) {
      final details = await jobsApi.details(job.slug);
      jobId = int.tryParse('${details['id'] ?? details['job_id'] ?? 0}') ?? 0;
    }
    if (jobId <= 0) {
      throw const ApiException(
          'This job could not be identified. Please refresh and try again.');
    }
    final saved = await jobsApi.toggleBookmark(jobId);
    final resolvedJob = job.copyWith(id: jobId, bookmarked: saved);
    jobs = jobs
        .map((item) => item.slug == job.slug
            ? item.copyWith(id: jobId, bookmarked: saved)
            : item)
        .toList();
    if (saved && !savedJobs.any((item) => item.slug == job.slug)) {
      savedJobs = [resolvedJob, ...savedJobs];
    } else if (!saved) {
      savedJobs = savedJobs.where((item) => item.slug != job.slug).toList();
    }
    notifyListeners();
    return saved;
  }

  Future<void> loadSavedJobs() async {
    if (!loggedIn) return;
    savedJobsBusy = true;
    notifyListeners();
    try {
      savedJobs = await jobsApi.getSavedJobs();
    } catch (e) {
      error = '$e';
    } finally {
      savedJobsBusy = false;
      notifyListeners();
    }
  }

  Future<void> loadNotifications() async {
    if (!loggedIn) return;
    notificationsBusy = true;
    notifyListeners();
    try {
      final response = await api.get('/notifications');
      dynamic payload = response['data'];
      while (payload is Map && payload['data'] != null) {
        payload = payload['data'];
      }
      notifications = (payload as List? ?? const [])
          .whereType<Map>()
          .map((item) => item.cast<String, dynamic>())
          .toList();
    } catch (e) {
      error = '$e';
    } finally {
      notificationsBusy = false;
      notifyListeners();
    }
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
    savedJobs = const [];
    notifications = const [];
    profileError = null;
    await loadJobs();
  }
}
