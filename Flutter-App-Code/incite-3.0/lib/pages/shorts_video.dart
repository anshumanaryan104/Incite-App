import 'dart:async';
import 'dart:developer';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/shorts_controller.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/screen_util.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/last_widget.dart';
import 'package:incite/widgets/short_video_controller.dart';
import 'package:preload_page_view/preload_page_view.dart';
import 'package:provider/provider.dart';
import 'package:video_player/video_player.dart';

class PreloadVideoPage extends StatefulWidget {
  const PreloadVideoPage({
    super.key,
    this.isFromVideo = false,
    required this.onHomeTap,
    required this.backTap,
    this.focusedIndex,
  });
  final VoidCallback onHomeTap, backTap;
  final bool? isFromVideo;
  final int? focusedIndex;

  @override
  State<PreloadVideoPage> createState() => _PreloadVideoPageState();
}

class _PreloadVideoPageState extends State<PreloadVideoPage> with AutomaticKeepAliveClientMixin {
  bool isShare = false;

  bool showTopHeader = false;

  late PreloadPageController pageController;

  int currentIndex = 0;

  @override
  void initState() {
    ScreenTimeout().enableScreenAwake();
    pageController =
        PreloadPageController(initialPage: widget.isFromVideo == true ? 0 : widget.focusedIndex ?? 0);
    currentIndex = widget.focusedIndex ?? 0;
    WidgetsBinding.instance.addPostFrameCallback((e) {
      var provider = Provider.of<AppProvider>(context, listen: false);

      if (currentIndex == widget.focusedIndex) {
        provider.addShortsViewsData(shortLists.blogModel.blogs[currentIndex].id ?? 0);
      }
      if (widget.isFromVideo == true) {
        pageController.jumpToPage(0);
        setState(() {});
      }
    });

    super.initState();
  }

  @override
  void deactivate() {
    if (Platform.isAndroid) {
      ScreenTimeout().disableScreenAwake();
    }
    super.deactivate();
  }

  @override
  void dispose() {
    pageController.dispose();
    ScreenTimeout().disableScreenAwake();
    super.dispose();
  }

  bool isLoad = false;

  Future<List<String>?> expensiveWork(BuildContext context) async {
    List<String>? data =
        await ShortsApi().fetchShorts(context, nextPageUrl: shortLists.blogModel.nextPageUrl) ?? [];

    // lots of calculations
    return data;
  }

  GlobalKey<RefreshIndicatorState> refreshKey = GlobalKey<RefreshIndicatorState>();

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return SafeArea(
        child: Scaffold(
      body: Stack(
        children: [
          shortLists.blogModel.blogs.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        allMessages.value.noShortsTitle ?? "No Shorts Available",
                        style: const TextStyle(fontSize: 20),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        allMessages.value.noShortsDescription ??
                            "No Shorts available at this moment. We will comeback soon!!",
                        textAlign: TextAlign.center,
                      )
                    ],
                  ),
                )
              : Consumer<AppProvider>(
                  builder: (context, provider, _) {
                    return RefreshIndicator(
                      key: refreshKey,
                      onRefresh: () async {
                        isShare = true;
                        setState(() {});
                        await Future.delayed(const Duration(milliseconds: 2000));
                        // provider.controllers[provider.focusedIndex]!.pause();
                        ShortsApi().fetchShorts(context).then((value) {
                          currentIndex = 0;
                          provider.setFocusedIndex = 0;
                          // provider.playVideoAtIndex(0);
                          // shortslikesIds.forEach((e){
                          //   provider.setLike()

                          // log(value.toString());
                          // }
                          isShare = false;
                          setState(() {});
                        }).onError((e, r) {
                          isShare = false;
                          setState(() {});
                        });
                      },
                      child: PreloadPageView.builder(
                        itemCount: shortLists.blogModel.blogs.length,
                        scrollDirection: Axis.vertical,
                        controller: pageController,
                        preloadPagesCount: 3,
                        physics: const BouncingScrollPhysics(),
                        onPageChanged: (index) async {
                          context.read<AppProvider>().setFocusedIndex = index;
                          currentIndex = context.watch<AppProvider>().focusedIndex;

                          if (index == currentIndex) {
                            context
                                .read<AppProvider>()
                                .addShortsViewsData(shortLists.blogModel.blogs[index].id ?? 0);
                          }

                          if (index % 8 == 0 && shortLists.blogModel.nextPageUrl != null && isLoad == false) {
                            isLoad = true;
                            setState(() {});

                            log('--------------- Current Index  -------------');
                            expensiveWork(context).whenComplete(() {
                              isLoad = false;
                              setState(() {});
                            });
                          }
                        },
                        itemBuilder: (context, value) {
                          return shortLists.blogModel.blogs[value].id == 000000
                              ? LastNewsWidget(
                                  onBack: widget.onHomeTap,
                                  keyword: allMessages.value.shorts ?? "Shorts",
                                  buttonText: allMessages.value.backToHome,
                                  isButton: true,
                                  icon: Icons.keyboard_arrow_up_rounded,
                                  isShort: true,
                                  onTap: () {
                                    // provider.onVideoIndexChanged(0);
                                    ScreenTimeout().enableScreenAwake();
                                    pageController.jumpToPage(0);
                                    refreshKey.currentState!.show();
                                    setState(() {});
                                  })
                              : VideoWidget(
                                  key: ValueKey(value),
                                  isLoading: false,
                                  index: value,
                                  blog: shortLists.blogModel.blogs[value],
                                  currIndex: currentIndex,
                                  // controller: provider.controllers[value],
                                  onTap: () {},
                                );
                        },
                      ),
                    );
                  },
                ),
        ],
      ),
    ));
  }

  @override
  bool get wantKeepAlive => true;
}

