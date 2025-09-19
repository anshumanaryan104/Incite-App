import 'dart:ui';

import 'package:incite/api_controller/user_controller.dart';

Color hexToRgb(String? hexColor) {
  if (hexColor != null && hexColor.isNotEmpty) {
    hexColor = hexColor.replaceAll('#', '');
    int hexValue = int.parse(hexColor, radix: 16);
    int alpha = 255;
    int red = (hexValue >> 16) & 0xFF;
    int green = (hexValue >> 8) & 0xFF;
    int blue = hexValue & 0xFF;
    return Color.fromARGB(alpha, red, green, blue);
  }
  return const Color.fromRGBO(255, 134, 44, 1);
}

var colorGradient = [hexToRgb(allSettings.value.primaryColor), hexToRgb(allSettings.value.secondaryColor)];
