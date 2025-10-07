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
                return _buildPolymathText(width ?? 80, height ?? 25);
              },
            )
          : _buildPolymathText(width ?? 80, height ?? 25),
    );
  }

  Widget _buildPolymathText(double width, double height) {
    return ShaderMask(
      shaderCallback: (bounds) => LinearGradient(
        colors: [
          Color(0xFFFF6B6B), // Coral
          Color(0xFFB8A4D4), // Purple
        ],
        begin: Alignment.centerLeft,
        end: Alignment.centerRight,
      ).createShader(bounds),
      child: Text(
        'Polymath',
        style: TextStyle(
          fontSize: height * 0.8,
          fontFamily: 'Roboto',
          fontWeight: FontWeight.w700,
          color: Colors.white,
          letterSpacing: -0.5,
          decoration: TextDecoration.none,
        ),
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
              return _buildPolymathText(width ?? 80, height ?? 25);
            },
          )
        : _buildPolymathText(width ?? 80, height ?? 25);
  }

  Widget _buildPolymathText(double width, double height) {
    return ShaderMask(
      shaderCallback: (bounds) => LinearGradient(
        colors: [
          Color(0xFFFF6B6B), // Coral
          Color(0xFFB8A4D4), // Purple
        ],
        begin: Alignment.centerLeft,
        end: Alignment.centerRight,
      ).createShader(bounds),
      child: Text(
        'Polymath',
        style: TextStyle(
          fontSize: height * 0.8,
          fontFamily: 'Roboto',
          fontWeight: FontWeight.w700,
          color: Colors.white,
          letterSpacing: -0.5,
          decoration: TextDecoration.none,
        ),
      ),
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
              return _buildPolymathText(width ?? 100, height ?? 30);
            },
          )
        : _buildPolymathText(width ?? 100, height ?? 30);
  }

  Widget _buildPolymathText(double width, double height) {
    return ShaderMask(
      shaderCallback: (bounds) => LinearGradient(
        colors: [
          Color(0xFFFF6B6B), // Coral
          Color(0xFFB8A4D4), // Purple
        ],
        begin: Alignment.centerLeft,
        end: Alignment.centerRight,
      ).createShader(bounds),
      child: Text(
        'Polymath',
        style: TextStyle(
          fontSize: height * 0.9,
          fontFamily: 'Roboto',
          fontWeight: FontWeight.w700,
          color: Colors.white,
          letterSpacing: -0.8,
          decoration: TextDecoration.none,
        ),
      ),
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
