import 'package:flutter/material.dart';
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
              onOpenJobs: () => setState(() => tab = 1),
              onRegister: openRegister,
              onLogin: openLogin),
          _JobsPage(state: widget.state, search: search),
          _CandidateAreaPage(
              state: widget.state,
              icon: Icons.bookmark_outline,
              title: 'Saved Jobs',
              signedInMessage: 'Your saved jobs will appear here.',
              onLogin: openLogin),
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
          onDestinationSelected: (value) => setState(() => tab = value),
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
      required this.onOpenJobs,
      required this.onRegister,
      required this.onLogin});
  final AppState state;
  final TextEditingController search;
  final VoidCallback onSearch;
  final VoidCallback onOpenJobs;
  final VoidCallback onRegister;
  final VoidCallback onLogin;
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
                      const Badge(
                          backgroundColor: AppColors.red,
                          child:
                              Icon(Icons.notifications_none_rounded, size: 27)),
                      const SizedBox(width: 12),
                      CircleAvatar(
                          radius: 17,
                          backgroundColor: AppColors.purple,
                          child: Text(
                              _initials('${state.currentUser['name'] ?? 'C'}'),
                              style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w700))),
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
                        onPressed: onSearch,
                        icon: const Icon(Icons.tune_rounded)))),
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
                child: _JobCard(job: job, state: state))),
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
  const _JobsPage({required this.state, required this.search});
  final AppState state;
  final TextEditingController search;
  @override
  Widget build(BuildContext context) => RefreshIndicator(
      onRefresh: () => state.loadJobs(keyword: search.text.trim()),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(
            parent: BouncingScrollPhysics()),
        padding: const EdgeInsets.fromLTRB(18, 24, 18, 30),
        children: [
          const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                BrandLogo(compact: true),
                Badge(
                    backgroundColor: AppColors.red,
                    child: Icon(Icons.notifications_none_rounded, size: 29))
              ]),
          const SizedBox(height: 24),
          TextField(
              controller: search,
              onSubmitted: (v) => state.loadJobs(keyword: v.trim()),
              decoration: InputDecoration(
                  hintText: 'Job title, keyword or company',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: IconButton(
                      onPressed: () =>
                          state.loadJobs(keyword: search.text.trim()),
                      icon: const Icon(Icons.arrow_forward)))),
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
              child: _JobCard(job: job, state: state))),
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
    final completion = int.tryParse(
            '${state.dashboard['profileComplated'] ?? user['profile_complete'] ?? 0}') ??
        0;
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
  Widget build(BuildContext context) => Container(
      padding: const EdgeInsets.all(17),
      decoration: BoxDecoration(
          color: AppColors.surfaceSoft,
          borderRadius: BorderRadius.circular(17),
          border: Border.all(color: AppColors.border)),
      child: const Row(children: [
        CircleAvatar(
            backgroundColor: Color(0xff2b1a55),
            child: Icon(Icons.school_outlined, color: AppColors.purple)),
        SizedBox(width: 13),
        Expanded(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Career Resources',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
          SizedBox(height: 3),
          Text('CV tips, interview preparation and career guidance',
              style: TextStyle(fontSize: 12, color: AppColors.muted)),
        ])),
        Icon(Icons.chevron_right, color: AppColors.muted),
      ]));
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
                    style: const TextStyle(fontWeight: FontWeight.w600)),
              ))));
}

class _JobCard extends StatelessWidget {
  const _JobCard({required this.job, required this.state});
  final Job job;
  final AppState state;
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
              Icon(job.bookmarked ? Icons.bookmark : Icons.bookmark_border,
                  color: job.bookmarked ? AppColors.red : AppColors.muted),
            ])),
      ));
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
