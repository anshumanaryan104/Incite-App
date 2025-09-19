import 'dart:async';
import 'dart:developer';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/pages/main/widgets/text.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/short_wrap.dart';

import 'package:incite/widgets/svg_icon.dart';
import 'package:incite/widgets/youtube_support_full_play.dart';
import 'package:provider/provider.dart';
import 'package:video_player/video_player.dart';
import 'package:visibility_detector/visibility_detector.dart';
import 'package:webviewtube/webviewtube.dart';
import '../../model/blog.dart';

class PlayAnyVideoPlayer extends StatefulWidget {
  const PlayAnyVideoPlayer(
      {super.key,
      this.videoUrl,
      this.controller,
      this.aspectRatio,
      this.isCurrentlyOpened = false,
      this.isShortVideo = false,
      this.onChangedOrientation,
      this.onChanged,
      this.isLive = false,
      this.isAds = false,
      this.isQuote = false,
      this.startAt = 0,
      this.isPlayCenter = false,
      this.model,
      this.isNormalVideo = false});

  final String? videoUrl;
  final double? aspectRatio;
  final Blog? model;
  final bool isAds, isLive, isCurrentlyOpened, isQuote;
  final int? startAt;
  final WebviewtubeController? controller;
  final ValueChanged? onChangedOrientation, onChanged;
  final bool isNormalVideo, isPlayCenter;
  final bool isShortVideo;

  @override
  State<PlayAnyVideoPlayer> createState() => _PlayAnyVideoPlayerState();
}

class _PlayAnyVideoPlayerState extends State<PlayAnyVideoPlayer> with WidgetsBindingObserver {
  bool fullScreen = false;
  bool playVideoOnDifferentPage = false;
  bool isLiveError = false;

  bool showOverlay = false;

  Timer? hoverOverlayTimer;

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

  late WebviewtubeController controller;
  late WebviewtubeOptions options;

  bool isExpand = false;

