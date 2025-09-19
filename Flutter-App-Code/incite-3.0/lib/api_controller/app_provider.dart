import 'dart:async';
import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/widgets.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:http/http.dart' as http;
import 'package:onesignal_flutter/onesignal_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../model/blog.dart';
import '../splash_screen.dart';
import '../urls/url.dart';
import 'blog_controller.dart';

DateTime? signInStart;
DateTime? signInEnd;

class AppProvider extends ChangeNotifier {
  bool _load = false;
  DataCollection? _blog;
  var _blogList;
  DataCollection? anotherBlog;

  List<int> selectedFeed = [];

  List<Blog> nextfeedBlogs = [];

  List<Blog> nextNewsBlogs = [];

  void selectedInterests(List<int> feed) {
    selectedFeed = feed;
    notifyListeners();
  }

  List<Map<String, dynamic>> adAnalytics = [
    {"data_type": 'ads', "type": "view", "ads_ids": []},
    {"data_type": 'ads', "type": "click", "ads_ids": []},
    {"data_type": 'shorts', "type": "view", "shorts_ids": []},
    {"data_type": 'shorts', "type": "share", "shorts_ids": []},
  ];

  List<Map<String, dynamic>> analytics = [
    {"type": 'bookmark', "blog_ids": []},
    {"type": 'poll_share', "blog_ids": []},
    {"type": 'share', "blog_ids": []},
    {"type": 'view', "blog_ids": []},
    {'type': 'sign_in', 'start_time': ''},
    {"type": "remove_bookmark", "blog_ids": []},
    {"type": "tts", "blogs": []},
  ];
  List<Map<String, dynamic>> blogTimeSpent = [];
  List<Map<String, dynamic>> ttsTimeline = [];
  List<Blog> bookmarks = [];
  List<int> permanentIds = [];
  List<int> shareIds = [],
      viewIds = [],
      bookmarkIds = [],
      pollIds = [],
      ttsIds = [],
      shortshareIds = [],
      shortsViewsIds = [];
  List<Blog> feedBlogs = [], allNewsBlogs = [], featureBlogs = [];
  DateTime? appStartTime;
  DataModel? feed, allNews;
  List<Blog> ads = [];

  // Mute function for shorts to integrate
  bool _isMute = false;

  bool get isMute => _isMute;

  set setMute(bool value) {
    _isMute = value;
    notifyListeners();
  }

  int _focusedIndex = 0;

  int get focusedIndex => _focusedIndex;

  // --------- setFocusedIndex -------------
  set setFocusedIndex(index) {
    _focusedIndex = index;
    notifyListeners();
  }

  int categoryIndex = 0;
  List<int> removeBookmarkIds = [];

  String? calledPageurl;

  AppProvider() {}

  DataCollection? get blog => _blog;

  List<Blog>? get getFeed => feedBlogs;

  List<Category> get blogList => _blogList ?? <Category>[];

  bool get load => _load;

  clearList() {
    _blog = null;
    _blogList = null;
    notifyListeners();
  }

  setNewsBlog(DataModel load) {
    allNews = load;
    notifyListeners();
  }

  setNewsClear() {
    allNews!.blogs.clear();
    notifyListeners();
  }

  setNewsBlogs(List<Blog> blogs) {
    allNews!.blogs = blogs;
    notifyListeners();
  }

  setCalledUrl(String? load) {
    calledPageurl = load;
    notifyListeners();
  }

  setCategoryBlog(DataCollection load) {
    _blog = load;
    notifyListeners();
  }

  setCategoryIndex(int index) {
    categoryIndex = index;
    notifyListeners();
  }

  setLoading({bool? load}) {
    _load = load!;
    notifyListeners();
  }

  setAllNews({DataModel? load}) {
    allNews = load;
    notifyListeners();
  }

  setMyFeed({DataModel? load}) {
    feed = load;
    notifyListeners();
  }

  appTime(DateTime? time) {
    appStartTime = time;
    notifyListeners();
  }

  setBookmark({required Blog blog}) {
    if (permanentIds.contains(blog.id)) {
      bookmarks.remove(blog);
      permanentIds.remove(blog.id);
    } else {
      bookmarks.add(blog);
      permanentIds.add(blog.id!.toInt());
    }
    if (bookmarks.contains(Blog(id: 2345678, title: 'Last-Bookmark'))) {
      bookmarks.remove(Blog(id: 2345678, title: 'Last-Bookmark'));
    }

    DataModel myModelJsonList = DataModel(blogs: bookmarks);
    var data = json.encode(myModelJsonList.toJson());
    prefs!.setString('bookmarks', data);
    prefs!.setBool('isBookmark', true);
    bookmarks.add(Blog(id: 2345678, title: 'Last-Bookmark'));
    notifyListeners();
  }

