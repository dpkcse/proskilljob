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
              search: search,
              onSearch: () {
                setState(() => tab = 1);
                widget.state.loadJobs(keyword: search.text.trim());
              },
              onRegister: openRegister,
              onLogin: openLogin),
          _JobsPage(state: widget.state, search: search),
          const _PlaceholderPage(
              icon: Icons.apartment_rounded, title: 'Companies'),
          const _PlaceholderPage(
              icon: Icons.chat_bubble_outline, title: 'Messages'),
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
                icon: Icon(Icons.apartment_outlined),
                selectedIcon: Icon(Icons.apartment, color: AppColors.red),
                label: 'Companies'),
            NavigationDestination(
                icon: Icon(Icons.chat_bubble_outline),
                selectedIcon: Icon(Icons.chat_bubble, color: AppColors.red),
                label: 'Messages'),
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
      {required this.search,
      required this.onSearch,
      required this.onRegister,
      required this.onLogin});
  final TextEditingController search;
  final VoidCallback onSearch;
  final VoidCallback onRegister;
  final VoidCallback onLogin;
  @override
  Widget build(BuildContext context) =>
      ListView(padding: const EdgeInsets.fromLTRB(22, 24, 22, 26), children: [
        const Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [BrandLogo(), Icon(Icons.menu_rounded, size: 34)]),
        const SizedBox(height: 38),
        Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
                border: Border.all(color: AppColors.border),
                borderRadius: BorderRadius.circular(12)),
            child: const Row(mainAxisSize: MainAxisSize.min, children: [
              Icon(Icons.work_outline, color: AppColors.purple, size: 19),
              SizedBox(width: 8),
              Text('10,000+ Jobs',
                  style: TextStyle(fontWeight: FontWeight.w600)),
              Padding(
                  padding: EdgeInsets.symmetric(horizontal: 10),
                  child: Text('•', style: TextStyle(color: AppColors.muted))),
              Icon(Icons.circle, color: Color(0xff4b5272), size: 16),
              SizedBox(width: 7),
              Flexible(
                  child: Text('500+ Companies',
                      style: TextStyle(fontWeight: FontWeight.w600))),
            ])),
        const SizedBox(height: 28),
        RichText(
            text: const TextSpan(
                style: TextStyle(
                    fontSize: 42, height: 1.08, fontWeight: FontWeight.w800),
                children: [
              TextSpan(text: 'Perfect Job\nMatches, '),
              TextSpan(
                  text: 'Faster', style: TextStyle(color: AppColors.purple)),
            ])),
        const SizedBox(height: 14),
        const Text(
            'Discover opportunities, connect with top companies and grow your career.',
            style:
                TextStyle(fontSize: 19, height: 1.45, color: AppColors.muted)),
        const SizedBox(height: 28),
        TextField(
            controller: search,
            onSubmitted: (_) => onSearch(),
            style: const TextStyle(color: AppColors.background),
            decoration: InputDecoration(
                filled: true,
                fillColor: const Color(0xfff7f7fa),
                hintText: 'Job title, keyword or company',
                hintStyle: const TextStyle(color: Color(0xff777b8b)),
                suffixIcon: IconButton(
                    onPressed: onSearch,
                    icon: const Icon(Icons.search, color: Color(0xff555c76))))),
        const SizedBox(height: 14),
        _ActionTile(
            color: AppColors.red,
            title: 'Find a Job',
            subtitle: 'For Job Seekers',
            onTap: onSearch),
        const SizedBox(height: 12),
        _ActionTile(
            color: const Color(0xfff5f5f8),
            title: 'Find Talent',
            subtitle: 'For Employers',
            darkText: true,
            onTap: () {}),
        const SizedBox(height: 22),
        Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
                border: Border.all(color: const Color(0xff6b1532)),
                borderRadius: BorderRadius.circular(15)),
            child: Column(children: [
              const Text('Join thousands of professionals',
                  style: TextStyle(color: AppColors.muted)),
              const SizedBox(height: 14),
              SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                      style: FilledButton.styleFrom(
                          backgroundColor: AppColors.purple,
                          padding: const EdgeInsets.all(15)),
                      onPressed: onRegister,
                      icon: const Icon(Icons.person_add_alt),
                      label: const Text('Create Free Account'))),
              const SizedBox(height: 10),
              SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.all(15),
                          side: const BorderSide(color: AppColors.border)),
                      onPressed: onLogin,
                      icon: const Icon(Icons.login),
                      label: const Text('Log In'))),
            ])),
      ]);
}

class _ActionTile extends StatelessWidget {
  const _ActionTile(
      {required this.color,
      required this.title,
      required this.subtitle,
      required this.onTap,
      this.darkText = false});
  final Color color;
  final String title;
  final String subtitle;
  final bool darkText;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) => Material(
      color: color,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
            child: Row(children: [
              Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text(title,
                        style: TextStyle(
                            fontSize: 19,
                            fontWeight: FontWeight.w700,
                            color: darkText ? AppColors.purple : Colors.white)),
                    Text(subtitle,
                        style: TextStyle(
                            color: darkText
                                ? const Color(0xff6c7080)
                                : Colors.white70)),
                  ])),
              Icon(Icons.chevron_right,
                  size: 30, color: darkText ? AppColors.purple : Colors.white),
            ])),
      ));
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

class _PlaceholderPage extends StatelessWidget {
  const _PlaceholderPage({required this.icon, required this.title});
  final IconData icon;
  final String title;
  @override
  Widget build(BuildContext context) => Center(
          child: Column(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 55, color: AppColors.purple),
        const SizedBox(height: 12),
        Text(title,
            style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
        const SizedBox(height: 6),
        const Text('Coming soon', style: TextStyle(color: AppColors.muted)),
      ]));
}
