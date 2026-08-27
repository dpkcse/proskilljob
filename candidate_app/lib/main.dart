import 'dart:ui' show PointerDeviceKind;

import 'package:flutter/material.dart';
import 'screens/home_screen.dart';
import 'services/api_client.dart';
import 'state/app_state.dart';
import 'theme/app_theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(ProSkillApp(state: AppState(ApiClient())));
}

class ProSkillApp extends StatefulWidget {
  const ProSkillApp({super.key, required this.state});
  final AppState state;
  @override
  State<ProSkillApp> createState() => _ProSkillAppState();
}

class AppScrollBehavior extends MaterialScrollBehavior {
  const AppScrollBehavior();

  @override
  Set<PointerDeviceKind> get dragDevices => const {
        PointerDeviceKind.touch,
        PointerDeviceKind.mouse,
        PointerDeviceKind.stylus,
        PointerDeviceKind.trackpad,
      };
}

class _ProSkillAppState extends State<ProSkillApp> {
  late final Future<void> _ready = widget.state.initialize();

  @override
  Widget build(BuildContext context) => MaterialApp(
        debugShowCheckedModeBanner: false,
        scrollBehavior: const AppScrollBehavior(),
        title: 'ProSkill Jobs',
        theme: buildAppTheme(),
        home: FutureBuilder<void>(
          future: _ready,
          builder: (_, snapshot) =>
              snapshot.connectionState == ConnectionState.done
                  ? HomeScreen(state: widget.state)
                  : const _AppLoadingScreen(),
        ),
      );
}

class _AppLoadingScreen extends StatefulWidget {
  const _AppLoadingScreen();

  @override
  State<_AppLoadingScreen> createState() => _AppLoadingScreenState();
}

class _AppLoadingScreenState extends State<_AppLoadingScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1050),
  )..repeat(reverse: true);

  late final Animation<double> _scale = Tween<double>(begin: .88, end: 1)
      .animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));

  late final Animation<double> _opacity = Tween<double>(begin: .58, end: 1)
      .animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: const Color(0xfffaf7fb),
        body: SafeArea(
          child: Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                FadeTransition(
                  opacity: _opacity,
                  child: ScaleTransition(
                    scale: _scale,
                    child: Container(
                      width: 154,
                      height: 154,
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color:
                                const Color(0xff67256a).withValues(alpha: .18),
                            blurRadius: 34,
                            spreadRadius: 2,
                          ),
                        ],
                      ),
                      child: Image.asset(
                        'assets/branding/proskill_loading_mark.png',
                        fit: BoxFit.contain,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                const Text(
                  'Proskill Job',
                  style: TextStyle(
                    color: Color(0xff67256a),
                    fontSize: 25,
                    fontWeight: FontWeight.w800,
                    letterSpacing: .2,
                  ),
                ),
                const SizedBox(height: 15),
                SizedBox(
                  width: 82,
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(20),
                    child: LinearProgressIndicator(
                      minHeight: 4,
                      color: const Color(0xffe31e3c),
                      backgroundColor:
                          const Color(0xff67256a).withValues(alpha: .12),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
}
