import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/job.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/brand_logo.dart';
import 'job_details_screen.dart';
import 'login_screen.dart';
import 'profile_screen.dart';
import 'register_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key, required this.state});
  final AppState state;
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int tab = 0;
  final search = TextEditingController();
  @override
  void initState() {
    super.initState();
    widget.state.addListener(_refresh);
  }

  @override
  void dispose() {
    widget.state.removeListener(_refresh);
    search.dispose();
    super.dispose();
  }

  void _refresh() {
    if (mounted) setState(() {});
  }

  void openRegister() => Navigator.push(context,
      MaterialPageRoute(builder: (_) => RegisterScreen(state: widget.state)));
  void openLogin() => Navigator.push(context,
      MaterialPageRoute(builder: (_) => LoginScreen(state: widget.state)));

  void openNotifications() {
    if (!widget.state.loggedIn) {
      openLogin();
      return;
    }
    Navigator.push(
        context,
        MaterialPageRoute(
            builder: (_) => _NotificationsScreen(state: widget.state)));
  }

  void openFilters() => showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      builder: (_) => _JobFilterSheet(state: widget.state, search: search));

  @override
  Widget build(BuildContext context) => Scaffold(
        body: SafeArea(
            child: IndexedStack(index: tab, children: [
          _LandingPage(
              state: widget.state,
              search: search,
              onSearch: () {
                widget.state.loadJobs(keyword: search.text.trim());
              },
              onFilter: openFilters,
              onOpenJobs: () => setState(() => tab = 1),
              onRegister: openRegister,
              onLogin: openLogin,
              onProfile: () => setState(() => tab = 4),
              onNotifications: openNotifications),
          _JobsPage(
              state: widget.state,
              search: search,
              onFilter: openFilters,
              onNotifications: openNotifications,
              onLogin: openLogin),
          _SavedJobsPage(state: widget.state, onLogin: openLogin),
          _CandidateAreaPage(
              state: widget.state,
              icon: Icons.assignment_outlined,
              title: 'Applications',
              signedInMessage: 'Track every application from one place.',
              onLogin: openLogin),
          ProfileScreen(
              state: widget.state,
              onLogin: openLogin,
              onOpenJobs: () => setState(() => tab = 1)),
        ])),
        bottomNavigationBar: NavigationBar(
          selectedIndex: tab,
          onDestinationSelected: (value) {
            setState(() => tab = value);
            if (value == 2 && widget.state.loggedIn) {
              widget.state.loadSavedJobs();
            }
          },
          backgroundColor: AppColors.background,
          indicatorColor: Colors.transparent,
          labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
          destinations: const [
            NavigationDestination(
                icon: Icon(Icons.home_outlined),
                selectedIcon: Icon(Icons.home, color: AppColors.red),
                label: 'Home'),
            NavigationDestination(
                icon: Icon(Icons.work_outline),
                selectedIcon: Icon(Icons.work, color: AppColors.red),
                label: 'Jobs'),
            NavigationDestination(
                icon: Icon(Icons.bookmark_outline),
                selectedIcon: Icon(Icons.bookmark, color: AppColors.red),
                label: 'Saved'),
            NavigationDestination(
                icon: Icon(Icons.assignment_outlined),
                selectedIcon: Icon(Icons.assignment, color: AppColors.red),
                label: 'Applications'),
            NavigationDestination(
                icon: Icon(Icons.person_outline),
                selectedIcon: Icon(Icons.person, color: AppColors.red),
                label: 'Profile'),
          ],
        ),
      );
}

