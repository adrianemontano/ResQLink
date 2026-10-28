import 'package:flutter/foundation.dart';

enum AuthStatus { checking, unauthenticated }

class AuthProvider extends ChangeNotifier {
  AuthStatus _status = AuthStatus.checking;

  AuthStatus get status => _status;

  Future<void> initialize() async {
    await Future<void>.delayed(Duration.zero);
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }
}
