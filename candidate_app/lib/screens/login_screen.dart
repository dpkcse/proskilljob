import 'package:flutter/material.dart';
import '../state/app_state.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, required this.state});
  final AppState state;
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final email = TextEditingController();
  final password = TextEditingController();
  bool busy = false;
  String? error;

  Future<void> submit() async {
    setState(() { busy = true; error = null; });
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
          const Text('স্বাগতম', style: TextStyle(fontSize: 30, fontWeight: FontWeight.w800)),
          const Text('আপনার ক্যান্ডিডেট অ্যাকাউন্টে লগইন করুন'),
          const SizedBox(height: 28),
          TextField(controller: email, keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(labelText: 'ইমেইল')),
          const SizedBox(height: 14),
          TextField(controller: password, obscureText: true,
              decoration: const InputDecoration(labelText: 'পাসওয়ার্ড')),
          if (error != null) Padding(
            padding: const EdgeInsets.only(top: 12),
            child: Text(error!, style: const TextStyle(color: Colors.red)),
          ),
          const SizedBox(height: 20),
          FilledButton(onPressed: busy ? null : submit,
              child: Padding(padding: const EdgeInsets.all(14),
                child: Text(busy ? 'অপেক্ষা করুন...' : 'লগইন'))),
          TextButton(
            onPressed: () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => RegisterScreen(state: widget.state))),
            child: const Text('নতুন অ্যাকাউন্ট তৈরি করুন'),
          ),
        ]),
      );
}

