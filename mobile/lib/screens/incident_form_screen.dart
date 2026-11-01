import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter_image_compress/flutter_image_compress.dart';
import 'package:image_picker/image_picker.dart';
import '../services/mobile_api.dart';

class IncidentFormScreen extends StatefulWidget {
  const IncidentFormScreen({super.key, required this.onLogout});
  final Future<void> Function() onLogout;
  @override
  State<IncidentFormScreen> createState() => _IncidentFormScreenState();
}

class _IncidentFormScreenState extends State<IncidentFormScreen> {
  final _form = GlobalKey<FormState>();
  final _barangay = TextEditingController();
  final _landmark = TextEditingController();
  final _people = TextEditingController();
  final _latitude = TextEditingController();
  final _longitude = TextEditingController();
  final _radius = TextEditingController(text: '300');
  final _notes = TextEditingController();
  String _category = 'Flood';
  XFile? _photo;
  Uint8List? _compressedPhoto;
  bool _busy = false;

  @override
  void dispose() {
    _barangay.dispose(); _landmark.dispose(); _people.dispose(); _latitude.dispose();
    _longitude.dispose(); _radius.dispose(); _notes.dispose(); super.dispose();
  }

  Future<void> _selectPhoto(ImageSource source) async {
    try {
      final image = await ImagePicker().pickImage(source: source, imageQuality: 68, maxWidth: 1600, maxHeight: 1600);
      if (image == null) return;
      if (!['jpg', 'jpeg', 'png'].contains(image.path.split('.').last.toLowerCase())) {
        _message('Select a JPG or PNG image.'); return;
      }
      final original = await image.readAsBytes();
      final bytes = await FlutterImageCompress.compressWithList(
        original,
        minWidth: 1600,
        minHeight: 1600,
        quality: 70,
        format: CompressFormat.jpeg,
      );
      if (bytes.length > 5 * 1024 * 1024) { _message('Photo must be 5 MB or smaller after compression.'); return; }
      setState(() { _photo = image; _compressedPhoto = bytes; });
    } catch (error) { _message('Could not open the selected image: $error'); }
  }

  Future<void> _choosePhoto() async {
    final source = await showModalBottomSheet<ImageSource>(context: context, builder: (context) => SafeArea(child: Wrap(children: [
      ListTile(leading: const Icon(Icons.camera_alt), title: const Text('Take a photo'), onTap: () => Navigator.pop(context, ImageSource.camera)),
      ListTile(leading: const Icon(Icons.photo_library), title: const Text('Choose from gallery'), onTap: () => Navigator.pop(context, ImageSource.gallery)),
    ])));
    if (source != null) await _selectPhoto(source);
  }

  Future<void> _submit() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _busy = true);
    try {
      final result = await MobileApi.instance.submitIncident({
        'category': _category, 'barangay': _barangay.text.trim(), 'nearest_landmark': _landmark.text.trim(),
        'affected_population': _people.text.trim(), 'latitude': _latitude.text.trim(),
        'longitude': _longitude.text.trim(), 'impact_radius': _radius.text.trim(), 'notes': _notes.text.trim(),
      }, _compressedPhoto);
      if (!mounted) return;
      final data = result['data'] as Map? ?? result;
      _message('Incident ${data['identifier'] ?? ''} submitted. Status: ${data['status'] ?? 'Reported'}.');
      for (final controller in [_barangay, _landmark, _people, _latitude, _longitude, _notes]) {
        controller.clear();
      }
      setState(() { _photo = null; _compressedPhoto = null; _radius.text = '300'; });
    } catch (error) { _message(error.toString().replaceFirst('Exception: ', '')); }
    finally { if (mounted) setState(() => _busy = false); }
  }

  void _message(String value) => ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(value)));

  String? _required(String? value, String label) => value == null || value.trim().isEmpty ? '$label is required.' : null;

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(title: const Text('Report an incident'), actions: [IconButton(tooltip: 'Sign out', onPressed: widget.onLogout, icon: const Icon(Icons.logout))]),
    body: SafeArea(child: Form(key: _form, child: ListView(padding: const EdgeInsets.all(16), children: [
      Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Incident details', style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 14),
        DropdownButtonFormField<String>(initialValue: _category, decoration: const InputDecoration(labelText: 'Category'), items: ['Flood','Earthquake','Landslide','Fire'].map((v) => DropdownMenuItem(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _category = v ?? 'Flood')),
        const SizedBox(height: 12),
        TextFormField(controller: _barangay, decoration: const InputDecoration(labelText: 'Barangay'), validator: (v) => _required(v, 'Barangay')),
        const SizedBox(height: 12),
        TextFormField(controller: _landmark, decoration: const InputDecoration(labelText: 'Nearest landmark'), validator: (v) => _required(v, 'Nearest landmark')),
        const SizedBox(height: 12),
        TextFormField(controller: _people, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Estimated affected persons'), validator: (v) { final n = int.tryParse(v ?? ''); return n == null || n < 0 ? 'Enter a valid non-negative count.' : null; }),
        const SizedBox(height: 12),
        Row(children: [Expanded(child: TextFormField(controller: _latitude, keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true), decoration: const InputDecoration(labelText: 'Latitude'), validator: (v) => double.tryParse(v ?? '') == null ? 'Enter valid latitude.' : null)), const SizedBox(width: 10), Expanded(child: TextFormField(controller: _longitude, keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true), decoration: const InputDecoration(labelText: 'Longitude'), validator: (v) => double.tryParse(v ?? '') == null ? 'Enter valid longitude.' : null))]),
        const SizedBox(height: 12),
        TextFormField(controller: _radius, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Impact radius (metres)'), validator: (v) => (double.tryParse(v ?? '') ?? 0) <= 0 ? 'Enter a positive radius.' : null),
        const SizedBox(height: 12),
        TextFormField(controller: _notes, maxLines: 3, decoration: const InputDecoration(labelText: 'Notes (optional)')),
      ]))),
      Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Incident photo (optional)', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 4), const Text('JPG or PNG · up to 5 MB · resized/compressed before sending'),
        const SizedBox(height: 12),
        if (_photo != null) ...[
          ClipRRect(borderRadius: BorderRadius.circular(10), child: Image.file(File(_photo!.path), height: 190, width: double.infinity, fit: BoxFit.cover)),
          Align(alignment: Alignment.centerRight, child: TextButton.icon(onPressed: () => setState(() { _photo = null; _compressedPhoto = null; }), icon: const Icon(Icons.delete_outline), label: const Text('Remove photo'))),
        ] else OutlinedButton.icon(onPressed: _choosePhoto, icon: const Icon(Icons.add_a_photo), label: const Text('Add photo')),
      ]))),
      const SizedBox(height: 8),
      FilledButton.icon(onPressed: _busy ? null : _submit, icon: const Icon(Icons.send), label: Text(_busy ? 'Submitting…' : 'Submit incident report')),
      const SizedBox(height: 18),
    ]))),
  );
}
