import 'dart:async';
import 'dart:developer';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/scheduler.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/main.dart';
import 'package:incite/pages/auth/login.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/main/dashboard.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/pages/main/widgets/text.dart';
import 'package:incite/splash_screen.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/shimmer.dart';
import 'package:incite/widgets/tap.dart';
import 'package:provider/provider.dart';

import '../../../api_controller/blog_controller.dart';
import '../../../model/blog.dart';
import '../../../model/home.dart';
import '../blog.dart';

class QuotePage extends StatefulWidget {
  const QuotePage({
    super.key,
    this.type,
    this.index = 0,
    this.currIndex = 0,
    this.initial = false,
    required this.model,
    this.onTap,
  });
  final Blog model;
  final BlogOptionType? type;
  final bool initial;
  final int currIndex, index;
  final VoidCallback? onTap;

  @override
  State<QuotePage> createState() => _QuotePageState();
}

class _QuotePageState extends State<QuotePage> {
  GlobalKey previewContainer = GlobalKey();
  bool load = false;
  late AppProvider provider;
  late Blog blog;

  var isExpand = false;

  int pageIndex = 0;
  @override
  void initState() {
    blog = widget.model;
    prefs!.remove('id');

    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      provider = Provider.of<AppProvider>(context, listen: false);
      if (widget.initial == true) {
        blog = await blogDetail(widget.model.id.toString());
      }
      if (blogListHolder.getList().blogs.isNotEmpty &&
          blogListHolder.getList().blogs[0].postType == PostType.quote &&
          blogListHolder.getList().blogs[0].backgroundImage != null) {
        precacheImage(
          CachedNetworkImageProvider(blogListHolder.getList().blogs[0].backgroundImage.toString()),
          context,
        );
      }
      if (widget.type == BlogOptionType.share) {
        SchedulerBinding.instance.addPostFrameCallback((timeStamp) async {
          Future.delayed(const Duration(milliseconds: 1400)).then((value) async {
            final data = await captureScreenshot(previewContainer, isQuote: true);
            Future.delayed(const Duration(milliseconds: 10));
            final data2 = await convertToXFile(data!);
            Future.delayed(const Duration(milliseconds: 10));
            provider.addShareData(blog.id!.toInt());
            shareImage(data2, "${Urls.baseUrl}share-blog?id=${widget.model.id}");
            //  load=false;
            setState(() {});
          });
        });
      }

      provider.addviewData(blog.id!.toInt());
      setState(() {});
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: widget.initial
          ? (didPop, result) {
              if (currentUser.value.id == null) {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (context) => const LoginPage()),
                );
              } else {
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (context) => const DashboardPage()),
                );
              }
            }
          : (didPop, result) {},
      child: SizedBox(
        width: size(context).width,
        height: size(context).height,
        child: CustomLoader(
          isLoading: load,
          child: GestureDetector(
            onTap: widget.onTap,
            child: Scaffold(
              body: blog.backgroundImage == null
                  ? ShimmerLoader(
                      margin: const EdgeInsets.all(16),
                      width: size(context).width,
                      height: size(context).height,
                    )
                  : Stack(
                      fit: StackFit.expand,
                      children: [
                        RepaintBoundary(
                          key: previewContainer,
                          child: Center(
                            child: Container(
                              width: size(context).width,
                              height: size(context).height,
                              margin: const EdgeInsets.symmetric(vertical: 0, horizontal: 0),
                              decoration: BoxDecoration(borderRadius: BorderRadius.circular(12)),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: blog.backgroundImage != null && blog.backgroundImage!.isNotEmpty
                                    ? CachedNetworkImage(
                                        imageUrl: blog.backgroundImage.toString(),
                                        height: double.infinity,
                                        width: double.infinity,
                                        alignment: Alignment.center,
                                        fit: BoxFit.fitHeight,
                                      )
                                    : blog.images != null && blog.images!.isNotEmpty
                                        ? PageView(
                                            onPageChanged: (value) {
                                              pageIndex = value;
                                              setState(() {});
                                            },
                                            children: [
                                              if (blog.videoUrl != null && blog.videoUrl!.isNotEmpty)
                                                PageStorage(
                                                  bucket: PageStorageBucket(),
                                                  key: ValueKey('ddd'),
                                                  child: Center(
                                                    child: SizedBox(
                                                      height: size(context).height,
                                                      child: PlayAnyVideoPlayer(
                                                        model: widget.model,
                                                        isShortVideo: true,
                                                        isCurrentlyOpened: widget.currIndex == widget.index,
                                                        isAds: true,
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                              ...List.generate(
                                                blog.images!.length,
                                                (index) => CachedNetworkImage(
                                                  imageUrl: blog.images![index].toString(),
                                                  height: double.infinity,
                                                  width: double.infinity,
                                                  errorWidget: (ctc, d, s) {
                                                    return AppIcon(isHandlerImage: true, fit: BoxFit.cover);
                                                  },
                                                  alignment: Alignment.center,
                                                  fit: BoxFit.fitHeight,
                                                ),
                                              ),
                                            ],
                                          )
                                        : SizedBox(),
                              ),
                            ),
                          ),
                        ),
                        if (blog.videoUrl != null && blog.videoUrl!.isNotEmpty && blog.images!.isEmpty)
                          Center(
                            child: SizedBox(
                              height: size(context).height / 1.2,
                              child: PlayAnyVideoPlayer(
                                model: widget.model,
                                isShortVideo: true,
                                isCurrentlyOpened: widget.currIndex == widget.index,
                                isAds: true,
                              ),
                            ),
                          ),
                        // if (blog.images!.isNotEmpty && blog.images!.length > 1)
                        //   Positioned(
                        //     left: languageCode.value.pos == 'rtl' ? null : 16,
                        //     right: languageCode.value.pos == 'rtl' ? 16 : null,
                        //     top: kToolbarHeight,
                        //     child:
                        //   ),
                        Positioned(
                          bottom: 40,
                          right: 0,
                          left: 0,
                          child: Container(
                            padding: const EdgeInsets.only(left: 16, right: 16, bottom: 20),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.center,
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Container(
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(8),
                                    color: Colors.black45,
                                  ),
                                  padding: EdgeInsets.only(left: 12, top: 4, bottom: 4, right: 12),
                                  child: Wrap(
                                    spacing: 4,
                                    children: [
                                      ...List.generate(
                                          blog.images!.length,
                                          (index) => CircleAvatar(
                                                radius: 4,
                                                backgroundColor:
                                                    pageIndex == index ? Colors.white : Colors.white24,
                                              ))
                                    ],
                                  ),
                                ),
                                Spacer(),
                                TapInk(
                                  radius: 100,
                                  pad: 8,
                                  onTap: () async {
                                    try {
                                      load = true;
                                      setState(() {});
                                      Future.delayed(const Duration(milliseconds: 10));
                                      final data = await captureScreenshot(previewContainer, isQuote: true);
                                      Future.delayed(const Duration(milliseconds: 10));
                                      final data2 = await convertToXFile(data!);
                                      Future.delayed(const Duration(milliseconds: 10));
                                      provider.addShareData(blog.id!.toInt());
                                      shareImage(data2, "${Urls.baseServer}share-blog?id=${blog.id}");
                                      load = false;
                                      setState(() {});
                                    } catch (e, stack) {
                                      log(e.toString());
                                      log(stack.toString());
                                    }
                                  },
                                  child: Container(
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(100),
                                      gradient: primaryGradient(context),
                                    ),
                                    padding: const EdgeInsets.all(6),
                                    child: SvgPicture.asset(
                                      SvgImg.share,
                                      colorFilter: colorFilterMode(context, color: Colors.white),
                                      width: 20,
                                      height: 20,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        Positioned(
                          top: 40,
                          right: languageCode.value.pos == 'rtl' ? null : 16,
                          left: languageCode.value.pos == 'rtl' ? 16 : null,
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [RectangleAppNoIcon(width: 80, height: 60)],
                          ),
                        ),
                      ],
                    ),
            ),
          ),
        ),
      ),
    );
  }
}
