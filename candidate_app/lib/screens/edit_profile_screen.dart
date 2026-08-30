import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../state/app_state.dart';
import '../theme/app_theme.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key, required this.state});

  final AppState state;

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final personalKey = GlobalKey<FormState>();
  final professionalKey = GlobalKey<FormState>();
  final contactKey = GlobalKey<FormState>();

  final fields = <String, TextEditingController>{};
  final socials = <_SocialEntry>[];
  List<Map<String, dynamic>> educations = const [];
  List<Map<String, dynamic>> academicQualifications = const [];
  List<Map<String, dynamic>> experienceEntries = const [];
  List<Map<String, dynamic>> references = const [];
  List<Map<String, dynamic>> professions = const [];
  List<Map<String, dynamic>> languages = const [];
  List<String> socialPlatforms = const [
    'facebook',
    'linkedin',
    'github',
    'twitter',
    'instagram',
    'youtube',
    'other',
  ];
  final selectedLanguages = <dynamic>{};
  final languageProficiencies = <int, String>{};
  final customSkills = <String>[];
  int? educationId;
  int? professionId;
  String? gender;
  String? maritalStatus;
  String availability = 'available';
  String? selectedPhotoPath;
  bool loading = true;
  String? loadError;
  String? savingSection;

  TextEditingController _field(String name) =>
      fields.putIfAbsent(name, TextEditingController.new);

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    for (final controller in fields.values) {
      controller.dispose();
    }
    for (final social in socials) {
      social.dispose();
    }
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      loadError = null;
    });
    try {
      final results = await Future.wait([
        widget.state.candidateApi.getSettings('personal'),
        widget.state.candidateApi.getSettings('profile'),
        widget.state.candidateApi.getSettings('contact'),
        widget.state.candidateApi.getSettings('social'),
      ]);
      _fillPersonal(results[0]);
      _fillProfessional(results[1]);
      _fillContact(results[2]);
      _fillSocial(results[3]);
    } catch (e) {
      loadError = '$e';
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  void _fillPersonal(Map<String, dynamic> data) {
    for (final key in [
      'name',
      'nationality',
      'nid_birth_registration_no',
      'passport_no',
      'passport_expiry_date',
      'date_of_birth',
      'address',
      'permanent_address',
    ]) {
      _field(key).text = '${data[key] ?? ''}';
    }
  }

  void _fillProfessional(Map<String, dynamic> data) {
    gender = _emptyToNull(data['gender']);
    maritalStatus = _emptyToNull(data['marital_status']);
    availability = '${data['availability'] ?? 'available'}';
    _field('bio').text = '${data['bio'] ?? ''}';
    _field('available_in').text = '${data['available_in'] ?? ''}';
    _field('preferred_job_locations').text =
        _stringList(data['preferred_job_locations']).join(', ');
    educations = _mapList(data['education_list']);
    academicQualifications = _mapList(data['education_qualifications']);
    experienceEntries = _mapList(data['experience_entries']);
    references = _mapList(data['references']);
    professions = _mapList(data['profession_list']);
    languages = _mapList(data['language_list']);
    professionId = _validId(data['profession_id'], professions);
    educationId = _validId(data['education_id'], educations);
    selectedLanguages
      ..clear()
      ..addAll(_mapList(data['languages']).map((item) => item['id']));
    languageProficiencies
      ..clear()
      ..addEntries(_mapList(data['languages']).map((item) => MapEntry(
          int.tryParse('${item['id']}') ?? 0,
          '${item['proficiency_level'] ?? 'basic'}')));
    customSkills
      ..clear()
      ..addAll(_mapList(data['skills']).map((item) => '${item['name']}'));
  }

  void _fillContact(Map<String, dynamic> data) {
    final contact = _map(data['contact_info']);
    _field('phone').text = '${contact['phone'] ?? ''}';
    _field('secondary_phone').text = '${contact['secondary_phone'] ?? ''}';
    _field('whatsapp_number').text = '${contact['whatsapp_no'] ?? ''}';
    _field('email').text =
        '${contact['email'] ?? widget.state.currentUser['email'] ?? ''}';
    _field('secondary_email').text = '${contact['secondary_email'] ?? ''}';
  }

  void _fillSocial(Map<String, dynamic> data) {
    for (final item in socials) {
      item.dispose();
    }
    socials
      ..clear()
      ..addAll(_mapList(data['social_media']).map((item) => _SocialEntry(
            platform: _socialValue(item['social_media']),
            url: '${item['url'] ?? ''}',
          )));
    final values = data['social_media_list'];
    final platforms = <String>{};
    if (values is Map) {
      platforms.addAll(values.keys.map(_socialValue));
    } else if (values is List) {
      platforms.addAll(values.map(_socialValue));
    }
    platforms.addAll(socials.map((item) => item.platform));
    if (platforms.isNotEmpty) socialPlatforms = platforms.toList();
  }

  @override
  Widget build(BuildContext context) => DefaultTabController(
        length: 5,
        child: Scaffold(
          appBar: AppBar(
            title: const Text('Edit Profile'),
            bottom: const TabBar(
              isScrollable: true,
              tabs: [
                Tab(text: 'Personal'),
                Tab(text: 'Education & Profession'),
                Tab(text: 'Contact'),
                Tab(text: 'Reference'),
                Tab(text: 'Social'),
              ],
            ),
          ),
          body: loading
              ? const Center(
                  child: CircularProgressIndicator(color: AppColors.red))
              : loadError != null
                  ? _LoadError(message: loadError!, onRetry: _load)
                  : TabBarView(children: [
                      _personalTab(),
                      _professionalTab(),
                      _contactTab(),
                      _referenceTab(),
                      _socialTab(),
                    ]),
        ),
      );

  Widget _personalTab() => _FormPage(
        formKey: personalKey,
        children: [
          _completionCard(),
          _photoPicker(),
          _text('name', 'Full name', required: true),
          _text('nid_birth_registration_no', 'NID / Birth Registration Number'),
          Row(children: [
            Expanded(child: _text('passport_no', 'Passport Number')),
            const SizedBox(width: 12),
            Expanded(
                child: _date('passport_expiry_date', 'Passport Expiry Date',
                    futureOnly: true)),
          ]),
          _date('date_of_birth', 'Date of birth', required: true),
          _text('nationality', 'Nationality'),
          const _GroupTitle('Present address'),
          _text('address', 'Present address',
              lines: 3,
              hint: 'Example: House No/Road, Village, District, Post Code'),
          const _GroupTitle('Permanent address'),
          _text('permanent_address', 'Permanent address',
              lines: 3,
              hint: 'Example: House No/Road, Village, District, Post Code'),
          _saveButton('personal', _savePersonal),
        ],
      );

  Widget _professionalTab() => _FormPage(
        formKey: professionalKey,
        children: [
          const _GroupTitle('Education'),
          _dropdown<int>(
              label: 'Education level',
              value: educationId,
              options: educations,
              onChanged: (value) => educationId = value),
          _academicEducationSection(),
          const _GroupTitle('Profession'),
          _dropdown<int>(
              label: 'Profession',
              value: professionId,
              options: professions,
              required: false,
              onChanged: (value) => professionId = value),
          _text('custom_profession', 'Or enter your profession'),
          const _GroupTitle('Experience'),
          _experienceSection(),
          const _GroupTitle('Language'),
          _languageSection(),
          const _GroupTitle('Skills'),
          _manualSkillsSection(),
          const _GroupTitle('Preferred Job Location'),
          _text('preferred_job_locations',
              'Preferred locations (comma separated)',
              lines: 2),
          const _GroupTitle('Additional professional details'),
          _dropdown<String>(
            label: 'Gender',
            value: gender,
            options: const [
              {'id': 'male', 'name': 'Male'},
              {'id': 'female', 'name': 'Female'},
              {'id': 'other', 'name': 'Other'},
            ],
            onChanged: (value) => gender = value,
          ),
          _dropdown<String>(
            label: 'Marital status',
            value: maritalStatus,
            required: false,
            options: const [
              {'id': 'single', 'name': 'Single'},
              {'id': 'married', 'name': 'Married'},
            ],
            onChanged: (value) => maritalStatus = value,
          ),
          _text('bio', 'Professional summary', required: true, lines: 6),
          _dropdown<String>(
            label: 'Availability',
            value: availability,
            options: const [
              {'id': 'available', 'name': 'Available now'},
              {'id': 'available_in', 'name': 'Available from a date'},
              {'id': 'not_available', 'name': 'Not available'},
            ],
            onChanged: (value) => setState(() => availability = value!),
          ),
          if (availability == 'available_in')
            _date('available_in', 'Available from', required: true),
          _saveButton('profile', _saveProfessional),
        ],
      );

  Widget _academicEducationSection() => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(children: [
            const Expanded(
              child: Text('Academic qualifications',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
            ),
            FilledButton.icon(
              onPressed: () => _openEducationEditor(),
              icon: const Icon(Icons.add, size: 18),
              label: const Text('Add'),
            ),
          ]),
          const SizedBox(height: 10),
          if (academicQualifications.isEmpty)
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: const Column(children: [
                Icon(Icons.school_outlined, size: 34, color: AppColors.muted),
                SizedBox(height: 8),
                Text('No academic qualification added yet.',
                    style: TextStyle(color: AppColors.muted)),
              ]),
            )
          else
            ...academicQualifications.map(_educationCard),
          const SizedBox(height: 8),
        ],
      );

  Widget _experienceSection() => _EntrySection(
        emptyIcon: Icons.work_history_outlined,
        emptyText: 'No professional experience added yet.',
        addLabel: 'Add experience',
        onAdd: () => _openExperienceEditor(),
        children: experienceEntries
            .map((item) => _ProfileEntryCard(
                  icon: Icons.business_center_outlined,
                  title: '${item['designation'] ?? ''}',
                  subtitle: '${item['company'] ?? ''}',
                  detail: _experiencePeriod(item),
                  onEdit: () => _openExperienceEditor(item: item),
                  onDelete: () => _deleteProfileEntry('experience', item),
                ))
            .toList(),
      );

  Widget _languageSection() => Column(
        children: languages.map((item) {
          final id = int.tryParse('${item['id']}') ?? 0;
          final selected = selectedLanguages.contains(item['id']);
          return Container(
            margin: const EdgeInsets.only(bottom: 9),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(13),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(children: [
              Checkbox(
                value: selected,
                onChanged: (value) => setState(() {
                  if (value == true) {
                    selectedLanguages.add(item['id']);
                    languageProficiencies.putIfAbsent(id, () => 'basic');
                  } else {
                    selectedLanguages.remove(item['id']);
                  }
                }),
              ),
              Expanded(child: Text('${item['name'] ?? ''}')),
              if (selected)
                SizedBox(
                  width: 135,
                  child: DropdownButton<String>(
                    value: languageProficiencies[id] ?? 'basic',
                    isExpanded: true,
                    underline: const SizedBox.shrink(),
                    items: const [
                      DropdownMenuItem(value: 'native', child: Text('Native')),
                      DropdownMenuItem(value: 'basic', child: Text('Basic')),
                      DropdownMenuItem(
                          value: 'professional', child: Text('Professional')),
                    ],
                    onChanged: (value) => setState(
                        () => languageProficiencies[id] = value ?? 'basic'),
                  ),
                ),
            ]),
          );
        }).toList(),
      );

  Widget _manualSkillsSection() => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(children: [
            Expanded(
              child: TextField(
                controller: _field('new_skill'),
                textInputAction: TextInputAction.done,
                decoration: const InputDecoration(
                    labelText: 'Type a skill', hintText: 'Example: AutoCAD'),
                onSubmitted: (_) => _addSkill(),
              ),
            ),
            const SizedBox(width: 9),
            IconButton.filled(
                onPressed: _addSkill, icon: const Icon(Icons.add)),
          ]),
          const SizedBox(height: 10),
          Wrap(
            spacing: 7,
            runSpacing: 7,
            children: customSkills
                .map((skill) => InputChip(
                      label: Text(skill),
                      onDeleted: () =>
                          setState(() => customSkills.remove(skill)),
                    ))
                .toList(),
          ),
          const SizedBox(height: 12),
        ],
      );

  void _addSkill() {
    final skill = _value('new_skill');
    if (skill.isEmpty) return;
    if (!customSkills
        .any((item) => item.toLowerCase() == skill.toLowerCase())) {
      setState(() => customSkills.add(skill));
    }
    _field('new_skill').clear();
  }

  Widget _referenceTab() => ListView(
        padding: const EdgeInsets.fromLTRB(18, 22, 18, 34),
        children: [
          const Text('Professional References',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
          const SizedBox(height: 5),
          const Text('Add people employers may contact for verification.',
              style: TextStyle(color: AppColors.muted)),
          const SizedBox(height: 18),
          _EntrySection(
            emptyIcon: Icons.contact_page_outlined,
            emptyText: 'No reference added yet.',
            addLabel: 'Add reference',
            onAdd: () => _openReferenceEditor(),
            children: references
                .map((item) => _ProfileEntryCard(
                      icon: Icons.person_outline,
                      title: '${item['name'] ?? ''}',
                      subtitle:
                          '${item['designation'] ?? ''} • ${item['organization'] ?? ''}',
                      detail: '${item['email'] ?? item['mobile'] ?? ''}',
                      onEdit: () => _openReferenceEditor(item: item),
                      onDelete: () => _deleteProfileEntry('reference', item),
                    ))
                .toList(),
          ),
        ],
      );

  String _experiencePeriod(Map<String, dynamic> item) {
    final end =
        item['currently_working'] == true ? 'Present' : '${item['end'] ?? ''}';
    return '${item['start'] ?? ''} — $end';
  }

  Widget _educationCard(Map<String, dynamic> item) {
    final degree = '${item['degree_name'] ?? ''}'.trim();
    final exam = '${item['exam_name'] ?? ''}'.trim();
    final institute = '${item['institute_name'] ?? ''}'.trim();
    final major = '${item['major_subject'] ?? ''}'.trim();
    final year = '${item['passing_year'] ?? ''}'.trim();
    final result = '${item['result'] ?? ''}'.trim();
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const CircleAvatar(
          backgroundColor: AppColors.purple,
          foregroundColor: Colors.white,
          child: Icon(Icons.school_outlined),
        ),
        const SizedBox(width: 12),
        Expanded(
          child:
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(degree.isNotEmpty ? degree : exam,
                style: const TextStyle(fontWeight: FontWeight.w800)),
            if (institute.isNotEmpty) ...[
              const SizedBox(height: 3),
              Text(institute, style: const TextStyle(color: AppColors.muted)),
            ],
            if (major.isNotEmpty || year.isNotEmpty || result.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                  [major, year, if (result.isNotEmpty) 'Result: $result']
                      .where((value) => value.isNotEmpty)
                      .join(' • '),
                  style: const TextStyle(fontSize: 12, color: AppColors.muted)),
            ],
          ]),
        ),
        PopupMenuButton<String>(
          onSelected: (action) => action == 'edit'
              ? _openEducationEditor(item: item)
              : _deleteEducation(item),
          itemBuilder: (_) => const [
            PopupMenuItem(value: 'edit', child: Text('Edit')),
            PopupMenuItem(value: 'delete', child: Text('Delete')),
          ],
        ),
      ]),
    );
  }

  Future<void> _openEducationEditor({Map<String, dynamic>? item}) async {
    final values = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: AppColors.background,
      builder: (_) => _EducationEditorSheet(initial: item),
    );
    if (values == null || !mounted) return;
    setState(() => savingSection = 'education');
    try {
      final saved = await widget.state.candidateApi
          .saveEducation(values, id: int.tryParse('${item?['id']}'));
      if (!mounted) return;
      setState(() {
        final id = int.tryParse('${saved['id']}');
        final index = academicQualifications
            .indexWhere((entry) => int.tryParse('${entry['id']}') == id);
        if (index < 0) {
          academicQualifications = [saved, ...academicQualifications];
        } else {
          final updated = [...academicQualifications];
          updated[index] = saved;
          academicQualifications = updated;
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(item == null
              ? 'Education added successfully!'
              : 'Education updated successfully!'),
          backgroundColor: const Color(0xff167c62)));
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => savingSection = null);
    }
  }

  Future<void> _openExperienceEditor({Map<String, dynamic>? item}) async {
    final values = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: AppColors.background,
      builder: (_) => _ExperienceEditorSheet(initial: item),
    );
    if (values == null || !mounted) return;
    await _saveProfileEntry('experience', values, item: item);
  }

  Future<void> _openReferenceEditor({Map<String, dynamic>? item}) async {
    final values = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: AppColors.background,
      builder: (_) => _ReferenceEditorSheet(initial: item),
    );
    if (values == null || !mounted) return;
    await _saveProfileEntry('reference', values, item: item);
  }

  Future<void> _saveProfileEntry(String type, Map<String, dynamic> values,
      {Map<String, dynamic>? item}) async {
    setState(() => savingSection = type);
    try {
      final id = int.tryParse('${item?['id']}');
      final saved = type == 'experience'
          ? await widget.state.candidateApi.saveExperience(values, id: id)
          : await widget.state.candidateApi.saveReference(values, id: id);
      if (!mounted) return;
      setState(() {
        final list = type == 'experience' ? experienceEntries : references;
        final savedId = int.tryParse('${saved['id']}');
        final index = list
            .indexWhere((entry) => int.tryParse('${entry['id']}') == savedId);
        final updated = [...list];
        if (index < 0) {
          updated.insert(0, saved);
        } else {
          updated[index] = saved;
        }
        if (type == 'experience') {
          experienceEntries = updated;
        } else {
          references = updated;
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(item == null
              ? '${_titleCase(type)} added successfully!'
              : '${_titleCase(type)} updated successfully!'),
          backgroundColor: const Color(0xff167c62)));
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => savingSection = null);
    }
  }

  Future<void> _deleteProfileEntry(
      String type, Map<String, dynamic> item) async {
    final id = int.tryParse('${item['id']}');
    if (id == null) return;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Delete $type?'),
        content: const Text('This information will be removed permanently.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancel')),
          FilledButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Delete')),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;
    try {
      final message = type == 'experience'
          ? await widget.state.candidateApi.deleteExperience(id)
          : await widget.state.candidateApi.deleteReference(id);
      if (!mounted) return;
      setState(() {
        if (type == 'experience') {
          experienceEntries = experienceEntries
              .where((entry) => int.tryParse('${entry['id']}') != id)
              .toList();
        } else {
          references = references
              .where((entry) => int.tryParse('${entry['id']}') != id)
              .toList();
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(message), backgroundColor: const Color(0xff167c62)));
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    }
  }

  Future<void> _deleteEducation(Map<String, dynamic> item) async {
    final id = int.tryParse('${item['id']}');
    if (id == null) return;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete education?'),
        content: const Text('This qualification will be removed permanently.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancel')),
          FilledButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Delete')),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;
    try {
      final message = await widget.state.candidateApi.deleteEducation(id);
      if (!mounted) return;
      setState(() => academicQualifications = academicQualifications
          .where((entry) => int.tryParse('${entry['id']}') != id)
          .toList());
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(message), backgroundColor: const Color(0xff167c62)));
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    }
  }

  Widget _contactTab() => _FormPage(
        formKey: contactKey,
        children: [
          Row(children: [
            Expanded(
                child: _text('phone', 'Phone',
                    required: true, keyboard: TextInputType.phone)),
            const SizedBox(width: 12),
            Expanded(
                child: _text('secondary_phone', 'Secondary phone',
                    keyboard: TextInputType.phone)),
          ]),
          _text('whatsapp_number', 'WhatsApp number',
              keyboard: TextInputType.phone),
          _text('email', 'Contact email',
              required: true, keyboard: TextInputType.emailAddress),
          _text('secondary_email', 'Secondary email',
              keyboard: TextInputType.emailAddress),
          _saveButton('contact', _saveContact),
        ],
      );

  Widget _socialTab() => ListView(
        padding: const EdgeInsets.fromLTRB(18, 22, 18, 34),
        children: [
          const Text('Social profiles',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
          const SizedBox(height: 6),
          const Text('Add professional links employers can verify.',
              style: TextStyle(color: AppColors.muted)),
          const SizedBox(height: 20),
          ...socials.asMap().entries.map((entry) {
            final index = entry.key;
            final social = entry.value;
            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(15),
                  border: Border.all(color: AppColors.border)),
              child: Column(children: [
                Row(children: [
                  Expanded(
                      child: DropdownButtonFormField<String>(
                    initialValue: social.platform,
                    decoration: const InputDecoration(labelText: 'Platform'),
                    items: socialPlatforms
                        .map((value) => DropdownMenuItem(
                            value: value, child: Text(_titleCase(value))))
                        .toList(),
                    onChanged: (value) => social.platform = value ?? 'other',
                  )),
                  IconButton(
                      onPressed: () => setState(() {
                            socials.removeAt(index).dispose();
                          }),
                      icon: const Icon(Icons.delete_outline,
                          color: AppColors.danger)),
                ]),
                const SizedBox(height: 10),
                TextField(
                    controller: social.url,
                    keyboardType: TextInputType.url,
                    decoration: const InputDecoration(
                        labelText: 'Profile URL',
                        prefixIcon: Icon(Icons.link))),
              ]),
            );
          }),
          OutlinedButton.icon(
              onPressed: () => setState(() =>
                  socials.add(_SocialEntry(platform: 'linkedin', url: ''))),
              icon: const Icon(Icons.add),
              label: const Text('Add social link')),
          const SizedBox(height: 18),
          _saveButton('social', _saveSocial),
        ],
      );

  Widget _completionCard() {
    final remaining = int.tryParse(
            '${widget.state.dashboard['profileComplated'] ?? widget.state.currentUser['profile_complete'] ?? 100}') ??
        100;
    final completion = (100 - remaining).clamp(0, 100);
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border)),
      child: Row(children: [
        SizedBox(
            width: 56,
            height: 56,
            child: Stack(alignment: Alignment.center, children: [
              CircularProgressIndicator(
                  value: completion / 100,
                  strokeWidth: 6,
                  backgroundColor: AppColors.border,
                  color: AppColors.purple),
              Text('$completion%',
                  style: const TextStyle(
                      fontSize: 11, fontWeight: FontWeight.w800)),
            ])),
        const SizedBox(width: 14),
        const Expanded(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Profile completion',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
          SizedBox(height: 3),
          Text('Complete every section to improve job matching.',
              style: TextStyle(fontSize: 12, color: AppColors.muted)),
        ])),
      ]),
    );
  }

  Widget _photoPicker() {
    final currentPhoto = '${widget.state.currentUser['photo_url'] ?? ''}';
    ImageProvider<Object>? image;
    if (selectedPhotoPath != null) {
      image = FileImage(File(selectedPhotoPath!));
    } else if (currentPhoto.startsWith('http')) {
      image = NetworkImage(currentPhoto);
    }
    return Container(
      margin: const EdgeInsets.only(top: 16, bottom: 18),
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border)),
      child: Row(children: [
        CircleAvatar(
            radius: 35,
            backgroundColor: AppColors.purple,
            backgroundImage: image,
            child: image == null
                ? const Icon(Icons.person, size: 34, color: Colors.white)
                : null),
        const SizedBox(width: 15),
        const Expanded(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Profile photo',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
          SizedBox(height: 3),
          Text('JPG or PNG, professional square photo recommended.',
              style: TextStyle(fontSize: 12, color: AppColors.muted)),
        ])),
        TextButton.icon(
            onPressed: _pickPhoto,
            icon: const Icon(Icons.photo_library_outlined),
            label: const Text('Choose')),
      ]),
    );
  }

  Widget _text(String key, String label,
          {bool required = false,
          int lines = 1,
          TextInputType? keyboard,
          String? hint}) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: TextFormField(
          controller: _field(key),
          keyboardType: keyboard,
          minLines: lines,
          maxLines: lines,
          decoration: InputDecoration(labelText: label, hintText: hint),
          validator: required
              ? (value) => value == null || value.trim().isEmpty
                  ? '$label is required'
                  : null
              : null,
        ),
      );

  Widget _date(String key, String label,
          {bool required = false, bool futureOnly = false}) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: TextFormField(
          controller: _field(key),
          readOnly: true,
          decoration: InputDecoration(
              labelText: label,
              suffixIcon: const Icon(Icons.calendar_month_outlined)),
          onTap: () => _pickDate(key, futureOnly: futureOnly),
          validator: required
              ? (value) =>
                  value == null || value.isEmpty ? '$label is required' : null
              : null,
        ),
      );

  Widget _dropdown<T>({
    required String label,
    required T? value,
    required List<Map<String, dynamic>> options,
    required ValueChanged<T?> onChanged,
    bool required = true,
  }) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: DropdownButtonFormField<T>(
          key: ValueKey('$label-$value-${options.length}'),
          initialValue: value,
          isExpanded: true,
          decoration: InputDecoration(labelText: label),
          items: options.map((item) {
            final raw = item['id'];
            final optionValue =
                T == int ? (int.tryParse('$raw') as T?) : ('$raw' as T);
            return DropdownMenuItem<T>(
                value: optionValue,
                child: Text('${item['name'] ?? ''}',
                    maxLines: 1, overflow: TextOverflow.ellipsis));
          }).toList(),
          onChanged: onChanged,
          validator: required
              ? (value) => value == null ? '$label is required' : null
              : null,
        ),
      );

  Widget _saveButton(String section, Future<void> Function() onSave) =>
      SizedBox(
        width: double.infinity,
        child: FilledButton.icon(
          style: FilledButton.styleFrom(
              backgroundColor: AppColors.red,
              foregroundColor: AppColors.onSecondary,
              padding: const EdgeInsets.all(15)),
          onPressed: savingSection == null ? onSave : null,
          icon: savingSection == section
              ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                      strokeWidth: 2, color: Colors.white))
              : const Icon(Icons.save_outlined),
          label: Text(savingSection == section ? 'Saving...' : 'Save changes'),
        ),
      );

  Future<void> _savePersonal() async {
    if (!(personalKey.currentState?.validate() ?? false)) return;
    final values = <String, dynamic>{
      'name': _value('name'),
      'nid_birth_registration_no': _value('nid_birth_registration_no'),
      'passport_no': _value('passport_no'),
      'passport_expiry_date': _value('passport_expiry_date'),
      'nationality': _value('nationality'),
      'date_of_birth': _value('date_of_birth'),
      'neighborhood': _value('address'),
      'permanent_address': _value('permanent_address'),
    };
    if (selectedPhotoPath == null) {
      await _save('personal', values);
      return;
    }
    setState(() => savingSection = 'personal');
    try {
      final message = await widget.state.candidateApi
          .updatePersonalWithPhoto(values, selectedPhotoPath!);
      selectedPhotoPath = null;
      await widget.state.loadProfile();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(message), backgroundColor: const Color(0xff167c62)));
        setState(() {});
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => savingSection = null);
    }
  }

  Future<void> _saveProfessional() async {
    if (!(professionalKey.currentState?.validate() ?? false) ||
        educationId == null ||
        (professionId == null && _value('custom_profession').isEmpty)) {
      return;
    }
    await _save('profile', {
      'gender': gender,
      'marital_status': maritalStatus,
      'profession': _value('custom_profession').isNotEmpty
          ? _value('custom_profession')
          : professionId,
      'education_id': educationId,
      'bio': _value('bio'),
      'status': availability,
      'available_in':
          availability == 'available_in' ? _value('available_in') : null,
      'skills': customSkills,
      'languages': selectedLanguages.toList(),
      'language_proficiencies': {
        for (final id in selectedLanguages)
          '$id': languageProficiencies[int.tryParse('$id') ?? 0] ?? 'basic',
      },
      'preferred_job_locations': _value('preferred_job_locations')
          .split(',')
          .map((value) => value.trim())
          .where((value) => value.isNotEmpty)
          .toSet()
          .toList(),
    });
  }

  Future<void> _saveContact() async {
    if (!(contactKey.currentState?.validate() ?? false)) return;
    await _save('contact', {
      'phone': _value('phone'),
      'secondary_phone': _value('secondary_phone'),
      'whatsapp_number': _value('whatsapp_number'),
      'email': _value('email'),
      'secondary_email': _value('secondary_email'),
    });
  }

  Future<void> _saveSocial() async => _save('social', {
        'social_media': socials.map((item) => item.platform).toList(),
        'url': socials.map((item) => item.url.text.trim()).toList(),
      });

  Future<void> _save(String section, Map<String, dynamic> values) async {
    setState(() => savingSection = section);
    try {
      final message =
          await widget.state.candidateApi.updateSettings(section, values);
      await widget.state.loadProfile();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(message), backgroundColor: const Color(0xff167c62)));
        setState(() {});
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text('$e')));
      }
    } finally {
      if (mounted) setState(() => savingSection = null);
    }
  }

  Future<void> _pickDate(String key, {bool futureOnly = false}) async {
    final current = DateTime.tryParse(_value(key));
    final today = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: current ??
          (futureOnly ? today.add(const Duration(days: 365)) : DateTime(2000)),
      firstDate: futureOnly ? today : DateTime(1940),
      lastDate: futureOnly ? DateTime(today.year + 20, 12, 31) : today,
    );
    if (picked != null) {
      _field(key).text =
          '${picked.year.toString().padLeft(4, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
    }
  }

  Future<void> _pickPhoto() async {
    final picked = await ImagePicker().pickImage(
      source: ImageSource.gallery,
      imageQuality: 85,
      maxWidth: 1200,
      maxHeight: 1200,
    );
    if (picked != null && mounted) {
      setState(() => selectedPhotoPath = picked.path);
    }
  }

  String _value(String key) => _field(key).text.trim();
  List<Map<String, dynamic>> _mapList(dynamic value) => (value as List? ?? [])
      .whereType<Map>()
      .map((item) => item.cast<String, dynamic>())
      .toList();
  Map<String, dynamic> _map(dynamic value) =>
      (value as Map? ?? {}).cast<String, dynamic>();
  List<String> _stringList(dynamic value) => (value as List? ?? const [])
      .map((item) => '$item'.trim())
      .where((item) => item.isNotEmpty)
      .toList();
  int? _validId(dynamic value, List<Map<String, dynamic>> list) {
    final id = int.tryParse('$value');
    return list.any((item) => int.tryParse('${item['id']}') == id) ? id : null;
  }

  String? _emptyToNull(dynamic value) =>
      value == null || '$value'.isEmpty ? null : '$value';
  static String _socialValue(dynamic value) {
    final normalized = '${value ?? ''}'.trim().toLowerCase();
    return normalized.isEmpty ? 'other' : normalized;
  }

  static String _titleCase(String value) =>
      value.isEmpty ? value : '${value[0].toUpperCase()}${value.substring(1)}';
}

