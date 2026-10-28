import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/app/app.dart';

void main() {
  testWidgets('shows the volunteer login screen', (tester) async {
    await tester.pumpWidget(const ResQLinkApp());
    await tester.pump();

    expect(find.text('ResQLink'), findsOneWidget);
    expect(find.text('Volunteer sign in'), findsOneWidget);
    expect(find.text('Username or email'), findsOneWidget);
    expect(find.text('Password'), findsOneWidget);
  });
}
