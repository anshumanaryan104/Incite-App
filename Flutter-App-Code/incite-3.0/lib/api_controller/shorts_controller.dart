import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'dart:isolate';

import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/widgets.dart';
import 'package:http/http.dart' as http;
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/main.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/splash_screen.dart';
import 'package:incite/urls/url.dart';
import 'package:mvc_pattern/mvc_pattern.dart';
import 'package:onesignal_flutter/onesignal_flutter.dart';

List<dynamic> liveNews = [];

var shortslikesIds = [];

class ShortsApi extends ControllerMVC {
  int currentPage = 1;

  void setIndex(int index) {
    currentPage = index;
  }

  DataModel blogModel = DataModel();

  void setList(DataModel list) {
    blogModel = list;
    setState(() {});
  }

  void updateList(DataModel list) {
    shortLists.blogModel.currentPage = list.currentPage;
    shortLists.blogModel.firstPageUrl = list.firstPageUrl;
    shortLists.blogModel.lastPageUrl = list.lastPageUrl;
    shortLists.blogModel.nextPageUrl = list.nextPageUrl;
    shortLists.blogModel.to = list.to;
    shortLists.blogModel.prevPageUrl = list.prevPageUrl;
    shortLists.blogModel.lastPage = list.lastPage;
    shortLists.blogModel.from = list.from;
    shortLists.blogModel.blogs.addAll(list.blogs.toSet().toList());
  }

  final String _baseUrl = '${Urls.baseUrl}get-short-videos';
  // Adjust this based on your API pagination settings

  Future<List<String>?> fetchShorts(context,
      {String? nextPageUrl, int? id, bool isInitialLoad = false}) async {
    try {
      final response = await http.get(
        Uri.parse(nextPageUrl ?? (id != null ? "$_baseUrl?post_id=$id" : _baseUrl)),
        headers: {
          HttpHeaders.contentTypeHeader: 'application/json',
        },
      );

      DataModel? dataModel;

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body)['data'];

        if (nextPageUrl != null) {
          dataModel = DataModel.fromJson(responseData, isShorts: true);
          shortLists.updateList(dataModel);
        } else {
          setShortCache(responseData);
          shortLists.blogModel =
              DataModel.fromJson(responseData, isShorts: true, fromDeepLink: id != null ? true : false);
        }

        List<String> videoList = [];
        // ignore: use_build  _context_synchronously

        for (var i = 0;
            i < (dataModel != null ? dataModel.blogs.length : shortLists.blogModel.blogs.length);
            i++) {
          //  if( !provider.permanentlikesIds.contains(element.id) && element.isUserLiked == 1 ){
          //       provider.setlike( blog: element);
          //   }
          String? youtubeurl;
          // ----- first url load --------
          if (dataModel == null) {
            if (shortLists.blogModel.blogs[i].videoUrl != null) {
              videoList.add(shortLists.blogModel.blogs[i].videoUrl ?? "");
            } else {
              videoList.add(shortLists.blogModel.blogs[i].videoFile ?? "");
            }
            setState(() {});
          } else {
            if (dataModel.blogs[i].videoUrl != null) {
              videoList.add(youtubeurl ?? "");
            } else {
              videoList.add(dataModel.blogs[i].videoFile ?? "");
            }
            setState(() {});
          }
        }

        if (shortLists.blogModel.nextPageUrl == null &&
            !shortLists.blogModel.blogs.contains(Blog(
                id: 000000,
                title: 'Great!!',
                description: 'You have viewed all shorts! Stay tuned for later updates.'))) {
          shortLists.blogModel.blogs.add(Blog(
              id: 000000,
              title: 'Great!!',
              description: 'You have viewed all shorts! Stay tuned for later updates.'));
        }

        currentPage++;
        setState(() {});

        return videoList;
      } else {
        throw Exception('Failed to fetch shorts');
      }
    } on Exception catch (e) {
      log("handle Exception${e.toString()}");
      throw Exception(e);
    }
  }

  Future createIsolate(int index) async {
    try {
      ReceivePort mainReceivePort = ReceivePort();

      await Isolate.spawn<SendPort>(getVideosTask, mainReceivePort.sendPort);

      SendPort isolateSendPort = await mainReceivePort.first;

      ReceivePort isolateResponseReceivePort = ReceivePort();

      isolateSendPort.send([index, isolateResponseReceivePort.sendPort]);

      isolateResponseReceivePort.listen((e) {
        final urls = e.first;

        log("Update urls ${urls.toString()}");
      });

      // Update new urls
    } on Exception catch (e) {
      log('recieve Port $e');
    }
  }

  Future getVideosTask(SendPort mySendPort) async {
    try {
      ReceivePort isolateReceivePort = ReceivePort();

      mySendPort.send(isolateReceivePort.sendPort);

      await for (var message in isolateReceivePort) {
        if (message is List) {
          final SendPort isolateResponseSendPort = message[1];

          var shortsUrls =
              await fetchShorts(navkey.currentState!.context, nextPageUrl: shortLists.blogModel.nextPageUrl);

          isolateResponseSendPort.send(shortsUrls);
        }
      }
    } on Exception catch (e) {
      log('video Task : $e');
    }
  }

  Future<List<dynamic>?> getLiveVideo(BuildContext context) async {
    try {
      final response = await http.get(
        Uri.parse("${Urls.baseUrl}get-live-stream"),
        headers: currentUser.value.id != null
            ? {
                HttpHeaders.contentTypeHeader: 'application/json',
                "api-token": currentUser.value.apiToken.toString(),
              }
            : {
                HttpHeaders.contentTypeHeader: 'application/json',
                "player-id": OneSignal.User.pushSubscription.id ?? "",
                "fcm-token": (Platform.isAndroid
                        ? await FirebaseMessaging.instance.getToken()
                        : await FirebaseMessaging.instance.getAPNSToken()) ??
                    ""
              },
      );

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);

        return responseData['data'];
      } else {
        throw Exception('Failed to fetch shorts');
      }
    } on Exception catch (e) {
      throw Exception(e);
    }
  }
}

setShortCache(Map<String, dynamic> map) async {
  if (prefs != null) {
    prefs!.setString('shorts', jsonEncode(map));
  }
}

DataModel? getShortCache() {
  if (prefs != null && prefs!.containsKey('shorts')) {
    return DataModel.fromJson(json.decode(prefs!.getString('shorts').toString()), isShorts: true);
  }
  return null;
}

ShortsApi shortLists = ShortsApi();
