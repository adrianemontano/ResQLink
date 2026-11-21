import 'package:flutter/material.dart';
import '../services/mobile_api.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, required this.onAuthenticated});
  final ValueChanged<bool> onAuthenticated;
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _form = GlobalKey<FormState>();
  final _login = TextEditingController();
  final _password = TextEditingController();
  bool _busy = false;
  bool _hidden = true;
  String? _error;

  @override
  void dispose() { _login.dispose(); _password.dispose(); super.dispose(); }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _busy = true; _error = null; });
    try {
      final result = await MobileApi.instance.login(_login.text.trim(), _password.text);
      if (!mounted) return;
      final forced = result['must_change_password'] == true;
      widget.onAuthenticated(forced);
    } catch (error) {
      setState(() => _error = error.toString().replaceFirst('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(child: Center(child: SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 420), child: Card(
        child: Padding(padding: const EdgeInsets.all(24), child: Form(
          key: _form,
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            const Icon(Icons.health_and_safety, size: 46, color: Color(0xFF5A0006)),
            const SizedBox(height: 12),
            Text('ResQLink', textAlign: TextAlign.center, style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w800)),
            const SizedBox(height: 4),
            const Text('Barangay Responder sign in', textAlign: TextAlign.center),
            const SizedBox(height: 24),
            TextFormField(controller: _login, decoration: const InputDecoration(labelText: 'Username or email'), validator: (v) => (v == null || v.trim().isEmpty) ? 'Enter your username or email.' : null),
            const SizedBox(height: 14),
            TextFormField(controller: _password, obscureText: _hidden, decoration: InputDecoration(labelText: 'Password', suffixIcon: IconButton(onPressed: () => setState(() => _hidden = !_hidden), icon: Icon(_hidden ? Icons.visibility : Icons.visibility_off))), validator: (v) => (v == null || v.isEmpty) ? 'Enter your password.' : null),
            if (_error != null) ...[const SizedBox(height: 12), Text(_error!, style: const TextStyle(color: Color(0xFFD92200)))],
            const SizedBox(height: 18),
            FilledButton(onPressed: _busy ? null : _submit, child: _busy ? const CircularProgressIndicator() : const Text('Sign in')),
          ]),
        )),
      )),
    ))),
  );
}