  Future setAllBookmarks() async {
    SharedPreferences pref = await SharedPreferences.getInstance();
    if (prefs!.containsKey('bookmarks')) {
      var data = json.decode(pref.getString('bookmarks').toString());
      DataModel data2 = DataModel.fromJson(data);
      bookmarks = data2.blogs;
      permanentIds = [];
      for (var element in data2.blogs) {
        permanentIds.add(element.id!.toInt());
      }
      bookmarks.add(Blog(id: 2345678, title: 'Last-Bookmark'));
      blogListHolder2.clearList();
      blogListHolder2.setList(data2);
      blogListHolder2.setBlogType(BlogType.bookmarks);
    }
    notifyListeners();
  }

  Future<DataModel?> getAllBookmarks() async {
    try {
      var url = "${Urls.baseUrl}get-bookmarks";

      var result = await http.get(
        Uri.parse(url),
        headers: {
          HttpHeaders.contentTypeHeader: "application/json",
          "api-token": currentUser.value.apiToken ?? '',
          "language-code": languageCode.value.language ?? '',
        },
      );

      final data = json.decode(result.body);

      if (data['success'] == true) {
        if (data['data'] != null) {
          final list = DataModel.fromJson(data['data']);
          bookmarks = list.blogs;
          for (var element in list.blogs) {
            permanentIds.add(element.id!.toInt());
          }

          prefs!.setBool('isBookmark', true);
          prefs!.setString('bookmarks', json.encode(data['data']));
          return list;
        } else {
          setAllBookmarks();
        }

        notifyListeners();
      } else {
        return null;
      }
      return null;
    } on SocketException catch (e) {
      print("🔴 SOCKET ERROR: $e");
      throw const SocketException('No Internet Connection');
    } on Exception catch (e) {
      print("🔴 API ERROR: $e");
      log(e.toString());
      throw Exception(e);
    }
  }

  Future getSubCategory() async {
    try {
      var url = "${Urls.baseUrl}blog-category-list";

      // DEBUG: Print API call details
      print("🔍 DEBUG: Calling SubCategory API: $url");
      var headers = currentUser.value.id != null
                ? {
                  HttpHeaders.contentTypeHeader: "application/json",
                  "api-token": currentUser.value.apiToken ?? '',
                  "language-code": languageCode.value.language ?? '',
                }
                : {
                  HttpHeaders.contentTypeHeader: "application/json",
                  "language-code": languageCode.value.language ?? '',
                };
      print("🔍 DEBUG: SubCategory Headers: $headers");

      var result = await http.get(
        Uri.parse(url),
        headers: headers,
      );

      // DEBUG: Print response details
      print("🔍 DEBUG: SubCategory Response status: ${result.statusCode}");
      print("🔍 DEBUG: SubCategory Response body (first 200 chars): ${result.body.substring(0, result.body.length > 200 ? 200 : result.body.length)}");

      if (result.statusCode == 200) {}
    } on Exception catch (e) {
      print("🔍 DEBUG: SubCategory Error: $e");
      print(e);
    }
  }

  void addWhenFirstUrl(int i) {
    // DEBUG: Check category data
    print("🔍 DEBUG: Processing category ${_blog!.categories![i].name} with ${_blog!.categories![i].data?.blogs.length ?? 0} blogs");

    if (!selectedFeed.contains(_blog!.categories![i].id!.toInt()) && _blog!.categories![i].isFeed == true) {
      selectedFeed.add(_blog!.categories![i].id!.toInt());
    }

    for (var j = 0; j < _blog!.categories![i].data!.blogs.length; j++) {
      if (_blog!.categories![i].isFeed == true) {
        if (!feedBlogs.contains(_blog!.categories![i].data!.blogs[j])) {
          feedBlogs.add(_blog!.categories![i].data!.blogs[j]);
        }
      }

      if (_blog!.categories![i].data!.blogs[j].isFeatured == 1 &&
          _blog!.categories![i].data!.blogs[j].type != 'quote') {
        if (!featureBlogs.contains(_blog!.categories![i].data!.blogs[j]) && featureBlogs.length < 10) {
          featureBlogs.add(_blog!.categories![i].data!.blogs[j]);
        }
      }
      if (!allNewsBlogs.contains(_blog!.categories![i].data!.blogs[j])) {
        allNewsBlogs.add(_blog!.categories![i].data!.blogs[j]);
      }
    }
    notifyListeners();
  }

