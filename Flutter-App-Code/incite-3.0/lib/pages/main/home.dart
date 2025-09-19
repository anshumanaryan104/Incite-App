import 'dart:io';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/api_controller/news_repo.dart';
import 'package:incite/api_controller/repository.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/main/widgets/poll.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/rgbo_to_hex.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:incite/widgets/drawer.dart';
import 'package:incite/widgets/tap.dart';
import 'package:provider/provider.dart';

import '../../api_controller/app_provider.dart';
import '../../api_controller/blog_controller.dart';
import '../../api_controller/user_controller.dart';
import '../../model/blog.dart';
import '../../splash_screen.dart';
import '../../utils/app_theme.dart';
import '../../utils/image_util.dart';
import '../../widgets/shimmer.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key, required this.onChanged, this.initial = false, required this.menuTapped});
  final ValueChanged<int> onChanged;
  final bool initial;
  final ValueChanged<bool> menuTapped;

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> with AutomaticKeepAliveClientMixin {
  var controller = TextEditingController();

  int isCurr = 0;

  PageController pageController = PageController(viewportFraction: 0.9);
  GlobalKey<ScaffoldState> sfkey = GlobalKey();

  bool isLoading = true, isLoadQuotes = true, isloadPolls = true;
  late UserProvider userProvider;

  GlobalKey<RefreshIndicatorState> refreshkey = GlobalKey<RefreshIndicatorState>();

  @override
  void initState() {
    userProvider = UserProvider();
    prefs!.remove('data');
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      if (widget.initial) {
        refreshkey.currentState?.show();
      }

      if (!prefs!.containsKey('player_id')) {
        await updateToken();
      }

      if (currentUser.value.id != null) {
        getNotification().then((value) {
          appThemeModel.value.isNotificationEnabled.value = value as bool;
          setState(() {});
        });
      }

      getBlogPollOrQuotes(
          type: 'quote',
          onLoading: (value) {
            isLoadQuotes = value;
            setState(() {});
          });
      getBlogPollOrQuotes(
          type: 'post',
          onLoading: (value) {
            isloadPolls = value;
            setState(() {});
          });

      Future.delayed(const Duration(milliseconds: 1000), () {
        isLoading = false;
        setState(() {});
      });
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    var appProvider = Provider.of<AppProvider>(context, listen: false);

    super.build(context);
    var size = MediaQuery.of(context).size;
    var length = appProvider.featureBlogs.isEmpty ? 5 : appProvider.featureBlogs.length;
    return Consumer<AppProvider>(builder: (context, appProvider, child) {
      var whenFeaturedStoriesPresent = appProvider.featureBlogs.isNotEmpty && isLoading == false;
      var whenFeaturedStoriesNotPresent = appProvider.featureBlogs.isEmpty && isLoading == false;
      var whenFeaturedStoriesNotPresentLoading = appProvider.featureBlogs.isEmpty && isLoading == true;
      return Scaffold(
        drawer: const DrawerPage(),
        key: sfkey,
        backgroundColor: Theme.of(context).scaffoldBackgroundColor,
        extendBody: true,
        onDrawerChanged: (isOpened) {
          if (isOpened == true) {
            widget.menuTapped(true);
          } else {
            widget.menuTapped(false);
          }
        },
        appBar: AppBar(
          leadingWidth: 0,
          titleSpacing: 0,
          elevation: 0,
          toolbarHeight: 66,
          automaticallyImplyLeading: false,
          title: Container(
            width: size.width,
            padding: const EdgeInsets.symmetric(horizontal: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                TapInk(
                  radius: 100,
                  onTap: () {
                    sfkey.currentState!.openDrawer();
                    // Navigator.push(context, PagingTransform(widget: const BookmarkPage()));
                  },
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 17, vertical: 15),
                    child: SvgPicture.asset(
                      SvgImg.menu,
                      width: 21,
                      height: 15,
                      colorFilter: ColorFilter.mode(
                          dark(context) ? ColorUtil.white : ColorUtil.textblack, BlendMode.srcIn),
                    ),
                  ),
                ),
                Expanded(
                  child: Row(
                    children: [
                      const RectangleAppIcon(),
                      const Spacer(),
                      TapInk(
                        radius: 100,
                        onTap: () {
                          Navigator.pushNamed(context, '/SearchPage');
                        },
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
                          child: Hero(
                            tag: "search124",
                            child: SvgPicture.asset(SvgImg.search,
                                width: 22,
                                height: 22,
                                colorFilter: ColorFilter.mode(
                                    dark(context) ? ColorUtil.white : ColorUtil.textblack, BlendMode.srcIn)),
                          ),
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: ProfileWidget(
                          radius: 18,
                          size: 22,
                          onTap: currentUser.value.id == null
                              ? () {
                                  Navigator.pushNamed(context, '/LoginPage');
                                }
                              : () {
                                  Navigator.pushNamed(context, '/UserProfile', arguments: true);
                                },
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        body: RefreshIndicator(
          key: refreshkey,
          onRefresh: () async {
            await Future.delayed(const Duration(milliseconds: 2000));
            var userProvider = UserProvider();

            // userProvider.checkSettingUpdate(); // Skip update check
            // Skip analytics and ads - just get categories
            appProvider.getCategory();
            // userProvider.adBlogs();  // Skip ads

            setState(() {});
          },
          child: SafeArea(
            child: CustomScrollView(
              slivers: [
                SliverPadding(
                  padding: const EdgeInsets.only(left: 24, right: 24),
                  sliver: SliverToBoxAdapter(
                    child: TopHeader(
                        provider: appProvider,
                        isLoad: isLoading,
                        onTap: () {
                          if (currentUser.value.id != null) {
                            appProvider.feed!.blogs = appProvider.feedBlogs;
                            blogListHolder.setList(appProvider.feed as DataModel);
                            blogListHolder.setBlogType(BlogType.feed);
                            widget.onChanged(0);
                            setState(() {});
                          } else {
                            Navigator.pushNamed(context, '/LoginPage');
                          }
                        }),
                  ),
                ),
                SliverPadding(
                  padding: EdgeInsets.only(
                      top: isLoading == true
                          ? 16
                          : whenFeaturedStoriesNotPresentLoading
                              ? 16
                              : whenFeaturedStoriesPresent
                                  ? 16
                                  : 0,
                      bottom: 24),
                  sliver: SliverToBoxAdapter(
                    child: SizedBox(
                      height: whenFeaturedStoriesNotPresent ? 0 : size.height * 0.28,
                      child: Column(
                        children: [
                          Expanded(
                            child: PageView(
                                padEnds: true,
                                controller: pageController,
                                onPageChanged: (value) {
                                  isCurr = value;
                                  setState(() {});
                                },
                                children: isLoading == true
                                    ? [
                                        ...List.generate(
                                            5,
                                            (index) => ShimmerLoader(
                                                  width: size.width * 0.85,
                                                  height: size.height * 0.258,
                                                  margin:
                                                      EdgeInsets.only(left: index == 0 ? 4 : 0, right: 12),
                                                ))
                                      ]
                                    : [
                                        ...List.generate(
                                            whenFeaturedStoriesNotPresentLoading
                                                ? 5
                                                : whenFeaturedStoriesPresent
                                                    ? length - 1
                                                    : 0,
                                            (index) => whenFeaturedStoriesNotPresentLoading || appProvider.featureBlogs.isEmpty
                                                ? FContainer(
                                                      title: 'Loading...',
                                                      image: '',
                                                      category: 'Loading',
                                                      currIndex: isCurr,
                                                      color: '#FF6B6B',
                                                      index: index,
                                                      onTap: () {},
                                                      isfirst: index == 0,
                                                    )
                                                : FContainer(
                                                      title: appProvider.featureBlogs[index].title,
                                                  image: appProvider.featureBlogs[index].images != null &&
                                                          appProvider.featureBlogs[index].images!.isNotEmpty
                                                      ? appProvider.featureBlogs[index].images![0]
                                                      : '',
                                                  category: appProvider.featureBlogs[index].categoryName,
                                                  currIndex: isCurr,
                                                  color: appProvider.featureBlogs[index].categoryColor,
                                                  index: index,
                                                  onTap: () {
                                                    blogListHolder
                                                        .setList(DataModel(blogs: appProvider.featureBlogs));
                                                    blogListHolder.setBlogType(BlogType.featured);
                                                    setState(() {});
                                                    widget.onChanged(index);
                                                  },
                                                  isfirst: index == 0,
                                                )),
                                      ]),
                          ),
                          if (appProvider.featureBlogs.length > 2) const SizedBox(height: 15),
                          if (appProvider.featureBlogs.length > 2)
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                ...List.generate(
                                    appProvider.blog == null ? 5 : length - 1,
                                    (index) => Container(
                                        margin: const EdgeInsets.only(right: 5),
                                        child: CirlceDot(
                                          radius: isCurr == index ? 9 : 7,
                                          color: isCurr == index
                                              ? null
                                              : dark(context)
                                                  ? Theme.of(context).cardColor
                                                  : ColorUtil.whiteGrey,
                                        )))
                              ],
                            )
                        ],
                      ),
                    ),
                  ),
                ),
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  sliver: SliverToBoxAdapter(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (appProvider.featureBlogs.isNotEmpty && isLoading == false)
                          Text(allMessages.value.filterByTopics ?? 'Filter By Topics',
                              style: const TextStyle(
                                fontFamily: 'Roboto',
                                fontSize: 18,
                                fontWeight: FontWeight.w600,
                              )),
                        const SizedBox(height: 20),
                        GridView(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: int.parse(allSettings.value.categoryIconColumn ?? '4'),
                                crossAxisSpacing: 15,
                                mainAxisSpacing: 15,
                                mainAxisExtent: 100,
                                childAspectRatio: 4),
                            children: appProvider.blog == null
                                ? [...List.generate(12, (es) => const CategoryShimmer())]
                                : [
                                    AnimationFadeSlide(
                                        key: const ValueKey('345'),
                                        dx: 0.1,
                                        child: TopicContainer(
                                            key: const ValueKey('3456'),
                                            image:
                                                "${allSettings.value.baseImageUrl}/${allSettings.value.appLogo}",
                                            onTap: () {
                                              blogListHolder.clearList();
                                              appProvider.allNews!.blogs = appProvider.allNewsBlogs;
                                              blogListHolder.setList(appProvider.allNews as DataModel);
                                              widget.onChanged(0);
                                              blogListHolder.setBlogType(BlogType.allnews);
                                              setState(() {});
                                            },
                                            title: allMessages.value.allNews ?? 'All News')),
                                    if ((appProvider.blog == null || appProvider.blog != null) &&
                                        appProvider.blog!.categories != null &&
                                        appProvider.blog!.categories!.isEmpty)
                                      ...List.generate(7, (index) => const CategoryShimmer())
                                    else
                                      ...appProvider.blog!.categories!.asMap().entries.map((e) =>
                                          AnimationFadeSlide(
                                            key: ValueKey(
                                                "${e.key}${appProvider.blog!.categories![e.key].id}"),
                                            dx: 0.0250 * e.key + 1,
                                            child: TopicContainer(
                                                key:
                                                    ValueKey("123${appProvider.blog!.categories![e.key].id}"),
                                                onTap: () async {
                                                  if (blogAds.value.isNotEmpty) {
                                                    var blogs = await appProvider.arrangeAds(
                                                        appProvider.blog!.categories![e.key].data!.blogs);
                                                    appProvider.blog!.categories![e.key].data!.blogs = blogs;
                                                  }
                                                  if (appProvider.calledPageurl ==
                                                          appProvider
                                                              .blog!.categories![e.key].data!.lastPageUrl &&
                                                      !appProvider.blog!.categories![e.key].data!.blogs
                                                          .contains(Blog(
                                                              categoryName:
                                                                  appProvider.blog!.categories![e.key].name,
                                                              id: appProvider.blog!.categories![e.key].id,
                                                              title: 'Last-Category'))) {
                                                    appProvider.blog!.categories![e.key].data!.blogs.add(Blog(
                                                      categoryName: appProvider.blog!.categories![e.key].name,
                                                      id: appProvider.blog!.categories![e.key].id,
                                                      title: 'Last-Category',
                                                    ));
                                                  }
                                                  blogListHolder.clearList();
                                                  blogListHolder
                                                      .setList(appProvider.blog!.categories![e.key].data!);
                                                  widget.onChanged(0);
                                                  appProvider.setCategoryIndex(e.key);
                                                  blogListHolder.setBlogType(BlogType.category);
                                                  setState(() {});
                                                },
                                                title: e.value.name,
                                                image: e.value.image),
                                          )),
                                    if (allSettings.value.liveNewsStatus == '1')
                                      TopicContainer(
                                          title: allMessages.value.liveNews ?? 'Live News',
                                          onTap: () {
                                            Navigator.pushNamed(context, '/LiveNews');
                                          },
                                          image:
                                              '${allSettings.value.baseImageUrl}/${allSettings.value.liveNewsLogo}'),
                                    if (allSettings.value.ePaperStatus == '1')
                                      TopicContainer(
                                          title: allMessages.value.eNews ?? 'E News',
                                          image:
                                              '${allSettings.value.baseImageUrl}/${allSettings.value.ePaperLogo}',
                                          onTap: () {
                                            Navigator.pushNamed(context, '/ENews');
                                          }),
                                  ]),
                      ],
                    ),
                  ),
                ),
                SliverToBoxAdapter(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(height: 10),
                      if (isLoadQuotes == false && quotes != null && quotes!.blogs.isNotEmpty)
                        TopHeader(
                          onTap: () {
                            blogListHolder.setList(quotes as DataModel);
                            blogListHolder.setBlogType(BlogType.category);

                            widget.onChanged(0);
                            setState(() {});
                          },
                          provider: appProvider,
                          padding: EdgeInsets.only(top: 14, left: 16, right: 16),
                          leadingText: allMessages.value.quotes,
                          endingText: allMessages.value.view,
                        ),
                      if (isLoadQuotes == false && quotes != null && quotes!.blogs.isNotEmpty)
                        SizedBox(height: 6),
                      if (isLoadQuotes == false && quotes != null && quotes!.blogs.isNotEmpty)
                        QuoteWrap(
                          isLoading: isLoadQuotes,
                          onPageTap: widget.onChanged,
                        ),
                      SizedBox(height: 20),
                      if (isloadPolls == false && blogPolls != null && blogPolls!.blogs.isNotEmpty)
                        TopHeader(
                          onTap: () {
                            blogListHolder.setList(blogPolls as DataModel);
                            blogListHolder.setBlogType(BlogType.category);
                            widget.onChanged(0);
                            setState(() {});
                          },
                          provider: appProvider,
                          padding: EdgeInsets.only(top: 14, left: 16, right: 16),
                          leadingText: allMessages.value.poll ?? "",
                          endingText: allMessages.value.view,
                        ),
                      if (isLoadQuotes == false && blogPolls != null && blogPolls!.blogs.isNotEmpty)
                        SizedBox(height: 6),
                      if (isLoadQuotes == false && blogPolls != null && blogPolls!.blogs.isNotEmpty)
                        BlogPollWrap(
                          isLoading: isloadPolls,
                          onPageTap: (vale) {
                            blogListHolder.setList(blogPolls as DataModel);
                            blogListHolder.setBlogType(BlogType.category);
                            blogListHolder.setIndex(vale);

                            widget.onChanged(vale);
                            setState(() {});
                          },
                        ),
                      SizedBox(height: 30),
                      Center(
                        child: Text(allMessages.value.stayBlessedAndConnected ?? 'Stay Blessed & Connected',
                            style: const TextStyle(fontFamily: 'Roboto', fontSize: 14, color: Colors.grey)),
                      ),
                      SizedBox(height: Platform.isIOS ? 90 : 100),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    });
  }

  @override
  bool get wantKeepAlive => true;
}

class CategoryShimmer extends StatelessWidget {
  const CategoryShimmer({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 68,
      height: 68,
      child: Column(
        children: [
          ShimmerLoader(
            borderRadius: allSettings.value.categoryIconShape == 'square' ? 8 : 100,
            width: 56,
            height: 56,
          ),
          const SizedBox(height: 12),
          const ShimmerLoader(
            width: 68,
            height: 12,
            borderRadius: 4,
          )
        ],
      ),
    );
  }
}

class TopicContainer extends StatelessWidget {
  const TopicContainer({super.key, this.image, this.title, this.onTap});

  final String? image, title;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 68,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          InkResponse(
            radius: 40,
            onTap: onTap ?? () {},
            child: Container(
              width: 68,
              height: allSettings.value.categoryIconColumn == '5' ? 48 : 68,
              decoration: BoxDecoration(
                color: dark(context) == false ? Colors.grey.shade200 : Colors.grey.shade800,
                borderRadius:
                    allSettings.value.categoryIconShape == 'square' ? BorderRadius.circular(8) : null,
                shape: allSettings.value.categoryIconShape == 'square' ? BoxShape.rectangle : BoxShape.circle,
                image: image != null &&
                        (image!.contains('.png') ||
                            image!.contains('.jpg') ||
                            image!.contains('.webp') ||
                            image!.contains('.jpeg')) &&
                        image!.contains('http')
                    ? DecorationImage(image: CachedNetworkImageProvider(image.toString()))
                    : DecorationImage(image: AssetImage(image ?? Img.logo), fit: BoxFit.cover),
              ),
            ),
          ),
          const SizedBox(height: 10),
          Text(title ?? 'data',
              softWrap: true,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'Roboto',
                fontSize: allSettings.value.categoryIconColumn == '5' ? 12 : 14,
                letterSpacing: -0.1,
                height: 1.2,
              ))
        ],
      ),
    );
  }
}

class FContainer extends StatelessWidget {
  const FContainer(
      {super.key,
      this.title,
      this.image,
      this.currIndex = 0,
      this.category,
      this.color,
      this.index = 0,
      this.isfirst = false,
      this.onTap});

  final String? title, image, category;
  final VoidCallback? onTap;
  final int index, currIndex;
  final String? color;
  final bool isfirst;

  @override
  Widget build(BuildContext context) {
    var size = MediaQuery.of(context).size;
    return Stack(
      fit: StackFit.expand,
      children: [
        Container(
          margin: EdgeInsets.only(
              right: currIndex == index ? 8 : 0,
              left: isfirst
                  ? 4
                  : currIndex == index
                      ? 8
                      : 0),
          alignment: Alignment.center,
          child: image != null && image!.isNotEmpty
              ? ClipRRect(
                  borderRadius: BorderRadius.circular(20),
                  child: CachedNetworkImage(
                      imageUrl: image ?? Img.img1,
                      // width: size.width*0.75,
                      height: size.height * 0.258,
                      fit: BoxFit.cover,
                      placeholder: (context, str) {
                        return const ShimmerLoader();
                      }),
                )
              : Image.asset(Img.logo, fit: BoxFit.cover),
        ),
        Positioned.fill(
            child: Container(
          margin: EdgeInsets.only(
              right: currIndex == index ? 8 : 0,
              left: isfirst
                  ? 4
                  : currIndex == index
                      ? 8
                      : 0),
          decoration:
              BoxDecoration(borderRadius: BorderRadius.circular(20), gradient: ColorUtil.textGradient),
        )),
        Positioned.fill(
            top: 0,
            bottom: 0,
            left: isfirst
                ? 4
                : currIndex == index
                    ? 8
                    : 0,
            right: 8,
            child: TapInk(
              splash: ColorUtil.whiteGrey,
              radius: 20,
              onTap: onTap ?? () {},
              child: Container(
                alignment: Alignment.bottomLeft,
                child: title != null && title!.isNotEmpty
                    ? Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: Text(title ?? '',
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                                fontFamily: 'Roboto',
                                fontSize: 16,
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                                height: 1.5)),
                      )
                    : const SizedBox(),
              ),
            )),
        Positioned(
            left: isfirst
                ? 12
                : currIndex == index
                    ? 16
                    : 12,
            top: 8,
            child: CategoryWrap(name: category, colored: color, color: hexToRgb(color ?? '#000000')))
      ],
    );
  }
}

