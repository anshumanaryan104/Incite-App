import 'dart:developer';

import 'package:flutter/material.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/shorts_controller.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/main.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/pages/main/blog.dart';
import 'package:incite/pages/main/dashboard.dart';
import 'package:incite/splash_screen.dart';
import 'package:incite/widgets/loader.dart';
import 'package:provider/provider.dart';

enum BlogAction { bookmark, share }

class Loader extends StatefulWidget {
  const Loader({super.key, required this.blog, this.action, this.type});
  final String? action;
  final Blog blog;
  final String? type;
  @override
  State<Loader> createState() => _LoaderState();
}

class _LoaderState extends State<Loader> {
  late Blog blog;

  @override
  void initState() {
    super.initState();

    blog = widget.blog;
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      var provider = Provider.of<AppProvider>(context, listen: false);
      if (widget.type == 'shorts') {
        if (allSettings.value.isShortEnable == '1') {
          await ShortsApi()
              .fetchShorts(
            context,
            id: widget.blog.id,
            isInitialLoad: true,
          )
              .then((shorts) {
            //  List<Blog> blogs = shortLists.blogModel.blogs;
            // DataModel datas = shortLists.blogModel;
            // //print(blogs);
            // shortLists.blogModel.blogs = [];
            // shortLists.blogModel.blogs.add(value);
            // for (var element in blogs) {
            //   if (element.id == blog.id) {
            //     blogs.remove(value);
            //   }
            // }
            // datas.blogs.addAll(blogs);
            // shortLists.blogModel.blogs = datas.blogs;
            // setState(() {});

            if (shorts != null && shorts.isNotEmpty) {
              log("shorts.toString()");
              log(shorts.toString());
              // preloadProvider.urls = shorts;
              // preloadProvider.initialize();
              // preloadProvider.playVideoAtIndex(0);
              ShortsApi().setIndex(0);

              Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(
                      builder: (context) => DashboardPage(
                          key: UniqueKey(),
                          index: 0,
                          bottomIndex: 1,
                          isFromVideo: true,
                          action: widget.action == 'Share'
                              ? BlogOptionType.share
                              : widget.action == 'Bookmark'
                                  ? BlogOptionType.bookmark
                                  : null)),
                  (route) => false);
              // shortslikesIds.forEach((e){
              //   preloadProvider.setLike();
              // });
              // log(value.toString());
            }
          });
        } else {
          Navigator.pushAndRemoveUntil(context,
              MaterialPageRoute(builder: (context) => const DashboardPage(index: 1)), (route) => false);
        }
      } else {
        await blogDetail(blog.id.toString()).then((value) async {
          if (prefs!.containsKey('id')) {
            prefs!.remove('id');
            provider.setAnalyticData();

            if (!prefs!.containsKey('collection')) {
              provider.getCategory(deepLink: true, deeplinkblog: value).whenComplete(() {
                provider.getCacheBlog(deepLink: true, blog: value).then((blogList) async {
                  blogListHolder.setList(provider.allNews as DataModel);
                  blogListHolder.setBlogType(BlogType.allnews);
                  setState(() {});
                  //  await Future.delayed(const Duration(seconds: 2));

                  Navigator.pushAndRemoveUntil(
                      context,
                      MaterialPageRoute(
                          builder: (context) => DashboardPage(
                              index: 1,
                              isLoad: false,
                              action: widget.action == "Share"
                                  ? BlogOptionType.share
                                  : widget.action == "Bookmark"
                                      ? BlogOptionType.bookmark
                                      : null)),
                      (route) => false);
                });
              });
            } else {
              provider.getCacheBlog(deepLink: true, blog: value).then((sta) async {
                var allNEWS = provider.allNews;

                allNEWS!.blogs = sta ?? [];

                provider.setAllNews(load: allNEWS);

                blogListHolder.setBlogType(BlogType.allnews);
                blogListHolder.setList(allNEWS);
                setState(() {});

                // await Future.delayed(const Duration(seconds: 2));

                Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(
                        builder: (context) => DashboardPage(
                              index: 1,
                              fromInitial: false,
                              isLoad: false,
                              action: widget.action == "Share"
                                  ? BlogOptionType.share
                                  : widget.action == "Bookmark"
                                      ? BlogOptionType.bookmark
                                      : null,
                            )),
                    (route) => false);
              });
            }
            // inactiveState(value);
          } else {
            provider.getCacheBlog(deepLink: true, blog: value).then((bloglist) async {
              var allNEWS = provider.allNews;

              allNEWS!.blogs = bloglist ?? [];

              provider.setAllNews(load: allNEWS);

              blogListHolder.setBlogType(BlogType.allnews);
              blogListHolder.setList(provider.allNews as DataModel);
              setState(() {});

              Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(
                      builder: (context) => DashboardPage(
                            index: 1,
                            fromInitial: false,
                            isLoad: false,
                            action: widget.action == "Share"
                                ? BlogOptionType.share
                                : widget.action == "Bookmark"
                                    ? BlogOptionType.bookmark
                                    : null,
                          )),
                  (route) => false);
              // await activeState(value);
            });
          }
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return const CustomLoader(
        isLoading: true,
        opacity: 0.6,
        child: Opacity(
          opacity: 0.3,
          child: Material(
            color: Colors.transparent,
          ),
        ));
  }
}
