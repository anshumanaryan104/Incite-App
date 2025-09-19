import 'dart:async';
import 'dart:developer';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/shorts_controller.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/pages/main/widgets/text.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/full_screen.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/short_wrap.dart';
import 'package:flutter_preload_videos/models/play_video_from.dart';
import 'package:flutter_preload_videos/video_extraction.dart';
import 'package:incite/widgets/svg_icon.dart';
import 'package:provider/provider.dart';
import 'package:video_player/video_player.dart';
import 'package:visibility_detector/visibility_detector.dart';
import '../../model/blog.dart';

class VideoQalityUrls {
  int quality;
  String url;
  VideoQalityUrls({
    required this.quality,
    required this.url,
  });

  @override
  String toString() => 'VideoQalityUrls(quality: $quality, urls: $url)';
}

class ShortPlayAnyVideo extends StatefulWidget {
  const ShortPlayAnyVideo(
      {super.key,
      this.controller,
      this.aspectRatio,
      this.isYoutube = false,
      this.isShortVideo = false,
      this.onChangedOrientation,
      this.onVideoLoad,
      this.isAutoPlay = false,
      this.model,
      this.index,
      this.currIndex});

  final double? aspectRatio;
  final Blog? model;
  final VideoPlayerController? controller;
  final ValueChanged? onChangedOrientation, onVideoLoad;
  final bool isShortVideo, isYoutube, isAutoPlay;
  final int? index, currIndex;

  @override
  State<ShortPlayAnyVideo> createState() => _ShortPlayAnyVideoState();
}

class _ShortPlayAnyVideoState extends State<ShortPlayAnyVideo> {
  late AppProvider provider;
  bool fullScreen = false;
  VideoPlayerController? videocontroller;

  bool showOverlay = false;

  Timer? hoverOverlayTimer;

  bool isLoaded = true;

  bool isExpand = false;