class CategoryWrap extends StatelessWidget {
  const CategoryWrap({super.key, this.name, this.color, this.radius, this.colored});

  final String? name;
  final double? radius;
  final Color? color;
  final String? colored;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(radius ?? 30),
          boxShadow: const [
            BoxShadow(offset: Offset(0.2, 0.7), spreadRadius: 1, blurRadius: 10, color: Colors.white12)
          ]),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(name ?? 'Sports',
              style: const TextStyle(fontFamily: 'Roboto', fontSize: 14, color: Colors.white))
        ],
      ),
    );
  }
}

class CirlceDot extends StatelessWidget {
  const CirlceDot({
    super.key,
    this.radius,
    this.color,
    this.child,
    this.border,
  });

  final Color? color;
  final double? radius;
  final Widget? child;
  final BoxBorder? border;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: radius ?? 8,
      height: radius ?? 8,
      decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
          border: border,
          gradient: color == null
              ? LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [
                  isBlack(Theme.of(context).primaryColor) && dark(context)
                      ? Colors.white
                      : Theme.of(context).primaryColor,
                  isBlack(Theme.of(context).primaryColor) && dark(context)
                      ? Colors.white24
                      : Theme.of(context).colorScheme.secondary
                ])
              : null),
      child: child,
    );
  }
}

