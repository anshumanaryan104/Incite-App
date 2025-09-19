import 'dart:async';
import 'dart:convert';
import 'dart:developer';

import 'dart:io';

import 'package:flutter/material.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart' as google;
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/home.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/main/widgets/quotes.dart';
import 'package:easy_audience_network/easy_audience_network.dart' as facebook;
import 'package:incite/utils/app_theme.dart';

import 'package:incite/utils/color_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:incite/widgets/back.dart';
import 'package:incite/widgets/banner_ads.dart';
import 'package:incite/widgets/button.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:incite/widgets/drawer.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/last_widget.dart';
import 'package:incite/widgets/short_wrap.dart';
import 'package:preload_page_view/preload_page_view.dart';
import 'package:provider/provider.dart';
import 'package:unity_ads_plugin/unity_ads_plugin.dart';
import '../../api_controller/app_provider.dart';
import '../../api_controller/repository.dart';
import '../../api_controller/user_controller.dart';
import '../../model/blog.dart';
import '../../utils/theme_util.dart';
import 'blog.dart';
import 'package:http/http.dart' as http;
import 'widgets/blog_ad.dart';

class BlogWrapPage extends StatefulWidget {
  const BlogWrapPage({
    super.key,
    required this.onChanged,
    this.type,
    this.isBookmark = false,
    this.index = 0,
    this.preloadPageController,
    this.isBack = false,
  });
  final ValueChanged onChanged;
  final bool isBookmark;
  final BlogOptionType? type;
  final bool isBack;
  final PreloadPageController? preloadPageController;
  final int index;

  @override
  State<BlogWrapPage> createState() => _BlogWrapPageState();
}

class _BlogWrapPageState extends State<BlogWrapPage> with AutomaticKeepAliveClientMixin {
  int cur = 0;

  bool isWebOpen = false;
  bool isNotExist = false;
  bool isData = true;

  bool isInterstialLoaded = false;
  late int fbAdindex;
  late int adindex;
  late int unityindex;
  google.InterstitialAd? _interstitialAd;
  facebook.InterstitialAd? facebookInterstitialAd;

  bool _isInterstitialAdLoaded = false;

  bool showTopHeader = false;

  @override
  void initState() {
    super.initState();
    // Fix null parsing issues - use safe defaults
    fbAdindex = 0; // Disable ads for now
    // Original code commented for reference:
    // fbAdindex = int.parse(
    //       allSettings.value.fbAdsFrequency != null && allSettings.value.fbAdsFrequency.toString().isEmpty
    //           ? '0'
    //           : allSettings.value.fbAdsFrequency.toString(),
    //     ) +
    //     int.parse(
    //       allSettings.value.admobFrequency != null && allSettings.value.admobFrequency.toString().isEmpty
    //           ? '0'
    //           : allSettings.value.admobFrequency.toString(),
    //     );

    // Fix null parsing issues - use safe defaults
    adindex = 0; // Disable ads for now
    unityindex = 0; // Disable unity ads for now
    // Original code commented for reference:
    // adindex = int.parse(
    //   (allSettings.value.admobFrequency != null && allSettings.value.admobFrequency.toString().isEmpty
    //       ? '0'
    //       : allSettings.value.admobFrequency.toString()),
    // );
    // unityindex = fbAdindex +
    //     int.parse(
    //       (allSettings.value.unityAdsFrequency == null ||
    //               allSettings.value.unityAdsFrequency.toString().isEmpty
    //           ? '0'
    //           : (allSettings.value.unityAdsFrequency ?? "1").toString()),
    //     );
    if (currentUser.value.id != null && widget.isBookmark == false) {
      getStatusAccount(context);
    }
    WidgetsBinding.instance.addPostFrameCallback((timestamp) async {
      if (widget.isBookmark == true && widget.preloadPageController!.hasClients) {
        widget.preloadPageController!.jumpToPage(widget.index);
      } else if (blogListHolder.blogType == BlogType.featured ||
          blogListHolder.blogType == BlogType.category) {
        widget.preloadPageController!.jumpToPage(widget.index);
      }
    });
    if (Platform.isAndroid &&
        allSettings.value.enableFbAds == '1' &&
        allSettings.value.fbInterstitialIdAndroid != null) {
      _loadInterstitialAd();
    }

    if (Platform.isIOS &&
        allSettings.value.enableFbAds == '1' &&
        allSettings.value.fbAdsPlacementIdIos != null) {
      _loadInterstitialAd();
    }

    if (Platform.isAndroid &&
        allSettings.value.enableUnityAds == '1' &&
        allSettings.value.unityPlacementAndoidId != null) {
      UnityAppAdsInBlogs.loadAd(AdManager.interstitialVideoAdPlacementId);
    }
    if (Platform.isIOS &&
        allSettings.value.enableUnityAds == '1' &&
        allSettings.value.unityPlacementIosId != null) {
      UnityAppAdsInBlogs.loadAd(AdManager.interstitialVideoAdPlacementId);
    }
  }

