import 'package:flutter_test/flutter_test.dart';
import 'package:proskill_candidate/main.dart';
import 'package:proskill_candidate/services/api_client.dart';
import 'package:proskill_candidate/state/app_state.dart';

void main() {
  testWidgets('app shows loading state', (tester) async {
    await tester.pumpWidget(ProSkillApp(state: AppState(ApiClient())));
    expect(find.byType(ProSkillApp), findsOneWidget);
  });
}