  // This is to add next page url data
  void addWhenNextUrl(int i) {
    for (var j = 0; j < anotherBlog!.categories![i].data!.blogs.length; j++) {
      if (_blog!.categories![i].isFeed == true) {
        if (!feedBlogs.contains(anotherBlog!.categories![i].data!.blogs[j]) &&
            !nextfeedBlogs.contains(anotherBlog!.categories![i].data!.blogs[j])) {
          nextfeedBlogs.add(anotherBlog!.categories![i].data!.blogs[j]);
        }
      }

      if (anotherBlog!.categories![i].data!.blogs[j].isFeatured == 1 &&
          anotherBlog!.categories![i].data!.blogs[j].type != 'quote') {
        if (!featureBlogs.contains(anotherBlog!.categories![i].data!.blogs[j]) && featureBlogs.length < 10) {
          featureBlogs.add(anotherBlog!.categories![i].data!.blogs[j]);
        }
      }

      if (!allNewsBlogs.contains(anotherBlog!.categories![i].data!.blogs[j]) &&
          !nextNewsBlogs.contains(anotherBlog!.categories![i].data!.blogs[j])) {
        nextNewsBlogs.add(anotherBlog!.categories![i].data!.blogs[j]);
      }
    }
    notifyListeners();
  }

  Future<int> getMaxTotal(List<Category> blogs) async {
    return blogs.map((blog) => blog.data!.total).reduce((a, b) => a! > b! ? a : b)!.toInt();
  }