  @override
  void didChangeDependencies() {
    if (blogListHolder.blogType == BlogType.featured || blogListHolder.blogType == BlogType.category) {
      cur = blogListHolder.getIndex();
      setState(() {});
    }

    super.didChangeDependencies();
  }

  void _loadInterstitialAd() {
    facebookInterstitialAd = facebook.InterstitialAd(
      Platform.isIOS == true
          ? allSettings.value.fbInterstitialIdIos ?? ''
          : allSettings.value.fbInterstitialIdAndroid ?? "",
    );

    facebookInterstitialAd!.listener = facebook.InterstitialAdListener(
      onLoaded: () {
        _isInterstitialAdLoaded = true;
        setState(() {});
      },
      onDismissed: () {
        facebookInterstitialAd!.destroy();
        _isInterstitialAdLoaded = false;
        _loadInterstitialAd();
      },
    );
  }

  void createInterstitialAd() {
    Platform.isAndroid &&
            allSettings.value.enableAds == '1' &&
            allSettings.value.admobInterstitialIdAndroid != null
        ? google.InterstitialAd.load(
            adUnitId: allSettings.value.admobInterstitialIdAndroid ?? '',
            request: const google.AdRequest(),
            adLoadCallback: google.InterstitialAdLoadCallback(
              onAdLoaded: (google.InterstitialAd ad) {
                // Keep a reference to the ad so you can show it later.
                isInterstialLoaded = true;
                _interstitialAd = ad;
                setState(() {});
              },
              onAdFailedToLoad: (google.LoadAdError error) {
                isInterstialLoaded = false;
                _interstitialAd!.dispose();
                setState(() {});
                debugPrint('InterstitialAd failed to load: $error');
              },
            ),
          )
        : null;

    Platform.isIOS && allSettings.value.enableAds == '1' && allSettings.value.admobInterstitialIdIos != null
        ? google.InterstitialAd.load(
            adUnitId: allSettings.value.admobInterstitialIdIos ?? '',
            request: const google.AdRequest(),
            adLoadCallback: google.InterstitialAdLoadCallback(
              onAdLoaded: (google.InterstitialAd ad) {
                // Keep a reference to the ad so you can show it later.
                isInterstialLoaded = true;
                _interstitialAd = ad;
                setState(() {});
              },
              onAdFailedToLoad: (google.LoadAdError error) {
                isInterstialLoaded = false;
                _interstitialAd!.dispose();
                setState(() {});
                debugPrint('InterstitialAd failed to load: $error');
              },
            ),
          )
        : null;
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);
    var provider = Provider.of<AppProvider>(context, listen: false);
    return widget.isBookmark == false && blogListHolder.getList().blogs.isEmpty
        ? const NoPostFound()
        : Stack(
            children: [
              GestureDetector(
                onTap: () {
                  showTopHeader = !showTopHeader;
                  setState(() {});
                },
                child: PreloadPageView.builder(
                  key: ValueKey(
                    widget.isBookmark ? blogListHolder2.getList().total : blogListHolder.getBlogType(),
                  ),
                  itemCount: widget.isBookmark
                      ? blogListHolder2.getList().blogs.length
                      : blogListHolder.getList().blogs.length,
                  physics: isWebOpen
                      ? const NeverScrollableScrollPhysics()
                      : MediaQuery.of(context).orientation == Orientation.landscape
                          ? const NeverScrollableScrollPhysics()
                          : const BouncingScrollPhysics(
                              decelerationRate: ScrollDecelerationRate.fast,
                              parent: CustomPageViewScrollPhysics(),
                            ),
                  controller: widget.preloadPageController,
                  scrollDirection: Axis.vertical,
                  preloadPagesCount: 7,
                  onPageChanged: (value) {
                    cur = value;
                    isWebOpen = false;

                    addFeedLast(provider);

                    if (widget.isBookmark) {
                      blogListHolder2.setIndex(value);
                    } else {
                      blogListHolder.setIndex(value);
                    }

                    if (widget.isBookmark == false) {
                      var fb = int.parse(allSettings.value.fbAdsFrequency.toString()) +
                          int.parse(allSettings.value.admobFrequency.toString());

                      if (value != 0 &&
                          value % 13 == 0 &&
                          widget.isBookmark == false &&
                          blogListHolder.getList().nextPageUrl != null &&
                          provider.calledPageurl != blogListHolder.getList().lastPageUrl) {
                        provider.getCategory(nextpageurl: blogListHolder.getList().nextPageUrl ?? '');
                      }

                      addNewsLast(provider);
                      addCategoryLast(provider);

                      if (widget.isBookmark == false && blogListHolder.getList().blogs[value].type == 'ads') {
                        provider.adsViewData(blogListHolder.getList().blogs[value].id ?? 0);
                      } else if (widget.isBookmark == false &&
                          blogListHolder.getList().blogs[value].type != 'quote' &&
                          (blogListHolder.getList().blogs[value].title != 'Last-Category' &&
                              blogListHolder.getList().blogs[value].title != 'Last-Feed' &&
                              blogListHolder.getList().blogs[value].title != 'Last-News')) {
                        provider.addviewData(blogListHolder.getList().blogs[value].id!.toInt());
                      }

                      showTopHeader = false;

                      fbInterstitialAds(value, fb);

                      loadGoogleInterstitialAds(value);

                      loadUnityInterstitialAds(value);
                    }
                    setState(() {});
                  },
                  itemBuilder: (context, index) {
                    if (widget.isBookmark) {
                      return blogListHolder2.getList().blogs[index].postType == PostType.video
                          ? PlayAnyVideoPlayer(
                              key: ValueKey(blogListHolder2.getList().blogs[index].id),
                              model: blogListHolder2.getList().blogs[index],
                              isShortVideo: true,
                            )
                          : blogListHolder2.getList().blogs[index].postType == PostType.quote
                              ? QuotePage(
                                  currIndex: cur,
                                  index: index,
                                  model: blogListHolder2.getList().blogs[index],
                                  key: ValueKey(index),
                                )
                              : blogListHolder2.getList().blogs[index].postType == PostType.ads
                                  ? BlogAd(
                                      model: blogListHolder2.getList().blogs[index],
                                      isBack: true,
                                      index: index,
                                      currIndex: cur,
                                      key: ValueKey(index),
                                    )
                                  : blogListHolder2.getList().blogs[index].postType == PostType.image
                                      ? BlogPage(
                                          key: ValueKey(index),
                                          isBackAllowed: true,
                                          index: index,
                                          currIndex: cur,
                                          onChanged: (value) {
                                            isWebOpen = value;
                                            showTopHeader = !value;
                                            setState(() {});
                                          },
                                          model: blogListHolder2.getList().blogs[index],
                                        )
                                      : blogListHolder2.getList().blogs[index].title == 'Last-Bookmark' &&
                                              blogListHolder2.getList().blogs[index].id == 2345678
                                          ? LastNewsWidget(
                                              onBack: () {
                                                Navigator.pop(context);
                                              },
                                              keyword: allMessages.value.mySavedStories,
                                              isButton: false,
                                            )
                                          : const SizedBox();
                    } else {
                      var isLastWidget = blogListHolder.getList().blogs[index].title == 'Last-Category' ||
                          blogListHolder.getList().blogs[index].title == 'Last-Feed' ||
                          blogListHolder.getList().blogs[index].title == 'Last-News';
                      return isLastWidget
                          ? LastNewsWidget(
                              key: ValueKey(blogListHolder.getList().blogs[index].id),
                              onBack: widget.isBack
                                  ? () {
                                      Navigator.pop(context);
                                    }
                                  : () {
                                      widget.onChanged(0);
                                    },
                              keyword: "${blogListHolder.getList().blogs[index].categoryName} Stories",
                              onTap: widget.isBack
                                  ? () {
                                      Navigator.pop(context);
                                    }
                                  : () {
                                      blogListHolder.clearList();
                                      provider.allNews!.blogs = provider.allNewsBlogs;
                                      blogListHolder.setBlogType(BlogType.allnews);
                                      blogListHolder.setList(provider.allNews as DataModel);

                                      setState(() {});
                                    },
                            )
                          : blogListHolder.getList().blogs[index].title == 'Last-Featured' &&
                                  blogListHolder.getList().blogs[index].id == 2345678876543212345
                              ? LastNewsWidget(
                                  onBack: () {
                                    widget.onChanged(0);
                                  },
                                  keyword: allMessages.value.featuredStories,
                                  onTap: () {
                                    blogListHolder.clearList();
                                    provider.allNews!.blogs = provider.allNewsBlogs;
                                    blogListHolder.setList(provider.allNews as DataModel);
                                    blogListHolder.setBlogType(BlogType.allnews);
                                    setState(() {});
                                  },
                                )
                              : blogListHolder.getList().blogs[index].postType == PostType.video
                                  ? PlayAnyVideoPlayer(
                                      isShortVideo: true,
                                      videoUrl: blogListHolder.getList().blogs[index].videoUrl ?? "",
                                    )
                                  : blogListHolder.getList().blogs[index].postType == PostType.quote
                                      ? QuotePage(
                                          model: blogListHolder.getList().blogs[index],
                                          type: widget.type,
                                          onTap: () {
                                            showTopHeader = !showTopHeader;
                                            setState(() {});
                                          },
                                          key: ValueKey(index),
                                        )
                                      : blogListHolder.getList().blogs[index].postType == PostType.ads
                                          ? BlogAd(
                                              isBack: widget.isBack,
                                              model: blogListHolder.getList().blogs[index],
                                              onTap: () {
                                                showTopHeader = !showTopHeader;
                                                setState(() {});
                                              },
                                              key: ValueKey(index),
                                            )
                                          : blogListHolder.getList().blogs[index].postType == PostType.image
                                              ? BlogPage(
                                                  onChanged: (value) {
                                                    isWebOpen = value;
                                                    setState(() {});
                                                  },
                                                  type: widget.type,
                                                  onTap: () {
                                                    showTopHeader = !showTopHeader;
                                                    setState(() {});
                                                  },
                                                  key: ValueKey(
                                                      "$index${blogListHolder.getList().blogs[index].id}"),
                                                  isBackAllowed: widget.isBack,
                                                  index: index,
                                                  currIndex: cur,
                                                  model: blogListHolder.getList().blogs[index],
                                                )
                                              : const SizedBox();
                    }
                  },
                ),
              ),
              AnimatedPositioned(
                left: 0,
                right: 0,
                duration: const Duration(milliseconds: 300),
                curve: Curves.easeInSine,
                child: InciteHeader(
                  showTopHeader: showTopHeader,
                  onTap: widget.isBack
                      ? () {
                          Navigator.pop(context);
                        }
                      : () {
                          widget.onChanged(false);
                          setState(() {});
                        },
                ),
              ),
            ],
          );
  }

  void fbInterstitialAds(int value, int fb) {
    if (_isInterstitialAdLoaded == true &&
        allSettings.value.enableFbAds != '0' &&
        value % fbAdindex == 0 &&
        facebookInterstitialAd != null) {
      facebookInterstitialAd!.show();
      fbAdindex += fb;
    } else {
      debugPrint("Interstial Ad not yet loaded!");
    }
  }

  void loadGoogleInterstitialAds(int value) {
    if (isInterstialLoaded && _interstitialAd != null && value != 0 && value % adindex == 0) {
      adindex += int.parse(allSettings.value.admobFrequency!.toString()) * 2;
      _interstitialAd!.show();
    } else {
      _interstitialAd = null;
      createInterstitialAd();
    }
  }

  void loadUnityInterstitialAds(int value) {
    if (value != 0 && allSettings.value.enableUnityAds == '1' && value % unityindex == 0) {
      unityindex += int.parse((allSettings.value.unityAdsFrequency ?? '1').toString()) * 2;
      UnityAppAdsInBlogs.showAd(AdManager.interstitialVideoAdPlacementId);
      log('------- unity ads -------------');
    }
  }

  Future getLatestCategory({AppProvider? provider, String? nextpageurl}) async {
    var _blog = provider!.blog;

    try {
      DataCollection? anotherBlog;
      var url = nextpageurl ?? '';

      var result = await http.get(
        Uri.parse(url),
        headers: currentUser.value.id != null
            ? {
                HttpHeaders.contentTypeHeader: "application/json",
                "api-token": currentUser.value.apiToken ?? '',
                "language-code": languageCode.value.language ?? '',
              }
            : {
                HttpHeaders.contentTypeHeader: "application/json",
                "language-code": languageCode.value.language ?? '',
              },
      );
      final data = json.decode(result.body);
      anotherBlog = DataCollection.fromJson(data);

      if (nextpageurl != null) {
        for (var i = 0; i < anotherBlog.categories!.length; i++) {
          //for (var i = 0; i < _blog.; i++) {
          _blog!.categories![i].data!.currentPage = anotherBlog.categories![i].data!.currentPage;
          _blog.categories![i].data!.firstPageUrl = anotherBlog.categories![i].data!.firstPageUrl;
          _blog.categories![i].data!.lastPageUrl = anotherBlog.categories![i].data!.lastPageUrl;
          _blog.categories![i].data!.nextPageUrl = anotherBlog.categories![i].data!.nextPageUrl;
          _blog.categories![i].data!.to = anotherBlog.categories![i].data!.currentPage;
          _blog.categories![i].data!.prevPageUrl = anotherBlog.categories![i].data!.prevPageUrl;
          _blog.categories![i].data!.lastPage = anotherBlog.categories![i].data!.currentPage;
          _blog.categories![i].data!.from = anotherBlog.categories![i].data!.currentPage;
          _blog.categories![i].data!.blogs.addAll(anotherBlog.categories![i].data!.blogs);

          // }
        }
      }

      if (data['success'] == true) {
        DataModel? allNews, feed;
        var blog = Blog(
          title: 'Last-Featured',
          id: 2345678876543212345,
          sourceName: allMessages.value.great,
          description: "${allMessages.value.youHaveViewedAll} ${allMessages.value.featuredStories}",
        );
        List<Blog> allNewsBlogs = [], feedBlogs = [];
        for (var i = 0; i < anotherBlog.categories!.length; i++) {
          for (var j = 0; j < anotherBlog.categories![i].data!.blogs.length; j++) {
            if (anotherBlog.categories![i].isFeed == true) {
              if (!provider.feedBlogs.contains(anotherBlog.categories![i].data!.blogs[j])) {
                feedBlogs.add(anotherBlog.categories![i].data!.blogs[j]);
              }
            }

            if (anotherBlog.categories![i].data!.blogs[j].isFeatured == 1 &&
                provider.featureBlogs.length != 11) {
              if (provider.featureBlogs.contains(blog)) {
                provider.featureBlogs.remove(blog);
              }

              if (!provider.featureBlogs.contains(anotherBlog.categories![i].data!.blogs[j])) {
                provider.featureBlogs.add(anotherBlog.categories![i].data!.blogs[j]);
              }
            }

            if (!provider.allNewsBlogs.contains(anotherBlog.categories![i].data!.blogs[j])) {
              allNewsBlogs.add(anotherBlog.categories![i].data!.blogs[j]);
            }
          }
        }
        provider.setCalledUrl(nextpageurl);

        allNewsBlogs.sort((a, b) {
          return DateTime.parse(
            b.scheduleDate.toString(),
          ).compareTo(DateTime.parse(a.scheduleDate.toString()));
        });
        feedBlogs.sort((a, b) {
          return DateTime.parse(
            b.scheduleDate.toString(),
          ).compareTo(DateTime.parse(a.scheduleDate.toString()));
        });

        if (!provider.featureBlogs.contains(blog)) {
          provider.featureBlogs.add(blog);
        }

        if (blogAds.value.isNotEmpty) {
          provider.allNewsBlogs.addAll(await provider.arrangeAds(allNewsBlogs));
          provider.feedBlogs.addAll(await provider.arrangeAds(feedBlogs));
        } else {
          provider.allNewsBlogs.addAll(allNewsBlogs);
          provider.feedBlogs.addAll(feedBlogs);
        }

        feed = DataModel(
          currentPage: _blog!.categories![0].data!.currentPage,
          firstPageUrl: _blog.categories![0].data!.firstPageUrl,
          lastPageUrl: _blog.categories![0].data!.lastPageUrl,
          nextPageUrl: _blog.categories![0].data!.nextPageUrl,
          to: _blog.categories![0].data!.to,
          prevPageUrl: _blog.categories![0].data!.prevPageUrl,
          lastPage: _blog.categories![0].data!.lastPage,
          from: _blog.categories![0].data!.from,
          blogs: provider.feedBlogs,
        );

        allNews = DataModel(
          currentPage: _blog.categories![0].data!.currentPage,
          firstPageUrl: _blog.categories![0].data!.firstPageUrl,
          lastPageUrl: _blog.categories![0].data!.lastPageUrl,
          nextPageUrl: _blog.categories![0].data!.nextPageUrl,
          to: _blog.categories![0].data!.to,
          prevPageUrl: _blog.categories![0].data!.prevPageUrl,
          lastPage: _blog.categories![0].data!.lastPage,
          from: _blog.categories![0].data!.from,
          blogs: provider.allNewsBlogs,
        );

        if (blogListHolder.getBlogType() == BlogType.feed) {
          blogListHolder.updateList(feed);
        } else if (blogListHolder.getBlogType() == BlogType.allnews) {
          blogListHolder.updateList(allNews);
        } else {
          blogListHolder.updateList(provider.blog!.categories![provider.categoryIndex].data as DataModel);
        }

        provider.setCategoryBlog(_blog);
        provider.setAllNews(load: allNews);
        provider.setMyFeed(load: feed);
      }
    } on SocketException {
      showCustomToast(context, allMessages.value.noInternetConnection ?? 'No Internet Connection');
    } finally {}
  }

  void addCategoryLast(AppProvider provider) {
    if (provider.calledPageurl == provider.blog!.categories![provider.categoryIndex].data!.lastPageUrl &&
        !provider.blog!.categories![provider.categoryIndex].data!.blogs.contains(
          Blog(
            categoryName: provider.blog!.categories![provider.categoryIndex].name,
            id: provider.blog!.categories![provider.categoryIndex].id,
            title: 'Last-Category',
          ),
        )) {
      provider.blog!.categories![provider.categoryIndex].data!.blogs.add(
        Blog(
          categoryName: provider.blog!.categories![provider.categoryIndex].name,
          id: provider.blog!.categories![provider.categoryIndex].id,
          title: 'Last-Category',
        ),
      );
    }
  }

  void addNewsLast(AppProvider provider) {
    if (provider.calledPageurl == provider.allNews!.lastPageUrl) {
      if (blogListHolder.getBlogType() == BlogType.allnews &&
          !blogListHolder.getList().blogs.contains(
                Blog(title: 'Last-News', categoryName: "All News", id: 1111111111111, sourceName: 'Great'),
              )) {
        blogListHolder.getList().blogs.add(
              Blog(title: 'Last-News', categoryName: "All News", id: 1111111111111, sourceName: 'Great'),
            );
      } else {
        if (blogListHolder.getBlogType() != BlogType.allnews &&
            blogListHolder.getList().blogs.contains(
                  Blog(title: 'Last-News', categoryName: "All News", id: 1111111111111, sourceName: 'Great'),
                )) {
          blogListHolder.getList().blogs.remove(
                Blog(title: 'Last-News', categoryName: "All News", id: 1111111111111, sourceName: 'Great'),
              );
        }
      }
    }
  }

  void addFeedLast(AppProvider provider) {
    if (provider.calledPageurl == provider.feed!.lastPageUrl) {
      if (blogListHolder.getBlogType() == BlogType.feed &&
          !blogListHolder.getList().blogs.contains(
                Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
              )) {
        blogListHolder.getList().blogs.add(
              Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
            );
      } else {
        if (blogListHolder.getBlogType() != BlogType.feed &&
            blogListHolder.getList().blogs.contains(
                  Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
                )) {
          blogListHolder.getList().blogs.remove(
                Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
              );
        }
      }
    }
  }

  @override
  bool get wantKeepAlive => isData;
}