  @override
  void initState() {
    WidgetsBinding.instance.addObserver(this);
    options = WebviewtubeOptions(
        showControls: !widget.isShortVideo,
        forceHd: false,
        currentTimeUpdateInterval: widget.isShortVideo == true ? 170 : 50,
        mute: context.read<AppProvider>().isMute,
        loop: appThemeModel.value.isAutoPlay.value,
        interfaceLanguage: languageCode.value.language ?? "en");
    controller = widget.controller ??
        WebviewtubeController(
          options: options,
          onPlayerReady: appThemeModel.value.isAutoPlay.value == false &&
                  (widget.isShortVideo == false || widget.isAds == true)
              ? null
              : () {
                  if (widget.onChangedOrientation == null) {
                    controller.pause();
                    playVideoOnDifferentPage = false;

                    if (widget.isCurrentlyOpened == true &&
                        widget.isShortVideo == false &&
                        widget.isAds == false) {
                      controller.play();
                    }

                    // it will play initially only in the case of live news
                    if (widget.isLive == true) {
                      controller.play();
                    } else if (widget.isCurrentlyOpened == true &&
                        widget.isShortVideo == true &&
                        widget.isAds == false) {
                      // it will play initially only in the case of shorts
                      log("[plating]");
                      controller.play();
                    }
                  } else if (widget.onChangedOrientation != null ||
                      widget.isShortVideo == true ||
                      widget.isLive == true) {
                    if (widget.isAds == true) {
                      controller.play();
                    } else if (appThemeModel.value.isAutoPlay.value == true) {
                      log("[plating]");
                      controller.play();
                    }

                    playVideoOnDifferentPage = false;
                  }
                  setState(() {});
                },
          onPlayerError: (error) {
            if (error.errorCode == 150) {
              if (widget.isLive == false) {
                if (widget.onChanged != null) {
                  widget.onChanged!(true);
                  // -------- if video not played then switch to another player ------
                }
                playVideoOnDifferentPage = true;
              }

              if (widget.isLive == true) {
                isLiveError = true;
                setState(() {});
              }
            }
          },
          onPlayerWebResourceError: (error) {},
        );

    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      if (widget.onChangedOrientation != null) {
        widget.onChangedOrientation!(true);
      }
    });

    super.initState();
  }

  @override
  void dispose() {
    // If a controller is passed to the player, remember to dispose it when
    // it's not in need.
    controller.pause();
    playVideoOnDifferentPage = false;
    controller.dispose();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didUpdateWidget(covariant PlayAnyVideoPlayer oldWidget) {
    if (widget.isCurrentlyOpened != oldWidget.isCurrentlyOpened && widget.isCurrentlyOpened == false) {
      controller.pause();
      setState(() {});
    }

    super.didUpdateWidget(oldWidget);
  }

  @override
  Widget build(BuildContext context) {
    return VisibilityDetector(
      key: ObjectKey(widget.videoUrl.toString()),
      onVisibilityChanged: appThemeModel.value.isAutoPlay.value == false &&
              (widget.isShortVideo == false || widget.isAds == true)
          ? (visibility) {
              var visiblePercentage = visibility.visibleFraction * 100;
              if (visiblePercentage < 50.0) {
                controller.pause();
              }
            }
          : (visibility) {
              var visiblePercentage = visibility.visibleFraction * 100;

              if (visiblePercentage >= 50.0 && mounted && widget.isCurrentlyOpened == true) {
                controller.play();

                if (widget.startAt != null) {
                  controller.seekTo(Duration(seconds: controller.value.position.inSeconds));
                }

                if (context.read<AppProvider>().isMute == true) {
                  controller.mute();
                } else {
                  controller.unMute();
                }

                // isShowOverlay(true);
              } else if (visiblePercentage < 50.0) {
                controller.pause();
              }
            },
      child: RepaintBoundary(
        child: OrientationBuilder(builder: (context, orientation) {
          var height2 = widget.isShortVideo == true
              ? size(context).height
              : widget.aspectRatio != null
                  ? null
                  : widget.onChangedOrientation != null
                      ? size(context).height
                      : size(context).height * 0.3;
          return Stack(
            children: [
              if (playVideoOnDifferentPage == false)
                SizedBox(
                  width: size(context).width,
                  height: height2,
                  child: WebviewtubePlayer(
                    videoId: extractYouTubeVideoId(
                            widget.model != null ? widget.model!.videoUrl ?? "" : widget.videoUrl ?? "") ??
                        "",
                    controller: controller,
                  ),
                )
              else
                SizedBox(
                  width: size(context).width,
                  height: height2,
                  child: Stack(
                    children: [
                      if (widget.isQuote == false)
                        Container(
                            foregroundDecoration: BoxDecoration(
                              color: Colors.black.customOpacity(0.6),
                            ),
                            child: CachedNetworkImage(
                              fit: BoxFit.cover,
                              height: height2,
                              imageUrl: widget.isShortVideo == true &&
                                      !widget.model!.backgroundImage!.contains('https')
                                  ? "${Urls.baseServer}uploads/short_video/${widget.model!.backgroundImage}"
                                  : widget.model != null && widget.model!.images!.isNotEmpty
                                      ? widget.model!.images![0]
                                      : '${widget.model!.backgroundImage}',
                              errorWidget: (context, url, str) {
                                return const AppIcon();
                              },
                            )),
                      Positioned.fill(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            Center(
                              child: InkResponse(
                                onTap: () {
                                  Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                          builder: (context) => YoutubeSupportPlayScreen(
                                                blog: widget.model,
                                                isShortVideo: widget.isShortVideo,
                                              )));
                                },
                                child: const BlurWidget(
                                  width: 60,
                                  height: 60,
                                  padding: EdgeInsets.all(20),
                                  child: SvgIcon(
                                    'assets/svg/play-button.svg',
                                    color: Colors.white,
                                    width: 16,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              if (isLiveError == false && playVideoOnDifferentPage == false && widget.isShortVideo == false)
                Positioned(
                    right: 8,
                    left: 0,
                    bottom: 12,
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Spacer(),
                        InkWell(
                          onTap: () {
                            if (widget.onChangedOrientation == null) {
                              //  controller.pause();
                              //  setState(() { });

                              Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                      builder: (context) => YoutubeSupportPlayScreen(
                                          isWebViewPlayer: true,
                                          blog: widget.model,
                                          startAt: controller.value.position.inSeconds
                                          // webviewtubeController: controller
                                          )));
                            } else {
                              var orientation = MediaQuery.of(context).orientation == Orientation.landscape;

                              if (orientation == false) {
                                widget.onChangedOrientation!(true);
                              } else {
                                widget.onChangedOrientation!(false);
                                Navigator.pop(context);
                              }
                            }
                          },
                          child: Container(
                            height: 28,
                            decoration:
                                BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(4)),
                            // width: 80,
                            padding: const EdgeInsets.only(left: 8, right: 3),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.end,
                              children: [
                                const RectangleAppIcon(
                                  width: 45,
                                ),
                                const SizedBox(width: 5),
                                Icon(
                                  widget.onChangedOrientation != null
                                      ? Icons.fullscreen_exit_rounded
                                      : Icons.fullscreen,
                                  size: 18,
                                  color: Colors.black,
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ))
              else if (widget.isShortVideo == true && widget.isAds == false)
                Positioned(
                    bottom: 0,
                    right: 0,
                    left: 0,
                    child: Container(
                        width: size(context).width,
                        decoration: BoxDecoration(
                            gradient: LinearGradient(
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                                colors: [
                              isExpand ? Colors.transparent : Colors.transparent,
                              isExpand ? Colors.black87 : Colors.transparent,
                              Colors.black87
                            ])),
                        padding: const EdgeInsets.only(left: 15, right: 0, bottom: 30),
                        child: shortsActions()))
            ],
          );
        }),
      ),
    );
  }

  @override
  void deactivate() {
    super.deactivate();
    controller.pause();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused) {
      controller.pause();
      setState(() {});
    }
  }

  Widget shortsActions() {
    var getMute = context.read<AppProvider>().isMute;

    return Row(
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
                      model: widget.model ?? Blog()),
                ],
              )),
        ),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.center, children: [
            InkResponse(
              onTap: () {
                if (controller.value.isMuted == true) {
                  controller.unMute();
                } else {
                  controller.mute();
                }
                context.read<AppProvider>().setMute = controller.value.isMuted;

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
    );
  }
}

class LinearVideoProgress extends StatefulWidget {
  const LinearVideoProgress({
    super.key,
    required this.controller,
  });

  final WebviewtubeController controller;

  @override
  State<LinearVideoProgress> createState() => _LinearVideoProgressState();
}

class _LinearVideoProgressState extends State<LinearVideoProgress> {
  @override
  void initState() {
    setState(() {});
    super.initState();
  }

  @override
  void didUpdateWidget(covariant LinearVideoProgress oldWidget) {
    if (widget.key != oldWidget.key) {
      setState(() {});
    }
    super.didUpdateWidget(oldWidget);
  }

  @override
  Widget build(BuildContext context) {
    var progressIndicator;
    if (widget.controller.value.isReady) {
      final int duration = widget.controller.value.videoMetadata.duration.inMilliseconds;
      final int position = widget.controller.value.position.inMilliseconds;
      log((position / duration).toString());
      var string = (position / duration).toString();
      progressIndicator = Stack(
        fit: StackFit.passthrough,
        children: <Widget>[
          LinearProgressIndicator(
            value: double.parse(duration.toString()),
            valueColor: const AlwaysStoppedAnimation<Color>(Colors.grey),
            backgroundColor: Colors.black45,
          ),
          LinearProgressIndicator(
            value: string != 'NaN' ? double.parse(string) : 0.0,
            valueColor: AlwaysStoppedAnimation<Color>(Theme.of(context).primaryColor),
            backgroundColor: Colors.transparent,
          ),
        ],
      );
    } else {
      progressIndicator = LinearProgressIndicator(
        valueColor: AlwaysStoppedAnimation<Color>(Theme.of(context).primaryColor),
        backgroundColor: Colors.black45,
      );
    }
    return progressIndicator;
  }
}

String? extractYouTubeVideoId(String url) {
  final RegExp regExp = RegExp(
    r'(?<=watch\?v=|youtu\.be/|shorts/)([a-zA-Z0-9_-]{11})',
    caseSensitive: false,
  );

  final match = regExp.firstMatch(url);
  return match?.group(0);
}

class ShowOverlay extends StatefulWidget {
  const ShowOverlay({
    super.key,
    this.isPlay = false,
    this.onTap,
    required this.controller,
    this.onOverlayTap,
    this.onFullScreen,
    this.isFullScreen = false,
    this.showLay = false,
    this.isAds = false,
  });

  final bool isPlay, showLay, isFullScreen, isAds;
  final WebviewtubeVideoPlayer controller;
  final VoidCallback? onTap, onOverlayTap, onFullScreen;

  @override
  State<ShowOverlay> createState() => _ShowOverlayState();
}

class _ShowOverlayState extends State<ShowOverlay> {
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
                Spacer(flex: widget.isAds || widget.isFullScreen ? 1 : 3),
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
                const SizedBox(height: 8),
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

class ExpandableDescription extends StatefulWidget {
  const ExpandableDescription({super.key, required this.blog});

  final Blog blog;

  @override
  State<ExpandableDescription> createState() => _ExpandableDescriptionState();
}

class _ExpandableDescriptionState extends State<ExpandableDescription> {
  bool isExpand = false;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        isExpand = !isExpand;
        setState(() {});
      },
      child: Container(
          decoration: BoxDecoration(
              gradient: isExpand == true
                  ? const LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [Colors.black87, Colors.black])
                  : null),
          width: size(context).width / 1.33,
          child: RichText(
              maxLines: isExpand == true ? 12 : 3,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.left,
              text: TextSpan(
                children: [
                  TextSpan(
                    text: isExpand == false && widget.blog.description!.length > 99
                        ? widget.blog.description.toString().substring(0, 100)
                        : widget.blog.description.toString(),
                    style: Theme.of(context).textTheme.bodySmall!.copyWith(
                          fontSize: 15,
                          color: Colors.white,
                        ),
                  ),
                  if (widget.blog.description!.length > 89)
                    TextSpan(
                        text: isExpand == true
                            ? "...${allMessages.value..readLess}"
                            : '...${allMessages.value.readMore}',
                        style: Theme.of(context)
                            .textTheme
                            .labelSmall!
                            .copyWith(fontSize: 17, color: Colors.white, fontWeight: FontWeight.w600))
                ],
              ))),
    );
  }
}