  Future getCategory({
    bool allowUpdate = true,
    deepLink = false,
    bool headCall = true,
    String? nextpageurl,
    Blog? deeplinkblog,
  }) async {
    try {
      var url = nextpageurl ?? "${Urls.baseUrl}blog-list";

      // DEBUG: Print API call details
      print("🔍 DEBUG: Calling API: $url");
      print("🔍 DEBUG: Base URL is: ${Urls.baseUrl}");
      print("🔍 DEBUG: Headers being sent:");
      var headers = currentUser.value.id != null
                ? {
                  HttpHeaders.contentTypeHeader: "application/json",
                  "api-token": currentUser.value.apiToken ?? '',
                  "language-code": languageCode.value.language ?? '',
                }
                : {
                  HttpHeaders.contentTypeHeader: "application/json",
                  "language-code": languageCode.value.language ?? '',
                };
      print("🔍 DEBUG: Headers: $headers");

      var result = await http.get(
        Uri.parse(url),
        headers: headers,
      );

      // DEBUG: Print response details
      print("🔍 DEBUG: Response status: ${result.statusCode}");
      print("🔍 DEBUG: Response headers: ${result.headers}");
      print("🔍 DEBUG: Response body (first 200 chars): ${result.body.substring(0, result.body.length > 200 ? 200 : result.body.length)}");

      final data = json.decode(result.body);

      if (data['success'] == true) {
        int maxTotalBlogs = 0;

        if (nextpageurl == null) {
          prefs!.setString('collection', result.body);
        }

        log(result.body);

        if (nextpageurl == null) {
          _blog = DataCollection.fromJson(data);

          maxTotalBlogs = await getMaxTotal(_blog!.categories as List<Category>);

          if (_blog!.categories!.isNotEmpty) {
            calledPageurl = _blog!.categories![0].data!.firstPageUrl;
          }
        } else {
          anotherBlog = DataCollection.fromJson(data);

          maxTotalBlogs = await getMaxTotal(anotherBlog!.categories as List<Category>);

          for (var i = 0; i < anotherBlog!.categories!.length; i++) {
            if (maxTotalBlogs == anotherBlog!.categories![i].data!.total!.toInt()) {
              _blog!.categories![i].data!.nextPageUrl = anotherBlog!.categories![i].data!.nextPageUrl;
              _blog!.categories![i].data!.lastPageUrl = anotherBlog!.categories![i].data!.lastPageUrl;
            }

            _blog!.categories![i].data!.currentPage = anotherBlog!.categories![i].data!.currentPage;
            _blog!.categories![i].data!.firstPageUrl = anotherBlog!.categories![i].data!.firstPageUrl;

            _blog!.categories![i].data!.to = anotherBlog!.categories![i].data!.to;
            _blog!.categories![i].data!.prevPageUrl = anotherBlog!.categories![i].data!.prevPageUrl;
            _blog!.categories![i].data!.lastPage = anotherBlog!.categories![i].data!.lastPage;
            _blog!.categories![i].data!.from = anotherBlog!.categories![i].data!.from;
            _blog!.categories![i].data!.total = anotherBlog!.categories![i].data!.total;

            for (var j = 0; j < anotherBlog!.categories![i].data!.blogs.length; j++) {
              if (!_blog!.categories![i].data!.blogs.contains(anotherBlog!.categories![i].data!.blogs[j])) {
                _blog!.categories![i].data!.blogs.add(anotherBlog!.categories![i].data!.blogs[j]);
              }
            }
          }
        }
        notifyListeners();

        if (nextpageurl == null) {
          feedBlogs = [];
          featureBlogs = [];
          allNewsBlogs = [];
          selectedFeed = [];
        } else {
          nextfeedBlogs = [];
        }

        for (var i = 0; i < _blog!.categories!.length; i++) {
          if (nextpageurl == null) {
            //--------- First Url will be called.
            addWhenFirstUrl(i);
          } else {
            //---------- next Url will be called.
            addWhenNextUrl(i);
          }
        }

        if (nextpageurl == null) {
          feedBlogs.sort((a, b) {
            return DateTime.parse(
              b.scheduleDate.toString(),
            ).compareTo(DateTime.parse(a.scheduleDate.toString()));
          });

          featureBlogs.sort((a, b) {
            return DateTime.parse(
              b.scheduleDate.toString(),
            ).compareTo(DateTime.parse(a.scheduleDate.toString()));
          });

          allNewsBlogs.sort((a, b) {
            return DateTime.parse(
              b.scheduleDate.toString(),
            ).compareTo(DateTime.parse(a.scheduleDate.toString()));
          });
        } else {
          nextNewsBlogs.sort((a, b) {
            return DateTime.parse(
              b.scheduleDate.toString(),
            ).compareTo(DateTime.parse(a.scheduleDate.toString()));
          });

          allNewsBlogs.addAll(nextNewsBlogs);
        }

        if (blogAds.value.isNotEmpty && nextpageurl == null) {
          allNewsBlogs = await arrangeAds(allNewsBlogs);
          feedBlogs = await arrangeAds(feedBlogs);
        }

        var firstWhere = _blog!.categories!.firstWhere((e) => e.data!.total == maxTotalBlogs);

        if (nextpageurl == null) {
          feed = DataModel(
            currentPage: _blog!.categories![0].data!.currentPage,
            firstPageUrl: _blog!.categories![0].data!.firstPageUrl,
            lastPageUrl: firstWhere.data!.lastPageUrl,
            nextPageUrl: firstWhere.data!.nextPageUrl,
            to: _blog!.categories![0].data!.to,
            prevPageUrl: _blog!.categories![0].data!.prevPageUrl,
            lastPage: firstWhere.data!.lastPage,
            from: _blog!.categories![0].data!.from,
            blogs: feedBlogs,
          );

          allNews = DataModel(
            currentPage: _blog!.categories![0].data!.currentPage,
            firstPageUrl: _blog!.categories![0].data!.firstPageUrl,
            lastPageUrl: firstWhere.data!.lastPageUrl,
            nextPageUrl: firstWhere.data!.nextPageUrl,
            to: _blog!.categories![0].data!.to,
            prevPageUrl: _blog!.categories![0].data!.prevPageUrl,
            lastPage: firstWhere.data!.lastPage,
            from: _blog!.categories![0].data!.from,
            blogs: allNewsBlogs.toSet().toList(),
          );
        }

        addFeedLast();

        addFeatured();

        if (deepLink == false && !prefs!.containsKey('setNotification')) {
          if (nextpageurl == null) {
            if (currentUser.value.id != null) {
              blogListHolder.setList(feed!);
              blogListHolder.setBlogType(BlogType.feed);
            } else {
              blogListHolder.setList(allNews!);
              blogListHolder.setBlogType(BlogType.allnews);
            }
          } else {
            if (blogListHolder.getBlogType() == BlogType.feed) {
              var nextfeed = DataModel(
                currentPage: _blog!.categories![0].data!.currentPage,
                firstPageUrl: _blog!.categories![0].data!.firstPageUrl,
                lastPageUrl: firstWhere.data!.lastPageUrl,
                nextPageUrl: firstWhere.data!.nextPageUrl,
                to: _blog!.categories![0].data!.to,
                prevPageUrl: _blog!.categories![0].data!.prevPageUrl,
                lastPage: firstWhere.data!.lastPage,
                from: _blog!.categories![0].data!.from,
                blogs: nextfeedBlogs.toSet().toList(),
              );

              blogListHolder.updateList(nextfeed);
              notifyListeners();
            } else if (blogListHolder.getBlogType() == BlogType.allnews) {
              var allNewsNext = DataModel(
                currentPage: _blog!.categories![0].data!.currentPage,
                firstPageUrl: _blog!.categories![0].data!.firstPageUrl,
                lastPageUrl: firstWhere.data!.lastPageUrl,
                nextPageUrl: firstWhere.data!.nextPageUrl,
                to: _blog!.categories![0].data!.to,
                prevPageUrl: _blog!.categories![0].data!.prevPageUrl,
                lastPage: firstWhere.data!.lastPage,
                from: _blog!.categories![0].data!.from,
                blogs: nextNewsBlogs.toSet().toList(),
              );

              allNews = DataModel(
                currentPage: _blog!.categories![0].data!.currentPage,
                firstPageUrl: _blog!.categories![0].data!.firstPageUrl,
                lastPageUrl: firstWhere.data!.lastPageUrl,
                nextPageUrl: firstWhere.data!.nextPageUrl,
                to: _blog!.categories![0].data!.to,
                prevPageUrl: _blog!.categories![0].data!.prevPageUrl,
                lastPage: firstWhere.data!.lastPage,
                from: _blog!.categories![0].data!.from,
                blogs: allNewsBlogs.toSet().toList(),
              );

              blogListHolder.updateList(allNewsNext);
              notifyListeners();
            }
          }
        } else {
          allNews = DataModel(
            currentPage: _blog!.categories![0].data!.currentPage,
            firstPageUrl: _blog!.categories![0].data!.firstPageUrl,
            lastPageUrl: firstWhere.data!.lastPageUrl,
            nextPageUrl: firstWhere.data!.nextPageUrl,
            to: _blog!.categories![0].data!.to,
            prevPageUrl: _blog!.categories![0].data!.prevPageUrl,
            lastPage: firstWhere.data!.lastPage,
            from: _blog!.categories![0].data!.from,
            blogs: allNewsBlogs.toSet().toList(),
          );
          notifyListeners();
        }
      }
    } on SocketException {
      getCacheBlog();
      notifyListeners();
    } catch (stackTrace, e) {
      // final lines = stackTrace.toString().split('\n');
      debugPrint(e.toString());
      getCacheBlog();
      notifyListeners();
    }
  }