class _LandingPage extends StatelessWidget {
  const _LandingPage(
      {required this.state,
      required this.search,
      required this.onSearch,
      required this.onFilter,
      required this.onOpenJobs,
      required this.onRegister,
      required this.onLogin,
      required this.onProfile,
      required this.onNotifications});
  final AppState state;
  final TextEditingController search;
  final VoidCallback onSearch;
  final VoidCallback onFilter;
  final VoidCallback onOpenJobs;
  final VoidCallback onRegister;
  final VoidCallback onLogin;
  final VoidCallback onProfile;
  final VoidCallback onNotifications;
  @override
  Widget build(BuildContext context) => RefreshIndicator(
      onRefresh: () async {
        await state.loadJobs(keyword: search.text.trim());
        if (state.loggedIn) await state.loadProfile();
      },
      child: ListView(
          physics: const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics()),
          padding: const EdgeInsets.fromLTRB(18, 24, 18, 30),
          children: [
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const BrandLogo(compact: true),
              state.loggedIn
                  ? Row(children: [
                      Badge(
                          backgroundColor: AppColors.red,
                          child: IconButton(
                              onPressed: onNotifications,
                              icon: const Icon(Icons.notifications_none_rounded,
                                  size: 27))),
                      const SizedBox(width: 12),
                      Semantics(
                          button: true,
                          label: 'Open profile',
                          child: InkWell(
                              onTap: onProfile,
                              customBorder: const CircleBorder(),
                              child: CircleAvatar(
                                  radius: 17,
                                  backgroundColor: AppColors.purple,
                                  child: Text(
                                      _initials(
                                          '${state.currentUser['name'] ?? 'C'}'),
                                      style: const TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.w700))))),
                    ])
                  : TextButton.icon(
                      onPressed: onLogin,
                      icon: const Icon(Icons.login, size: 18),
                      label: const Text('Log In')),
            ]),
            const SizedBox(height: 22),
            Text(
                state.loggedIn
                    ? 'Find your next opportunity'
                    : 'Find the right job for you',
                style:
                    const TextStyle(fontSize: 27, fontWeight: FontWeight.w800)),
            const SizedBox(height: 7),
            Text(
                state.loggedIn
                    ? 'Fresh opportunities matched to your career.'
                    : 'Explore trusted jobs before creating an account.',
                style: const TextStyle(color: AppColors.muted)),
            const SizedBox(height: 18),
            TextField(
                controller: search,
                onSubmitted: (_) => onSearch(),
                decoration: InputDecoration(
                    hintText: 'Job title, keyword or company',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: IconButton(
                        onPressed: onFilter,
                        icon: Badge(
                            isLabelVisible: state.activeFilterCount > 0,
                            label: Text('${state.activeFilterCount}'),
                            child: const Icon(Icons.tune_rounded))))),
            const SizedBox(height: 14),
            SizedBox(
                height: 45,
                child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    physics: const BouncingScrollPhysics(),
                    itemCount: state.categories.length + 1,
                    itemBuilder: (_, index) {
                      if (index == 0) {
                        return _FilterChip('All',
                            selected: state.selectedCategoryId == null,
                            onTap: () => state.selectCategory(null,
                                keyword: search.text.trim()));
                      }
                      final category = state.categories[index - 1];
                      return _FilterChip(category.name,
                          selected: state.selectedCategoryId == category.id,
                          onTap: () => state.selectCategory(category.id,
                              keyword: search.text.trim()));
                    })),
            const SizedBox(height: 18),
            state.loggedIn
                ? _CandidateSummary(state: state)
                : _GuestBenefitCard(onRegister: onRegister, onLogin: onLogin),
            const SizedBox(height: 24),
            _SectionHeader(
                title: state.loggedIn ? 'Recommended for You' : 'Popular Jobs',
                onViewAll: onOpenJobs),
            const SizedBox(height: 12),
            if (state.busy) const LinearProgressIndicator(color: AppColors.red),
            if (state.error != null)
              _InlineError(message: state.error!, onRetry: onSearch),
            ...state.jobs.take(6).map((job) => Padding(
                padding: const EdgeInsets.only(bottom: 9),
                child: _JobCard(job: job, state: state, onLogin: onLogin))),
            if (!state.busy && state.jobs.isEmpty)
              const Padding(
                  padding: EdgeInsets.symmetric(vertical: 28),
                  child: Center(child: Text('No jobs found'))),
            if (state.categories.isNotEmpty) ...[
              const SizedBox(height: 20),
              const Text('Browse by Category',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
              const SizedBox(height: 12),
              Wrap(
                  spacing: 9,
                  runSpacing: 9,
                  children: state.categories
                      .take(8)
                      .map((category) => ActionChip(
                          avatar: const Icon(Icons.work_outline, size: 17),
                          label: Text(category.name),
                          onPressed: () => state.selectCategory(category.id,
                              keyword: search.text.trim())))
                      .toList()),
            ],
            const SizedBox(height: 24),
            const _CareerCard(),
          ]));

  static String _initials(String value) {
    final parts = value.trim().split(RegExp(r'\s+'));
    return parts.take(2).where((e) => e.isNotEmpty).map((e) => e[0]).join();
  }
}