class _EntrySection extends StatelessWidget {
  const _EntrySection({
    required this.emptyIcon,
    required this.emptyText,
    required this.addLabel,
    required this.onAdd,
    required this.children,
  });
  final IconData emptyIcon;
  final String emptyText;
  final String addLabel;
  final VoidCallback onAdd;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Align(
            alignment: Alignment.centerRight,
            child: FilledButton.icon(
                onPressed: onAdd,
                icon: const Icon(Icons.add, size: 18),
                label: Text(addLabel)),
          ),
          const SizedBox(height: 10),
          if (children.isEmpty)
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(children: [
                Icon(emptyIcon, size: 34, color: AppColors.muted),
                const SizedBox(height: 8),
                Text(emptyText, style: const TextStyle(color: AppColors.muted)),
              ]),
            )
          else
            ...children,
          const SizedBox(height: 10),
        ],
      );
}

class _ProfileEntryCard extends StatelessWidget {
  const _ProfileEntryCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.detail,
    required this.onEdit,
    required this.onDelete,
  });
  final IconData icon;
  final String title;
  final String subtitle;
  final String detail;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.border),
        ),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          CircleAvatar(
              backgroundColor: AppColors.purple,
              foregroundColor: Colors.white,
              child: Icon(icon)),
          const SizedBox(width: 12),
          Expanded(
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.w800)),
              if (subtitle.trim().isNotEmpty)
                Text(subtitle, style: const TextStyle(color: AppColors.muted)),
              if (detail.trim().isNotEmpty) ...[
                const SizedBox(height: 4),
                Text(detail,
                    style:
                        const TextStyle(fontSize: 12, color: AppColors.muted)),
              ],
            ]),
          ),
          PopupMenuButton<String>(
            onSelected: (value) => value == 'edit' ? onEdit() : onDelete(),
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'edit', child: Text('Edit')),
              PopupMenuItem(value: 'delete', child: Text('Delete')),
            ],
          ),
        ]),
      );
}