  void isShowOverlay(bool val, {Duration? delay}) {
    if (val == true) {
      showOverlay = true;
      setState(() {});
      hoverOverlayTimer = Timer(const Duration(seconds: 3), () {
        showOverlay = val;
        isShowOverlay(false);
        if (mounted) {
          setState(() {});
        }
      });
    } else {
      showOverlay = false;
      hoverOverlayTimer?.cancel();
      if (mounted) {
        setState(() {});
      }
    }
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      var videoExtract =
          widget.model!.videoUrl!.length < 100 ? await extractYoutube(widget.index ?? 0) : null;

      var isVideoExtracted = widget.model != null ? widget.model!.videoUrl!.length < 100 : false;

      var youtubeLoaded = isVideoExtracted
          ? VideoPlayerController.networkUrl(Uri.parse(videoExtract ?? ""))
          : VideoPlayerController.networkUrl(
              Uri.parse(widget.model != null ? widget.model!.videoUrl ?? "" : ""));

      videocontroller = youtubeLoaded;

      videocontroller!.initialize().then((_) {
        if (widget.isAutoPlay == appThemeModel.value.isAutoPlay.value && widget.currIndex == widget.index) {
          videocontroller!.play();
          videocontroller!.setLooping(true);
        } else if (appThemeModel.value.isAutoPlay.value == false && widget.isShortVideo == false) {
          videocontroller!.pause();
        }

        if (isVideoExtracted) {
          if (widget.isShortVideo == false) {
            blogListHolder.getList().blogs[widget.index ?? 0].videoUrl = videoExtract;
          } else if (widget.isShortVideo == true) {
            shortLists.blogModel.blogs[widget.index ?? 0].videoUrl = videoExtract;
          }
        }
        setState(() {});
      });
      isLoaded = false;
      setState(() {});
    });
  }

  Future<String?> extractYoutube(int i) async {
    log(widget.model!.videoUrl.toString());
    var urlss = await getVideoQualityUrlsFromYoutube(
      PlayVideoFrom.youtube(widget.model!.videoUrl ?? "").dataSource ?? "",
      false,
    );

    final youtubeurl = urlss != null && urlss.isNotEmpty
        ? await getUrlFromVideoQualityUrls(
            qualityList: [720, 480, 360, 240],
            videoUrls: urlss,
            initQuality: 360,
          )
        : null;

    return youtubeurl;
  }

  @override
  Widget build(BuildContext context) {
    var getMute = context.read<AppProvider>().isMute;
    return VisibilityDetector(
      key: ObjectKey(widget.model!.videoUrl),
      onVisibilityChanged: (visibility) {
        var visiblePercentage = visibility.visibleFraction * 100;

        if (visiblePercentage >= 50.0 && mounted && videocontroller != null) {
          if (widget.currIndex == widget.index) {
            if (appThemeModel.value.isAutoPlay.value == true) {
              videocontroller!.play();
            }
          }
        } else if (visiblePercentage < 50.0 && videocontroller != null) {
          videocontroller!.pause();
        }
      },
      child: RepaintBoundary(
        child: OrientationBuilder(builder: (context, orientation) {
          return Stack(
            children: [
              if (videocontroller != null && videocontroller!.value.isInitialized)
                SizedBox(
                  width: size(context).width,
                  height: orientation == Orientation.landscape
                      ? size(context).height
                      : widget.aspectRatio != null
                          ? null
                          : orientation == Orientation.landscape
                              ? size(context).height
                              : size(context).height * 0.3,
                  child: AspectRatio(
                    aspectRatio: widget.aspectRatio ??
                        (widget.isShortVideo == true &&
                                ((widget.model != null && widget.model!.videoUrl!.contains('shorts')))
                            ? 9 / 16
                            : videocontroller!.value.aspectRatio),
                    child: VideoPlayer(videocontroller!),
                  ),
                ),
              if (videocontroller != null)
                ShowOverlay(
                  controller: videocontroller!,
                  showLay: showOverlay,
                  blog: widget.model!,
                  isAds: widget.isShortVideo,
                  onTap: () {
                    if (videocontroller!.value.isPlaying == true) {
                      videocontroller!.pause();
                      showOverlay = true;
                      hoverOverlayTimer?.cancel();
                    } else {
                      videocontroller!.play();
                      isShowOverlay(true);
                    }
                    setState(() {});
                  },
                  isFullScreen: orientation == Orientation.landscape,
                  onOverlayTap: () {
                    if (showOverlay == false) {
                      isShowOverlay(true);
                    } else {
                      isShowOverlay(false);
                    }
                  },
                  // onFullScreen: widget.onChangedOrientation??,
                  onFullScreen: () {
                    if (widget.onChangedOrientation == null) {
                      Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (context) => FullScreenVideo(controller: videocontroller!)));
                    } else {
                      var orientation = MediaQuery.of(context).orientation == Orientation.landscape;

                      if (orientation == false) {
                        widget.onChangedOrientation!(true);
                      } else {
                        widget.onChangedOrientation!(false);
                      }
                      // Navigator.pop(context);
                    }
                  },
                  isPlay: videocontroller!.value.isPlaying,
                ),
              if (videocontroller != null && widget.isShortVideo == true)
                Positioned(
                  bottom: 0,
                  right: 0,
                  left: 0,
                  child: Container(
                    width: size(context).width,
                    decoration: BoxDecoration(
                        gradient:
                            LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [
                      isExpand ? Colors.transparent : Colors.transparent,
                      isExpand ? Colors.black87 : Colors.transparent,
                      Colors.black87
                    ])),
                    padding: const EdgeInsets.only(left: 15, right: 0, bottom: 30),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      mainAxisAlignment: MainAxisAlignment.start,
                      children: [
                        Expanded(
                          flex: 4,
                          child: GestureDetector(
                              onTap: () {
                                isExpand = !isExpand;
                                setState(() {});
                              },
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(widget.model!.title ?? "",
                                      overflow: isExpand == true ? null : TextOverflow.ellipsis,
                                      maxLines: isExpand == false ? 2 : null,
                                      style: Theme.of(context)
                                          .textTheme
                                          .headlineMedium!
                                          .copyWith(fontSize: 17, color: Colors.white)),
                                  const SizedBox(height: 4),
                                  Description(
                                      maxLines: isExpand == false ? 2 : null,
                                      color: Colors.white70,
                                      model: widget.model),
                                ],
                              )),
                        ),
                        if (videocontroller != null && widget.isShortVideo == true)
                          Expanded(
                            child: Column(crossAxisAlignment: CrossAxisAlignment.center, children: [
                              InkResponse(
                                onTap: () {
                                  if (videocontroller!.value.volume == 0) {
                                    videocontroller!.setVolume(100);
                                  } else {
                                    videocontroller!.setVolume(0);
                                  }
                                  context.read<AppProvider>().setMute = videocontroller!.value.volume == 0;

                                  setState(() {});
                                },
                                child: BlurWidget(
                                  width: 42,
                                  height: 42,
                                  padding: const EdgeInsets.all(10),
                                  child: Icon(
                                    key: ValueKey(getMute),
                                    getMute == true ? Icons.volume_off_rounded : Icons.volume_up_rounded,
                                    color: Colors.white,
                                    size: 20,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 16),
                              InkResponse(
                                  onTap: () async {
                                    shareText("${Urls.baseServer}shorts/${widget.model!.id}");
                                    context.watch<AppProvider>().addShortsShareData(widget.model!.id ?? 0);
                                  },
                                  radius: 28,
                                  child: const BlurWidget(
                                    width: 42,
                                    height: 42,
                                    padding: EdgeInsets.all(10),
                                    child: SvgIcon('assets/svg/share.svg', color: Colors.white),
                                  )),
                              const SizedBox(height: 4),
                            ]),
                          ),
                      ],
                    ),
                  ),
                ),
              if (isLoaded == true)
                Positioned.fill(
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      Container(
                        foregroundDecoration: const BoxDecoration(
                          color: Colors.black54,
                        ),
                        child: CachedNetworkImage(
                            imageUrl: widget.isShortVideo == true
                                ? "${Urls.baseServer}uploads/short_video/${widget.model!.backgroundImage ?? ''}"
                                : widget.model!.images![0],
                            errorWidget: (context, url, error) {
                              return Padding(
                                padding: const EdgeInsets.all(50.0),
                                child: Image.asset(
                                  'assets/images/app_icon.png',
                                ),
                              );
                            },
                            fit: BoxFit.cover),
                      ),
                      const Align(
                          alignment: Alignment.center, child: Center(child: CircularProgressIndicator())),
                    ],
                  ),
                ),
              if (videocontroller != null)
                Positioned(
                    left: 0,
                    right: 0,
                    bottom: 0,
                    child: VideoProgressIndicator(
                        padding: const EdgeInsets.only(top: 5), videocontroller!, allowScrubbing: true))
            ],
          );
        }),
      ),
    );
  }

  @override
  void dispose() {
    if (videocontroller != null) {
      videocontroller!.pause();
      videocontroller = null;
    }
    super.dispose();
  }
}

