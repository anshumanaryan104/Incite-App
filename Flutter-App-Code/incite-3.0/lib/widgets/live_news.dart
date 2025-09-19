import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/svg_icon.dart';
import 'package:incite/widgets/tap.dart';

import 'shimmer.dart';

class ListShimmer extends StatelessWidget {
  const ListShimmer({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        children: [
          const ShimmerLoader(
            height: 70,
            width: 70,
            borderRadius: 100,
          ),
          const SizedBox(width: 12),
          Column(
            children: [
              ShimmerLoader(width: size(context).width / 2, height: 20),
              SizedBox(height: height10(context)),
              ShimmerLoader(width: size(context).width / 2, height: 20)
            ],
          )
        ],
      ),
    );
  }
}

class LiveWidget extends StatelessWidget {
  const LiveWidget({
    super.key,
    this.title,
    this.fontWeight,
    this.onTap,
    this.playState = false,
    this.padding,
    this.image,
    this.radius,
    this.isPlay = false,
    this.onShare,
  });

  final String? title, image;
  final bool isPlay, playState;
  final EdgeInsetsGeometry? padding;
  final FontWeight? fontWeight;

  final double? radius;
  final VoidCallback? onTap, onShare;

  @override
  Widget build(BuildContext context) {
    return TapInk(
        splash: ColorUtil.textgrey,
        onTap: onTap ?? () {},
        child: Container(
          decoration: BoxDecoration(
              border: Border(
                  bottom: BorderSide(
                      width: 1,
                      color: dark(context)
                          ? ColorUtil.textgrey.customOpacity(0.3)
                          : ColorUtil.textgrey.customOpacity(0.1)))),
          margin: const EdgeInsets.only(left: 20, right: 20),
          padding: padding ?? const EdgeInsets.only(top: 16, bottom: 16),
          child: Row(
            children: [
              CircleAvatar(
                radius: radius ?? 32,
                backgroundImage: CachedNetworkImageProvider(image ?? Img.tv),
              ),
              const SizedBox(width: 15),
              Expanded(
                child: Text(title ?? 'Tv',
                    maxLines: 2,
                    style: TextStyle(
                        fontSize: 17,
                        color: textColor(context),
                        fontFamily: 'Roboto',
                        overflow: TextOverflow.ellipsis,
                        fontWeight: fontWeight ?? FontWeight.w500)),
              ),
              InkResponse(
                onTap: onShare,
                child: CircleAvatar(
                  backgroundColor: Theme.of(context).cardColor,
                  child: SvgIcon(SvgImg.share, color: dark(context) ? Colors.white : Colors.black),
                ),
              )
            ],
          ),
        ));
  }
}