/// Custom Feed Widget consisting video
class VideoWidget extends StatefulWidget {
  const VideoWidget(
      {super.key,
      required this.isLoading,
      this.controller,
      this.onTap,
      this.index = 0,
      this.currIndex = 0,
      required this.blog});

  final bool isLoading;
  final VoidCallback? onTap;
  final int index, currIndex;
  final Blog blog;
  final VideoPlayerController? controller;

  @override
  State<VideoWidget> createState() => _VideoWidgetState();
}

class _VideoWidgetState extends State<VideoWidget> {
  VideoPlayerController? loadController;

  bool isLoading = false, isVideoPlayerPlayed = false;

  var isExpand = false;

  @override
  void initState() {
    loadController = widget.controller;
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    var getCurrentIndex = context.read<AppProvider>().focusedIndex;
    if (isVideoPlayerPlayed == false) {
      return PlayAnyVideoPlayer(
        model: widget.blog,
        aspectRatio: 9 / 16,
        onChanged: (value) {
          isVideoPlayerPlayed = value;
          setState(() {});
        },
        isShortVideo: true,
        isCurrentlyOpened: widget.index == getCurrentIndex,
      );
    } else {
      return ShortPlayAnyVideo(
          model: widget.blog,
          currIndex: getCurrentIndex,
          controller: loadController,
          aspectRatio: 9 / 16,
          isShortVideo: true,
          index: widget.index,
          isAutoPlay: appThemeModel.value.isAutoPlay.value);
    }
  }
}

class ShowOverlay extends StatefulWidget {
  const ShowOverlay({
    super.key,
  });

  @override
  State<ShowOverlay> createState() => _ShowOverlayState();
}

class _ShowOverlayState extends State<ShowOverlay> {
  Timer? hoverOverlayTimer;

  @override
  void initState() {
    hoverOverlayTimer = Timer(
      const Duration(seconds: 3),
      () => isShowOverlay(false),
    );
    super.initState();
  }

  void isShowOverlay(bool val, {Duration? delay}) {
    if (val == true) {
      hoverOverlayTimer = Timer(const Duration(seconds: 2), () {});
    } else {
      hoverOverlayTimer?.cancel();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: Container(
        color: Colors.black38,
        child: const Center(
          child: CircleAvatar(
            radius: 40,
            backgroundColor: Colors.black45,
            child: Icon(Icons.volume_off_rounded),
          ),
        ),
      ),
    );
  }
}

class SmoothScrollPhysics extends ScrollPhysics {
  final double friction;

  const SmoothScrollPhysics({super.parent, this.friction = 0.015});

  @override
  SmoothScrollPhysics applyTo(ScrollPhysics? ancestor) {
    return SmoothScrollPhysics(friction: friction, parent: buildParent(ancestor));
  }

  @override
  double applyPhysicsToUserOffset(ScrollMetrics position, double offset) {
    // Custom smooth friction here to make the scrolling smoother
    return super.applyPhysicsToUserOffset(position, offset * friction);
  }
}