  void addFeatured() {
    if (featureBlogs.isNotEmpty &&
        featureBlogs.contains(
          Blog(
            title: 'Last-Featured',
            id: 2345678876543212345,
            sourceName: 'Great',
            description: 'You have viewed all featured stories',
          ),
        )) {
      featureBlogs.remove(
        Blog(
          title: 'Last-Featured',
          id: 2345678876543212345,
          sourceName: 'Great',
          description: 'You have viewed all featured stories',
        ),
      );
      featureBlogs.add(
        Blog(
          title: 'Last-Featured',
          id: 2345678876543212345,
          sourceName: 'Great',
          description: 'You have viewed all featured stories',
        ),
      );
    } else {
      if (featureBlogs.isNotEmpty) {
        featureBlogs.add(
          Blog(
            title: 'Last-Featured',
            id: 2345678876543212345,
            sourceName: 'Great',
            description: 'You have viewed all featured stories',
          ),
        );
      }
    }
  }

  void addFeedLast() {
    if (feed!.blogs.isNotEmpty && _blog!.categories![0].data!.nextPageUrl == null) {
      if (feed!.blogs.contains(
        Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
      )) {
        feed!.blogs.remove(
          Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
        );
        feed!.blogs.add(
          Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
        );
      } else {
        feed!.blogs.add(
          Blog(title: 'Last-Feed', categoryName: "My Feed", id: 234202120, sourceName: 'Great'),
        );
      }
    }
  }

