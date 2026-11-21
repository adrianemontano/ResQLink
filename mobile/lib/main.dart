import 'package:flutter/material.dart';
import 'screens/incident_form_screen.dart';
import 'screens/login_screen.dart';
import 'screens/password_change_screen.dart';
import 'services/mobile_api.dart';

void main() => runApp(const ResQLinkApp());

class ResQLinkApp extends StatefulWidget {
  const ResQLinkApp({super.key});
  @override
  State<ResQLinkApp> createState() => _ResQLinkAppState();
}

class _ResQLinkAppState extends State<ResQLinkApp> {
  bool _ready = false;
  bool _mustChange = false;

  @override
  void initState() {
    super.initState();
    _restore();
  }

  Future<void> _restore() async {
    await MobileApi.instance.restoreToken();
    _mustChange = MobileApi.instance.mustChangePassword;
    if (mounted) setState(() => _ready = true);
  }

  void _authenticated(bool mustChange) => setState(() => _mustChange = mustChange);
  void _passwordChanged() => setState(() => _mustChange = false);
  Future<void> _logout() async {
    await MobileApi.instance.logout();
    if (mounted) setState(() { _mustChange = false; _ready = true; });
  }

  @override
  Widget build(BuildContext context) => MaterialApp(
    title: 'ResQLink Volunteer',
    debugShowCheckedModeBanner: false,
    theme: ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: const Color(0xFF5A0006),
        primary: const Color(0xFF5A0006),
        secondary: const Color(0xFFF37100),
        onSecondary: const Color(0xFF5A0006),
        error: const Color(0xFFD92200),
        surface: Colors.white,
      ),
      scaffoldBackgroundColor: const Color(0xFFFBF3EA),
      appBarTheme: const AppBarTheme(backgroundColor: Color(0xFF5A0006), foregroundColor: Colors.white),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      ),
    ),
    home: !_ready
        ? const Scaffold(body: Center(child: CircularProgressIndicator()))
        : MobileApi.instance.token == null
            ? LoginScreen(onAuthenticated: _authenticated)
            : _mustChange
                ? PasswordChangeScreen(onChanged: _passwordChanged)
                : IncidentFormScreen(onLogout: _logout),
  );
}
