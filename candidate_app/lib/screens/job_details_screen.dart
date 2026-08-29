import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import '../models/job.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';

class JobDetailsScreen extends StatefulWidget {
  const JobDetailsScreen({super.key, required this.state, required this.job});
  final AppState state;
  final Job job;
  @override
  State<JobDetailsScreen> createState() => _JobDetailsScreenState();
}

class _JobDetailsScreenState extends State<JobDetailsScreen> {
  late final Future<Map<String, dynamic>> details;
  Map<String, dynamic>? detailData;
  bool saved = false;
  bool applied = false;
  bool openingApplication = false;

  @override
  void initState() {
    super.initState();
    saved = widget.job.bookmarked;
    details = widget.state.jobsApi.details(widget.job.slug).then((data) {
      detailData = data;
      applied = _isTrue(data['applied']);
      if (mounted) setState(() {});
      return data;
    });
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(actions: [
          IconButton(
              onPressed: () async {
                if (!widget.state.loggedIn) {
                  await _requestLogin(
                      'Log in to save jobs and access them on every device.');
                  return;
                }
                try {
                  final value = await widget.state.toggleSaved(widget.job);
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
                      onPressed: applied || openingApplication
                          ? null
                          : () async {
                              if (!widget.state.loggedIn) {
                                await _requestLogin(
                                    'Create or log in to your free account to apply and track this application.');
                                return;
                              }
                              await _openApplicationSheet();
                            },
                      child: Text(applied
                          ? 'Already Applied'
                          : openingApplication
                              ? 'Preparing...'
                              : 'Apply Now'))),
              const SizedBox(height: 9),
              SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.all(15),
                          side: const BorderSide(color: AppColors.border)),
                      onPressed: () async {
                        if (!widget.state.loggedIn) {
                          await _requestLogin(
                              'Log in to save this job and receive updates.');
                          return;
                        }
                        try {
                          final value =
                              await widget.state.toggleSaved(widget.job);
                          if (mounted) setState(() => saved = value);
                        } catch (e) {
                          if (mounted) {
                            ScaffoldMessenger.of(this.context)
                                .showSnackBar(SnackBar(content: Text('$e')));
                          }
                        }
                      },
                      child: const Text('Save Job'))),
            ])),
      );

  Future<void> _requestLogin(String message) async {
    final shouldLogin = await showModalBottomSheet<bool>(
      context: context,
      backgroundColor: AppColors.surface,
      showDragHandle: true,
      builder: (sheetContext) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(22, 8, 22, 24),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const CircleAvatar(
                radius: 28,
                backgroundColor: Color(0xff2b1a55),
                child: Icon(Icons.person_outline,
                    color: AppColors.purple, size: 30)),
            const SizedBox(height: 14),
            const Text('Candidate account required',
                style: TextStyle(fontSize: 21, fontWeight: FontWeight.w800)),
            const SizedBox(height: 7),
            Text(message,
                textAlign: TextAlign.center,
                style: const TextStyle(color: AppColors.muted)),
            const SizedBox(height: 18),
            SizedBox(
                width: double.infinity,
                child: FilledButton(
                    onPressed: () => Navigator.pop(sheetContext, true),
                    child: const Text('Continue to Log In'))),
            TextButton(
                onPressed: () => Navigator.pop(sheetContext, false),
                child: const Text('Continue browsing')),
          ]),
        ),
      ),
    );
    if (shouldLogin == true && mounted) {
      await Navigator.push(context,
          MaterialPageRoute(builder: (_) => LoginScreen(state: widget.state)));
    }
  }

  Future<void> _openApplicationSheet() async {
    final data = detailData;
    if (data == null) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Please wait while the job loads.')));
      return;
    }
    if ('${data['apply_on'] ?? 'app'}' != 'app') {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('This employer accepts applications externally.')));
      return;
    }

    setState(() => openingApplication = true);
    if (widget.state.resumes.isEmpty) await widget.state.loadProfile();
    if (!mounted) return;
    setState(() => openingApplication = false);

    if (widget.state.resumes.isEmpty) {
      await showModalBottomSheet<void>(
        context: context,
        backgroundColor: AppColors.surface,
        showDragHandle: true,
        builder: (sheetContext) => SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(24, 8, 24, 28),
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              const Icon(Icons.upload_file, size: 48, color: AppColors.purple),
              const SizedBox(height: 14),
              const Text('Upload a CV first',
                  style: TextStyle(fontSize: 21, fontWeight: FontWeight.w800)),
              const SizedBox(height: 8),
              const Text(
                  'Add a CV from your Profile before applying for this job.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppColors.muted)),
              const SizedBox(height: 18),
              SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                      onPressed: () => Navigator.pop(sheetContext),
                      child: const Text('Go to Profile'))),
            ]),
          ),
        ),
      );
      return;
    }

    final coverLetter = TextEditingController();
    final formKey = GlobalKey<FormState>();
    int? selectedResumeId =
        int.tryParse('${widget.state.resumes.first['id'] ?? ''}');
    String? result;
    try {
      result = await showModalBottomSheet<String>(
        context: context,
        isScrollControlled: true,
        backgroundColor: AppColors.surface,
        showDragHandle: true,
        builder: (sheetContext) {
          bool submitting = false;
          String? submitError;
          return StatefulBuilder(builder: (context, setSheetState) {
            Future<void> submit() async {
              if (!(formKey.currentState?.validate() ?? false) ||
                  selectedResumeId == null) {
                return;
              }
              setSheetState(() {
                submitting = true;
                submitError = null;
              });
              try {
                final message = await widget.state.jobsApi.apply(
                    jobId: widget.job.id,
                    resumeId: selectedResumeId!,
                    coverLetter: coverLetter.text.trim());
                if (sheetContext.mounted) {
                  Navigator.pop(sheetContext, message);
                }
              } catch (e) {
                if (sheetContext.mounted) {
                  setSheetState(() {
                    submitting = false;
                    submitError = '$e';
                  });
                }
              }
            }

            return Padding(
              padding: EdgeInsets.fromLTRB(
                  20, 4, 20, MediaQuery.viewInsetsOf(context).bottom + 22),
              child: Form(
                key: formKey,
                child: SingleChildScrollView(
                  child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Apply for this job',
                            style: TextStyle(
                                fontSize: 23, fontWeight: FontWeight.w800)),
                        const SizedBox(height: 4),
                        Text(widget.job.title,
                            style: const TextStyle(color: AppColors.muted)),
                        const SizedBox(height: 20),
                        const Text('Select CV',
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 8),
                        IgnorePointer(
                          ignoring: submitting,
                          child: RadioGroup<int>(
                            groupValue: selectedResumeId,
                            onChanged: (value) =>
                                setSheetState(() => selectedResumeId = value),
                            child: ConstrainedBox(
                              constraints: const BoxConstraints(maxHeight: 270),
                              child: ListView(
                                shrinkWrap: true,
                                physics: const BouncingScrollPhysics(),
                                children: widget.state.resumes.map((resume) {
                                  final id =
                                      int.tryParse('${resume['id'] ?? ''}');
                                  return Container(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    decoration: BoxDecoration(
                                        color: AppColors.surfaceSoft,
                                        borderRadius: BorderRadius.circular(13),
                                        border: Border.all(
                                            color: AppColors.border)),
                                    child: RadioListTile<int>(
                                      value: id ?? -1,
                                      activeColor: AppColors.purple,
                                      secondary: const Icon(
                                          Icons.picture_as_pdf_outlined,
                                          color: AppColors.red),
                                      title:
                                          Text('${resume['name'] ?? 'Resume'}'),
                                      subtitle: Text(
                                          '${resume['file_size'] ?? ''}',
                                          style: const TextStyle(
                                              fontSize: 12,
                                              color: AppColors.muted)),
                                    ),
                                  );
                                }).toList(),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        TextFormField(
                          controller: coverLetter,
                          enabled: !submitting,
                          minLines: 5,
                          maxLines: 8,
                          maxLength: 2000,
                          decoration: const InputDecoration(
                              labelText: 'Cover letter',
                              alignLabelWithHint: true,
                              hintText:
                                  'Briefly explain why you are a good fit for this role.'),
                          validator: (value) =>
                              value == null || value.trim().isEmpty
                                  ? 'Please enter a cover letter'
                                  : null,
                        ),
                        if (submitError != null)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: Text(submitError!,
                                style: const TextStyle(color: AppColors.red)),
                          ),
                        SizedBox(
                          width: double.infinity,
                          child: FilledButton.icon(
                            style: FilledButton.styleFrom(
                                backgroundColor: AppColors.red,
                                padding: const EdgeInsets.all(15)),
                            onPressed: submitting ? null : submit,
                            icon: submitting
                                ? const SizedBox(
                                    width: 18,
                                    height: 18,
                                    child: CircularProgressIndicator(
                                        strokeWidth: 2, color: Colors.white))
                                : const Icon(Icons.send_outlined),
                            label: Text(submitting
                                ? 'Submitting...'
                                : 'Submit Application'),
                          ),
                        ),
                      ]),
                ),
              ),
            );
          });
        },
      );
    } finally {
      coverLetter.dispose();
    }

    if (result != null && mounted) {
      setState(() => applied = true);
      await widget.state.loadProfile();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(result), backgroundColor: const Color(0xff167c62)));
      }
    }
  }

  bool _isTrue(dynamic value) =>
      value == true || value == 1 || '$value'.toLowerCase() == 'true';
}