class ShowOverlay extends StatefulWidget {
  const ShowOverlay(
      {super.key,
      this.isPlay = false,
      this.onTap,
      required this.controller,
      this.onOverlayTap,
      this.onFullScreen,
      this.isFullScreen = false,
      this.showLay = false,
      this.isAds = false,
      required this.blog});

  final bool isPlay, showLay, isFullScreen, isAds;
  final VideoPlayerController controller;
  final Blog blog;
  final VoidCallback? onTap, onOverlayTap, onFullScreen;

  @override
  State<ShowOverlay> createState() => _ShowOverlayState();
}

class _ShowOverlayState extends State<ShowOverlay> {
  bool isExpand = false;

  @override
  Widget build(BuildContext context) {
    if (widget.showLay == true) {
      return Positioned.fill(
        child: GestureDetector(
          onTap: widget.onOverlayTap,
          child: Container(
            color: Colors.black.customOpacity(0.53),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Spacer(flex: 1),
                Center(
                  child: InkResponse(
                    key: ValueKey(widget.isPlay),
                    onTap: widget.onTap,
                    child: BlurWidget(
                      width: 60,
                      height: 60,
                      padding: EdgeInsets.all(widget.isPlay == true ? 18 : 20),
                      child: SvgIcon(
                        widget.isPlay == false ? 'assets/svg/play-button.svg' : 'assets/svg/pause.svg',
                        color: Colors.white,
                        width: 16,
                      ),
                    ),
                  ),
                ),
                const Spacer(),
                if (widget.isAds == false)
                  Padding(
                    padding: const EdgeInsets.only(left: 16.0, right: 8),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.start,
                      children: [
                        DurationWidget(
                            key: ValueKey(widget.controller.value.position.inSeconds),
                            controller: widget.controller),
                        const Spacer(),
                        IconButton(
                          onPressed: widget.onFullScreen,
                          icon: widget.isFullScreen == false
                              ? const Icon(Icons.fullscreen, color: Colors.white)
                              : const Icon(Icons.fullscreen_exit, color: Colors.white),
                        )
                      ],
                    ),
                  ),
              ],
            ),
          ),
        ),
      );
    } else {
      return Positioned.fill(
          child: GestureDetector(
              onTap: widget.onOverlayTap,
              child: Container(
                color: Colors.transparent,
              )));
    }
  }
}
