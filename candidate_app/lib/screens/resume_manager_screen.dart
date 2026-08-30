import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../config/app_config.dart';
import '../services/api_client.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';

class ResumeManagerScreen extends StatefulWidget {
  const ResumeManagerScreen({super.key, required this.state});

  final AppState state;

  @override
  State<ResumeManagerScreen> createState() => _ResumeManagerScreenState();
}

class _ResumeManagerScreenState extends State<ResumeManagerScreen> {
  List<Map<String, dynamic>> resumes = const [];
  bool loading = true;
  bool working = false;
  String? error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      resumes = await widget.state.candidateApi.getResumes();
    } catch (exception) {
      error = '$exception';
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<PlatformFile?> _pickFile() async {
    final file = await FilePicker.pickFile(
      type: FileType.custom,
      allowedExtensions: const ['pdf', 'doc', 'docx'],
    );
    if (file == null) return null;
    if (await file.length() > 5 * 1024 * 1024) {
      _showMessage('Resume file must not exceed 5 MB.', error: true);
      return null;
    }
    return file;
  }

  Future<String?> _askName(String initial, {required String title}) async {
    final controller = TextEditingController(text: initial);
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: TextField(
          controller: controller,
          autofocus: true,
          maxLength: 100,
          textInputAction: TextInputAction.done,
          decoration: const InputDecoration(
            labelText: 'Resume name',
            hintText: 'e.g. Frontend Developer Resume',
          ),
          onSubmitted: (value) {
            if (value.trim().isNotEmpty) Navigator.pop(context, value.trim());
          },
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () {
              final value = controller.text.trim();
              if (value.isNotEmpty) Navigator.pop(context, value);
            },
            child: const Text('Continue'),
          ),
        ],
      ),
    );
    // The dialog route can still be finalizing its inherited dependencies
    // when showDialog completes. Dispose after that frame has finished.
    WidgetsBinding.instance.addPostFrameCallback((_) => controller.dispose());
    return result;
  }

  Future<void> _upload() async {
    final file = await _pickFile();
    if (file == null || !mounted) return;
    final defaultName = file.name.replaceFirst(RegExp(r'\.[^.]+$'), '');
    final name = await _askName(defaultName, title: 'Upload Resume');
    if (name == null || !mounted) return;
    await _perform(() async {
      final bytes = await file.readAsBytes();
      final saved = await widget.state.candidateApi.uploadResume(
        name: name,
        fileName: file.name,
        fileBytes: bytes,
      );
      await _refreshAfterChange();
      final id = int.tryParse('${saved['id']}');
      if (id != null && !resumes.any((item) => item['id'] == id)) {
        throw const ApiException(
            'Upload completed, but the saved resume could not be verified.');
      }
      _showMessage('Resume uploaded and saved successfully.');
    });
  }

  Future<void> _replace(Map<String, dynamic> resume) async {
    final file = await _pickFile();
    if (file == null || !mounted) return;
    final id = int.tryParse('${resume['id']}');
    if (id == null) return;
    await _perform(() async {
      final bytes = await file.readAsBytes();
      await widget.state.candidateApi.replaceResume(
        id: id,
        name: '${resume['name'] ?? 'Resume'}',
        fileName: file.name,
        fileBytes: bytes,
      );
      await _refreshAfterChange();
      _showMessage('Resume file replaced successfully.');
    });
  }

  Future<void> _rename(Map<String, dynamic> resume) async {
    final id = int.tryParse('${resume['id']}');
    if (id == null) return;
    final name =
        await _askName('${resume['name'] ?? ''}', title: 'Rename Resume');
    if (name == null || !mounted) return;
    await _perform(() async {
      await widget.state.candidateApi.renameResume(id: id, name: name);
      await _refreshAfterChange();
      _showMessage('Resume name updated successfully.');
    });
  }

  Future<void> _delete(Map<String, dynamic> resume) async {
    final id = int.tryParse('${resume['id']}');
    if (id == null) return;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete resume?'),
        content: Text(
          '“${resume['name'] ?? 'Resume'}” will be permanently removed. A resume used in a job application cannot be deleted.',
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancel')),
          FilledButton(
            style: FilledButton.styleFrom(
                backgroundColor: AppColors.danger,
                foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;
    await _perform(() async {
      await widget.state.candidateApi.deleteResume(id);
      await _refreshAfterChange();
      _showMessage('Resume deleted successfully.');
    });
  }

  Future<void> _open(Map<String, dynamic> resume) async {
    var url = '${resume['file_url'] ?? ''}'.trim();
    if (url.isEmpty) {
      final relative =
          '${resume['file'] ?? ''}'.replaceFirst(RegExp(r'^/+'), '');
      if (relative.isNotEmpty) {
        url = '${Uri.parse(AppConfig.apiBaseUrl).origin}/$relative';
      }
    }
    final opened = url.isNotEmpty &&
        await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
    if (!opened) _showMessage('Resume file could not be opened.', error: true);
  }

  Future<void> _perform(Future<void> Function() action) async {
    setState(() => working = true);
    try {
      await action();
    } catch (exception) {
      _showMessage('$exception', error: true);
    } finally {
      if (mounted) setState(() => working = false);
    }
  }

  Future<void> _refreshAfterChange() async {
    resumes = await widget.state.candidateApi.getResumes();
    if (mounted) setState(() {});
  }

  void _showMessage(String message, {bool error = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(message),
      backgroundColor:
          error ? const Color(0xff8f1735) : const Color(0xff17644f),
    ));
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('My Resumes')),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: working ? null : _upload,
          backgroundColor: AppColors.purple,
          foregroundColor: Colors.white,
          icon: const Icon(Icons.upload_file_rounded),
          label: const Text('Upload Resume'),
        ),
        body: Stack(
          children: [
            RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(18, 18, 18, 110),
                children: [
                  _UploadGuide(onUpload: working ? null : _upload),
                  const SizedBox(height: 22),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Saved resumes',
                          style: TextStyle(
                              fontSize: 19, fontWeight: FontWeight.w800)),
                      Text('${resumes.length}',
                          style: const TextStyle(color: AppColors.muted)),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (loading)
                    const Padding(
                      padding: EdgeInsets.all(42),
                      child: Center(child: CircularProgressIndicator()),
                    )
                  else if (error != null)
                    _ResumeError(message: error!, onRetry: _load)
                  else if (resumes.isEmpty)
                    _EmptyResumes(onUpload: _upload)
                  else
                    ...resumes.map((resume) => _ResumeCard(
                          resume: resume,
                          onOpen: () => _open(resume),
                          onRename: () => _rename(resume),
                          onReplace: () => _replace(resume),
                          onDelete: () => _delete(resume),
                        )),
                ],
              ),
            ),
            if (working)
              Positioned.fill(
                child: ColoredBox(
                  color: Colors.black.withValues(alpha: .42),
                  child: const Center(
                    child: Card(
                      child: Padding(
                        padding: EdgeInsets.all(22),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            CircularProgressIndicator(),
                            SizedBox(height: 14),
                            Text('Saving resume securely...'),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      );
}

class _UploadGuide extends StatelessWidget {
  const _UploadGuide({required this.onUpload});
  final VoidCallback? onUpload;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xff20124a), Color(0xff111b3a)],
          ),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xff4a367c)),
        ),
        child: Row(children: [
          Container(
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              color: AppColors.purple.withValues(alpha: .2),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.description_outlined,
                color: Color(0xffb99aff), size: 30),
          ),
          const SizedBox(width: 15),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Keep your best resume ready',
                    style:
                        TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
                SizedBox(height: 5),
                Text('PDF, DOC or DOCX • Maximum 5 MB',
                    style: TextStyle(color: AppColors.muted, fontSize: 12)),
              ],
            ),
          ),
          IconButton.filled(
            onPressed: onUpload,
            tooltip: 'Upload resume',
            icon: const Icon(Icons.add),
          ),
        ]),
      );
}

