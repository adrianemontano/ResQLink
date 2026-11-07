import 'package:flutter/material.dart';
import '../services/mobile_api.dart';

class PasswordChangeScreen extends StatefulWidget {
  const PasswordChangeScreen({super.key, required this.onChanged});
  final VoidCallback onChanged;
  @override
  State<PasswordChangeScreen> createState() => _PasswordChangeScreenState();
}

class _PasswordChangeScreenState extends State<PasswordChangeScreen> {
  final _form = GlobalKey<FormState>();
  final _current = TextEditingController();
  final _next = TextEditingController();
  final _confirm = TextEditingController();
  bool _hideCurrent = true;
  bool _hideNew = true;
  bool _busy = false;
  String? _error;

  @override
  void dispose() { _current.dispose(); _next.dispose(); _confirm.dispose(); super.dispose(); }

  int get _strength {
    final value = _next.text;
    return (value.length >= 8 ? 1 : 0) +
        (RegExp('[A-Z]').hasMatch(value) ? 1 : 0) +
        (RegExp('[0-9]').hasMatch(value) ? 1 : 0) +
        (RegExp('[^A-Za-z0-9]').hasMatch(value) ? 1 : 0);
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() { _busy = true; _error = null; });
    try {
      await MobileApi.instance.changePassword(_current.text, _next.text, _confirm.text);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password changed successfully.')));
      widget.onChanged();
    } catch (error) {
      setState(() => _error = error.toString().replaceFirst('Exception: ', ''));
    } finally { if (mounted) setState(() => _busy = false); }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Change temporary password'), automaticallyImplyLeading: false),
    body: SafeArea(child: Center(child: SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 480), child: Card(
        child: Padding(padding: const EdgeInsets.all(22), child: Form(
          key: _form,
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            const Icon(Icons.lock_reset, size: 44, color: Color(0xFF5A0006)),
            const SizedBox(height: 10),
            Text('Secure your account', textAlign: TextAlign.center, style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 8),
            const Text('Choose a new password before continuing.', textAlign: TextAlign.center),
            const SizedBox(height: 20),
            TextFormField(controller: _current, obscureText: _hideCurrent, decoration: InputDecoration(labelText: 'Current temporary password', suffixIcon: IconButton(onPressed: () => setState(() => _hideCurrent = !_hideCurrent), icon: Icon(_hideCurrent ? Icons.visibility : Icons.visibility_off))), validator: (v) => (v == null || v.isEmpty) ? 'Enter your current password.' : null),
            const SizedBox(height: 14),
            TextFormField(controller: _next, obscureText: _hideNew, onChanged: (_) => setState(() {}), decoration: InputDecoration(labelText: 'New password', helperText: 'At least 8 characters', suffixIcon: IconButton(onPressed: () => setState(() => _hideNew = !_hideNew), icon: Icon(_hideNew ? Icons.visibility : Icons.visibility_off))), validator: (v) { if (v == null || v.length < 8) return 'Use at least 8 characters.'; if (v == _current.text) return 'New password must differ from the temporary password.'; return null; }),
            const SizedBox(height: 6),
            LinearProgressIndicator(value: _next.text.isEmpty ? 0 : _strength / 4, color: _strength >= 3 ? const Color(0xFF476B45) : const Color(0xFFF37100), backgroundColor: const Color(0xFFE8DCD0)),
            Align(alignment: Alignment.centerRight, child: Text(['Too short', 'Weak', 'Fair', 'Good', 'Strong'][_strength], style: Theme.of(context).textTheme.labelSmall)),
            const SizedBox(height: 14),
            TextFormField(controller: _confirm, obscureText: _hideNew, decoration: const InputDecoration(labelText: 'Confirm new password'), validator: (v) => v != _next.text ? 'Passwords do not match.' : null),
            if (_error != null) ...[const SizedBox(height: 10), Text(_error!, style: const TextStyle(color: Color(0xFFD92200)))],
            const SizedBox(height: 18),
            FilledButton(onPressed: _busy ? null : _submit, child: _busy ? const CircularProgressIndicator() : const Text('Save password and continue')),
          ]),
        )),
      )),
    ))),
  );
}
