import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:incite/api_controller/shorts_controller.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/widgets/short_wrap.dart';
import 'package:video_player/video_player.dart';

/// Custom Feed Widget consisting video
class VideoWidget extends StatelessWidget {
  const VideoWidget(
      {super.key,
      required this.isLoading,
      required this.controller,
      this.controller2,
      this.onTap,
      this.index = 0,
      required this.blog});

  final bool isLoading;
  final VoidCallback? onTap;
  final int index;
  final Blog blog;
  final VideoPlayerController? controller, controller2;

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        ShortsWrap(
          onMute: () {},
          blog: blog,
          onLike: () {},
          onShare: () {},
          index: index,
          child: controller != null
              ? AspectRatio(aspectRatio: controller!.value.aspectRatio, child: VideoPlayer(controller!))
              : Stack(
                  fit: StackFit.expand,
                  children: [
                    CachedNetworkImage(
                        fit: BoxFit.cover,
                        imageUrl:
                            "${Urls.baseUrl}uploads/short_video/${shortLists.blogModel.blogs[index].backgroundImage}"),
                    Positioned.fill(
                        child: Container(
                            color: Colors.black.customOpacity(0.4),
                            child: const Center(
                                child: CircularProgressIndicator(
                              color: Colors.white,
                            ))))
                  ],
                ),
        ),
        if (controller != null)
          Positioned(
              bottom: 75,
              left: 0,
              right: 0,
              child: VideoProgressIndicator(controller!, allowScrubbing: true)),
      ],
    );
  }
}
