import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import '../models/job.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';

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
  bool saved = false;
  @override
  void initState() {
    super.initState();
    saved = widget.job.bookmarked;
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(actions: [
          IconButton(
              onPressed: () async {
                try {
                  final value =
                      await widget.state.jobsApi.toggleBookmark(widget.job.id);
                  if (mounted) {
                    setState(() => saved = value);
                  }
                } catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context)
                        .showSnackBar(SnackBar(content: Text('$e')));
                  }
                }
              },
              icon: Icon(saved ? Icons.bookmark : Icons.bookmark_border,
                  color: saved ? AppColors.red : null)),
          IconButton(onPressed: () {}, icon: const Icon(Icons.share_outlined)),
          const SizedBox(width: 8),
        ]),
        body: FutureBuilder<Map<String, dynamic>>(
            future: details,
            builder: (_, snapshot) {
              if (snapshot.connectionState != ConnectionState.done) {
                return const Center(
                    child: CircularProgressIndicator(color: AppColors.red));
              }
              if (snapshot.hasError) {
                return Center(
                    child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Text('${snapshot.error}')));
              }
              final data = snapshot.data!;
              final company = data['company'] as Map? ?? {};
              final benefits = data['benefits'] as List? ?? const [];
              return ListView(
                  padding: const EdgeInsets.fromLTRB(20, 8, 20, 120),
                  children: [
                    Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                              width: 74,
                              height: 74,
                              decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(15)),
                              child: widget.job.logo.isEmpty
                                  ? const Icon(Icons.business,
                                      color: AppColors.red, size: 35)
                                  : ClipRRect(
                                      borderRadius: BorderRadius.circular(15),
                                      child: Image.network(widget.job.logo,
                                          fit: BoxFit.contain))),
                          const SizedBox(width: 16),
                          Expanded(
                              child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                Text('${data['title'] ?? widget.job.title}',
                                    style: const TextStyle(
                                        fontSize: 24,
                                        fontWeight: FontWeight.w800)),
                                const SizedBox(height: 5),
                                Text('${company['name'] ?? widget.job.company}',
                                    style: const TextStyle(
                                        fontSize: 17, color: AppColors.muted)),
                              ])),
                        ]),
                    const SizedBox(height: 22),
                    Row(children: [
                      const Icon(Icons.location_on_outlined,
                          color: AppColors.muted),
                      const SizedBox(width: 5),
                      Expanded(
                          child: Text(
                              '${data['location'] ?? widget.job.location}')),
                      const Icon(Icons.work_outline, color: AppColors.muted),
                      const SizedBox(width: 6),
                      Text('${data['job_type'] ?? widget.job.type}')
                    ]),
                    const SizedBox(height: 12),
                    Text('Posted ${data['posted_at'] ?? ''}',
                        style: const TextStyle(color: AppColors.muted)),
                    const Divider(height: 34, color: AppColors.border),
                    const Text('Job Description',
                        style: TextStyle(
                            fontSize: 21, fontWeight: FontWeight.w800)),
                    Html(data: '${data['description'] ?? ''}', style: {
                      'body': Style(
                          color: AppColors.muted,
                          fontSize: FontSize(16),
                          margin: Margins.zero)
                    }),
                    const SizedBox(height: 18),
                    if (data['experience'] != null ||
                        data['education'] != null) ...[
                      const Text('Requirements',
                          style: TextStyle(
                              fontSize: 21, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 10),
                      if (data['experience'] != null)
                        Text('• Experience: ${data['experience']}',
                            style: const TextStyle(
                                fontSize: 16, color: AppColors.muted)),
                      if (data['education'] != null)
                        Text('• Education: ${data['education']}',
                            style: const TextStyle(
                                fontSize: 16, color: AppColors.muted)),
                      const SizedBox(height: 22),
                    ],
                    if (benefits.isNotEmpty) ...[
                      const Text('Benefits',
                          style: TextStyle(
                              fontSize: 21, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 12),
                      Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          children: benefits
                              .map((e) => Container(
                                  width: 105,
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                      color: AppColors.surface,
                                      borderRadius: BorderRadius.circular(13),
                                      border:
                                          Border.all(color: AppColors.border)),
                                  child: Column(children: [
                                    const Icon(Icons.favorite_border,
                                        color: AppColors.red),
                                    const SizedBox(height: 7),
                                    Text('$e',
                                        textAlign: TextAlign.center,
                                        style: const TextStyle(fontSize: 12))
                                  ])))
                              .toList()),
                    ],
                  ]);
            }),
        bottomNavigationBar: SafeArea(
            minimum: const EdgeInsets.all(14),
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                      style: FilledButton.styleFrom(
                          backgroundColor: AppColors.red,
                          padding: const EdgeInsets.all(16)),
                      onPressed: () => ScaffoldMessenger.of(context)
                          .showSnackBar(const SnackBar(
                              content: Text('CV selection will open here'))),
                      child: const Text('Apply Now'))),
              const SizedBox(height: 9),
              SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.all(15),
                          side: const BorderSide(color: AppColors.border)),
                      onPressed: () {},
                      child: const Text('Save Job'))),
            ])),
      );
}