  Future updateBlogList(Blog newBlog, AppProvider provider) async {
    var data = DataModel();
    data.blogs = [];

    data.blogs.add(newBlog);
    blogListHolder.setList(data);
    blogListHolder.setBlogType(BlogType.allnews);
    notifyListeners();
  }

  Future<List<Blog>?> getCacheBlog({bool deepLink = false, Blog? blog}) async {
    feedBlogs = [];
    featureBlogs = [];
    allNewsBlogs = [];
    ads = [];
    selectedFeed = [];

    if (prefs!.containsKey('collection')) {
      String collection = prefs!.getString('collection').toString();

      log(collection);

      _blog = DataCollection.fromJson(jsonDecode(collection));

      for (var i = 0; i < _blog!.categories!.length; i++) {
        addWhenFirstUrl(i);
      }

      featureBlogs.sort((a, b) {
        return DateTime.parse(b.scheduleDate.toString()).compareTo(DateTime.parse(a.scheduleDate.toString()));
      });
      allNewsBlogs.sort((a, b) {
        return DateTime.parse(b.scheduleDate.toString()).compareTo(DateTime.parse(a.scheduleDate.toString()));
      });
      feedBlogs.sort((a, b) {
        return DateTime.parse(b.scheduleDate.toString()).compareTo(DateTime.parse(a.scheduleDate.toString()));
      });

      if (blogAds.value.isNotEmpty) {
        allNewsBlogs = await arrangeAds(allNewsBlogs);
        feedBlogs = await arrangeAds(feedBlogs);
      }

      notifyListeners();

      feed = _blog!.categories![0].data!;
      allNews = _blog!.categories![0].data!;

      List<Blog> allBlogs = blog != null ? [blog] : [];

      if (blog != null) {
        if (allNewsBlogs.contains(blog)) {
          allNewsBlogs.remove(blog);
        }

        allBlogs.addAll(allNewsBlogs);
      }

      allBlogs = blog != null ? allBlogs : allNewsBlogs;

      setNewsBlogs(allBlogs);
      feed!.blogs = feedBlogs;

      if (!featureBlogs.contains(
            Blog(
              title: 'Last-Featured',
              id: 2345678876543212345,
              sourceName: 'Great',
              description: 'You have viewed all featured stories',
            ),
          ) &&
          featureBlogs.isNotEmpty) {
        featureBlogs.add(
          Blog(
            title: 'Last-Featured',
            id: 2345678876543212345,
            sourceName: 'Great',
            description: 'You have viewed all featured stories',
          ),
        );
      }
      // if () {
      if (deepLink == false) {
        if (currentUser.value.id == null) {
          blogListHolder.setList(allNews!);
          blogListHolder.setBlogType(BlogType.allnews);
        } else {
          blogListHolder.setList(feed!);
          blogListHolder.setBlogType(BlogType.feed);
        }
      } else {
        blogListHolder.setList(allNews ?? DataModel());
        blogListHolder.setBlogType(BlogType.allnews);
      }

      notifyListeners();
      //}
      return allBlogs;
    }
    return null;
  }

  void addShortsShareData(int id) {
    shortshareIds.add(id);
    adAnalytics[3] = {"data_type": 'shorts', 'type': 'share', 'shorts_ids': shortshareIds};
    notifyListeners();
  }

  void addShortsViewsData(int id) {
    shortsViewsIds.add(id);

    adAnalytics[2] = {"data_type": 'shorts', 'type': 'view', 'shorts_ids': shortsViewsIds};
    log(analytics[2].toString());
    notifyListeners();
  }

  void addShareData(int id) {
    shareIds.add(id);
    analytics[2] = {'type': 'share', 'blog_ids': shareIds};
    notifyListeners();
  }

  void addBookmarkData(int id) {
    bookmarkIds.add(id);
    analytics[0] = {'type': 'bookmark', 'blog_ids': bookmarkIds};
    notifyListeners();
  }

  void addTtsData(int id, String startTime, String endTime) {
    ttsTimeline.add({"id": id, "start_time": startTime, "end_time": endTime});
    analytics[6] = {"type": "tts", "blogs": ttsTimeline};
    notifyListeners();
  }

  void adsViewData(int id) {
    adAnalytics[0]['ads_ids'].add(id);
    notifyListeners();
  }

  void adsClickData(int id) {
    adAnalytics[1]['ads_ids'].add(id);
    notifyListeners();
  }