class NoPostFound extends StatelessWidget {
  const NoPostFound({super.key});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size(context).width,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          AnimationFadeScale(
            child: Image.asset(
              'assets/images/confuse.png',
              width: 100,
              height: 100,
              color: dark(context) ? ColorUtil.white : ColorUtil.blackGrey,
            ),
          ),
          const SizedBox(height: 12),
          AnimationFadeSlide(
            dx: 0,
            dy: 0.6,
            child: Text(allMessages.value.oops ?? 'Oops!!', style: Theme.of(context).textTheme.displayMedium),
          ),
          const SizedBox(height: 12),
          AnimationFadeSlide(
            dx: 0,
            dy: 0.4,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Text(
                blogListHolder.blogType == BlogType.feed
                    ? allMessages.value.nofeedSelected ??
                        'Seems like you have no interests selected. Please refer to page below to select interests'
                    : allMessages.value.noCategoryPost ?? 'No post found related to this Category.',
                style: Theme.of(context).textTheme.titleMedium,
                textAlign: TextAlign.center,
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (blogListHolder.blogType == BlogType.feed)
            ElevateButton(
              width: 120,
              style: const TextStyle(
                fontSize: 16,
                fontFamily: 'Roboto',
                color: Colors.white,
                fontWeight: FontWeight.w500,
              ),
              onTap: currentUser.value.id == null
                  ? () {
                      Navigator.pushNamed(context, '/LoginPage');
                    }
                  : () {
                      Navigator.pushNamed(context, '/SaveInterests', arguments: false);
                    },
              text: allMessages.value.myFeed ?? 'My feed',
            ),
        ],
      ),
    );
  }
}