class _ResumeCard extends StatelessWidget {
  const _ResumeCard({
    required this.resume,
    required this.onOpen,
    required this.onRename,
    required this.onReplace,
    required this.onDelete,
  });

  final Map<String, dynamic> resume;
  final VoidCallback onOpen;
  final VoidCallback onRename;
  final VoidCallback onReplace;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 11),
        padding: const EdgeInsets.fromLTRB(14, 13, 6, 13),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: AppColors.red.withValues(alpha: .1),
              borderRadius: BorderRadius.circular(13),
            ),
            child:
                const Icon(Icons.picture_as_pdf_outlined, color: AppColors.red),
          ),
          const SizedBox(width: 13),
          Expanded(
            child: InkWell(
              onTap: onOpen,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('${resume['name'] ?? 'Resume'}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 4),
                  Text('${resume['file_size'] ?? ''}',
                      style: const TextStyle(
                          color: AppColors.muted, fontSize: 12)),
                ],
              ),
            ),
          ),
          PopupMenuButton<String>(
            tooltip: 'Resume actions',
            onSelected: (value) {
              switch (value) {
                case 'open':
                  onOpen();
                case 'rename':
                  onRename();
                case 'replace':
                  onReplace();
                case 'delete':
                  onDelete();
              }
            },
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'open', child: Text('View resume')),
              PopupMenuItem(value: 'rename', child: Text('Rename')),
              PopupMenuItem(value: 'replace', child: Text('Replace file')),
              PopupMenuItem(
                  value: 'delete',
                  child: Text('Delete',
                      style: TextStyle(color: AppColors.danger))),
            ],
          ),
        ]),
      );
}

class _EmptyResumes extends StatelessWidget {
  const _EmptyResumes({required this.onUpload});
  final VoidCallback onUpload;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 40),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(17),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(children: [
          const Icon(Icons.cloud_upload_outlined,
              size: 50, color: AppColors.purple),
          const SizedBox(height: 13),
          const Text('No resume uploaded yet',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
          const SizedBox(height: 6),
          const Text('Upload a resume to start applying for jobs.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.muted)),
          const SizedBox(height: 17),
          OutlinedButton.icon(
            onPressed: onUpload,
            icon: const Icon(Icons.upload_file),
            label: const Text('Upload Resume'),
          ),
        ]),
      );
}

class _ResumeError extends StatelessWidget {
  const _ResumeError({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: const Color(0xff351625),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(children: [
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 10),
          TextButton(onPressed: onRetry, child: const Text('Try again')),
        ]),
      );
}
