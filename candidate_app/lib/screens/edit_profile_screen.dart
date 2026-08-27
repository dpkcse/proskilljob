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
  List<Map<String, dynamic>> experiences = const [];
  List<Map<String, dynamic>> educations = const [];
  List<Map<String, dynamic>> professions = const [];
  List<Map<String, dynamic>> skills = const [];
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
  final selectedSkills = <dynamic>{};
  final selectedLanguages = <dynamic>{};
  int? experienceId;
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
      'title',
      'website',
      'nationality',
      'date_of_birth',
      'district',
      'place',
      'address',
      'postcode',
      'permanent_address',
      'international_address',
    ]) {
      _field(key).text = '${data[key] ?? ''}';
    }
    experiences = _mapList(data['experience_list']);
    educations = _mapList(data['education_list']);
    experienceId = _validId(data['experience_id'], experiences);
    educationId = _validId(data['education_id'], educations);
  }

  void _fillProfessional(Map<String, dynamic> data) {
    gender = _emptyToNull(data['gender']);
    maritalStatus = _emptyToNull(data['marital_status']);
    availability = '${data['availability'] ?? 'available'}';
    _field('bio').text = '${data['bio'] ?? ''}';
    _field('available_in').text = '${data['available_in'] ?? ''}';
    professions = _mapList(data['profession_list']);
    skills = _mapList(data['skill_list']);
    languages = _mapList(data['language_list']);
    professionId = _validId(data['profession_id'], professions);
    selectedSkills
      ..clear()
      ..addAll(_mapList(data['skills']).map((item) => item['id']));
    selectedLanguages
      ..clear()
      ..addAll(_mapList(data['languages']).map((item) => item['id']));
  }

  void _fillContact(Map<String, dynamic> data) {
    final contact = _map(data['contact_info']);
    final location = _map(data['location']);
    _field('phone').text = '${contact['phone'] ?? ''}';
    _field('secondary_phone').text = '${contact['secondary_phone'] ?? ''}';
    _field('whatsapp_number').text = '${contact['whatsapp_no'] ?? ''}';
    _field('email').text =
        '${contact['email'] ?? widget.state.currentUser['email'] ?? ''}';
    _field('secondary_email').text = '${contact['secondary_email'] ?? ''}';
    for (final key in ['country', 'city', 'address', 'exact_location']) {
      _field('contact_$key').text = '${location[key] ?? ''}';
    }
  }

  void _fillSocial(Map<String, dynamic> data) {
    for (final item in socials) {
      item.dispose();
    }
    socials
      ..clear()
      ..addAll(_mapList(data['social_media']).map((item) => _SocialEntry(
            platform: '${item['social_media'] ?? 'linkedin'}',
            url: '${item['url'] ?? ''}',
          )));
    final values = data['social_media_list'];
    if (values is List && values.isNotEmpty) {
      socialPlatforms = values.map((item) => '$item').toList();
    } else if (values is Map && values.isNotEmpty) {
      socialPlatforms = values.values.map((item) => '$item').toList();
    }
  }

  @override
  Widget build(BuildContext context) => DefaultTabController(
        length: 4,
        child: Scaffold(
          appBar: AppBar(
            title: const Text('Edit Profile'),
            bottom: const TabBar(
              isScrollable: true,
              tabs: [
                Tab(text: 'Personal'),
                Tab(text: 'Professional'),
                Tab(text: 'Contact'),
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
          _text('title', 'Professional title', required: true),
          Row(children: [
            Expanded(
                child: _dropdown<int>(
                    label: 'Experience level',
                    value: experienceId,
                    options: experiences,
                    onChanged: (value) => experienceId = value)),
            const SizedBox(width: 12),
            Expanded(
                child: _dropdown<int>(
                    label: 'Education level',
                    value: educationId,
                    options: educations,
                    onChanged: (value) => educationId = value)),
          ]),
          _date('date_of_birth', 'Date of birth', required: true),
          _text('website', 'Personal website', keyboard: TextInputType.url),
          _text('nationality', 'Nationality'),
          const _GroupTitle('Present address'),
          Row(children: [
            Expanded(child: _text('district', 'District')),
            const SizedBox(width: 12),
            Expanded(child: _text('place', 'Thana/Area')),
          ]),
          _text('address', 'House, road or village'),
          _text('postcode', 'Post code'),
          _text('permanent_address', 'Permanent address', lines: 2),
          _text('international_address', 'International address', lines: 2),
          _saveButton('personal', _savePersonal),
        ],
      );

  Widget _professionalTab() => _FormPage(
        formKey: professionalKey,
        children: [
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
          _dropdown<int>(
              label: 'Profession',
              value: professionId,
              options: professions,
              onChanged: (value) => professionId = value),
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
          _multiSelect('Skills', skills, selectedSkills),
          _multiSelect('Languages', languages, selectedLanguages),
          _saveButton('profile', _saveProfessional),
        ],
      );

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
          const _GroupTitle('Location'),
          Row(children: [
            Expanded(child: _text('contact_country', 'Country')),
            const SizedBox(width: 12),
            Expanded(child: _text('contact_city', 'City')),
          ]),
          _text('contact_address', 'Address', lines: 2),
          _text('contact_exact_location', 'Exact location'),
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
                          color: AppColors.red)),
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
          {bool required = false, int lines = 1, TextInputType? keyboard}) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: TextFormField(
          controller: _field(key),
          keyboardType: keyboard,
          minLines: lines,
          maxLines: lines,
          decoration: InputDecoration(labelText: label),
          validator: required
              ? (value) => value == null || value.trim().isEmpty
                  ? '$label is required'
                  : null
              : null,
        ),
      );

  Widget _date(String key, String label, {bool required = false}) => Padding(
        padding: const EdgeInsets.only(bottom: 14),
        child: TextFormField(
          controller: _field(key),
          readOnly: true,
          decoration: InputDecoration(
              labelText: label,
              suffixIcon: const Icon(Icons.calendar_month_outlined)),
          onTap: () => _pickDate(key),
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

  Widget _multiSelect(String title, List<Map<String, dynamic>> options,
          Set<dynamic> selected) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 18),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title,
              style:
                  const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
          const SizedBox(height: 9),
          Container(
            constraints: const BoxConstraints(maxHeight: 210),
            child: SingleChildScrollView(
              child: Wrap(
                  spacing: 7,
                  runSpacing: 7,
                  children: options.map((item) {
                    final id = item['id'];
                    return FilterChip(
                        label: Text('${item['name'] ?? ''}'),
                        selected: selected.contains(id),
                        onSelected: (value) => setState(() =>
                            value ? selected.add(id) : selected.remove(id)));
                  }).toList()),
            ),
          ),
        ]),
      );

  Widget _saveButton(String section, Future<void> Function() onSave) =>
      SizedBox(
        width: double.infinity,
        child: FilledButton.icon(
          style: FilledButton.styleFrom(
              backgroundColor: AppColors.red,
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
    if (!(personalKey.currentState?.validate() ?? false) ||
        experienceId == null ||
        educationId == null) {
      return;
    }
    final values = <String, dynamic>{
      'name': _value('name'),
      'title': _value('title'),
      'experience_id': experienceId,
      'education_id': educationId,
      'website': _value('website'),
      'nationality': _value('nationality'),
      'date_of_birth': _value('date_of_birth'),
      'district': _value('district'),
      'place': _value('place'),
      'neighborhood': _value('address'),
      'postcode': _value('postcode'),
      'permanent_address': _value('permanent_address'),
      'international_address': _value('international_address'),
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
        professionId == null) {
      return;
    }
    await _save('profile', {
      'gender': gender,
      'marital_status': maritalStatus,
      'profession': professionId,
      'bio': _value('bio'),
      'status': availability,
      'available_in':
          availability == 'available_in' ? _value('available_in') : null,
      'skills': selectedSkills.toList(),
      'languages': selectedLanguages.toList(),
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
      'country': _value('contact_country'),
      'city': _value('contact_city'),
      'address': _value('contact_address'),
      'exact_location': _value('contact_exact_location'),
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

  Future<void> _pickDate(String key) async {
    final current = DateTime.tryParse(_value(key));
    final picked = await showDatePicker(
      context: context,
      initialDate: current ?? DateTime(2000),
      firstDate: DateTime(1940),
      lastDate: DateTime.now().add(const Duration(days: 3650)),
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
  int? _validId(dynamic value, List<Map<String, dynamic>> list) {
    final id = int.tryParse('$value');
    return list.any((item) => int.tryParse('${item['id']}') == id) ? id : null;
  }

  String? _emptyToNull(dynamic value) =>
      value == null || '$value'.isEmpty ? null : '$value';
  static String _titleCase(String value) =>
      value.isEmpty ? value : '${value[0].toUpperCase()}${value.substring(1)}';
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
            const Icon(Icons.error_outline, size: 48, color: AppColors.red),
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
