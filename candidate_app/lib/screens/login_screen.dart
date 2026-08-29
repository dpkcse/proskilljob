import 'package:flutter/material.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'register_screen.dart';
import 'forgot_password_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({
    super.key,
    required this.state,
    this.initialEmail = '',
    this.notice,
  });
  final AppState state;
  final String initialEmail;
  final String? notice;
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final email = TextEditingController();
  final password = TextEditingController();
  bool busy = false;
  String? error;

  @override
  void initState() {
    super.initState();
    email.text = widget.initialEmail;
  }

  @override
  void dispose() {
    email.dispose();
    password.dispose();
    super.dispose();
  }

  Future<void> submit() async {
    setState(() {
      busy = true;
      error = null;
    });
    try {
      await widget.state.login(email.text.trim(), password.text);
      if (mounted) Navigator.pop(context);
    } catch (e) {
      setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(),
        body: ListView(padding: const EdgeInsets.all(24), children: [
          const Text('Welcome back',
              style: TextStyle(fontSize: 30, fontWeight: FontWeight.w800)),
          const Text('Log in to your candidate account',
              style: TextStyle(color: AppColors.muted)),
          if (widget.notice != null)
            Container(
              margin: const EdgeInsets.only(top: 18),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xff123b32),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xff226c5a)),
              ),
              child: Text(widget.notice!,
                  style: const TextStyle(color: Color(0xff92e8cf))),
            ),
          const SizedBox(height: 28),
          TextField(
              controller: email,
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.email],
              decoration: const InputDecoration(
                  labelText: 'Email', prefixIcon: Icon(Icons.email_outlined))),
          const SizedBox(height: 14),
          TextField(
              controller: password,
              obscureText: true,
              autofillHints: const [AutofillHints.password],
              decoration: const InputDecoration(
                  labelText: 'Password', prefixIcon: Icon(Icons.lock_outline))),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton(
              onPressed: busy
                  ? null
                  : () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ForgotPasswordScreen(
                            state: widget.state,
                            initialEmail: email.text.trim(),
                          ),
                        ),
                      ),
              child: const Text('Forgot Password?'),
            ),
          ),
          if (error != null)
            Padding(
              padding: const EdgeInsets.only(top: 12),
              child: Text(error!, style: const TextStyle(color: Colors.red)),
            ),
          const SizedBox(height: 8),
          FilledButton(
              onPressed: busy ? null : submit,
              child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Text(busy ? 'Please wait...' : 'Log In'))),
          TextButton(
            onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(
                    builder: (_) => RegisterScreen(state: widget.state))),
            child: const Text('Create a new account'),
          ),
        ]),
      );
}
