import 'package:flutter/material.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key, required this.state});
  final AppState state;
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final formKey = GlobalKey<FormState>();
  bool formVisible = false;
  final name = TextEditingController();
  final email = TextEditingController();
  final password = TextEditingController();
  final confirmPassword = TextEditingController();
  bool busy = false;
  bool acceptedTerms = false;
  bool hidePassword = true;
  String? error;

  @override
  void dispose() {
    name.dispose();
    email.dispose();
    password.dispose();
    confirmPassword.dispose();
    super.dispose();
  }

  Future<void> submit() async {
    FocusScope.of(context).unfocus();
    if (!(formKey.currentState?.validate() ?? false)) return;
    if (!acceptedTerms) {
      setState(() => error = 'Please accept the Terms and Privacy Policy.');
      return;
    }
    setState(() {
      busy = true;
      error = null;
    });
    try {
      await widget.state.auth
          .register(name.text.trim(), email.text.trim(), password.text);
      if (!mounted) return;
      Navigator.pushReplacement(
          context,
          MaterialPageRoute(
              builder: (_) => LoginScreen(
                    state: widget.state,
                    initialEmail: email.text.trim(),
                    notice:
                        'Your account was created successfully. Please log in to continue.',
                  )));
    } catch (e) {
      if (mounted) setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('Create Account'), centerTitle: true),
        body: SafeArea(
            child: AnimatedSwitcher(
                duration: const Duration(milliseconds: 250),
                child: formVisible ? _form() : _roleSelection())),
      );

  Widget _progress(int active) => Padding(
      padding: const EdgeInsets.symmetric(vertical: 24),
      child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(
              4,
              (i) => Row(children: [
                    Container(
                        width: i == active ? 18 : 14,
                        height: i == active ? 18 : 14,
                        decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: i <= active
                                ? AppColors.purple
                                : const Color(0xff202643),
                            border: i == active
                                ? Border.all(
                                    color: const Color(0xffb896ff), width: 4)
                                : null)),
                    if (i < 3)
                      Container(
                          width: 48,
                          height: 2,
                          color: i < active
                              ? AppColors.purple
                              : const Color(0xff202643)),
                  ]))));

  Widget _roleSelection() => ListView(
          key: const ValueKey('roles'),
          padding: const EdgeInsets.fromLTRB(24, 0, 24, 26),
          children: [
            _progress(0),
            const SizedBox(height: 22),
            const Text('Join as',
                style: TextStyle(fontSize: 42, fontWeight: FontWeight.w800)),
            const SizedBox(height: 8),
            const Text('Choose an option that best\ndescribes you',
                style: TextStyle(
                    fontSize: 20, color: AppColors.muted, height: 1.4)),
            const SizedBox(height: 36),
            _RoleCard(
                icon: Icons.person,
                color: AppColors.purple,
                title: "I'm a Job Seeker",
                subtitle: 'Find jobs, apply and grow\nyour career',
                onTap: () => setState(() => formVisible = true)),
            const SizedBox(height: 42),
            const Center(
                child: Text('It only takes a minute',
                    style: TextStyle(color: AppColors.muted))),
          ]);

  Widget _form() => Form(
      key: formKey,
      child: ListView(
          key: const ValueKey('form'),
          keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
          padding: const EdgeInsets.fromLTRB(24, 0, 24, 30),
          children: [
            _progress(1),
            const SizedBox(height: 16),
            const Text('Create your profile',
                style: TextStyle(fontSize: 32, fontWeight: FontWeight.w800)),
            const SizedBox(height: 8),
            const Text('Start your career journey with ProSkill Job',
                style: TextStyle(color: AppColors.muted)),
            const SizedBox(height: 28),
            TextFormField(
                controller: name,
                textInputAction: TextInputAction.next,
                autofillHints: const [AutofillHints.name],
                validator: (value) {
                  if (value == null || value.trim().length < 2) {
                    return 'Enter your full name.';
                  }
                  return null;
                },
                decoration: const InputDecoration(
                    labelText: 'Full name',
                    prefixIcon: Icon(Icons.person_outline))),
            const SizedBox(height: 14),
            TextFormField(
                controller: email,
                keyboardType: TextInputType.emailAddress,
                textInputAction: TextInputAction.next,
                autofillHints: const [AutofillHints.email],
                validator: (value) {
                  final emailValue = value?.trim() ?? '';
                  if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$')
                      .hasMatch(emailValue)) {
                    return 'Enter a valid email address.';
                  }
                  return null;
                },
                decoration: const InputDecoration(
                    labelText: 'Email',
                    prefixIcon: Icon(Icons.email_outlined))),
            const SizedBox(height: 14),
            TextFormField(
                controller: password,
                obscureText: hidePassword,
                textInputAction: TextInputAction.next,
                autofillHints: const [AutofillHints.newPassword],
                validator: (value) {
                  if ((value ?? '').length < 8) {
                    return 'Password must be at least 8 characters.';
                  }
                  return null;
                },
                decoration: InputDecoration(
                    labelText: 'Password (minimum 8 characters)',
                    prefixIcon: const Icon(Icons.lock_outline),
                    suffixIcon: IconButton(
                        onPressed: () =>
                            setState(() => hidePassword = !hidePassword),
                        icon: Icon(hidePassword
                            ? Icons.visibility_outlined
                            : Icons.visibility_off_outlined)))),
            const SizedBox(height: 14),
            TextFormField(
                controller: confirmPassword,
                obscureText: hidePassword,
                textInputAction: TextInputAction.done,
                onFieldSubmitted: (_) => submit(),
                validator: (value) {
                  if (value != password.text) {
                    return 'Passwords do not match.';
                  }
                  return null;
                },
                decoration: const InputDecoration(
                    labelText: 'Confirm password',
                    prefixIcon: Icon(Icons.lock_reset_outlined))),
            const SizedBox(height: 12),
            CheckboxListTile(
                contentPadding: EdgeInsets.zero,
                controlAffinity: ListTileControlAffinity.leading,
                value: acceptedTerms,
                activeColor: AppColors.purple,
                onChanged: busy
                    ? null
                    : (value) => setState(() => acceptedTerms = value ?? false),
                title: const Text(
                    'I agree to the Terms & Conditions and Privacy Policy',
                    style: TextStyle(fontSize: 13, color: AppColors.muted))),
            if (error != null)
              Padding(
                  padding: const EdgeInsets.only(top: 14),
                  child: Text(error!,
                      style: const TextStyle(color: AppColors.red))),
            const SizedBox(height: 22),
            FilledButton(
                style: FilledButton.styleFrom(
                    backgroundColor: AppColors.purple,
                    padding: const EdgeInsets.all(16)),
                onPressed: busy ? null : submit,
                child: Text(busy ? 'Please wait...' : 'Create Account')),
          ]));
}

class _RoleCard extends StatelessWidget {
  const _RoleCard(
      {required this.icon,
      required this.color,
      required this.title,
      required this.subtitle,
      required this.onTap});
  final IconData icon;
  final Color color;
  final String title;
  final String subtitle;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) => Material(
      color: const Color(0xfff7f7fa),
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Padding(
            padding: const EdgeInsets.all(23),
            child: Row(children: [
              Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    CircleAvatar(
                        radius: 34,
                        backgroundColor: color,
                        child: Icon(icon, color: Colors.white, size: 34)),
                    const SizedBox(height: 20),
                    Text(title,
                        style: const TextStyle(
                            color: Colors.black,
                            fontSize: 23,
                            fontWeight: FontWeight.w800)),
                    const SizedBox(height: 7),
                    Text(subtitle,
                        style: const TextStyle(
                            color: Color(0xff666979),
                            fontSize: 16,
                            height: 1.4)),
                  ])),
              const Icon(Icons.chevron_right,
                  color: AppColors.purple, size: 36),
            ])),
      ));
}