class _JobsPage extends StatelessWidget {
  const _JobsPage(
      {required this.state,
      required this.search,
      required this.onFilter,
      required this.onNotifications,
      required this.onLogin});
  final AppState state;
  final TextEditingController search;
  final VoidCallback onFilter;
  final VoidCallback onNotifications;
  final VoidCallback onLogin;
  @override
  Widget build(BuildContext context) => RefreshIndicator(
      onRefresh: () => state.loadJobs(keyword: search.text.trim()),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(
            parent: BouncingScrollPhysics()),
        padding: const EdgeInsets.fromLTRB(18, 24, 18, 30),
        children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            const BrandLogo(compact: true),
            Badge(
                backgroundColor: AppColors.red,
                child: IconButton(
                    onPressed: onNotifications,
                    icon:
                        const Icon(Icons.notifications_none_rounded, size: 29)))
          ]),
          const SizedBox(height: 24),
          TextField(
              controller: search,
              onSubmitted: (v) => state.loadJobs(keyword: v.trim()),
              decoration: InputDecoration(
                  hintText: 'Job title, keyword or company',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: IconButton(
                      onPressed: onFilter,
                      icon: Badge(
                          isLabelVisible: state.activeFilterCount > 0,
                          label: Text('${state.activeFilterCount}'),
                          child: const Icon(Icons.tune_rounded))))),
          const SizedBox(height: 16),
          SizedBox(
              height: 52,
              child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(
                      parent: AlwaysScrollableScrollPhysics()),
                  itemCount: state.categories.length + 1,
                  itemBuilder: (context, index) {
                    if (index == 0) {
                      return _FilterChip('All',
                          selected: state.selectedCategoryId == null,
                          onTap: () => state.selectCategory(null,
                              keyword: search.text.trim()));
                    }
                    final category = state.categories[index - 1];
                    return _FilterChip(category.name,
                        selected: state.selectedCategoryId == category.id,
                        onTap: () => state.selectCategory(category.id,
                            keyword: search.text.trim()));
                  })),
          const SizedBox(height: 24),
          const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Popular Jobs',
                    style:
                        TextStyle(fontSize: 21, fontWeight: FontWeight.w700)),
                Text('View all',
                    style: TextStyle(
                        color: AppColors.red, fontWeight: FontWeight.w600)),
              ]),
          const SizedBox(height: 14),
          if (state.busy) const LinearProgressIndicator(color: AppColors.red),
          if (state.error != null)
            Padding(
                padding: const EdgeInsets.all(20),
                child: Text(state.error!,
                    style: const TextStyle(color: AppColors.muted))),
          ...state.jobs.map((job) => Padding(
              padding: const EdgeInsets.only(bottom: 9),
              child: _JobCard(job: job, state: state, onLogin: onLogin))),
          if (!state.busy && state.jobs.isEmpty)
            const Padding(
                padding: EdgeInsets.symmetric(vertical: 35),
                child: Center(child: Text('No jobs found'))),
        ],
      ));
}

