import 'package:flutter/material.dart';

class GradientText extends StatelessWidget {
  const GradientText(
    this.text, {
    super.key,
    required this.gradient,
    this.style,
  });

  final String text;
  final TextStyle? style;
  final Gradient gradient;

  @override
  Widget build(BuildContext context) {
    return ShaderMask(
      blendMode: BlendMode.srcIn,
      shaderCallback: (bounds) => gradient.createShader(
        Rect.fromLTWH(0, 0, bounds.width, bounds.height),
      ),
      child: Text(text, style: style),
    );
  }
}

// ignore: must_be_immutable
class GradientWidget extends StatelessWidget {
  GradientWidget({
    super.key,
    required this.child,
    this.gradient,
  });

  final Widget child;
  Gradient? gradient;

  @override
  Widget build(BuildContext context) {
    return gradient != null
        ? ShaderMask(
            blendMode: BlendMode.srcIn,
            shaderCallback: (bounds) => gradient!.createShader(
                  Rect.fromLTWH(0, 0, bounds.width, bounds.height),
                ),
            child: child)
        : child;
  }
}
