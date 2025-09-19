import 'dart:async';
import 'dart:developer';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/shorts_controller.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/main.dart';
import 'package:incite/pages/main/blog.dart';
import 'package:incite/pages/shorts_video.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/rgbo_to_hex.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/anim_util.dart';

import 'package:incite/widgets/gradient.dart';
import 'package:incite/widgets/svg_icon.dart';
import 'package:onesignal_flutter/onesignal_flutter.dart';
import 'package:preload_page_view/preload_page_view.dart';
import 'package:provider/provider.dart';

import 'package:upgrader/upgrader.dart';
import '../../api_controller/app_provider.dart';
import '../../model/blog.dart';
import '../../splash_screen.dart';
import '../../utils/image_util.dart';
import 'blog_wrap.dart';
import 'home.dart';

int shortsCurrIndex = 0;

class DashboardPage extends StatefulWidget {
  const DashboardPage(
      {super.key,
      this.action,
      this.index = 0,
      this.isFromVideo = false,
      this.blog,
      this.isLoad = true,
      this.bottomIndex = 0,
      this.fromInitial = false});

  final int index, bottomIndex;
  final Blog? blog;
  final bool isLoad;
  final BlogOptionType? action;
  final bool fromInitial, isFromVideo;

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage>
    with AutomaticKeepAliveClientMixin, WidgetsBindingObserver {
  late PageController controller, shortsHomeController;
  PreloadPageController preloadPageController = PreloadPageController();
  bool data = true;
  late int currIndex;
  UserProvider user = UserProvider();
  int curr = 0;
  Timer? timer;

  var keyButton = GlobalKey();
  var keyButton2 = GlobalKey();
  var keyButton3 = GlobalKey();
  var keyButton4 = GlobalKey();

  late GlobalKey<NavigatorState> navigatorkey;

  @override
  void initState() {
    currIndex = widget.index;
    shortsCurrIndex = widget.bottomIndex;
    prefs!.remove('id');

    navigatorkey = GlobalKey<NavigatorState>();
    controller = PageController(initialPage: widget.index);
    shortsHomeController = PageController(initialPage: widget.bottomIndex);

    // Force load categories on startup and clear cache
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final appProvider = Provider.of<AppProvider>(context, listen: false);
      print("🚀 Dashboard: Clearing cache and forcing category load");
      prefs!.remove('collection'); // Clear cached data
      appProvider.getCategory();
    });
    WidgetsBinding.instance.addObserver(this);

    timer = Timer.periodic(const Duration(seconds: 15), (timer) {
      var provider = context.read<AppProvider>();
      _fetchData(provider);

      if (shortsCurrIndex == 0 && provider.adAnalytics[0]["ads_ids"].isNotEmpty ||
          provider.adAnalytics[1]["ads_ids"].isNotEmpty) {
        getAdsAnalytics(provider);
      }
    });

    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      var provider = Provider.of<AppProvider>(context, listen: false);
      if (widget.index == 1) {
        blogListHolder.setBlogType(BlogType.allnews);
      }
      user.socialMediaList();
      UserProvider().getCMS(context);
      if (widget.isLoad == true) {
        provider.getCategory();
      }
      if (widget.isFromVideo == false) {
        if (allSettings.value.isShortEnable == '1') {
          await ShortsApi().fetchShorts(context).then((value) {
            setState(() {});
          }).onError((e, r) {});
        }
      }

