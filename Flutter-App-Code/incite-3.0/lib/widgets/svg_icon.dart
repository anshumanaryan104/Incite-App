import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

class SvgIcon extends StatelessWidget {
  const SvgIcon(
    this.icon, {
    super.key,
    this.height,
    this.width,
    this.fit,
    this.color,
  });
  final double? height, width;
  final Color? color;
  final BoxFit? fit;
  final String icon;

  @override
  Widget build(BuildContext context) {
    return SvgPicture.asset(
      icon,
      height: height,
      width: width,
      fit: fit ?? BoxFit.contain,
      colorFilter: color != null ? ColorFilter.mode(color as Color, BlendMode.srcIn) : null,
    );
  }
}
