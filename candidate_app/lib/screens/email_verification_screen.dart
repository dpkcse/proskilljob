import 'dart:async';

import 'package:flutter/material.dart';

import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';

class EmailVerificationScreen extends StatefulWidget {
  const EmailVerificationScreen({
    super.key,
    required this.state,
    required this.email,
  });

  final AppState state;
  final String email;

  @override
  State<EmailVerificationScreen> createState() =>
      _EmailVerificationScreenState();
}

class _EmailVerificationScreenState extends State<EmailVerificationScreen>
    with WidgetsBindingObserver {
  Timer? timer;
  int resendSeconds = 60;
  bool checking = false;
  bool resending = false;
  String? message;
  bool messageIsError = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _startTimer();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    timer?.cancel();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed && !checking) _checkStatus();
  }

  void _startTimer() {
    timer?.cancel();
    resendSeconds = 60;
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

  Future<void> _checkStatus() async {
    setState(() {
      checking = true;
      message = null;
    });
    try {
      final result = await widget.state.auth.verificationStatus(widget.email);
      if (!mounted) return;
      if (result['verified'] == true) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => LoginScreen(
              state: widget.state,
              initialEmail: widget.email,
              notice: 'Email verified successfully. You can now log in.',
            ),
          ),
        );
        return;
      }
      setState(() {
        messageIsError = result['expired'] == true;
        message = result['expired'] == true
            ? 'This verification link has expired. Request a new email.'
            : 'Verification is still pending. Open the link sent to your email.';
      });
    } catch (error) {
      if (mounted) {
        setState(() {
          messageIsError = true;
          message = '$error';
        });
      }
    } finally {
      if (mounted) setState(() => checking = false);
    }
  }

  Future<void> _resend() async {
    if (resendSeconds > 0 || resending) return;
    setState(() {
      resending = true;
      message = null;
    });
    try {
      final result = await widget.state.auth.resendVerification(widget.email);
      if (!mounted) return;
      setState(() {
        messageIsError = false;
        message = result;
      });
      _startTimer();
    } catch (error) {
      if (mounted) {
        setState(() {
          messageIsError = true;
          message = '$error';
        });
      }
    } finally {
      if (mounted) setState(() => resending = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('Verify your email')),
        body: SafeArea(
          child: ListView(
            padding: const EdgeInsets.fromLTRB(24, 30, 24, 30),
            children: [
              Center(
                child: Container(
                  width: 108,
                  height: 108,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: const LinearGradient(
                      colors: [Color(0xff8848f7), Color(0xff5f20cf)],
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.purple.withValues(alpha: .32),
                        blurRadius: 34,
                        offset: const Offset(0, 14),
                      ),
                    ],
                  ),
                  child: const Icon(Icons.mark_email_unread_outlined,
                      color: Colors.white, size: 48),
                ),
              ),
              const SizedBox(height: 34),
              const Text(
                'Check your inbox',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 30, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 12),
              const Text(
                'We sent a secure verification link to',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.muted, fontSize: 15),
              ),
              const SizedBox(height: 7),
              Text(
                widget.email,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 28),
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(17),
                  border: Border.all(color: AppColors.border),
                ),
                child: const Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.info_outline, color: Color(0xffa77cff)),
                    SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Open the email and tap “Verify Email Address”. The link remains valid for 24 hours. Then return to this app.',
                        style: TextStyle(color: AppColors.muted, height: 1.45),
                      ),
                    ),
                  ],
                ),
              ),
              if (message != null) ...[
                const SizedBox(height: 18),
                Text(
                  message!,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: messageIsError
                        ? AppColors.danger
                        : const Color(0xff69dbb8),
                  ),
                ),
              ],
              const SizedBox(height: 26),
              FilledButton.icon(
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.purple,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                onPressed: checking ? null : _checkStatus,
                icon: checking
                    ? const SizedBox(
                        width: 19,
                        height: 19,
                        child: CircularProgressIndicator(
                            strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.verified_outlined),
                label:
                    Text(checking ? 'Checking...' : 'I’ve verified my email'),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: resendSeconds == 0 && !resending ? _resend : null,
                child: Text(resending
                    ? 'Sending...'
                    : resendSeconds > 0
                        ? 'Resend email in ${resendSeconds}s'
                        : 'Resend verification email'),
              ),
              TextButton(
                onPressed: () => Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(
                    builder: (_) => LoginScreen(
                      state: widget.state,
                      initialEmail: widget.email,
                    ),
                  ),
                ),
                child: const Text('Back to login'),
              ),
            ],
          ),
        ),
      );
}