class _ExperienceEditorSheet extends StatefulWidget {
  const _ExperienceEditorSheet({this.initial});
  final Map<String, dynamic>? initial;
  @override
  State<_ExperienceEditorSheet> createState() => _ExperienceEditorSheetState();
}

class _ExperienceEditorSheetState extends State<_ExperienceEditorSheet> {
  final formKey = GlobalKey<FormState>();
  final fields = <String, TextEditingController>{};
  bool currentlyWorking = false;

  TextEditingController field(String key) => fields.putIfAbsent(
      key, () => TextEditingController(text: '${widget.initial?[key] ?? ''}'));

  @override
  void initState() {
    super.initState();
    currentlyWorking = widget.initial?['currently_working'] == true ||
        '${widget.initial?['currently_working']}' == '1';
  }

  @override
  void dispose() {
    for (final controller in fields.values) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => _EditorFrame(
        title: widget.initial == null ? 'Add Experience' : 'Edit Experience',
        formKey: formKey,
        children: [
          _editorField(field('designation'), 'Position', required: true),
          _editorField(field('company'), 'Working Organization',
              required: true),
          _editorField(field('department'), 'Department'),
          Row(children: [
            Expanded(child: _dateField('start', 'Start date', required: true)),
            const SizedBox(width: 10),
            Expanded(
                child:
                    _dateField('end', 'End date', enabled: !currentlyWorking)),
          ]),
          CheckboxListTile(
            contentPadding: EdgeInsets.zero,
            value: currentlyWorking,
            title: const Text('Currently working here'),
            onChanged: (value) =>
                setState(() => currentlyWorking = value ?? false),
          ),
          _editorField(field('supervisor'), 'Supervisor'),
          _editorField(field('hr_contact_number'), 'HR Contact Number',
              keyboard: TextInputType.phone),
          _editorField(field('responsibilities'), 'Professional Summary',
              lines: 4),
          FilledButton.icon(
              onPressed: submit,
              icon: const Icon(Icons.save_outlined),
              label: const Text('Save experience')),
        ],
      );

  Widget _dateField(String key, String label,
          {bool required = false, bool enabled = true}) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: TextFormField(
          controller: field(key),
          enabled: enabled,
          readOnly: true,
          decoration: InputDecoration(
              labelText: label, suffixIcon: const Icon(Icons.calendar_month)),
          validator: required
              ? (value) =>
                  value == null || value.isEmpty ? '$label is required' : null
              : null,
          onTap: () async {
            final today = DateTime.now();
            final picked = await showDatePicker(
              context: context,
              initialDate: DateTime.tryParse(field(key).text) ?? today,
              firstDate: DateTime(1950),
              lastDate: DateTime(today.year + 5),
            );
            if (picked != null) field(key).text = _isoDate(picked);
          },
        ),
      );