class _GuestBenefitCard extends StatelessWidget {
  const _GuestBenefitCard({required this.onRegister, required this.onLogin});
  final VoidCallback onRegister;
  final VoidCallback onLogin;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
              colors: [Color(0xff351369), Color(0xff171131)]),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: const Color(0xff5930a0)),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Row(children: [
            Icon(Icons.auto_awesome, color: Color(0xffb891ff)),
            SizedBox(width: 8),
            Expanded(
                child: Text('Get more from your job search',
                    style:
                        TextStyle(fontSize: 18, fontWeight: FontWeight.w800))),
          ]),
          const SizedBox(height: 8),
          const Text(
              'Create a free account to apply, save jobs, receive alerts and track applications.',
              style: TextStyle(height: 1.4, color: AppColors.muted)),
          const SizedBox(height: 15),
          Row(children: [
            Expanded(
                child: FilledButton(
                    style: FilledButton.styleFrom(
                        backgroundColor: AppColors.purple),
                    onPressed: onRegister,
                    child: const Text('Create Free Account'))),
            const SizedBox(width: 9),
            OutlinedButton(onPressed: onLogin, child: const Text('Log In')),
          ]),
        ]),
      );
}

class _CandidateSummary extends StatelessWidget {
  const _CandidateSummary({required this.state});
  final AppState state;

  int _value(String key) => int.tryParse('${state.dashboard[key] ?? 0}') ?? 0;

  @override
  Widget build(BuildContext context) {
    final user = state.currentUser;
    final remaining = int.tryParse(
            '${state.dashboard['profileComplated'] ?? user['profile_complete'] ?? 0}') ??
        0;
    final completion = (100 - remaining).clamp(0, 100);
    return Container(
      padding: const EdgeInsets.all(17),
      decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: AppColors.border)),
      child: Column(children: [
        Row(children: [
          Expanded(
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                Text('Welcome back, ${user['name'] ?? 'Candidate'}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                        fontSize: 17, fontWeight: FontWeight.w800)),
                const SizedBox(height: 5),
                Text('Profile $completion% complete',
                    style:
                        const TextStyle(fontSize: 12, color: AppColors.muted)),
              ])),
          SizedBox(
              width: 78,
              child: LinearProgressIndicator(
                  value: completion.clamp(0, 100) / 100,
                  minHeight: 7,
                  borderRadius: BorderRadius.circular(8),
                  backgroundColor: AppColors.border,
                  color: AppColors.purple)),
        ]),
        const SizedBox(height: 16),
        Row(children: [
          _MiniStat(value: _value('appliedJobs'), label: 'Applied'),
          _MiniStat(value: _value('favoriteJobs'), label: 'Saved'),
          _MiniStat(value: _value('notifications'), label: 'Alerts'),
        ]),
      ]),
    );
  }
}

class _MiniStat extends StatelessWidget {
  const _MiniStat({required this.value, required this.label});
  final int value;
  final String label;
  @override
  Widget build(BuildContext context) => Expanded(
          child: Column(children: [
        Text('$value',
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
        Text(label,
            style: const TextStyle(fontSize: 12, color: AppColors.muted)),
      ]));
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title, required this.onViewAll});
  final String title;
  final VoidCallback onViewAll;
  @override
  Widget build(BuildContext context) => Row(children: [
        Expanded(
            child: Text(title,
                style: const TextStyle(
                    fontSize: 20, fontWeight: FontWeight.w800))),
        TextButton(onPressed: onViewAll, child: const Text('View all')),
      ]);
}

class _InlineError extends StatelessWidget {
  const _InlineError({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;
  @override
  Widget build(BuildContext context) => Container(
      margin: const EdgeInsets.symmetric(vertical: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border)),
      child: Row(children: [
        const Icon(Icons.cloud_off_outlined, color: AppColors.muted),
        const SizedBox(width: 10),
        Expanded(
            child: Text(message,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 12, color: AppColors.muted))),
        IconButton(onPressed: onRetry, icon: const Icon(Icons.refresh)),
      ]));
}

