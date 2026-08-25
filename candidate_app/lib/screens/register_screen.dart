import 'package:flutter/material.dart';
import '../state/app_state.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key, required this.state});
  final AppState state;
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final name = TextEditingController();
  final email = TextEditingController();
  final password = TextEditingController();
  bool busy = false;
  String? message;

  Future<void> submit() async {
    setState(() { busy = true; message = null; });
    try {
      await widget.state.auth.register(name.text.trim(), email.text.trim(), password.text);
      setState(() => message = 'অ্যাকাউন্ট তৈরি হয়েছে। এখন লগইন করুন।');
    } catch (e) {
      setState(() => message = e.toString());
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('রেজিস্ট্রেশন')),
        body: ListView(padding: const EdgeInsets.all(24), children: [
          TextField(controller: name, decoration: const InputDecoration(labelText: 'পুরো নাম')),
          const SizedBox(height: 14),
          TextField(controller: email, keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(labelText: 'ইমেইল')),
          const SizedBox(height: 14),
          TextField(controller: password, obscureText: true,
              decoration: const InputDecoration(labelText: 'পাসওয়ার্ড (কমপক্ষে ৮ অক্ষর)')),
          if (message != null) Padding(
            padding: const EdgeInsets.only(top: 12), child: Text(message!)),
          const SizedBox(height: 20),
          FilledButton(onPressed: busy ? null : submit,
              child: Padding(padding: const EdgeInsets.all(14),
                child: Text(busy ? 'অপেক্ষা করুন...' : 'অ্যাকাউন্ট তৈরি করুন'))),
        ]),
      );
}