  void submit() {
    if (!(formKey.currentState?.validate() ?? false)) return;
    Navigator.pop(context, {
      for (final key in [
        'designation',
        'company',
        'department',
        'start',
        'end',
        'supervisor',
        'hr_contact_number',
        'responsibilities'
      ])
        key: field(key).text.trim(),
      'currently_working': currentlyWorking,
      if (currentlyWorking) 'end': null,
    });
  }
}

class _ReferenceEditorSheet extends StatefulWidget {
  const _ReferenceEditorSheet({this.initial});
  final Map<String, dynamic>? initial;
  @override
  State<_ReferenceEditorSheet> createState() => _ReferenceEditorSheetState();
}

class _ReferenceEditorSheetState extends State<_ReferenceEditorSheet> {
  final formKey = GlobalKey<FormState>();
  final fields = <String, TextEditingController>{};
  TextEditingController field(String key) => fields.putIfAbsent(
      key, () => TextEditingController(text: '${widget.initial?[key] ?? ''}'));
  @override
  void dispose() {
    for (final controller in fields.values) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => _EditorFrame(
        title: widget.initial == null ? 'Add Reference' : 'Edit Reference',
        formKey: formKey,
        children: [
          _editorField(field('name'), 'Name', required: true),
          _editorField(field('designation'), 'Position', required: true),
          _editorField(field('organization'), 'Organization', required: true),
          _editorField(field('email'), 'Email',
              keyboard: TextInputType.emailAddress,
              validator: (value) =>
                  value != null && value.isNotEmpty && !value.contains('@')
                      ? 'Enter a valid email'
                      : null),
          _editorField(field('mobile'), 'Phone', keyboard: TextInputType.phone),
          FilledButton.icon(
              onPressed: submit,
              icon: const Icon(Icons.save_outlined),
              label: const Text('Save reference')),
        ],
      );
  void submit() {
    if (!(formKey.currentState?.validate() ?? false)) return;
    Navigator.pop(context, {
      for (final key in [
        'name',
        'designation',
        'organization',
        'email',
        'mobile'
      ])
        key: field(key).text.trim(),
    });
  }
}

class _EditorFrame extends StatelessWidget {
  const _EditorFrame(
      {required this.title, required this.formKey, required this.children});
  final String title;
  final GlobalKey<FormState> formKey;
  final List<Widget> children;
  @override
  Widget build(BuildContext context) => Padding(
        padding: EdgeInsets.fromLTRB(
            18, 18, 18, MediaQuery.viewInsetsOf(context).bottom + 24),
        child: Form(
          key: formKey,
          child: SingleChildScrollView(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(children: [
                    Expanded(
                        child: Text(title,
                            style: const TextStyle(
                                fontSize: 20, fontWeight: FontWeight.w800))),
                    IconButton(
                        onPressed: () => Navigator.pop(context),
                        icon: const Icon(Icons.close)),
                  ]),
                  const SizedBox(height: 14),
                  ...children,
                ]),
          ),
        ),
      );
}

