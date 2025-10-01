// Common widgets extracted from deleted files
import 'package:flutter/material.dart';

class CategoryWrap extends StatelessWidget {
  const CategoryWrap({
    super.key,
    required this.color,
    required this.colored,
    required this.name,
  });

  final Color color;
  final String colored;
  final String name;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color, width: 1),
      ),
      child: Text(
        name,
        style: TextStyle(
          color: color,
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class CirlceDot extends StatelessWidget {
  const CirlceDot({
    super.key,
    required this.radius,
    this.border,
    this.color,
    this.child,
  });

  final double radius;
  final Border? border;
  final Color? color;
  final Widget? child;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: radius,
      height: radius,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: color ?? Colors.transparent,
        border: border,
      ),
      child: child,
    );
  }
}
