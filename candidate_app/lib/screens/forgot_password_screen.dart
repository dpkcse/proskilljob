import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({
    super.key,
    required this.state,
    this.initialEmail = '',
  });

  final AppState state;
  final String initialEmail;

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final emailKey = GlobalKey<FormState>();
  final resetKey = GlobalKey<FormState>();
  final email = TextEditingController();
  final code = TextEditingController();
  final password = TextEditingController();
  final confirmation = TextEditingController();
  Timer? timer;
  int resendSeconds = 0;
  bool codeSent = false;
  bool busy = false;
  bool obscurePassword = true;
  String? message;
  bool messageIsError = false;

  @override
  void initState() {
    super.initState();
    email.text = widget.initialEmail;
  }

  @override
  void dispose() {
    timer?.cancel();
    email.dispose();
    code.dispose();
    password.dispose();
    confirmation.dispose();
    super.dispose();
  }

  void _startCooldown() {
    timer?.cancel();
    setState(() => resendSeconds = 60);
    timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (resendSeconds <= 1) {
        timer.cancel();
        setState(() => resendSeconds = 0);
      } else {
        setState(() => resendSeconds--);
      }
    });
  }

  Future<void> _sendCode({bool validateEmail = true}) async {
    FocusScope.of(context).unfocus();
    if (validateEmail && !(emailKey.currentState?.validate() ?? false)) return;
    setState(() {
      busy = true;
      message = null;
    });
    try {
      await widget.state.auth.requestPasswordReset(email.text.trim());
      if (!mounted) return;
      setState(() {
        codeSent = true;
        messageIsError = false;
        message = 'A 6-digit reset code was sent to ${email.text.trim()}.';
      });
      _startCooldown();
    } catch (error) {
      if (mounted) {
        setState(() {
          messageIsError = true;
          message = '$error';
        });
      }
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  Future<void> _resetPassword() async {
    FocusScope.of(context).unfocus();
    if (!(resetKey.currentState?.validate() ?? false)) return;
    setState(() {
      busy = true;
      message = null;
    });
    try {
      await widget.state.auth.resetPassword(
        email: email.text.trim(),
        code: code.text.trim(),
        password: password.text,
      );
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (_) => LoginScreen(
            state: widget.state,
            initialEmail: email.text.trim(),
            notice:
                'Password reset successfully. Log in with your new password.',
          ),
        ),
      );
    } catch (error) {
      if (mounted) {
        setState(() {
          messageIsError = true;
          message = '$error';
        });
      }
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('Reset Password')),
        body: SafeArea(
          child: AnimatedSwitcher(
            duration: const Duration(milliseconds: 260),
            child: codeSent ? _resetForm() : _emailForm(),
          ),
        ),
      );

  Widget _emailForm() => Form(
        key: emailKey,
        child: ListView(
          key: const ValueKey('reset-email'),
          padding: const EdgeInsets.fromLTRB(24, 28, 24, 32),
          children: [
            _hero(Icons.lock_reset_rounded),
            const SizedBox(height: 30),
            const Text('Forgot your password?',
                style: TextStyle(fontSize: 29, fontWeight: FontWeight.w800)),
            const SizedBox(height: 9),
            const Text(
              'Enter your account email. We’ll send a secure 6-digit code that remains valid for 10 minutes.',
              style: TextStyle(color: AppColors.muted, height: 1.45),
            ),
            const SizedBox(height: 28),
            TextFormField(
              controller: email,
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.email],
              validator: _validateEmail,
              decoration: const InputDecoration(
                labelText: 'Account email',
                prefixIcon: Icon(Icons.email_outlined),
              ),
            ),
            _feedback(),
            const SizedBox(height: 24),
            FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.purple,
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              onPressed: busy ? null : _sendCode,
              icon: const Icon(Icons.outgoing_mail),
              label: Text(busy ? 'Sending...' : 'Send Reset Code'),
            ),
          ],
        ),
      );

  Widget _resetForm() => Form(
        key: resetKey,
        child: ListView(
          key: const ValueKey('reset-code'),
          padding: const EdgeInsets.fromLTRB(24, 24, 24, 32),
          children: [
            _hero(Icons.mark_email_read_outlined),
            const SizedBox(height: 25),
            const Text('Create a new password',
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800)),
            const SizedBox(height: 8),
            Text('Enter the code sent to ${email.text.trim()}.',
                style: const TextStyle(color: AppColors.muted)),
            const SizedBox(height: 25),
            TextFormField(
              controller: code,
              keyboardType: TextInputType.number,
              textInputAction: TextInputAction.next,
              inputFormatters: [
                FilteringTextInputFormatter.digitsOnly,
                LengthLimitingTextInputFormatter(6),
              ],
              validator: (value) =>
                  (value ?? '').length == 6 ? null : 'Enter the 6-digit code.',
              decoration: const InputDecoration(
                labelText: '6-digit reset code',
                prefixIcon: Icon(Icons.pin_outlined),
              ),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: password,
              obscureText: obscurePassword,
              textInputAction: TextInputAction.next,
              autofillHints: const [AutofillHints.newPassword],
              validator: (value) => (value ?? '').length >= 8
                  ? null
                  : 'Password must be at least 8 characters.',
              decoration: InputDecoration(
                labelText: 'New password',
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  onPressed: () =>
                      setState(() => obscurePassword = !obscurePassword),
                  icon: Icon(obscurePassword
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined),
                ),
              ),
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: confirmation,
              obscureText: obscurePassword,
              textInputAction: TextInputAction.done,
              onFieldSubmitted: (_) => _resetPassword(),
              validator: (value) =>
                  value == password.text ? null : 'Passwords do not match.',
              decoration: const InputDecoration(
                labelText: 'Confirm new password',
                prefixIcon: Icon(Icons.lock_reset_outlined),
              ),
            ),
            _feedback(),
            const SizedBox(height: 22),
            FilledButton.icon(
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.purple,
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              onPressed: busy ? null : _resetPassword,
              icon: const Icon(Icons.verified_user_outlined),
              label: Text(busy ? 'Updating...' : 'Update Password'),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: busy || resendSeconds > 0
                  ? null
                  : () => _sendCode(validateEmail: false),
              child: Text(resendSeconds > 0
                  ? 'Resend code in ${resendSeconds}s'
                  : 'Resend reset code'),
            ),
            TextButton(
              onPressed: busy
                  ? null
                  : () => setState(() {
                        codeSent = false;
                        code.clear();
                        message = null;
                      }),
              child: const Text('Use a different email'),
            ),
          ],
        ),
      );

  Widget _hero(IconData icon) => Center(
        child: Container(
          width: 96,
          height: 96,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: const LinearGradient(
              colors: [Color(0xff8848f7), Color(0xff5f20cf)],
            ),
            boxShadow: [
              BoxShadow(
                color: AppColors.purple.withValues(alpha: .28),
                blurRadius: 30,
                offset: const Offset(0, 12),
              ),
            ],
          ),
          child: Icon(icon, color: Colors.white, size: 43),
        ),
      );

  Widget _feedback() => message == null
      ? const SizedBox.shrink()
      : Padding(
          padding: const EdgeInsets.only(top: 16),
          child: Text(
            message!,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: messageIsError ? AppColors.red : const Color(0xff69dbb8),
            ),
          ),
        );

  String? _validateEmail(String? value) {
    final input = value?.trim() ?? '';
    return RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(input)
        ? null
        : 'Enter a valid email address.';
  }
}