class InciteHeader extends StatefulWidget {
  const InciteHeader({
    super.key,
    required this.showTopHeader,
    required this.onTap,
    this.padding,
    this.height,
    this.blur,
    this.showBackOnly = false,
  });

  final bool showTopHeader, showBackOnly;
  final VoidCallback onTap;
  final double? height, blur;
  final EdgeInsetsGeometry? padding;

  @override
  State<InciteHeader> createState() => _InciteHeaderState();
}

class _InciteHeaderState extends State<InciteHeader> {
  @override
  Widget build(BuildContext context) {
    return BlurWidget(
      width: size(context).width,
      radius: 0,
      blur: widget.blur,
      border: Border.all(width: 0, color: Colors.transparent),
      padding:
          widget.padding ?? const EdgeInsets.only(top: kToolbarHeight - 16, bottom: 8, left: 16, right: 16),
      height: widget.showTopHeader == true ? widget.height ?? 100 : 0,
      color: Theme.of(context).cardColor.customOpacity(0.75),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Backbut(key: const ValueKey('34543'), onTap: widget.onTap),
          if (widget.showBackOnly == false) const RectangleAppIcon(width: 70, height: 50),
          if (widget.showBackOnly == false)
            ToggleButton(
              isON: appThemeModel.value.isAutoPlay.value,
              isNativeToggle: false,
              onNormalToggleTap: () {
                if (appThemeModel.value.isAutoPlay.value == true) {
                  appThemeModel.value.isAutoPlay.value = false;
                } else {
                  appThemeModel.value.isAutoPlay.value = true;
                  showPopUpCustomToast(
                    context,
                    allMessages.value.autoPlayAlert ?? "Video Will be AutoPlay Now.",
                  );
                }
                toggleAutoPlay(appThemeModel.value.isAutoPlay.value);
                setState(() {});
              },
            ),
        ],
      ),
    );
  }
}

class CustomPageViewScrollPhysics extends ScrollPhysics {
  const CustomPageViewScrollPhysics({super.parent});

  @override
  CustomPageViewScrollPhysics applyTo(ScrollPhysics? ancestor) {
    return CustomPageViewScrollPhysics(parent: buildParent(ancestor)!);
  }

  @override
  SpringDescription get spring => SpringDescription(
      mass: 50,
      stiffness: 50,
      damping:
          1.33); // Critical damping+ to prevent overshoot); // Slightly higher damping to prevent overshooting);
}
