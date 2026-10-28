import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../features/authentication/presentation/auth_provider.dart';
import '../features/authentication/presentation/login_screen.dart';
import 'app_theme.dart';

class ResQLinkApp extends StatelessWidget {
  const ResQLinkApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AuthProvider()..initialize(),
      child: MaterialApp(
        debugShowCheckedModeBanner: false,
        title: 'ResQLink',
        theme: ResQLinkTheme.light,
        home: const _AuthenticationGate(),
      ),
    );
  }
}

class _AuthenticationGate extends StatelessWidget {
  const _AuthenticationGate();

  @override
  Widget build(BuildContext context) {
    final status = context.select<AuthProvider, AuthStatus>(
      (provider) => provider.status,
    );

    return switch (status) {
      AuthStatus.checking => const _StartupScreen(),
      AuthStatus.unauthenticated => const LoginScreen(),
    };
  }
}

class _StartupScreen extends StatelessWidget {
  const _StartupScreen();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Semantics(
          label: 'Starting ResQLink',
          child: const CircularProgressIndicator(),
        ),
      ),
    );
  }
}
