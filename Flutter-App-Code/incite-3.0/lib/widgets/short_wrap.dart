import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/svg_icon.dart';
import 'package:provider/provider.dart';

class ShortsWrap extends StatefulWidget {
  const ShortsWrap(
      {super.key,
      required this.child,
      required this.onMute,
      required this.blog,
      required this.onShare,
      required this.onLike,
      this.index = 0});
  final Widget child;
  final Blog blog;
  final int index;
  final VoidCallback? onLike, onShare, onMute;

  @override
  State<ShortsWrap> createState() => _ShortsWrapState();
}

class _ShortsWrapState extends State<ShortsWrap> {
  bool isExpand = false;

  @override
  Widget build(BuildContext context) {
    var provider = Provider.of<AppProvider>(context, listen: false);
    return Stack(
      fit: StackFit.expand,
      children: [
        widget.child,
        Positioned(
            bottom: 0,
            right: 0,
            left: 0,
            child: Container(
              width: size(context).width,
              decoration: const BoxDecoration(
                  gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [Colors.transparent, Colors.black54])),
              padding: const EdgeInsets.only(left: 15, right: 0, bottom: 30),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  shortsDescription(context),
                  // if (preload.controllers[widget.index] != null)
                  shortsActions(provider),
                ],
              ),
            )),
      ],
    );
  }

  GestureDetector shortsDescription(BuildContext context) {
    return GestureDetector(
      onTap: () {
        isExpand = !isExpand;
        setState(() {});
      },
      child: SizedBox(
          width: size(context).width / 1.33,
          child: RichText(
              maxLines: isExpand == true ? 12 : 3,
              overflow: TextOverflow.ellipsis,
              text: TextSpan(
                children: [
                  TextSpan(
                    text: isExpand == false && widget.blog.title!.length > 99
                        ? widget.blog.title.toString().substring(0, 100)
                        : widget.blog.title.toString(),
                    style: Theme.of(context).textTheme.bodySmall!.copyWith(
                          fontSize: 17,
                          color: Colors.white,
                        ),
                  ),
                  if (widget.blog.title!.length > 89)
                    TextSpan(
                        text: isExpand == true ? "...see less" : '...see more',
                        style: Theme.of(context)
                            .textTheme
                            .labelSmall!
                            .copyWith(fontSize: 17, color: Colors.white, fontWeight: FontWeight.w600))
                ],
              ))),
    );
  }

  Expanded shortsActions(AppProvider provider) {
    return Expanded(
      child: Column(crossAxisAlignment: CrossAxisAlignment.center, children: [
        // InkResponse(
        //   key: ValueKey(preload.controllers[widget.index]!
        //       .value.isPlaying),
        //   onTap: () {
        //     if (preload.controllers[widget.index]!.value
        //             .isPlaying ==
        //         true) {
        //       preload.pauseVideoAtIndex(widget.index);
        //     } else {
        //       preload.playVideoAtIndex(widget.index);
        //     }
        //     setState(() {});
        //   },
        //   child: BlurWidget(
        //     width:  42,
        //     height: 42,
        //     padding: const EdgeInsets.all(13),
        //     child: SvgIcon(
        //       preload.controllers[widget.index]!.value
        //                   .isPlaying ==
        //               false
        //           ? 'assets/svg/play-button.svg'
        //           : 'assets/svg/pause.svg',
        //       color: Colors.white,
        //       width:  20,
        //     ),
        //   ),
        // ),
        // const SizedBox(height: 16),
        InkResponse(
          onTap: () {
            // provider.setMute = widget.index;
            // setState(() {});
          },
          child: BlurWidget(
            width: 42,
            height: 42,
            padding: const EdgeInsets.all(10),
            child: Icon(
              provider.isMute == true ? Icons.volume_off_rounded : Icons.volume_up_rounded,
              color: Colors.white,
              size: 20,
            ),
          ),
        ),
        const SizedBox(height: 16),
        InkResponse(
            onTap: () async {
              shareText("${Urls.baseServer}shorts/${widget.blog.id}");
              provider.addShareData(widget.blog.id!.toInt());
            },
            radius: 28,
            child: const BlurWidget(
              width: 42,
              height: 42,
              padding: EdgeInsets.all(10),
              child: SvgIcon('assets/svg/share.svg', color: Colors.white),
            )),
        const SizedBox(height: 16),
      ]),
    );
  }
}

class BlurWidget extends StatelessWidget {
  const BlurWidget(
      {super.key,
      required this.child,
      this.padding,
      this.radius,
      this.color,
      this.width,
      this.blur,
      this.isWrapWidth = false,
      this.border,
      this.height});

  final double? radius, width, height, blur;
  final Widget child;
  final Color? color;
  final bool? isWrapWidth;
  final BoxBorder? border;
  final EdgeInsetsGeometry? padding;

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInSine,
        width: isWrapWidth == true ? null : width ?? 40,
        height: isWrapWidth == true ? null : height ?? 40,
        decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(radius ?? 100),
            color: color ?? Colors.black.customOpacity(0.2),
            border: border ?? Border.all(width: 0.5, color: Colors.white.customOpacity(0.2)),
            boxShadow: color != null
                ? null
                : const [BoxShadow(blurRadius: 25, spreadRadius: -2, color: Colors.black12)]),
        child: ClipRRect(
            borderRadius: BorderRadius.circular(radius ?? 100),
            child: BackdropFilter(
                filter: ImageFilter.blur(sigmaX: blur ?? 10, sigmaY: blur ?? 10),
                child: Padding(
                  padding: padding ?? const EdgeInsets.all(10.0),
                  child: child,
                ))));
  }
}