Widget _editorField(TextEditingController controller, String label,
        {bool required = false,
        int lines = 1,
        TextInputType? keyboard,
        String? Function(String?)? validator}) =>
    Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextFormField(
        controller: controller,
        minLines: lines,
        maxLines: lines,
        keyboardType: keyboard,
        decoration: InputDecoration(labelText: label),
        validator: validator ??
            (required
                ? (value) => value == null || value.trim().isEmpty
                    ? '$label is required'
                    : null
                : null),
      ),
    );

String _isoDate(DateTime date) =>
    '${date.year.toString().padLeft(4, '0')}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';

class _EducationEditorSheet extends StatefulWidget {
  const _EducationEditorSheet({this.initial});
  final Map<String, dynamic>? initial;

  @override
  State<_EducationEditorSheet> createState() => _EducationEditorSheetState();
}

class _EducationEditorSheetState extends State<_EducationEditorSheet> {
  final formKey = GlobalKey<FormState>();
  final controllers = <String, TextEditingController>{};
  String resultType = 'cgpa_4';

  static const resultTypes = [
    {'id': 'gpa_5', 'name': 'GPA (out of 5)'},
    {'id': 'cgpa_4', 'name': 'CGPA (out of 4)'},
    {'id': 'percentage', 'name': 'Percentage'},
    {'id': 'division', 'name': 'Division / Class'},
    {'id': 'other', 'name': 'Other'},
  ];