class _CareerCard extends StatelessWidget {
  const _CareerCard();
  @override
  Widget build(BuildContext context) => Material(
      color: Colors.transparent,
      child: InkWell(
          onTap: () => Navigator.push(
              context,
              MaterialPageRoute(
                  builder: (_) => const _CareerResourcesScreen())),
          borderRadius: BorderRadius.circular(17),
          child: Container(
              padding: const EdgeInsets.all(17),
              decoration: BoxDecoration(
                  color: AppColors.surfaceSoft,
                  borderRadius: BorderRadius.circular(17),
                  border: Border.all(color: AppColors.border)),
              child: const Row(children: [
                CircleAvatar(
                    backgroundColor: Color(0xff2b1a55),
                    child:
                        Icon(Icons.school_outlined, color: AppColors.purple)),
                SizedBox(width: 13),
                Expanded(
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Text('Career Resources',
                          style: TextStyle(
                              fontSize: 17, fontWeight: FontWeight.w800)),
                      SizedBox(height: 3),
                      Text('CV tips, interview preparation and career guidance',
                          style:
                              TextStyle(fontSize: 12, color: AppColors.muted)),
                    ])),
                Icon(Icons.chevron_right, color: AppColors.muted),
              ]))));
}

class _CareerResourcesScreen extends StatelessWidget {
  const _CareerResourcesScreen();

  static const resources = [
    (
      'Career Advice',
      'Build a practical roadmap for long-term growth.',
      Icons.explore_outlined,
      'https://proskilljob.com/career-advice'
    ),
    (
      'Interview Tips',
      'Prepare confidently and improve every interview.',
      Icons.record_voice_over_outlined,
      'https://proskilljob.com/interview-tips'
    ),
    (
      'Resume Writing Tips',
      'Create an ATS-friendly, recruiter-ready CV.',
      Icons.description_outlined,
      'https://proskilljob.com/resume-writing-tips'
    ),
    (
      'Cover Letter Guide',
      'Write a focused and convincing cover letter.',
      Icons.mark_email_read_outlined,
      'https://proskilljob.com/cover-letter'
    ),
    (
      'Education Guide',
      'Choose skills, courses and credentials wisely.',
      Icons.school_outlined,
      'https://proskilljob.com/education-guide'
    ),
  ];

  Future<void> _open(BuildContext context, String url) async {
    final opened =
        await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
    if (!opened && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Page could not be opened.')));
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
      appBar: AppBar(title: const Text('Career Resources')),
      body: ListView(
          padding: const EdgeInsets.fromLTRB(18, 8, 18, 30),
          children: [
            Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                    gradient: const LinearGradient(
                        colors: [Color(0xff2c165d), AppColors.surface]),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: AppColors.border)),
                child: const Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(Icons.auto_awesome, color: AppColors.purple),
                      SizedBox(height: 12),
                      Text('Grow with expert guidance',
                          style: TextStyle(
                              fontSize: 23, fontWeight: FontWeight.w800)),
                      SizedBox(height: 7),
                      Text(
                          'Practical resources to strengthen your profile, applications and interviews.',
                          style: TextStyle(color: AppColors.muted)),
                    ])),
            const SizedBox(height: 18),
            ...resources.map((item) => _resourceCard(context, item)),
          ]));

  Widget _resourceCard(
          BuildContext context, (String, String, IconData, String) item) =>
      Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: Card(
              child: InkWell(
                  borderRadius: BorderRadius.circular(16),
                  onTap: () => _open(context, item.$4),
                  child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(children: [
                        CircleAvatar(
                            backgroundColor: const Color(0xff2b1a55),
                            child: Icon(item.$3, color: AppColors.purple)),
                        const SizedBox(width: 14),
                        Expanded(
                            child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                              Text(item.$1,
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 16)),
                              const SizedBox(height: 4),
                              Text(item.$2,
                                  style: const TextStyle(
                                      color: AppColors.muted, fontSize: 12)),
                            ])),
                        const Icon(Icons.open_in_new_rounded,
                            size: 19, color: AppColors.muted),
                      ])))));
}

class _JobFilterSheet extends StatefulWidget {
  const _JobFilterSheet({required this.state, required this.search});
  final AppState state;
  final TextEditingController search;

