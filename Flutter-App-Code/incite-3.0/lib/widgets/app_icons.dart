// App icon widgets extracted from signup.dart for reuse
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/widgets/anim_util.dart';

class RectangleAppIcon extends StatelessWidget {
  const RectangleAppIcon({
    super.key,
    this.width,
    this.height,
  });

  final double? height, width;

  @override
  Widget build(BuildContext context) {
    return AnimationFadeSlide(
      dy: -0.6,
      dx: 0,
      child: allSettings.value.rectangualrAppLogo != null
          ? CachedNetworkImage(
              imageUrl: "${allSettings.value.baseImageUrl}${allSettings.value.rectangualrAppLogo}",
              width: width ?? 80,
              height: height ?? 80,
              errorWidget: (val, d, e) {
                return Image.asset(
                  'assets/images/logo.png',
                  width: width ?? 80,
                  height: height ?? 80,
                );
              },
            )
          : Image.asset(
              'assets/images/logo.png',
              width: width ?? 80,
              height: height ?? 80,
            ),
    );
  }
}

class RectangleAppNoIcon extends StatelessWidget {
  const RectangleAppNoIcon({
    super.key,
    this.width,
    this.height,
  });

  final double? height, width;

  @override
  Widget build(BuildContext context) {
    return allSettings.value.rectangualrAppLogo != null
        ? CachedNetworkImage(
            imageUrl: "${allSettings.value.baseImageUrl}${allSettings.value.rectangualrAppLogo}",
            width: width ?? 80,
            height: height ?? 80,
            errorWidget: (val, d, e) {
              return Image.asset(
                'assets/images/logo.png',
                width: width ?? 80,
                height: height ?? 80,
              );
            },
          )
        : Image.asset(
            'assets/images/logo.png',
            width: width ?? 80,
            height: height ?? 80,
          );
  }
}

class AppIcon extends StatelessWidget {
  const AppIcon({
    super.key,
    this.width,
    this.height,
    this.isHandlerImage = false,
    this.fit,
  });

  final double? width, height;
  final bool isHandlerImage;
  final BoxFit? fit;

  @override
  Widget build(BuildContext context) {
    return allSettings.value.appLogo != null
        ? CachedNetworkImage(
            imageUrl: "${allSettings.value.baseImageUrl}${allSettings.value.appLogo}",
            width: width ?? 100,
            height: height ?? 100,
            fit: fit,
            errorWidget: (val, d, e) {
              return Image.asset(
                'assets/images/logo.png',
                width: width ?? 100,
                height: height ?? 100,
                fit: fit,
              );
            },
          )
        : Image.asset(
            'assets/images/logo.png',
            width: width ?? 100,
            height: height ?? 100,
            fit: fit,
          );
  }
}

class PhoneNumberInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    // Allow only digits
    final newText = newValue.text.replaceAll(RegExp(r'[^0-9]'), '');
    return TextEditingValue(
      text: newText,
      selection: TextSelection.collapsed(offset: newText.length),
    );
  }
}