class TopHeader extends StatelessWidget {
  const TopHeader(
      {super.key,
      required this.onTap,
      required this.provider,
      this.isLoad = false,
      this.leadingText,
      this.padding,
      this.endingText});
  final VoidCallback onTap;
  final EdgeInsetsGeometry? padding;
  final bool isLoad;
  final AppProvider provider;
  final String? leadingText, endingText;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: padding ?? const EdgeInsets.only(top: 14),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Text(
                  leadingText ??
                      (provider.featureBlogs.isEmpty && isLoad == false
                          ? allMessages.value.filterByTopics ?? 'Filter By Topics'
                          : allMessages.value.featuredStories ?? 'Featured Stories'),
                  style: const TextStyle(
                      fontFamily: 'Roboto', fontSize: 22, letterSpacing: -0.5, fontWeight: FontWeight.w600)),
            ],
          ),
          TapInk(
            radius: 12,
            splash: Theme.of(context).primaryColor.customOpacity(0.3),
            onTap: onTap,
            child: Padding(
              padding: const EdgeInsets.only(left: 4),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(endingText ?? (allMessages.value.myFeed ?? 'My Feed'),
                      style: TextStyle(
                          fontFamily: 'Roboto',
                          fontSize: 16,
                          color: isBlack(Theme.of(context).primaryColor) && dark(context)
                              ? Colors.white
                              : Theme.of(context).primaryColor)),
                  const SizedBox(width: 4),
                  Icon(Icons.arrow_forward_rounded,
                      color: isBlack(Theme.of(context).primaryColor) && dark(context)
                          ? Colors.white
                          : Theme.of(context).primaryColor)
                ],
              ),
            ),
          )
        ],
      ),
    );
  }
}