  @override
  State<_JobFilterSheet> createState() => _JobFilterSheetState();
}

class _JobFilterSheetState extends State<_JobFilterSheet> {
  late String jobType = widget.state.selectedJobType;
  late String experience = widget.state.selectedExperience;
  late String sortBy = widget.state.selectedSort;
  late bool remote = widget.state.remoteOnly;
  late final minSalary =
      TextEditingController(text: widget.state.minSalary?.toString() ?? '');
  late final maxSalary =
      TextEditingController(text: widget.state.maxSalary?.toString() ?? '');

  @override
  void dispose() {
    minSalary.dispose();
    maxSalary.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => SafeArea(
      child: Padding(
          padding: EdgeInsets.fromLTRB(
              20, 14, 20, 20 + MediaQuery.viewInsetsOf(context).bottom),
          child: SingleChildScrollView(
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                Center(
                    child: Container(
                        width: 42,
                        height: 4,
                        decoration: BoxDecoration(
                            color: AppColors.border,
                            borderRadius: BorderRadius.circular(4)))),
                const SizedBox(height: 18),
                const Text('Filter Jobs',
                    style:
                        TextStyle(fontSize: 23, fontWeight: FontWeight.w800)),
                const SizedBox(height: 18),
                _dropdown('Job Type', jobType, widget.state.jobTypes,
                    (value) => setState(() => jobType = value ?? '')),
                const SizedBox(height: 14),
                _dropdown('Experience', experience, widget.state.experiences,
                    (value) => setState(() => experience = value ?? '')),
                const SizedBox(height: 14),
                _dropdown('Sort By', sortBy, const ['latest', 'featured'],
                    (value) => setState(() => sortBy = value ?? '')),
                const SizedBox(height: 14),
                Row(children: [
                  Expanded(child: _salaryField('Minimum salary', minSalary)),
                  const SizedBox(width: 10),
                  Expanded(child: _salaryField('Maximum salary', maxSalary)),
                ]),
                SwitchListTile.adaptive(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Remote jobs only'),
                    subtitle: const Text('Show work-from-home opportunities'),
                    value: remote,
                    onChanged: (value) => setState(() => remote = value)),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(
                      child: OutlinedButton(
                          onPressed: () async {
                            Navigator.pop(context);
                            await widget.state.clearJobFilters(
                                keyword: widget.search.text.trim());
                          },
                          child: const Text('Clear'))),
                  const SizedBox(width: 12),
                  Expanded(
                      flex: 2,
                      child: FilledButton.icon(
                          onPressed: () async {
                            Navigator.pop(context);
                            await widget.state.applyJobFilters(
                                jobType: jobType,
                                experience: experience,
                                sortBy: sortBy,
                                remote: remote,
                                salaryMin: int.tryParse(minSalary.text.trim()),
                                salaryMax: int.tryParse(maxSalary.text.trim()),
                                keyword: widget.search.text.trim());
                          },
                          icon: const Icon(Icons.search),
                          label: const Text('Apply Filters'))),
                ]),
              ]))));

  Widget _dropdown(String label, String value, List<String> values,
          ValueChanged<String?> onChanged) =>
      DropdownButtonFormField<String>(
          initialValue: value.isEmpty ? null : value,
          decoration: InputDecoration(labelText: label),
          items: values
              .map((item) => DropdownMenuItem(
                  value: item,
                  child: Text(item == 'latest'
                      ? 'Latest first'
                      : item == 'featured'
                          ? 'Featured jobs'
                          : item)))
              .toList(),
          onChanged: onChanged);

  Widget _salaryField(String label, TextEditingController controller) =>
      TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          decoration: InputDecoration(labelText: label));
}