      if (currentUser.value.id != null) {
        if (!prefs!.containsKey('isBookmark')) {
          provider.getAllBookmarks().then((DataModel? value) {});
        } else {
          provider.setAllBookmarks();
        }
      }
      setState(() {});
    });

    super.initState();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    // Handle the lifecycle state change
    var provider = Provider.of<AppProvider>(context, listen: false);

    switch (state) {
      case AppLifecycleState.resumed:
        timer = Timer.periodic(const Duration(seconds: 15), (timer) {
          _fetchData(provider);
          if (shortsCurrIndex == 0 && provider.adAnalytics[0]["ads_ids"].isNotEmpty ||
              provider.adAnalytics[1]["ads_ids"].isNotEmpty) {
            getAdsAnalytics(provider);
          }
        });
        setState(() {});
        break;

      case AppLifecycleState.paused:
        if (timer != null) {
          timer!.cancel();
        }
        _fetchData(provider);

        if (provider.adAnalytics[0]["ads_ids"].isNotEmpty &&
            shortsCurrIndex == 0 &&
            provider.adAnalytics[1]["ads_ids"].isNotEmpty) {
          getAdsAnalytics(provider);
        }

        break;
      case AppLifecycleState.inactive:
        if (timer != null) {
          timer!.cancel();
        }
        _fetchData(provider);
        if (provider.adAnalytics[0]["ads_ids"].isNotEmpty &&
            shortsCurrIndex == 0 &&
            provider.adAnalytics[1]["ads_ids"].isNotEmpty) {
          getAdsAnalytics(provider);
        }
        break;
      case AppLifecycleState.detached:
        prefs!.remove('isBookmark');
        if (timer != null) {
          timer!.cancel();
        }
        _fetchData(provider);
        if (provider.adAnalytics[0]["ads_ids"].isNotEmpty &&
            shortsCurrIndex == 0 &&
            provider.adAnalytics[1]["ads_ids"].isNotEmpty) {
          getAdsAnalytics(provider);
        }

        break;
      case AppLifecycleState.hidden:
        break;
    }
  }

  void _fetchData(AppProvider provider) {
    if ((provider.analytics[0]["blog_ids"].isNotEmpty ||
        provider.analytics[1]["blog_ids"].isNotEmpty ||
        provider.analytics[2]["blog_ids"].isNotEmpty ||
        provider.analytics[3]["blog_ids"].isNotEmpty ||
        provider.analytics[4]["start_time"].isNotEmpty ||
        provider.analytics[5]["blog_ids"].isNotEmpty ||
        provider.analytics[6]["blogs"].isNotEmpty)) {
      provider.getAnalyticData();
    }
  }

  void getAdsAnalytics(AppProvider provider) {
    provider.getAnalyticData(isAds: true);
  }

  @override
  void dispose() {
    super.dispose();
    WidgetsBinding.instance.removeObserver(this);
    if (timer != null) {
      timer!.cancel();
    }
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    var appProvider = Provider.of<AppProvider>(context, listen: false);
    return UpgradeAlert(
        navigatorKey: navigatorkey,
        upgrader: !prefs!.containsKey('update_duration') && upgrader != null
            ? upgrader
            : Upgrader(durationUntilAlertAgain: const Duration(days: 3)),
        onIgnore: () {
          prefs!.setString('update_duration', DateTime.now().toIso8601String());
          setState(() {});
          return true;
        },
        onLater: () {
          prefs!.setString('update_duration', DateTime.now().toIso8601String());
          setState(() {});
          return true;
        },
        shouldPopScope: () => Platform.isAndroid
            ? allSettings.value.isAndroidForceUpdate != '1'
            : allSettings.value.isIosForceUpdate != '1',
        showIgnore: false,
        showLater: Platform.isAndroid
            ? allSettings.value.isAndroidForceUpdate != '1'
            : allSettings.value.isIosForceUpdate != '1',
        child: ValueListenableBuilder(
            valueListenable: allSettings,
            builder: (context, value, child) {
              var list = [
                [allMessages.value.dashboard ?? "Home", "assets/svg/dash.svg"],
                [allMessages.value.shorts ?? "Shorts", "assets/svg/shorts.svg"]
              ];
              return value.enableMaintainanceMode == '1'
                  ? PopScope(
                      canPop: false,
                      onPopInvokedWithResult: (value, result) async {
                        showCustomDialog(
                            context: context,
                            title: allMessages.value.confirmExitTitle ?? "Exit Application",
                            text: allMessages.value.confirmExitApp ?? 'Do you want to exit from app ?',
                            onTap: () {
                              var provider = Provider.of<AppProvider>(context, listen: false);
                              var end = DateTime.now();
                              provider.addAppTimeSpent(startTime: provider.appStartTime, endTime: end);
                              provider.getAnalyticData(isAds: true);
                              provider.getAnalyticData(isAds: false);
                              Future.delayed(const Duration(milliseconds: 300));
                              exit(0);
                            },
                            isTwoButton: true);
                      },
                      child: Material(
                        child: SizedBox(
                          width: size(context).width,
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 24),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Stack(children: [
                                  Image.asset('assets/images/maintain.png', width: 200, height: 200),
                                  Positioned(
                                      top: kToolbarHeight,
                                      right: 50,
                                      child: Image.asset(Img.logo, width: 30, height: 30))
                                ]),
                                Text(value.maintainanceTitle.toString(),
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(
                                        fontFamily: 'Roboto', fontSize: 24, fontWeight: FontWeight.bold)),
                                const SizedBox(height: 12),
                                Text(value.maintainanceShortText.toString(),
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(
                                        fontFamily: 'Roboto', fontSize: 14, fontWeight: FontWeight.w400)),
                              ],
                            ),
                          ),
                        ),
                      ),
                    )
                  : PopScope(
                      canPop: false,
                      onPopInvokedWithResult: (didPop, c) async {
                        // if ((Platform.isAndroid && allSettings.value.isAndroidForceUpdate == '1') ||
                        //     (Platform.isIOS && allSettings.value.isIosForceUpdate == '1')) {
                        //   return;
                        // } else

                        if (MediaQuery.of(context).orientation == Orientation.landscape) {
                          SystemChrome.setEnabledSystemUIMode(SystemUiMode.manual,
                              overlays: [SystemUiOverlay.top, SystemUiOverlay.bottom]);
                          SystemChrome.setPreferredOrientations([
                            DeviceOrientation.portraitUp,
                            DeviceOrientation.portraitDown,
                          ]);
                        } else if (shortsCurrIndex == 1 &&
                            MediaQuery.of(context).orientation == Orientation.portrait) {
                          shortsHomeController.animateToPage(0,
                              duration: const Duration(milliseconds: 200), curve: Curves.easeIn);
                          shortsCurrIndex = 0;
                          setState(() {});
                        } else if (currIndex == 1 &&
                            MediaQuery.of(context).orientation == Orientation.portrait) {
                          controller.animateToPage(0,
                              duration: const Duration(milliseconds: 200), curve: Curves.easeIn);
                          currentUser.value.isNewUser = false;
                          prefs!.setBool('is_tutorial_taken', true);
                          setState(() {});
                        } else {
                          showCustomDialog(
                              context: context,
                              title: allMessages.value.confirmExitTitle ?? "Exit Application",
                              text: allMessages.value.confirmExitApp ?? 'Do you want to exit from app ?',
                              onTap: () {
                                var provider = Provider.of<AppProvider>(context, listen: false);
                                var end = DateTime.now();
                                provider.addAppTimeSpent(startTime: provider.appStartTime, endTime: end);
                                provider.getAnalyticData();
                                Future.delayed(const Duration(milliseconds: 300));
                                exit(0);
                              },
                              isTwoButton: true);
                        }
                      },
                      child: AnnotatedRegion(
                        value: SystemUiOverlayStyle(
                            statusBarIconBrightness: dark(context) ? Brightness.light : Brightness.dark,
                            statusBarColor: Colors.transparent),
                        child: Scaffold(
                          body: Stack(
                            children: [
                              PageView(
                                controller: shortsHomeController,
                                physics: const NeverScrollableScrollPhysics(),
                                children: [
                                  PageView(
                                    physics: (Platform.isAndroid &&
                                                allSettings.value.isAndroidForceUpdate == '1') ||
                                            (Platform.isIOS && allSettings.value.isIosForceUpdate == '1')
                                        ? const NeverScrollableScrollPhysics()
                                        : MediaQuery.of(context).orientation == Orientation.landscape
                                            ? const NeverScrollableScrollPhysics()
                                            : null,
                                    controller: controller,
                                    onPageChanged: (value) async {
                                      currIndex = value;
                                      setState(() {});
                                    },
                                    children: [
                                      HomePage(
                                          menuTapped: (value) {
                                            currIndex = value == true ? 1 : 0;
                                            setState(() {});
                                          },
                                          onChanged: backToHome),
                                      Stack(
                                        children: [
                                          BlogWrapPage(
                                              key: ValueKey("${blogListHolder.blogType}$curr"),
                                              preloadPageController: preloadPageController,
                                              index: curr,
                                              type: widget.action,
                                              onChanged: (value) {
                                                controller.animateToPage(0,
                                                    duration: const Duration(milliseconds: 300),
                                                    curve: Curves.easeIn);
                                              }),
                                          !prefs!.containsKey('is_tutorial_taken')
                                              ? Positioned(left: 0, right: 0, child: instructions(context))
                                              : const SizedBox(),
                                        ],
                                      ),
                                    ],
                                  ),
                                  if (allSettings.value.isShortEnable == '1')
                                    (shortsCurrIndex == 0
                                        ? const SizedBox()
                                        : Container(
                                            color: shortsCurrIndex == 1 ? Colors.black : null,
                                            padding: const EdgeInsets.only(bottom: 80),
                                            child: PreloadVideoPage(
                                              isFromVideo: widget.isFromVideo,
                                              focusedIndex: appProvider.focusedIndex,
                                              onHomeTap: () {
                                                shortsHomeController.jumpToPage(0);
                                              },
                                              backTap: () {},
                                            ),
                                          ))
                                ],
                              ),
                              if (allSettings.value.isShortEnable == '1') bottomNav(context, list)
                            ],
                          ),
                        ),
                      ),
                    );
            }));
  }

  Widget bottomNav(BuildContext context, List<List<String>> list) {
    return ValueListenableBuilder(
        valueListenable: allSettings,
        builder: (context, value, child) {
          return value.isShortEnable == '0'
              ? const SizedBox()
              : Positioned(
                  left: 16,
                  right: 16,
                  bottom: 16,
                  child: Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(100),
                      color: shortsCurrIndex == 1 ? Colors.black : Theme.of(context).cardColor,
                    ),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 300),
                      curve: Curves.easeIn,
                      decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(100),
                          color: shortsCurrIndex == 1
                              ? const Color.fromRGBO(20, 20, 20, 1)
                              : Theme.of(context).cardColor,
                          boxShadow: const [
                            BoxShadow(
                                offset: Offset(15, 15),
                                blurRadius: 30,
                                spreadRadius: 0,
                                color: Color.fromRGBO(0, 0, 0, 0.08))
                          ]),
                      height: currIndex == 0 ? 50 : 0,
                      width: size(context).width,
                      alignment: Alignment.center,
                      // margin: const EdgeInsets.all(12),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          ...list.asMap().entries.map((e) {
                            return Expanded(
                              child: Padding(
                                padding: const EdgeInsets.all(4.0),
                                child: AnimationFadeSlide(
                                  dx: 0,
                                  key: ValueKey("${e.key}"),
                                  child: AnimatedContainer(
                                    duration: const Duration(milliseconds: 300),
                                    height: currIndex == 0 ? 65 : 0,
                                    curve: Curves.easeInSine,
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(100),
                                      color: shortsCurrIndex == e.key
                                          ? Theme.of(context).primaryColor.customOpacity(0.1)
                                          : null,
                                    ),
                                    child: InkWell(
                                        onTap: () {
                                          shortsHomeController.jumpToPage(e.key);
                                          shortsCurrIndex = e.key;
                                          setState(() {});
                                        },
                                        child: Row(
                                          crossAxisAlignment: CrossAxisAlignment.center,
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            GradientWidget(
                                              gradient: shortsCurrIndex == e.key
                                                  ? LinearGradient(colors: colorGradient)
                                                  : null,
                                              child: SvgIcon(
                                                e.value[1],
                                                width: 24,
                                                color: shortsCurrIndex == e.key
                                                    ? dark(context)
                                                        ? Colors.white
                                                        : Theme.of(context).primaryColor
                                                    : Colors.grey,
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            shortsCurrIndex == e.key
                                                ? GradientText(e.value[0],
                                                    gradient: LinearGradient(colors: colorGradient))
                                                : Text(e.value[0],
                                                    style: TextStyle(
                                                        fontSize: 14,
                                                        fontFamily: 'Roboto',
                                                        fontWeight: shortsCurrIndex == e.key
                                                            ? FontWeight.w600
                                                            : FontWeight.w400,
                                                        color: shortsCurrIndex == e.key
                                                            ? dark(context)
                                                                ? Colors.white
                                                                : Theme.of(context).primaryColor
                                                            : Colors.grey)),
                                          ],
                                        )),
                                  ),
                                ),
                              ),
                            );
                          })
                        ],
                      ),
                    ),
                  ),
                );
        });
  }

  GestureDetector instructions(BuildContext context) {
    return GestureDetector(
      onVerticalDragStart: (details) {
        prefs!.setBool('is_tutorial_taken', true);
        setState(() {});
      },
      onHorizontalDragStart: (details) {
        prefs!.setBool('is_tutorial_taken', true);
        setState(() {});
      },
      onTap: () {
        prefs!.setBool('is_tutorial_taken', true);
        setState(() {});
      },
      child: Container(
        height: size(context).height,
        color: Colors.black.customOpacity(0.9),
        padding: const EdgeInsets.symmetric(horizontal: 30),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              allMessages.value.swipeDown ?? "Swipe Down",
              style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 15.0),
            ),
            Padding(
              padding: EdgeInsets.only(top: 10.0),
              child: Text(
                allMessages.value.swipeDownText ?? "To See the Previous Story",
                style: TextStyle(color: Colors.white, fontSize: 12),
              ),
            ),
            const SizedBox(height: 40),
            AnimatedArrow(key: keyButton, icon: Icons.arrow_downward_rounded, dy: -0.5, dx: 0),
            SizedBox(
              height: size(context).height / 4,
              child: Row(
                children: [
                  Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      AnimatedArrow(key: keyButton4, icon: Icons.arrow_back_rounded, dx: 0.5),
                      const SizedBox(height: 20),
                      Text(
                        allMessages.value.swipeLeft ?? "Swipe Left",
                        style: TextStyle(fontWeight: FontWeight.w600, color: Colors.white, fontSize: 15.0),
                      ),
                      Padding(
                        padding: EdgeInsets.only(top: 10.0),
                        child: Text(
                          allMessages.value.swipeLeftText ?? "To Read Full Story",
                          style: TextStyle(color: Colors.white, fontSize: 14),
                        ),
                      )
                    ],
                  ),
                  const Spacer(),
                  Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      AnimatedArrow(key: keyButton3, icon: Icons.arrow_forward_rounded, dx: -0.5),
                      const SizedBox(height: 20),
                      Text(
                        allMessages.value.swipeRight ?? "Swipe Right",
                        style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 15.0),
                      ),
                      Padding(
                        padding: EdgeInsets.only(top: 10.0),
                        child: Text(
                          allMessages.value.swipeRightText ?? "To Visit Dashbaord",
                          style: TextStyle(color: Colors.white, fontSize: 12),
                        ),
                      )
                    ],
                  ),
                ],
              ),
            ),
            //  --------------  Swipe Up Guide ---------
            AnimatedArrow(key: keyButton2, icon: Icons.arrow_upward_rounded, dy: 0.5, dx: 0),
            const SizedBox(height: 40),
            Text(
              allMessages.value.swipeUp ?? "Swipe Up",
              style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 15.0),
            ),
            Padding(
              padding: EdgeInsets.only(top: 10.0),
              child: Text(
                allMessages.value.swipeUpText ?? "To See the Next Story",
                style: TextStyle(color: Colors.white, fontSize: 12),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void backToHome(int value) async {
    data = false;
    curr = value;
    // if (preloadPageController.hasClients) {
    //   preloadPageController.jumpToPage(curr);
    // }
    setState(() {});
    controller.animateToPage(1, duration: const Duration(milliseconds: 300), curve: Curves.easeIn);
  }

  @override
  bool get wantKeepAlive => data;
}

class AnimatedArrow extends StatefulWidget {
  const AnimatedArrow({super.key, required this.icon, this.dx, this.dy});

  final IconData icon;
  final double? dx, dy;

  @override
  State<AnimatedArrow> createState() => _AnimatedArrowState();
}

class _AnimatedArrowState extends State<AnimatedArrow> {
  bool isPlay = false;

  @override
  void initState() {
    isPlay = true;
    setState(() {});
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return AnimationFadeSlide(
      curve: Curves.easeIn,
      dy: widget.dy ?? 0,
      dx: widget.dx ?? -0.5,
      duration: 700,
      isFade: false,
      play: isPlay,
      repeat: true,
      child: Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(width: 1, color: Colors.white)),
        child: Icon(widget.icon, color: Colors.white),
      ),
    );
  }
}
