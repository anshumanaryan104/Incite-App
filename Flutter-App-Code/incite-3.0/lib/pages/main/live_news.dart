import 'dart:developer';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/pages/main/dashboard.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/splash_screen.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/live_news.dart';
import 'package:incite/widgets/loader.dart';
import 'package:share_plus/share_plus.dart';
import '../../api_controller/news_repo.dart';
import '../../utils/color_util.dart';
import '../../utils/theme_util.dart';
import '../../widgets/anim_util.dart';
import '../../widgets/back.dart';

class LiveNews extends StatefulWidget {
  const LiveNews({super.key, this.id});

  final int? id;

  @override
  // ignore: library_private_types_in_public_api
  _LiveNewsState createState() => _LiveNewsState();
}

class _LiveNewsState extends State<LiveNews> {
  bool isFullscreen = false;
  int? selectedVideo;

  bool isShare = false;
  String? errorMessage;

  @override
  void initState() {
    super.initState();

    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await getliveNews().then((value) async {
        if (widget.id != null && value.isNotEmpty) {
          for (var i = 0; i < value.length; i++) {
            log(widget.id.toString());
            log(i.toString());
            if (widget.id == value[i].id) {
              selectedVideo = i;
              isShare = false;
              setState(() {});
              return;
            }
          }
        } else {
          selectedVideo = 0;

          isShare = false;
          setState(() {});
        }
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return CustomLoader(
      isLoading: isShare,
      child: PopScope(
          canPop: false,
          onPopInvokedWithResult: (didPop, F) {
            if (didPop) {
              return;
            }
            if (!prefs!.containsKey('id')) {
              Navigator.pop(context);
            } else if (widget.id != null && prefs!.containsKey('id')) {
              prefs!.remove('id');
              Navigator.pushAndRemoveUntil(context,
                  MaterialPageRoute(builder: (context) => const DashboardPage(index: 0)), (route) => false);
            }
          },
          child: MediaQuery.removePadding(
              context: context,
              removeTop: isFullscreen,
              removeLeft: true,
              removeRight: true,
              removeBottom: true,
              child: Scaffold(
                appBar: isFullscreen == true
                    ? null
                    : AppBar(
                        centerTitle: false,
                        leadingWidth: 0,
                        backgroundColor: Theme.of(context).appBarTheme.backgroundColor,
                        automaticallyImplyLeading: false,
                        titleSpacing: 24,
                        toolbarHeight: 60,
                        elevation: 0,
                        title: Row(
                          children: [
                            Backbut(onTap: () {
                              if (widget.id != null && prefs!.containsKey('id')) {
                                prefs!.remove('id');
                                Navigator.pushAndRemoveUntil(
                                    context,
                                    MaterialPageRoute(builder: (context) => const DashboardPage(index: 0)),
                                    (route) => false);
                              } else {
                                Navigator.pop(context);
                              }
                            }),
                            const SizedBox(width: 15),
                            AnimationFadeSlide(
                              dx: 0.3,
                              duration: 500,
                              child: Text(allMessages.value.liveNews ?? 'Live News',
                                  style: TextStyle(
                                      fontFamily: 'Roboto',
                                      fontSize: 20,
                                      color: dark(context) ? ColorUtil.white : ColorUtil.textblack,
                                      fontWeight: FontWeight.w600)),
                            )
                          ],
                        ),
                      ),
                body: liveNews.isEmpty
                    ? SingleChildScrollView(
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
                          child: Column(
                            children: [...List.generate(8, (index) => const ListShimmer())],
                          ),
                        ),
                      )
                    : Column(
                        children: [
                          SizedBox(
                            height: height10(context) * 25,
                            width: MediaQuery.of(context).size.width,
                            child: PlayAnyVideoPlayer(
                              key: ValueKey(selectedVideo),
                              isLive: true,
                              isCurrentlyOpened: true,
                              model: Blog(
                                  id: liveNews[selectedVideo ?? 0].id,
                                  images: [liveNews[selectedVideo ?? 0].image],
                                  videoUrl: liveNews[selectedVideo ?? 0].url),
                              videoUrl: liveNews[selectedVideo ?? 0].url.toString(),
                            ),
                          ),
                          selectedVideo == null
                              ? Container()
                              : isFullscreen
                                  ? const SizedBox()
                                  : Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                                      child: Row(
                                        children: [
                                          ClipRRect(
                                            borderRadius: BorderRadius.circular(100),
                                            child: CachedNetworkImage(
                                              imageUrl: liveNews[selectedVideo!.toInt()].image.toString(),
                                              height: 30,
                                              width: 30,
                                              fit: BoxFit.cover,
                                            ),
                                          ),
                                          const SizedBox(
                                            width: 10,
                                          ),
                                          Text(
                                            liveNews[selectedVideo!.toInt()].companyName.toString(),
                                            style: Theme.of(context)
                                                .textTheme
                                                .titleLarge!
                                                .copyWith(fontWeight: FontWeight.w600),
                                          ),
                                        ],
                                      ),
                                    ),
                          isFullscreen ? const SizedBox() : const Divider(height: 1, thickness: 1),
                          isFullscreen
                              ? const SizedBox()
                              : Expanded(
                                  child: ListView.builder(
                                    itemCount: liveNews.length,
                                    itemBuilder: (context, index) {
                                      return Container(
                                        key: ValueKey(index),
                                        height: 100,
                                        foregroundDecoration: BoxDecoration(
                                          color: selectedVideo != index
                                              ? Colors.transparent
                                              : Theme.of(context).primaryColor.customOpacity(0.1),
                                        ),
                                        child: LiveWidget(
                                          padding: const EdgeInsets.only(
                                            top: 16,
                                            bottom: 16,
                                          ),
                                          title: liveNews[index].companyName,
                                          fontWeight: FontWeight.w500,
                                          isPlay: selectedVideo == index,
                                          image: liveNews[index].image,
                                          playState: selectedVideo == index,
                                          onShare: () async {
                                            isShare = true;
                                            setState(() {});
                                            await downloadImage(
                                                    liveNews[index].image ?? allSettings.value.appLogo ?? "")
                                                .then((image) async {
                                              shareImage(image ?? XFile(''),
                                                  "${Urls.baseServer}live-news/${liveNews[index].id}");
                                              isShare = false;
                                              setState(() {});
                                            });
                                          },
                                          onTap: () async {
                                            selectedVideo = index;
                                            errorMessage = null;
                                            setState(() {});
                                          },
                                        ),
                                      );
                                    },
                                  ),
                                ),
                        ],
                      ),
              ))),
    );
  }

  @override
  void dispose() {
    super.dispose();
  }
}