class _FilterChip extends StatelessWidget {
  const _FilterChip(this.label, {required this.onTap, this.selected = false});
  final String label;
  final bool selected;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) => Padding(
      padding: const EdgeInsets.only(right: 9),
      child: Material(
          color: selected ? AppColors.red : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
          child: InkWell(
              onTap: onTap,
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 17, vertical: 11),
                decoration: BoxDecoration(
                    border: Border.all(
                        color: selected ? AppColors.red : AppColors.border),
                    borderRadius: BorderRadius.circular(12)),
                child: Text(label,
                    style: TextStyle(
                        color:
                            selected ? AppColors.onSecondary : AppColors.text,
                        fontWeight: FontWeight.w600)),
              ))));
}

class _JobCard extends StatelessWidget {
  const _JobCard(
      {required this.job, required this.state, required this.onLogin});
  final Job job;
  final AppState state;
  final VoidCallback onLogin;
  @override
  Widget build(BuildContext context) => Card(
          child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () => Navigator.push(
            context,
            MaterialPageRoute(
                builder: (_) => JobDetailsScreen(state: state, job: job))),
        child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12)),
                  child: job.logo.isEmpty
                      ? const Icon(Icons.business, color: AppColors.purple)
                      : ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: Image.network(job.logo,
                              fit: BoxFit.contain,
                              errorBuilder: (_, __, ___) => const Icon(
                                  Icons.business,
                                  color: AppColors.purple)))),
              const SizedBox(width: 13),
              Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text(job.title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                            fontSize: 17, fontWeight: FontWeight.w700)),
                    Text(job.company,
                        style: const TextStyle(color: AppColors.muted)),
                    const SizedBox(height: 8),
                    Row(children: [
                      const Icon(Icons.location_on_outlined,
                          size: 17, color: AppColors.muted),
                      const SizedBox(width: 3),
                      Expanded(
                          child: Text(job.location,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                  fontSize: 12, color: AppColors.muted))),
                      Text(job.type,
                          style: const TextStyle(
                              fontSize: 12, color: AppColors.muted))
                    ]),
                  ])),
              const SizedBox(width: 7),
              IconButton(
                  tooltip: job.bookmarked ? 'Unsave job' : 'Save job',
                  onPressed: () async {
                    if (!state.loggedIn) {
                      onLogin();
                      return;
                    }
                    try {
                      final saved = await state.toggleSaved(job);
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                            content: Text(saved
                                ? 'Job saved successfully.'
                                : 'Job removed from saved jobs.')));
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context)
                            .showSnackBar(SnackBar(content: Text('$e')));
                      }
                    }
                  },
                  icon: Icon(
                      job.bookmarked ? Icons.bookmark : Icons.bookmark_border,
                      color: job.bookmarked ? AppColors.red : AppColors.muted)),
            ])),
      ));
}

class _SavedJobsPage extends StatelessWidget {
  const _SavedJobsPage({required this.state, required this.onLogin});
  final AppState state;
  final VoidCallback onLogin;

  @override
  Widget build(BuildContext context) {
    if (!state.loggedIn) {
      return _CandidateAreaPage(
          state: state,
          icon: Icons.bookmark_outline,
          title: 'Saved Jobs',
          signedInMessage: 'Your saved jobs will appear here.',
          onLogin: onLogin);
    }
    return RefreshIndicator(
        onRefresh: state.loadSavedJobs,
        child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(18, 24, 18, 30),
            children: [
              const Text('Saved Jobs',
                  style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800)),
              const SizedBox(height: 6),
              Text('${state.savedJobs.length} jobs saved for later',
                  style: const TextStyle(color: AppColors.muted)),
              const SizedBox(height: 20),
              if (state.savedJobsBusy)
                const LinearProgressIndicator(color: AppColors.red),
              ...state.savedJobs.map((job) => Padding(
                  padding: const EdgeInsets.only(bottom: 9),
                  child: _JobCard(job: job, state: state, onLogin: onLogin))),
              if (!state.savedJobsBusy && state.savedJobs.isEmpty)
                const Padding(
                    padding: EdgeInsets.only(top: 90),
                    child: Column(children: [
                      Icon(Icons.bookmark_add_outlined,
                          size: 58, color: AppColors.purple),
                      SizedBox(height: 14),
                      Text('No saved jobs yet',
                          style: TextStyle(
                              fontSize: 20, fontWeight: FontWeight.w700)),
                      SizedBox(height: 6),
                      Text('Tap the bookmark icon on a job to save it.',
                          style: TextStyle(color: AppColors.muted)),
                    ])),
            ]));
  }
}