class DurationWidget extends StatefulWidget {
  const DurationWidget({
    super.key,
    required this.controller,
  });

  final VideoPlayerController controller;

  @override
  State<DurationWidget> createState() => _DurationWidgetState();
}

class _DurationWidgetState extends State<DurationWidget> {
  late VideoPlayerController video;

  late VoidCallback listener;

  // ignore: non_constant_identifier_names
  _VideoProgressIndicatorState() {
    listener = () {
      if (!mounted) {
        return;
      }
      setState(() {});
    };
  }

  @override
  void initState() {
    video = widget.controller;
    _VideoProgressIndicatorState();
    video.addListener(listener);
    super.initState();
  }

  String formatDuration(int seconds) {
    int hours = (seconds ~/ 3600);
    int minutes = (seconds % 3600) ~/ 60;
    int secondsRemaining = seconds % 60;

    // Format the hours, minutes, and seconds with leading zeros if needed
    String formattedHours = hours.toString().padLeft(2, '0');
    String formattedMinutes = minutes.toString().padLeft(2, '0');
    String formattedSeconds = secondsRemaining.toString().padLeft(2, '0');

    return hours > 1
        ? '$formattedHours:$formattedMinutes:$formattedSeconds'
        : '$formattedMinutes:$formattedSeconds';
  }

  @override
  Widget build(BuildContext context) {
    return Text(
      "${formatDuration(video.value.position.inSeconds)} / ${formatDuration(video.value.duration.inSeconds)}",
      key: ValueKey(video.value.position.inSeconds),
      textAlign: TextAlign.left,
      style: const TextStyle(color: Colors.white),
    );
  }
}
