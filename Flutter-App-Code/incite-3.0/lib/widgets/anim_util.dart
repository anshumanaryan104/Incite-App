import 'package:flutter/material.dart';
import 'package:incite/utils/theme_util.dart';

// @immutable
class AnimateRepeat extends StatefulWidget {
  final Duration duration;
  final double deltaX;
  final Widget child;
  final Curve curve;

  const AnimateRepeat({
    super.key, 
    this.duration = const Duration(milliseconds: 300),
    this.deltaX = 20,
    this.curve = Curves.bounceOut,
    required this.child,
  });

  @override
  State<AnimateRepeat> createState() => _AnimateRepeatState();
}

class _AnimateRepeatState extends State<AnimateRepeat>
    with SingleTickerProviderStateMixin {
  late AnimationController controller;

  @override
  void initState() {
    super.initState();
    controller = AnimationController(
      duration: widget.duration,
      vsync: this,
    )
      ..forward()
      ..addListener(() {
        if (controller.isCompleted) {
          controller.repeat();
        }
      });
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();  
  }

  /// convert 0-1 to 0-1-0
  double shake(double animation) =>
      2 * (0.5 - (0.5 - widget.curve.transform(animation)).abs());

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: controller,
      builder: (context, child) => Transform.translate(
        offset: Offset(widget.deltaX * controller.value, 0),
        child: child,
      ),
      child: widget.child,
    );
  }
}

class AnimationFadeSlide extends StatefulWidget {
  const AnimationFadeSlide(
      {super.key,
      this.dy = 0,
      this.dx = 1,
      this.duration = 500,
      this.curve,
      this.play = true,
      this.isFade=true,
      this.repeat=false,
      required this.child});
  final Widget child;
  final double dx, dy;
  final bool play,repeat;
  final bool isFade;
  final int duration;
  final Curve? curve;

  @override
  State<AnimationFadeSlide> createState() => _AnimationFadeSlideState();
}

class _AnimationFadeSlideState extends State<AnimationFadeSlide>
    with SingleTickerProviderStateMixin {
  late AnimationController controller;
  late CurvedAnimation animation;
  late Animation<Offset> offset;

  @override
  void initState() {
    controller = AnimationController(
        duration: Duration(milliseconds: widget.duration), vsync: this);
    animation = CurvedAnimation(
        parent: controller, curve: widget.curve ?? Curves.easeIn);
    offset = animation.drive(Tween<Offset>(
            begin: Offset(widget.dx, widget.dy), end: const Offset(0, 0))
        .chain(CurveTween(curve: Curves.ease)));
     
    /*animation.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        controller.reverse();
      } else 
      if (status == AnimationStatus.dismissed) {
        controller.forward();
      }
    });*/

    if(widget.repeat == true){
          controller.repeat(reverse : true);
       } else if (widget.play == true) {
      controller.forward();
    }
    
    super.initState();
  }

  // @override
  // void didUpdateWidget(covariant AnimationFadeSlide oldWidget) {
  //   if (oldWidget.play == widget.play) {
  //    widget.repeat == false ? null 
  //    :widget.repeat == true ?  controller.repeat(reverse : true) : controller.forward();
  //   }
  //   super.didUpdateWidget(oldWidget);
  // }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    var fadeTransition = FadeTransition(opacity: animation, child: widget.child);
    return SlideTransition(
      position: offset,
      child:widget.isFade  == true ? fadeTransition : widget.child,
    );
  }
}


class WidthAnimation extends StatefulWidget {
  const WidthAnimation({super.key,required this.child});
  final Widget child;

  @override
  _WidthAnimationState createState() => _WidthAnimationState();
}

