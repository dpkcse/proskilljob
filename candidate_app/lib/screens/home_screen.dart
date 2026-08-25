import 'package:flutter/material.dart';
import '../models/job.dart';
import '../state/app_state.dart';
import 'job_details_screen.dart';
import 'login_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key, required this.state});
  final AppState state;
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
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

  @override
  Widget build(BuildContext context) {
    final state = widget.state;
    return Scaffold(
      appBar: AppBar(
        title: const Text('ProSkill Jobs', style: TextStyle(fontWeight: FontWeight.w700)),
        actions: [
          if (state.loggedIn)
            IconButton(
              tooltip: 'লগআউট',
              onPressed: state.logout,
              icon: const Icon(Icons.logout),
            )
          else
            TextButton(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => LoginScreen(state: state)),
              ),
              child: const Text('লগইন'),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => state.loadJobs(keyword: search.text.trim()),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            const Text('আপনার পরবর্তী চাকরি খুঁজুন',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800)),
            const SizedBox(height: 6),
            const Text('বাংলাদেশের সেরা সুযোগগুলো এক জায়গায়'),
            const SizedBox(height: 18),
            TextField(
              controller: search,
              textInputAction: TextInputAction.search,
              onSubmitted: (value) => state.loadJobs(keyword: value.trim()),
              decoration: InputDecoration(
                hintText: 'পদ বা কীওয়ার্ড লিখুন',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: IconButton(
                  onPressed: () => state.loadJobs(keyword: search.text.trim()),
                  icon: const Icon(Icons.arrow_forward),
                ),
              ),
            ),
            const SizedBox(height: 20),
            if (state.busy) const LinearProgressIndicator(),
            if (state.error != null)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 24),
                child: Text(state.error!, style: const TextStyle(color: Colors.red)),
              ),
            if (!state.busy && state.error == null && state.jobs.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 40),
                child: Center(child: Text('কোনো চাকরি পাওয়া যায়নি')),
              ),
            ...state.jobs.map((job) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: _JobCard(job: job, state: state),
                )),
          ],
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: 0,
        destinations: const [
          NavigationDestination(icon: Icon(Icons.work_outline), label: 'চাকরি'),
          NavigationDestination(icon: Icon(Icons.bookmark_outline), label: 'সেভড'),
          NavigationDestination(icon: Icon(Icons.person_outline), label: 'প্রোফাইল'),
        ],
      ),
    );
  }
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
              builder: (_) => JobDetailsScreen(state: state, job: job),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              CircleAvatar(
                radius: 25,
                backgroundColor: const Color(0xffe8efff),
                backgroundImage: job.logo.isNotEmpty ? NetworkImage(job.logo) : null,
                child: job.logo.isEmpty ? const Icon(Icons.business) : null,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(job.title,
                      style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 4),
                  Text(job.company),
                  const SizedBox(height: 10),
                  Wrap(spacing: 8, runSpacing: 6, children: [
                    _tag(Icons.location_on_outlined, job.location),
                    _tag(Icons.schedule, job.type),
                    if (job.salary.isNotEmpty) _tag(Icons.payments_outlined, job.salary),
                  ]),
                ]),
              ),
              Icon(job.bookmarked ? Icons.bookmark : Icons.bookmark_border),
            ]),
          ),
        ),
      );

  Widget _tag(IconData icon, String text) => Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 15, color: Colors.blueGrey),
        const SizedBox(width: 3),
        Text(text, style: const TextStyle(fontSize: 12)),
      ]);
}