  TextEditingController field(String key) => controllers.putIfAbsent(
      key, () => TextEditingController(text: '${widget.initial?[key] ?? ''}'));

  @override
  void initState() {
    super.initState();
    final existing = '${widget.initial?['result_type'] ?? ''}';
    if (resultTypes.any((item) => item['id'] == existing)) {
      resultType = existing;
    }
  }

  @override
  void dispose() {
    for (final controller in controllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Padding(
        padding: EdgeInsets.fromLTRB(
            18, 18, 18, MediaQuery.viewInsetsOf(context).bottom + 24),
        child: Form(
          key: formKey,
          child: SingleChildScrollView(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(children: [
                    Expanded(
                      child: Text(
                        widget.initial == null
                            ? 'Add Education Qualification'
                            : 'Edit Education Qualification',
                        style: const TextStyle(
                            fontSize: 20, fontWeight: FontWeight.w800),
                      ),
                    ),
                    IconButton(
                        onPressed: () => Navigator.pop(context),
                        icon: const Icon(Icons.close)),
                  ]),
                  const SizedBox(height: 14),
                  input('exam_name', 'Degree / Qualification', required: true),
                  input('degree_name', 'Degree name'),
                  input('institute_name', 'Institution', required: true),
                  input('major_subject', 'Subject / Major'),
                  input('passing_year', 'Passing year',
                      keyboard: TextInputType.number, validator: (value) {
                    if (value == null || value.trim().isEmpty) return null;
                    final year = int.tryParse(value);
                    if (year == null ||
                        value.length != 4 ||
                        year < 1940 ||
                        year > DateTime.now().year + 10) {
                      return 'Enter a valid passing year';
                    }
                    return null;
                  }),
                  Padding(
                    padding: const EdgeInsets.only(bottom: 14),
                    child: DropdownButtonFormField<String>(
                      initialValue: resultType,
                      decoration:
                          const InputDecoration(labelText: 'Result type'),
                      items: resultTypes
                          .map((item) => DropdownMenuItem(
                              value: item['id'], child: Text(item['name']!)))
                          .toList(),
                      onChanged: (value) => setState(() => resultType = value!),
                    ),
                  ),
                  input('result', resultLabel,
                      keyboard:
                          const TextInputType.numberWithOptions(decimal: true),
                      validator: validateResult),
                  input('board', 'Board / Awarding body'),
                  const SizedBox(height: 6),
                  FilledButton.icon(
                    onPressed: submit,
                    icon: const Icon(Icons.save_outlined),
                    label: Text(widget.initial == null
                        ? 'Add qualification'
                        : 'Save changes'),
                  ),
                ]),
          ),
        ),
      );

