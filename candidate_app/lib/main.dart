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
                  : const Scaffold(
                      body: Center(child: CircularProgressIndicator())),
        ),
      );
}
