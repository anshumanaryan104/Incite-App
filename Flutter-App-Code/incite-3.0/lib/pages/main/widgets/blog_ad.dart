import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/main/web_view.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/button.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/short_wrap.dart';
import 'package:provider/provider.dart';
import 'package:video_player/video_player.dart';
import 'package:visibility_detector/visibility_detector.dart';
import '../../../model/blog.dart';

class BlogAd extends StatefulWidget {
  const BlogAd({super.key, this.isBack = false, this.model, this.onTap, this.currIndex, this.index});
  final VoidCallback? onTap;
  final Blog? model;
  final int? index, currIndex;
  final bool isBack;

  @override
  State<BlogAd> createState() => _BlogAdState();
}

class _BlogAdState extends State<BlogAd> {
  @override
  Widget build(BuildContext context) {
    var provider = Provider.of<AppProvider>(context, listen: false);
    return SizedBox(
      width: size(context).width,
      height: size(context).height,
      child: GestureDetector(
        onTap: widget.onTap,
        child: Scaffold(
          body: SafeArea(
            left: false,
            right: false,
            child: Center(
              child: SizedBox(
                height: size(context).height / 1.25,
                child: Stack(fit: StackFit.expand, children: [
                  if (widget.model!.images != null && widget.model!.mediaType == 'image')
                    CachedNetworkImage(
                        imageUrl: widget.model!.media ?? '',
                        errorWidget: (context, url, error) {
                          return Padding(
                            padding: const EdgeInsets.all(50.0),
                            child: Image.asset(
                              'assets/images/app_icon.png',
                            ),
                          );
                        },
                        fit: BoxFit.cover)
                  else if (widget.model!.videoUrl != null && widget.model!.mediaType == 'video_url')
                    PlayAnyVideoPlayer(
                      model: widget.model,
                      // aspectRatio: 9/16,
                      isAds: true,
                      isCurrentlyOpened: widget.currIndex == widget.index,
                      isShortVideo: true,
                    )
                  else if (widget.model!.media!.isNotEmpty && widget.model!.mediaType == 'video')
                    MyVideoPlayer(
                        isCurrentlyOpened: widget.currIndex == widget.index, url: widget.model!.media ?? ""),
                  if (widget.model!.sourceLink != '')
                    Positioned(
                        left: 24,
                        right: 24,
                        bottom: height10(context) * 3,
                        child: SizedBox(
                            child: IconTextButton(
                          text: widget.model!.sourceName ?? 'Explore Now',
                          splash: ColorUtil.whiteGrey,
                          width: size(context).width,
                          trailIcon: SvgPicture.asset(
                            SvgImg.arrowRight,
                            colorFilter: colorFilterMode(context, color: ColorUtil.textblack),
                            width: 20,
                            height: 20,
                          ),
                          color: ColorUtil.whiteGrey,
                          style: const TextStyle(
                              fontFamily: 'Roboto',
                              fontSize: 18,
                              color: ColorUtil.textblack,
                              fontWeight: FontWeight.w500),
                          onTap: () {
                            provider.adsClickData(widget.model!.id ?? 0);

                            Navigator.push(
                                context,
                                CupertinoPageRoute(
                                    builder: (context) => CustomWebView(
                                        onTap: () {
                                          Navigator.pop(context);
                                        },
                                        url: widget.model!.sourceLink.toString())));
                          },
                        ))),
                  if ((widget.model!.videoUrl!.isNotEmpty && widget.model!.mediaType == 'video_url') ||
                      (widget.model!.media!.isNotEmpty && widget.model!.mediaType == 'video'))
                    Positioned(
                        bottom: 10,
                        right: 16,
                        child: Container(
                          decoration:
                              BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(4)),
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          child: const RectangleAppIcon(
                            width: 70,
                            height: 25,
                          ),
                        )),
                  Positioned(
                      top: 10,
                      right: 16,
                      child: BlurWidget(
                          isWrapWidth: true,
                          padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 12),
                          child: Center(
                            child: Row(
                              children: [
                                CircleAvatar(
                                  radius: 3,
                                  backgroundColor: Theme.of(context).primaryColor,
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  allMessages.value.promotedAd ?? 'PROMOTED',
                                  style: Theme.of(context).textTheme.bodySmall!.copyWith(color: Colors.white),
                                ),
                              ],
                            ),
                          )))
                ]),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class MyVideoPlayer extends StatefulWidget {
  const MyVideoPlayer({super.key, required this.url, this.isCurrentlyOpened = false});
  final String url;
  final bool isCurrentlyOpened;

  @override
  State<MyVideoPlayer> createState() => _MyVideoPlayerState();
}

class _MyVideoPlayerState extends State<MyVideoPlayer> {
  late VideoPlayerController controller;

  @override
  void initState() {
    super.initState();

    controller = VideoPlayerController.networkUrl(Uri.parse(widget.url));
    controller.initialize().then((_) {
      // precache the video
      controller.setLooping(true);
      // controller.play();
      setState(() {});
      // controller.pause();
    });
  }

  @override
  void dispose() {
    controller.pause();
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      fit: StackFit.expand,
      children: [
        VisibilityDetector(
          key: Key(widget.key.toString()),
          onVisibilityChanged: (visibility) {
            var visiblePercentage = visibility.visibleFraction * 100;
            if (visiblePercentage < 1 && mounted) {
              controller.pause();
              setState(() {});
              //pausing  functionality
            } else if (mounted && widget.isCurrentlyOpened == true) {
              controller.play();
              setState(() {});
              //playing  functionality
            }
          },
          child: VideoPlayer(
            controller,
          ),
        ),
      ],
    );
  }
}
