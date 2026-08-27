import 'dart:ui' show PointerDeviceKind;

import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:url_launcher/url_launcher.dart';
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
        body: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xffffffff), Color(0xfff8f1f8)],
            ),
          ),
          child: SafeArea(
            child: Stack(
              children: [
                const Positioned(
                  top: -90,
                  right: -75,
                  child: _GlowOrb(
                    size: 230,
                    color: Color(0xff67256a),
                    opacity: .055,
                  ),
                ),
                const Positioned(
                  bottom: -85,
                  left: -70,
                  child: _GlowOrb(
                    size: 210,
                    color: Color(0xffe31e3c),
                    opacity: .045,
                  ),
                ),
                Center(
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
                                  color: const Color(0xff67256a)
                                      .withValues(alpha: .18),
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
                const Positioned(
                  left: 24,
                  right: 24,
                  bottom: 26,
                  child: _SocialFooter(),
                ),
              ],
            ),
          ),
        ),
      );
}

class _GlowOrb extends StatelessWidget {
  const _GlowOrb({
    required this.size,
    required this.color,
    required this.opacity,
  });

  final double size;
  final Color color;
  final double opacity;

  @override
  Widget build(BuildContext context) => IgnorePointer(
        child: Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: color.withValues(alpha: opacity),
          ),
        ),
      );
}

class _SocialFooter extends StatelessWidget {
  const _SocialFooter();

  static const links = [
    _SocialDestination(
      label: 'Facebook',
      icon: FontAwesomeIcons.facebookF,
      color: Color(0xff1877f2),
      url: 'https://www.facebook.com/proskilljob2026/',
    ),
    _SocialDestination(
      label: 'LinkedIn',
      icon: FontAwesomeIcons.linkedinIn,
      color: Color(0xff0a66c2),
      url: 'https://www.linkedin.com/company/proskill-job-com',
    ),
    _SocialDestination(
      label: 'Instagram',
      icon: FontAwesomeIcons.instagram,
      color: Color(0xffc13584),
      url: 'https://www.instagram.com/proskilljob02/?hl=en',
    ),
    _SocialDestination(
      label: 'YouTube',
      icon: FontAwesomeIcons.youtube,
      color: Color(0xffff0000),
      url: 'https://www.youtube.com/@ProskillJob',
    ),
  ];

  Future<void> _open(BuildContext context, String url) async {
    final opened = await launchUrl(
      Uri.parse(url),
      mode: LaunchMode.externalApplication,
    );
    if (!opened && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('লিংকটি এখন খোলা যাচ্ছে না।')),
      );
    }
  }

  @override
  Widget build(BuildContext context) => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text(
            'CONNECT WITH US',
            style: TextStyle(
              color: Color(0xff817485),
              fontSize: 10,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.8,
            ),
          ),
          const SizedBox(height: 13),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: links
                .map((item) => Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 7),
                      child: Semantics(
                        button: true,
                        label: 'Open Proskill Job on ${item.label}',
                        child: Tooltip(
                          message: item.label,
                          child: Material(
                            color: Colors.white.withValues(alpha: .88),
                            shape: const CircleBorder(),
                            elevation: 0,
                            child: InkWell(
                              customBorder: const CircleBorder(),
                              onTap: () => _open(context, item.url),
                              child: Container(
                                width: 46,
                                height: 46,
                                alignment: Alignment.center,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: item.color.withValues(alpha: .16),
                                  ),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xff28142b)
                                          .withValues(alpha: .07),
                                      blurRadius: 16,
                                      offset: const Offset(0, 7),
                                    ),
                                  ],
                                ),
                                child: FaIcon(
                                  item.icon,
                                  size: 19,
                                  color: item.color,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ))
                .toList(),
          ),
          const SizedBox(height: 11),
          const Text(
            'Follow Proskill Job for career updates',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Color(0xff766b7a),
              fontSize: 11,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      );
}

class _SocialDestination {
  const _SocialDestination({
    required this.label,
    required this.icon,
    required this.color,
    required this.url,
  });

  final String label;
  final FaIconData icon;
  final Color color;
  final String url;
}
