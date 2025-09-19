import 'package:flutter/material.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:incite/widgets/tap.dart';
import '../../../widgets/anim.dart';

class RadiusBox extends StatefulWidget {
  const RadiusBox(
      {super.key,
      required this.title,
      this.isGradient = false,
      this.dur = 100,
      this.padding,
      this.onTap,
      this.isSelected = false,
      this.color,
      this.index = 1});
  final String title;
  final bool isSelected, isGradient;
  final int index, dur;
  final EdgeInsetsGeometry? padding;
  final VoidCallback? onTap;
  final Color? color;

  @override
  State<RadiusBox> createState() => _RadiusBoxState();
}

class _RadiusBoxState extends State<RadiusBox> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();

    // Animation controller with a duration of 2 seconds
    _controller = AnimationController(
      duration: const Duration(seconds: 1),
      vsync: this,
    );

    // Tween for the gradient stops
    _animation = Tween<double>(begin: 0.0, end: 1.0).animate(_controller);

    // Start the animation
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimationFadeSlide(
      duration: widget.dur * widget.index,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        decoration: BoxDecoration(
            color: widget.isSelected
                ? ColorUtil.textblack
                : dark(context)
                    ? Theme.of(context).cardColor
                    : ColorUtil.whiteGrey,
            borderRadius: BorderRadius.circular(100),
            gradient: widget.isGradient
                ? LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, stops: [
                    _animation.value - 1.0,
                    _animation.value
                  ], colors: [
                    dark(context)
                        ? widget.isSelected
                            ? widget.color != null
                                ? generateShades(widget.color ?? Colors.white, 2)
                                : Theme.of(context).colorScheme.secondary
                            : ColorUtil.blackGrey
                        : widget.isSelected
                            ? widget.color != null
                                ? widget.color!.customOpacity(0.4)
                                : Theme.of(context).primaryColor
                            : Colors.white,
                    widget.isSelected
                        ? widget.color ?? Theme.of(context).primaryColor
                        : dark(context)
                            ? ColorUtil.blackGrey
                            : ColorUtil.white
                  ])
                : null),
        child: TapInk(
          radius: 100,
          onTap: () {
            _controller.forward();
            if (widget.onTap != null) {
              widget.onTap!.call();
            }
          },
          child: Padding(
            padding: widget.padding ?? const EdgeInsets.symmetric(horizontal: 15, vertical: 10),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  widget.title,
                  style: TextStyle(
                    fontFamily: 'Roboto',
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    // shadows: const [
                    //   BoxShadow(
                    //     offset: Offset(0, 0.2),
                    //     spreadRadius: -4,
                    //     blurRadius: 14,
                    //     color: Colors.black26
                    //   )
                    // ],
                    color: widget.isSelected == false
                        ? dark(context)
                            ? ColorUtil.white
                            : ColorUtil.textblack
                        : Colors.white,
                  ),
                ),
                const SizedBox(width: 6),
                Container(
                  width: 20,
                  height: 20,
                  decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border:
                          Border.all(width: 1, color: widget.isSelected ? Colors.white : ColorUtil.textgrey)),
                  child: AnimateIcon(
                    child: widget.isSelected == false
                        ? const Icon(null, key: ValueKey(67))
                        : Icon(Icons.done_rounded,
                            key: const ValueKey(76),
                            size: 12,
                            color: widget.isSelected ? Colors.white : ColorUtil.textgrey),
                  ),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }

  Color generateShades(Color color, int steps) {
    final List<Color> shades = [];

    for (int i = 1; i <= steps; i++) {
      final double fraction = i / steps;
      final Color? shadeColor = Color.lerp(Colors.white, color, fraction);

      shades.add(shadeColor!);
    }

    return shades.first;
  }
}