  Widget input(String key, String label,
          {bool required = false,
          TextInputType? keyboard,
          String? Function(String?)? validator}) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: TextFormField(
          controller: field(key),
          keyboardType: keyboard,
          decoration: InputDecoration(labelText: label),
          validator: validator ??
              (required
                  ? (value) => value == null || value.trim().isEmpty
                      ? '$label is required'
                      : null
                  : null),
        ),
      );

  String get resultLabel => switch (resultType) {
        'gpa_5' => 'Result / GPA (maximum 5)',
        'cgpa_4' => 'Result / CGPA (maximum 4)',
        'percentage' => 'Result / Percentage (maximum 100)',
        _ => 'Result',
      };

  String? validateResult(String? value) {
    if (value == null || value.trim().isEmpty) return null;
    final result = double.tryParse(value);
    if (result == null || result < 0) return 'Enter a valid result';
    final maximum = switch (resultType) {
      'gpa_5' => 5.0,
      'cgpa_4' => 4.0,
      'percentage' => 100.0,
      _ => null,
    };
    if (maximum != null && result > maximum) {
      return 'Result cannot exceed ${maximum.toInt()}';
    }
    return null;
  }

  void submit() {
    if (!(formKey.currentState?.validate() ?? false)) return;
    Navigator.pop(context, {
      'exam_name': field('exam_name').text.trim(),
      'degree_name': field('degree_name').text.trim(),
      'institute_name': field('institute_name').text.trim(),
      'major_subject': field('major_subject').text.trim(),
      'passing_year': field('passing_year').text.trim(),
      'result_type': resultType,
      'result': field('result').text.trim(),
      'board': field('board').text.trim(),
    });
  }
}

class _FormPage extends StatelessWidget {
  const _FormPage({required this.formKey, required this.children});
  final GlobalKey<FormState> formKey;
  final List<Widget> children;
  @override
  Widget build(BuildContext context) => Form(
        key: formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(18, 22, 18, 34),
          children: children
              .expand((child) => [child, const SizedBox(height: 2)])
              .toList(),
        ),
      );
}

class _GroupTitle extends StatelessWidget {
  const _GroupTitle(this.text);
  final String text;
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(top: 8, bottom: 12),
        child: Text(text,
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
      );
}

class _LoadError extends StatelessWidget {
  const _LoadError({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;
  @override
  Widget build(BuildContext context) => Center(
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(Icons.error_outline, size: 48, color: AppColors.danger),
            const SizedBox(height: 12),
            Text(message, textAlign: TextAlign.center),
            const SizedBox(height: 16),
            OutlinedButton.icon(
                onPressed: onRetry,
                icon: const Icon(Icons.refresh),
                label: const Text('Try again')),
          ]),
        ),
      );
}

class _SocialEntry {
  _SocialEntry({required this.platform, required String url})
      : url = TextEditingController(text: url);
  String platform;
  final TextEditingController url;
  void dispose() => url.dispose();
}
