// CMMS Leonplast Mobile Widget Test

import 'package:flutter_test/flutter_test.dart';
import 'package:cmms_leonplast_mobile/main.dart';

void main() {
  testWidgets('CMMS Leonplast Mobile App smoke test', (WidgetTester tester) async {
    // Renderizar la app CmmsApp
    await tester.pumpWidget(const CmmsApp());

    // Verificar que se muestre el título principal de OTs
    expect(find.text('OTs en Planta (Offline First)'), findsOneWidget);
  });
}
