import 'package:flutter/material.dart';
import 'screens/home_screen.dart';
import 'services/api_client.dart';
import 'state/app_state.dart';

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

class _ProSkillAppState extends State<ProSkillApp> {
  late final Future<void> _ready = widget.state.initialize();

  @override
  Widget build(BuildContext context) => MaterialApp(
        debugShowCheckedModeBanner: false,
        title: 'ProSkill Jobs',
        theme: ThemeData(
          useMaterial3: true,
          colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xff2563eb)),
          scaffoldBackgroundColor: const Color(0xfff7f9fc),
          inputDecorationTheme: const InputDecorationTheme(
            border: OutlineInputBorder(),
          ),
          cardTheme: const CardThemeData(
            elevation: 0,
            margin: EdgeInsets.zero,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.all(Radius.circular(16)),
              side: BorderSide(color: Color(0xffe5e7eb)),
            ),
          ),
        ),
        home: FutureBuilder<void>(
          future: _ready,
          builder: (_, snapshot) => snapshot.connectionState == ConnectionState.done
              ? HomeScreen(state: widget.state)
              : const Scaffold(body: Center(child: CircularProgressIndicator())),
        ),
      );
}

