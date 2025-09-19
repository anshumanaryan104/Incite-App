import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/widgets.dart';
import 'package:http/http.dart' as http;
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../model/news.dart';
import '../splash_screen.dart';
import '../urls/url.dart';
import 'user_controller.dart';

Dio dio = Dio();
List<ENews> eNews = [];
List<LiveNews> liveNews = [];
DataModel? blogPolls;
DataModel? quotes;

Future<bool?> checkUpdate({
  String route = 'epaper-list',
  String etag = 'enews-tag',
  bool headCall = true,
}) async {
  prefs = await SharedPreferences.getInstance();
  if (headCall == true) {
    final response = await dio.head(
      '${Urls.baseUrl}$route',
      options: Options(
        headers: currentUser.value.id != null ? {"api-token": currentUser.value.apiToken} : {},
      ),
    );
    var eTag = response.headers.value('ETag');
    var prefTag = prefs!.containsKey(etag) ? prefs!.getString(etag) : '';

    if ((prefTag != '' || prefTag != null) && eTag != prefTag) {
      prefs!.setString(etag, eTag.toString());
      //  print('new update');
      return false;
    } else if (prefTag == '' || prefTag == null) {
      return false;
    } else {
      return true;
    }
  } else {
    return false;
  }
}

Future<List<ENews>> getENews(BuildContext context) async {
  try {
    //  await checkUpdate().then((value) async{
    //   if (value == true) {
    //       eNews = [];
    //      json.decode(prefs!.get('enews').toString())['data'].forEach((e){
    //       eNews.add(ENews.fromJson(e));
    //    });
    //    return eNews;
    // } else {
    final String url = '${Urls.baseUrl}epaper-list';
    // var client = SentryHttpClient(captureFailedRequests: true);
    final response = await http.get(
      Uri.parse(url),
      headers: {HttpHeaders.contentTypeHeader: 'application/json'},
    );
    if (response.statusCode == 200) {
      eNews = [];
      json.decode(response.body)['data'].forEach((e) {
        eNews.add(ENews.fromJson(e));
      });
      prefs!.setString('enews', response.body);
      return eNews;
    }
  } on SocketException {
    showCustomToast(context, allMessages.value.noInternetConnection ?? '');

    return [];
  } catch (e) {
    Exception(e);
  }
  return [];
}

Future<List<LiveNews>> getliveNews() async {
  try {
    final String url = '${Urls.baseUrl}live-news-list';
    final response = await http.get(
      Uri.parse(url),
      headers: {HttpHeaders.contentTypeHeader: 'application/json'},
    );
    if (response.statusCode == 200) {
      liveNews = [];
      var decode = json.decode(response.body);

      decode['data'].forEach((e) {
        liveNews.add(LiveNews.fromJson(e));
      });
      prefs!.setString('livenews', response.body);

      log(response.body);
      log(liveNews.toString());

      return liveNews;
    }
  } on SocketException {
    return [];
  } catch (e) {
    Exception(e);
  }
  return [];
}

Future getBlogPollOrQuotes({String? type, int count = 10, required ValueChanged<bool> onLoading}) async {
  try {
    onLoading(true);
    if (type == 'post') {
      localPollCache();
    } else {
      localQuoteCache();
    }

    final String url = '${Urls.baseUrl}view-all-post?type=$type&count=$count';
    final response = await http.get(
      Uri.parse(url),
      headers: {
        HttpHeaders.contentTypeHeader: 'application/json',
        if (currentUser.value.apiToken != null) "api-token": currentUser.value.apiToken ?? '',
      },
    );
    if (response.statusCode == 200) {
      onLoading(false);
      var decode = json.decode(response.body);

      log(response.body);

      if (type == 'post') {
        getBlogPoll(decode, response);
      } else {
        getQuotes(decode, response);
      }

      log(response.body);
    }
  } on SocketException {
    onLoading(false);
    if (type == 'post') {
      localPollCache();
    } else {
      localQuoteCache();
    }
  } catch (e) {
    onLoading(false);

    if (type == 'post') {
      localPollCache();
    } else {
      localQuoteCache();
    }
    Exception(e);
  }
}

void localPollCache() {
  if (prefs!.containsKey('blogPolls')) {
    var localCache = jsonDecode(prefs!.getString('blogPolls').toString());
    blogPolls = DataModel.fromJson(localCache['data']);
  }
}

void localQuoteCache() {
  if (prefs!.containsKey('quotes')) {
    var localCache = jsonDecode(prefs!.getString('quotes').toString());
    quotes = DataModel.fromJson(localCache['data']);
  }
}

void getBlogPoll(decode, http.Response response) {
  blogPolls = DataModel.fromJson(decode['data']);

  prefs!.setString('blogPolls', response.body);
}

void getQuotes(decode, http.Response response) {
  quotes = DataModel.fromJson(decode['data']);

  prefs!.setString('quotes', response.body);
}