class _NotificationsScreen extends StatefulWidget {
  const _NotificationsScreen({required this.state});
  final AppState state;

  @override
  State<_NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<_NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    widget.state.addListener(_refresh);
    widget.state.loadNotifications();
  }

  void _refresh() {
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    widget.state.removeListener(_refresh);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      body: RefreshIndicator(
          onRefresh: widget.state.loadNotifications,
          child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(18, 8, 18, 30),
              children: [
                if (widget.state.notificationsBusy)
                  const LinearProgressIndicator(color: AppColors.red),
                ...widget.state.notifications.map((notification) {
                  final data = notification['data'] is Map
                      ? (notification['data'] as Map)
                      : notification;
                  final title =
                      '${data['title'] ?? data['subject'] ?? 'Notification'}';
                  final message =
                      '${data['message'] ?? data['body'] ?? data['text'] ?? 'You have a new update.'}';
                  final date = '${notification['created_at'] ?? ''}';
                  final unread = notification['read_at'] == null;
                  return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: Card(
                          child: ListTile(
                              contentPadding: const EdgeInsets.all(14),
                              leading: CircleAvatar(
                                  backgroundColor: unread
                                      ? const Color(0xff3b174d)
                                      : AppColors.surfaceSoft,
                                  child: Icon(Icons.notifications_outlined,
                                      color: unread
                                          ? AppColors.red
                                          : AppColors.muted)),
                              title: Text(title,
                                  style: TextStyle(
                                      fontWeight: unread
                                          ? FontWeight.w800
                                          : FontWeight.w600)),
                              subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const SizedBox(height: 5),
                                    Text(message),
                                    if (date.isNotEmpty) ...[
                                      const SizedBox(height: 6),
                                      Text(date,
                                          style: const TextStyle(fontSize: 11)),
                                    ],
                                  ]))));
                }),
                if (!widget.state.notificationsBusy &&
                    widget.state.notifications.isEmpty)
                  const Padding(
                      padding: EdgeInsets.only(top: 110),
                      child: Column(children: [
                        Icon(Icons.notifications_off_outlined,
                            size: 58, color: AppColors.purple),
                        SizedBox(height: 14),
                        Text('No notifications yet',
                            style: TextStyle(
                                fontSize: 20, fontWeight: FontWeight.w700)),
                        SizedBox(height: 6),
                        Text('Job alerts and account updates will appear here.',
                            textAlign: TextAlign.center,
                            style: TextStyle(color: AppColors.muted)),
                      ])),
              ])));
}

class _CandidateAreaPage extends StatelessWidget {
  const _CandidateAreaPage({
    required this.state,
    required this.icon,
    required this.title,
    required this.signedInMessage,
    required this.onLogin,
  });
  final AppState state;
  final IconData icon;
  final String title;
  final String signedInMessage;
  final VoidCallback onLogin;

  @override
  Widget build(BuildContext context) => Center(
          child: Column(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 55, color: AppColors.purple),
        const SizedBox(height: 12),
        Text(title,
            style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
        const SizedBox(height: 7),
        Padding(
            padding: const EdgeInsets.symmetric(horizontal: 35),
            child: Text(
                state.loggedIn
                    ? signedInMessage
                    : 'Log in to sync and manage your ${title.toLowerCase()}.',
                textAlign: TextAlign.center,
                style: const TextStyle(color: AppColors.muted))),
        if (!state.loggedIn) ...[
          const SizedBox(height: 20),
          FilledButton.icon(
              onPressed: onLogin,
              icon: const Icon(Icons.login),
              label: const Text('Log In')),
        ],
      ]));
}