  void removeBookmarkData(int id) {
    removeBookmarkIds.add(id);
    analytics[5] = {"type": "remove_bookmark", "blog_ids": removeBookmarkIds};

    notifyListeners();
  }

  void addPollShare(int id) {
    pollIds.add(id);
    analytics[1] = {"type": "poll_share", "blog_ids": pollIds};
    notifyListeners();
  }

  void addviewData(int id) {
    viewIds.add(id);
    analytics[3] = {"type": "view", "blog_ids": viewIds};
    notifyListeners();
  }

  void addAppTimeSpent({DateTime? startTime, DateTime? endTime}) {
    var data = {
      'type': 'app_time_spent',
      'start_time': startTime!.toIso8601String(),
      'end_time': endTime!.toIso8601String(),
    };
    analytics.add(data);
    notifyListeners();
  }

  Future getAnalyticData({bool isAds = false}) async {
    final msg = isAds == true ? jsonEncode(adAnalytics) : jsonEncode(analytics);

    try {
      final String url = '${Urls.baseUrl}add-analytics';

      var headers =
          currentUser.value.id != null
              ? {
                HttpHeaders.contentTypeHeader: 'application/json',
                "api-token": currentUser.value.apiToken.toString(),
              }
              : {
                HttpHeaders.contentTypeHeader: 'application/json',
                "player-id": OneSignal.User.pushSubscription.id ?? "",
                "fcm-token":
                    (Platform.isAndroid
                        ? await FirebaseMessaging.instance.getToken()
                        : await FirebaseMessaging.instance.getAPNSToken()) ??
                    "",
              };
      final response = await http
          .post(
            Uri.parse(url),
            headers: headers,
            body: msg,
            // ignore: data_might_complete_normally_catch_error, body_might_complete_normally_catch_error
          )
          // ignore: body_might_complete_normally_catch_error
          .catchError((e) {
            debugPrint("register error $e");
          });

      var res = json.decode(response.body);

      if (res['success'] == true) {
        if (isAds == true) {
          setAdAnalyticData();
        } else {
          setAnalyticData();
        }
        notifyListeners();
      }
    } on Exception catch (e) {
      throw Exception(e);
    }
    notifyListeners();
  }

  void setAdAnalyticData() {
    adAnalytics = [
      {"data_type": 'ads', "type": "view", "ads_ids": []},
      {"data_type": 'ads', "type": "click", "ads_ids": []},
      {"data_type": 'shorts', "type": "view", "shorts_ids": []},
      {"data_type": 'shorts', "type": "share", "shorts_ids": []},
    ];
    notifyListeners();
  }

  void setAnalyticData() {
    analytics = [
      {"type": 'bookmark', "blog_ids": []},
      {"type": 'poll_share', "blog_ids": []},
      {"type": 'share', "blog_ids": []},
      {"type": 'view', "blog_ids": []},
      {'type': 'sign_in', 'start_time': ''},
      {"type": "remove_bookmark", "blog_ids": []},
      {"type": "tts", "blogs": []},
    ];
    viewIds = [];
    notifyListeners();
  }

  void addUserSession({bool isSignup = false, isSocialSignup = false, bool isSignin = false}) {
    var data = {
      'type':
          isSignup
              ? 'sign_up'
              : isSocialSignup
              ? 'social_media_signup'
              : isSignup == false && isSocialSignup == false && isSignin == false
              ? 'social_media_signin'
              : 'sign_in',
      'start_time': DateTime.now().toIso8601String(),
    };
    analytics[4] = data;
    notifyListeners();
  }

  void logoutUserSession() {
    var data = {'type': 'logout'};
    analytics.add(data);
    notifyListeners();
  }

  void clearLists() {
    selectedFeed = [];
    feedBlogs = [];
    bookmarks = [];
    notifyListeners();
  }

  Future<List<Blog>> arrangeAds(List<Blog> blogss) async {
    List<Blog> blogsWithAds = [];
    int adCount = 0;
    int frequencyCount = blogAds.value[adCount].frequency!.toInt();
    for (int i = 0; i < blogss.length; i++) {
      blogsWithAds.add(blogss[i]);

      if ((i + 1) % frequencyCount == 0) {
        blogsWithAds.add(blogAds.value[adCount]);
        if (adCount == blogAds.value.length - 1) {
          adCount = 0;
        } else {
          adCount += 1;
        }
        frequencyCount += blogAds.value[adCount].frequency!.toInt();
      }
    }
    return blogsWithAds;
  }
}