class _WidthAnimationState extends State<WidthAnimation>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _widthAnimation;
  late Animation<Alignment> _alignmentAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 250),
      vsync: this,
      
    );

    _widthAnimation = Tween<double>(begin: 0.35, end:1.0).animate( CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.5, 1.0, curve: Curves.easeInOut), // Smooth transition for height
      ),);

    _alignmentAnimation = Tween<Alignment>(
      begin: Alignment.center,
      end: Alignment.centerLeft,
    ).animate(CurvedAnimation(
        parent: _controller,
        curve: const Interval(0.0, 0.5, curve: Curves.easeInOut), // Smooth transition for alignment
      ),);

    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return SizedBox(
          width: size(context).width,
          child: Center(
            child: AnimatedContainer(
              duration: _controller.duration!,
              alignment: _alignmentAnimation.value,
              curve: Curves.easeInOut,
              width: size(context).width * _widthAnimation.value,
              height: 50,
              decoration: BoxDecoration(
              color: Theme.of(context).cardColor,
                borderRadius: BorderRadius.circular(100)
              ),
              child:  widget.child
            ),
          ),
        );
      },
    );
  }
}

class VoiceAnimation extends StatefulWidget {
  const VoiceAnimation({super.key});

  @override
  _VoiceAnimationState createState() => _VoiceAnimationState();
}

class _VoiceAnimationState extends State<VoiceAnimation>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    )..repeat(reverse: true);
    _animation = Tween<double>(begin: 1.0, end: 0.0).animate(_controller)
      ..addListener(() {
        setState(() {});
      });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        _buildDot(0),
        const SizedBox(width: 5),
        _buildDot(1),
        const SizedBox(width: 5),
        _buildDot(2),
      ],
    );
  }

  Widget _buildDot(int index) {
    double scale = index == 0
        ? _animation.value
        : index == 1
            ? 1.0
            : 1.0 - _animation.value;

    return Transform.scale(
      scale: scale,
      child: Opacity(
        opacity: index == 0
            ? _animation.value
            : index == 1
                ? _animation.value
                : _animation.value,
        child: Container(
          width: 10,
          height: 10,
          decoration: BoxDecoration(
            color: Theme.of(context).primaryColor,
            borderRadius: BorderRadius.circular(5),
          ),
        ),
      ),
    );
  }
}

class AnimationFadeScale extends StatefulWidget {

  const AnimationFadeScale({
       super.key,
      this.duration = 500,
      this.fadeIn = true,
      this.curve,
      this.scale=0.65,
      this.play = true,
      this.repeat=false,
      required this.child
  });
  
  final Widget child;
  final bool fadeIn, play, repeat;
  final int duration;
  final double scale;
  final Curve? curve;
  @override
  State<AnimationFadeScale> createState() => _AnimationFadeScaleState();
}

class _AnimationFadeScaleState extends State<AnimationFadeScale>
    with SingleTickerProviderStateMixin {
  late AnimationController controller;

  late CurvedAnimation animation;
  late Animation<double> scale;

  @override
  void initState() {
    controller = AnimationController(
        duration: Duration(
          milliseconds: widget.duration,
        ),
        vsync: this);
    animation = CurvedAnimation(
        parent: controller, curve: widget.curve ?? Curves.easeIn);
    scale = animation.drive(Tween<double>(
            begin: widget.fadeIn ? 0 :widget.scale, end: widget.fadeIn ? 1 : 0)
        .chain(CurveTween(curve: Curves.ease)));
    /*animation.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        controller.reverse();
      } else 
      if (status == AnimationStatus.dismissed) {
        controller.forward();
      }
    });*/

  if(widget.repeat == true){
          controller.repeat(reverse : true);
       } else if (widget.play == true) {
      controller.forward();
    }
    super.initState();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ScaleTransition(
      scale: scale,
      child: FadeTransition(
          opacity: animation.drive(Tween<double>(
                  begin: widget.fadeIn ? 0.85 : 1, end: widget.fadeIn ? 1 : 0)
              .chain(CurveTween(curve: Curves.ease))),
          child: widget.child),
    );
  }
}
