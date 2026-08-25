import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import '../models/job.dart';
import '../state/app_state.dart';

class JobDetailsScreen extends StatefulWidget {
  const JobDetailsScreen({super.key, required this.state, required this.job});
  final AppState state;
  final Job job;
  @override
  State<JobDetailsScreen> createState() => _JobDetailsScreenState();
}

class _JobDetailsScreenState extends State<JobDetailsScreen> {
  late final Future<Map<String, dynamic>> details =
      widget.state.jobsApi.details(widget.job.slug);

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('চাকরির বিস্তারিত')),
        body: FutureBuilder<Map<String, dynamic>>(
          future: details,
          builder: (_, snapshot) {
            if (snapshot.connectionState != ConnectionState.done) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.hasError) return Center(child: Text('${snapshot.error}'));
            final data = snapshot.data!;
            final company = data['company'] as Map? ?? {};
            return ListView(padding: const EdgeInsets.all(20), children: [
              Text('${data['title'] ?? widget.job.title}',
                  style: const TextStyle(fontSize: 25, fontWeight: FontWeight.w800)),
              const SizedBox(height: 6),
              Text('${company['name'] ?? widget.job.company}',
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 18),
              Wrap(spacing: 10, runSpacing: 10, children: [
                Chip(label: Text('${data['job_type'] ?? widget.job.type}')),
                Chip(label: Text('${data['location'] ?? widget.job.location}')),
                Chip(label: Text('${data['salary'] ?? widget.job.salary}')),
              ]),
              const SizedBox(height: 18),
              const Text('চাকরির বিবরণ',
                  style: TextStyle(fontSize: 19, fontWeight: FontWeight.w700)),
              Html(data: '${data['description'] ?? ''}'),
              if (data['benefits'] is List && (data['benefits'] as List).isNotEmpty) ...[
                const Text('সুবিধা',
                    style: TextStyle(fontSize: 19, fontWeight: FontWeight.w700)),
                ...((data['benefits'] as List).map((e) => Text('• $e'))),
              ],
              const SizedBox(height: 90),
            ]);
          },
        ),
        bottomNavigationBar: SafeArea(
          minimum: const EdgeInsets.all(12),
          child: FilledButton.icon(
            onPressed: () => ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('পরবর্তী ধাপে CV নির্বাচন ও আবেদন ফর্ম যুক্ত হবে')),
            ),
            icon: const Icon(Icons.send),
            label: const Padding(
              padding: EdgeInsets.all(14), child: Text('আবেদন করুন')),
          ),
        ),
      );
}

