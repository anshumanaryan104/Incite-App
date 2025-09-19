import 'dart:async';
import 'dart:convert';
import 'dart:developer';
import 'dart:io';

import 'package:easy_audience_network/easy_audience_network.dart';
import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:get_it/get_it.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';
import 'package:http/http.dart' as http;
import 'package:incite/enable_notification.dart';
import 'package:incite/model/messages.dart';
import 'package:onesignal_flutter/onesignal_flutter.dart';
import 'package:path_provider/path_provider.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:unity_ads_plugin/unity_ads_plugin.dart';
import 'package:upgrader/upgrader.dart';
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/repository.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/app_util.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/rgbo_to_hex.dart';
import 'package:incite/utils/route_util.dart';
import 'package:incite/utils/shared_util.dart';
import 'package:url_launcher/url_launcher.dart';

import 'api_controller/app_provider.dart';
import 'firebase_options.dart';
import 'model/lang.dart';
import 'splash_screen.dart';
import 'test_connection.dart';

GlobalKey<NavigatorState> navkey = GlobalKey<NavigatorState>();
bool flexibleUpdateAvailable = false;
Upgrader? upgrader;

final StreamController<String?> selectNotificationStream = StreamController<String?>.broadcast();

final StreamController<ReceivedNotification> didReceiveLocalNotificationStream =
    StreamController<ReceivedNotification>.broadcast();

AndroidNotificationChannel channel = const AndroidNotificationChannel(
  'high_importance_channel', // id
  'High Importance Notifications', // title
  description: 'This channel is used for important notifications.', // description
  importance: Importance.high,
);

bool isFlutterLocalNotificationsInitialized = false;

FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

@pragma('vm:entry-point')
void notificationTapBackground(NotificationResponse notificationResponse) {
  log('notificationTapBackground');
  log(
    'notification(${notificationResponse.id}) action tapped: '
    '${notificationResponse.actionId} with'
    ' payload: ${notificationResponse.payload}',
  );
}

Future<String> _saveImage(http.Response response) async {
  // Get the directory to save the image
  final directory = await getApplicationDocumentsDirectory();

  // Create a file in the directory
  final file = File('${directory.path}/downloaded_image.png');

  // Write the response bytes to the file
  await file.writeAsBytes(response.bodyBytes);
  return file.path; // Return the path of the saved image
}

void showFlutterNotification(RemoteMessage message) async {
  http.Response response = await http.get(
    Uri.parse(Platform.isAndroid ? message.data['image'] ?? "" : message.data['']['image']),
  );
  var image = await _saveImage(response);
  var bigPictureStyleInformation = BigPictureStyleInformation(
    hideExpandedLargeIcon: true,
    ByteArrayAndroidBitmap.fromBase64String(base64Encode(response.bodyBytes)),
  );

  if (!kIsWeb) {
    flutterLocalNotificationsPlugin.show(
      message.hashCode,
      message.data['title'],
      message.data['body'],
      NotificationDetails(
        android: AndroidNotificationDetails(
          channel.id,
          channel.name,
          channelDescription: channel.description,
          icon: 'notification_icon',
          colorized: true,
          color: Colors.orange,
          largeIcon: ByteArrayAndroidBitmap.fromBase64String(base64Encode(response.bodyBytes)),
          styleInformation: bigPictureStyleInformation,
          number: 1,
        ),
        iOS: DarwinNotificationDetails(
          subtitle: message.data['title'],
          attachments: [DarwinNotificationAttachment(image)],
        ),
      ),
      payload: jsonEncode(message.data),
    );

    firebaseNotificationStore(
      NotificationResponse(
        notificationResponseType: NotificationResponseType.selectedNotification,
        payload: jsonEncode({
          "id": message.data['post_id'],
          "title": message.data['title'],
          "body": message.data['body'],
          "image": message.data['image'],
        }),
      ),
    );
  }
}

String? initialRoute;

Future<void> setupFlutterNotifications() async {
  if (isFlutterLocalNotificationsInitialized) {
    return;
  }

  await flutterLocalNotificationsPlugin.initialize(
    InitializationSettings(
      android: const AndroidInitializationSettings('notification_icon'),
      iOS: DarwinInitializationSettings(
        requestAlertPermission: true,
        // onDidReceiveLocalNotification: (int id, String? title, String? body, String? payload) async {
        //   var receivedNotification = ReceivedNotification(
        //     id: id,
        //     title: title,
        //     body: body,
        //     payload: payload,
        //   );
        //   didReceiveLocalNotificationStream.add(
        //     receivedNotification,
        //   );

        //   firebaseNotificationStore(NotificationResponse(
        //       notificationResponseType: NotificationResponseType.selectedNotification,
        //       payload: jsonEncode({
        //         "id": jsonDecode(receivedNotification.payload.toString())['post_id'],
        //         "title": receivedNotification.title,
        //         "body": receivedNotification.body,
        //         "image": receivedNotification.payload
        //       })));
        // },
      ),
    ),
    onDidReceiveNotificationResponse: (NotificationResponse notificationResponse) async {
      switch (notificationResponse.notificationResponseType) {
        case NotificationResponseType.selectedNotification:
          selectNotificationStream.add(notificationResponse.payload);

          if (PushNotification.notificationType == NotificationType.onesignal) {
            foregroundOneSignalNotificationOpener(notificationResponse);
          } else {
            firebaseNotificationStore(
              NotificationResponse(
                notificationResponseType: NotificationResponseType.selectedNotification,
                payload: jsonEncode({
                  "id": jsonDecode(notificationResponse.payload.toString())['post_id'],
                  "title": jsonDecode(notificationResponse.payload.toString())['title'],
                  "body": jsonDecode(notificationResponse.payload.toString())['body'],
                  "image": jsonDecode(notificationResponse.payload.toString())['image'],
                }),
              ),
            );
          }
          break;

        case NotificationResponseType.selectedNotificationAction:
          if (notificationResponse.actionId == 'share' || notificationResponse.actionId == 'bookmark') {
            selectNotificationStream.add(notificationResponse.payload);
            firebaseNotificationStore(notificationResponse);
          }
          break;
      }
    },
    onDidReceiveBackgroundNotificationResponse: notificationTapBackground,
  );

  /// Create an Android Notification Channel.
  ///
  /// We use this channel in the `AndroidManifest.xml` file to override the
  /// default FCM channel to enable heads up notifications.
  await flutterLocalNotificationsPlugin
      .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
      ?.createNotificationChannel(channel);

  /// Update the iOS foreground notification presentation options to allow
  /// heads up notifications.
  await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
    alert: true,
    badge: true,
    sound: true,
  );
  isFlutterLocalNotificationsInitialized = true;
}

void firebaseNotificationStore(NotificationResponse notificationResponse) {
  var notifications =
      prefs!.containsKey('notifications')
          ? jsonDecode(prefs!.getString('notifications').toString()) as List
          : [];
  var decodePayload = jsonDecode(notificationResponse.payload.toString());

  // log(notificationResponse.payload);
  notifications.add({
    "id": decodePayload['id'],
    "images": [decodePayload['image']],
    "title": decodePayload['title'],
    "body": decodePayload['body'],
    "updated_at": DateTime.now().toIso8601String(),
  });
  prefs!.setString('notifications', jsonEncode(notifications));
}

Future<Blog> blogDetail(String id) async {
  var url = "${Urls.baseUrl}blog-detail/$id";
  var result = await http.get(Uri.parse(url));
  Map data = await json.decode(result.body);
  final list = Blog.fromJson(data['data'], isNotification: true);
  return list;
}

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  showFlutterNotification(message);
  firebaseNotificationStore(
    NotificationResponse(
      notificationResponseType: NotificationResponseType.selectedNotification,
      payload: jsonEncode({
        "id": message.data['post_id'],
        "title": message.notification!.title,
        "body": message.notification!.body,
        "image": message.data['image'],
      }),
    ),
  );
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Temporarily disable Firebase
  // await Firebase.initializeApp(
  //   options: DefaultFirebaseOptions.currentPlatform,
  // );
  try {
    prefs = await SharedPreferences.getInstance();
    GetIt.instance.registerSingleton<SharedPreferencesUtils>(
      await SharedPreferencesUtils.getInstance() as SharedPreferencesUtils,
    );
    await UserProvider().checkSettingUpdate();
    prefs!.remove('setNotification');

    prefs!.setString('id', '1');

    await getDataFromSharedPrefs();
    await getMessageAndSetting();
    PushNotification.notificationType =
        allSettings.value.enableOsNotifications == '1'
            ? NotificationType.onesignal
            : NotificationType.firebase;
    if (allSettings.value.enableUnityAds == '1') {
      UnityAds.init(
        gameId:
            Platform.isAndroid
                ? allSettings.value.unityAndroidGameId ?? ""
                : allSettings.value.unityIosGameId ?? "",
        testMode: true,
        onComplete: () {
          log('Initialization Complete');
          // loadAd(AdManager.bannerAdPlacementId);
        },
        onFailed: (error, message) => log('Initialization Failed: $error $message'),
      );
    }
    // Disable Firebase and OneSignal temporarily
    // await firebaseInitialise();
    // await setupFlutterNotifications();
    // if (PushNotification.notificationType == NotificationType.onesignal) {
    //   await oneSignalInitialise();
    // }
    await getCurrentUser();
    await getInitialNotification();
  } catch (e) {
    prefs = await SharedPreferences.getInstance();
    prefs!.remove('setNotification');
    prefs!.setString('id', '1');

    await getCurrentUser();
    await getDataFromSharedPrefs();
    await getMessageAndSetting();
    debugPrint('error happened $e');
  }

  // Disable Firebase Analytics and Ads
  // FirebaseAnalytics.instance.setAnalyticsCollectionEnabled(true);
  // if (allSettings.value.enableAds == '1') {
  //   await MobileAds.instance.initialize();
  // }
  await Upgrader.clearSavedSettings();
  // initialLink = await FirebaseDynamicLinks.instance.getInitialLink();
  SystemChrome.setPreferredOrientations([DeviceOrientation.portraitUp]);

  // Disable Facebook Ads
  // if (allSettings.value.enableFbAds == '1') {
  //   EasyAudienceNetwork.init(
  //     testingId: "37b1da9d-b48c-4103-a393-2e095e734bd6", //optional
  //     iOSAdvertiserTrackingEnabled: true, //default false
  //   );
  // }

  runApp(
    MultiProvider(
      providers: [ChangeNotifierProvider(create: (context) => AppProvider())],
      child: const MyApp(),
    ),
  );
}

Future oneSignalInitialise() async {
  OneSignal.initialize("------- onesignal app id ----------");
  OneSignal.consentRequired(true);
  OneSignal.consentGiven(true);
  await Future.delayed(const Duration(milliseconds: 100));
  await OneSignal.Notifications.requestPermission(true);
}

Future<void> firebaseInitialise() async {
  if (PushNotification.notificationType == NotificationType.firebase) {
    FirebaseMessaging.instance.requestPermission();
    deviceToken =
        Platform.isAndroid
            ? await FirebaseMessaging.instance.getToken()
            : await FirebaseMessaging.instance.getAPNSToken();
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
  }
}

Future<void> getInitialNotification() async {
  await FirebaseMessaging.instance.getInitialMessage().then((val) async {
    final NotificationAppLaunchDetails? notificationAppLaunchDetails =
        !kIsWeb && Platform.isLinux
            ? null
            : await flutterLocalNotificationsPlugin.getNotificationAppLaunchDetails();
    initialRoute = "/";
    if (notificationAppLaunchDetails?.didNotificationLaunchApp ?? false) {
      var selectedNotificationPayload = notificationAppLaunchDetails!.notificationResponse?.payload;
      var route = jsonDecode(selectedNotificationPayload.toString())['post_id'];
      initialRoute = "${allSettings.value.androidSchema}/blog/$route";
    }
  });
}

Future<void> notificationOpener(OSNotificationClickEvent result, {String? id}) async {
  try {
    final blog =
        Platform.isIOS
            ? result.notification.rawPayload!['custom']['a']['blog'].toString()
            : json.decode(result.notification.rawPayload!['custom'].toString())['a']['blog'];

    final action = result.notification.rawPayload!['actionId'];

    if (prefs!.containsKey('id')) {
      prefs!.setString('setNotification', '$blog');
    }

    if (Platform.isAndroid) {
      var url2 = "${allSettings.value.androidSchema}/blog/$blog";
      var uri = "${allSettings.value.androidSchema}/blog/$blog/action/$action";

      await launchUrl(Uri.parse(action != null ? uri : url2));
    } else {
      var url2 = "${allSettings.value.iosSchema}/blog/$blog";
      var uri = "${allSettings.value.iosSchema}/blog/$blog/action/$action";

      await launchUrl(Uri.parse(action != null ? uri : url2));
    }
  } on Exception catch (e) {
    log(e.toString());
  }
}

Future<void> foregroundOneSignalNotificationOpener(NotificationResponse result, {String? id}) async {
  try {
    var decode = json.decode(result.payload.toString());

    // log(decode['custom']['a']);

    var decode2 = json.decode(decode['custom']);

    final blog = decode2['a']['blog'];

    final action = decode2['a']['actionId'].toString();

    if (prefs!.containsKey('id')) {
      prefs!.setString('setNotification', '$blog');
    }

    if (Platform.isAndroid) {
      var url2 = "${allSettings.value.androidSchema}/blog/$blog";
      var uri = "${allSettings.value.androidSchema}/blog/$blog/action/$action";

      await launchUrl(Uri.parse(action != '__DEFAULT__' ? uri : url2));
    } else {
      var url2 = "${allSettings.value.iosSchema}/blog/$blog";
      var uri = "${allSettings.value.iosSchema}/blog/$blog/action/$action";

      await launchUrl(Uri.parse(action != '__DEFAULT__' ? uri : url2));
    }
  } on Exception catch (e) {
    log(e.toString());
  }
}

class MyApp extends StatefulWidget {
  const MyApp({super.key});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  // This widget is the root of your application.
  UserProvider userProvider = UserProvider();
  late DateTime startTime;
  DateTime? endTime;
  String? initialMessage;

  @override
  void dispose() {
    didReceiveLocalNotificationStream.close();
    selectNotificationStream.close();
    super.dispose();
  }

  UserProvider user = UserProvider();

  void updateBlogList(Blog newBlog, AppProvider provider) {
    var data = provider.allNews!.blogs;
    provider.allNews!.blogs = [];
    provider.allNews!.blogs.add(newBlog);
    if (data.isNotEmpty && data.contains(newBlog)) {
      data.remove(newBlog);
    }
    provider.allNews!.blogs.addAll(data);
    blogListHolder.clearList();
    blogListHolder.setList(provider.allNews as DataModel);
    blogListHolder.setBlogType(BlogType.allnews);
    setState(() {});
  }

  void _configureSelectNotificationSubject() {
    selectNotificationStream.stream.listen((String? payload) async {
      var payload2 = jsonDecode(payload.toString());

      String? action;

      log(payload.toString());

      if (Platform.isAndroid) {
        var url2 = "${allSettings.value.androidSchema}/blog/${payload2['post_id']}";
        var uri = "${allSettings.value.androidSchema}/blog//${payload2['post_id']}/action/$action";

        // ignore: unnecessary_null_comparison
        await launchUrl(Uri.parse(action != null ? uri : url2));
      } else {
        var url2 = "${allSettings.value.iosSchema}/blog/${payload2['post_id']}";
        var uri = "${allSettings.value.iosSchema}/blog/${payload2['post_id']}/action/$action";

        // ignore: unnecessary_null_comparison
        await launchUrl(Uri.parse(action != null ? uri : url2));
      }
    });
  }

  void loadAd(String placementId) {
    UnityAds.load(
      placementId: placementId,
      onComplete: (placementId) {
        print('Load Complete $placementId');
      },
      onFailed: (placementId, error, message) => print('Load Failed $placementId: $error $message'),
    );
  }

  @override
  void initState() {
    super.initState();
    if (allSettings.value.enableUnityAds == '1') {
      UnityAds.setPrivacyConsent(PrivacyConsentType.gdpr, true);
      UnityAds.setPrivacyConsent(PrivacyConsentType.pipl, true);
      UnityAds.setPrivacyConsent(PrivacyConsentType.ageGate, true);
      UnityAds.setPrivacyConsent(PrivacyConsentType.ccpa, true);
    }
    upgrader = Upgrader(
      durationUntilAlertAgain:
          (Platform.isAndroid && allSettings.value.isAndroidForceUpdate == '1') ||
                  (Platform.isIOS && allSettings.value.isIosForceUpdate == '1')
              ? const Duration(seconds: 1)
              : Duration(
                days:
                    prefs!.containsKey('update_duration')
                        ? DateTime.parse(prefs!.getString('update_duration').toString()).day
                        : 0,
              ),
    );

    // Disable notification listeners
    // if (PushNotification.notificationType == NotificationType.firebase) {
    //   FirebaseMessaging.onMessage.listen(showFlutterNotification);
    //   _configureSelectNotificationSubject();
    //
    //   FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) async {
    //     log(message.data.toString());
    //     log('message.data.toString()');
    //     await getFirebasePostOpenIntent(message);
    //   });
    // }
    //
    // if (PushNotification.notificationType == NotificationType.onesignal) {
    //   onesignalMethods();
    // }

    startTime = DateTime.now();

    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      var provider = Provider.of<AppProvider>(context, listen: false);

      // Disable Firebase token
      // deviceToken =
      //     Platform.isAndroid
      //         ? await FirebaseMessaging.instance.getToken()
      //         : await FirebaseMessaging.instance.getAPNSToken();
      deviceToken = "test-token";

      get7DaysOlderDelete();
      user.getLanguageFromServer(context);

      //  checkForUpdate();
      provider.appTime(startTime);
    });
  }

  void onesignalMethods() {
    OneSignal.Notifications.addPermissionObserver((permission) {
      if (permission == false) {
        OneSignal.Notifications.canRequest().then((val) {
          if (val == true) {
            OneSignal.User.pushSubscription.optIn();
          } else {
            OneSignal.User.pushSubscription.optOut();
          }
        });
      } else {
        OneSignal.User.pushSubscription.optIn();
      }
    });

    OneSignal.User.pushSubscription.addObserver((accepted) async {
      // if(accepted.current.optedIn == true){
      final status = OneSignal.User.pushSubscription.id;
      final String? osUserID = status;
      if (osUserID != null) {
        //  if (prefs!.containsKey('player_id')) {
        currentUser.value.playerId = osUserID.toString();
        if (!prefs!.containsKey('player_id')) {
          if (accepted.current.optedIn == true) {
            OneSignal.User.pushSubscription.optIn();
            appThemeModel.value.isNotificationEnabled.value = true;
          } else {
            OneSignal.User.pushSubscription.optOut();
            appThemeModel.value.isNotificationEnabled.value = false;
          }
          prefs!.setString('player_id', accepted.current.id ?? "");
          prefs!.setString('app_data', jsonEncode(appThemeModel.value.toMap()));
        }
      }
      // }
    });
    // userProvider.checkSettingUpdate().whenComplete(() {
    //  getDataFromSharedPrefs();

    OneSignal.Notifications.addClickListener((OSNotificationClickEvent result) async {
      // result. (result.notification.payload);
      result.preventDefault();
      result.notification.display();
      log(result.toString());
      await notificationOpener(result);
    });

    OneSignal.Notifications.addForegroundWillDisplayListener((event) async {
      // Will be called whenever a notification is received in foreground
      // Display Notification, pass null param for not displaying the notification
      // if(event.notification.body != null){
      event.preventDefault();

      http.Response response = await http.get(
        Uri.parse(
          Platform.isAndroid ? event.notification.bigPicture : event.notification.attachments!['image'],
        ),
      );
      var image = await _saveImage(response);
      var bigPictureStyleInformation = BigPictureStyleInformation(
        hideExpandedLargeIcon: true,
        ByteArrayAndroidBitmap.fromBase64String(base64Encode(response.bodyBytes)),
      );

      flutterLocalNotificationsPlugin.show(
        event.hashCode,
        event.notification.title,
        event.notification.body,
        NotificationDetails(
          android: AndroidNotificationDetails(
            channel.id,
            channel.name,
            channelDescription: channel.description,
            icon: 'notification_icon',
            colorized: true,
            color: Colors.orange,
            largeIcon: ByteArrayAndroidBitmap.fromBase64String(base64Encode(response.bodyBytes)),
            styleInformation: bigPictureStyleInformation,
            number: 1,
          ),
          iOS: DarwinNotificationDetails(
            subtitle: event.notification.title,
            attachments: [DarwinNotificationAttachment(image)],
          ),
        ),
        payload: jsonEncode(event.notification.rawPayload),
      );

      flutterLocalNotificationsPlugin.getNotificationAppLaunchDetails();
    });
  }

  Future<void> getFirebasePostOpenIntent(RemoteMessage message) async {
    var action = message.notification!.android!.clickAction;

    if (Platform.isAndroid) {
      var url2 = "${allSettings.value.androidSchema}/blog/${message.data['post_id']}";
      var uri = "${allSettings.value.androidSchema}/blog//${message.data['post_id']}/action/$action";

      await launchUrl(Uri.parse(action != null ? uri : url2));
    } else {
      var url2 = "${allSettings.value.iosSchema}/blog/${message.data['post_id']}";
      var uri = "${allSettings.value.iosSchema}/blog/${message.data['post_id']}/action/$action";

      await launchUrl(Uri.parse(action != null ? uri : url2));
    }
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder(
      valueListenable: allSettings,
      builder: (context, setting, child) {
        return ValueListenableBuilder(
          valueListenable: appThemeModel,
          builder: (context, AppModel value, child) {
            return ValueListenableBuilder(
              valueListenable: languageCode,
              builder: (context, Language lang, child) {
                return AnnotatedRegion<SystemUiOverlayStyle>(
                  value: SystemUiOverlayStyle(
                    systemNavigationBarIconBrightness:
                        appThemeModel.value.isDarkModeEnabled.value == ThemeMode.dark
                            ? Brightness.light
                            : Brightness.dark,
                    systemNavigationBarColor:
                        appThemeModel.value.isDarkModeEnabled.value == ThemeMode.dark
                            ? ColorUtil.textblack
                            : Colors.white,
                  ),
                  child: MaterialApp(
                    navigatorKey: navkey,
                    title: setting.appName ?? 'Incite',
                    debugShowCheckedModeBanner: false,
                    home:
                        !prefs!.containsKey('setNotification')
                            ? SplashScreen(isNotificationClick: prefs!.containsKey('setNotification'))
                            : const Scaffold(),
                    builder: (context, child) {
                      child = Directionality(
                        textDirection: lang.pos == 'rtl' ? TextDirection.rtl : TextDirection.ltr,
                        child: child as Widget,
                      ); //do something
                      return child;
                    },
                    initialRoute: initialRoute,
                    locale: Locale(languageCode.value.language.toString()),
                    onGenerateRoute: RouteGenerator.generateRoute,
                    themeMode: appThemeModel.value.isDarkModeEnabled.value,
                    darkTheme: getDarkThemeData(
                      hexToRgb(setting.primaryColor),
                      hexToRgb(setting.secondaryColor),
                    ),
                    theme: getLightThemeData(
                      hexToRgb(setting.primaryColor),
                      hexToRgb(setting.secondaryColor),
                    ),
                  ),
                );
              },
            );
          },
        );
      },
    );
  }

  void get7DaysOlderDelete() {
    if (prefs!.containsKey('notifications')) {
      var notifications = [];
      jsonDecode(prefs!.getString('notifications').toString()).forEach((e) {
        var parse = DateTime.parse(e['updated_at']);
        var sevenDaysAgo = parse.subtract(const Duration(days: 7));
        if (parse.isAfter(sevenDaysAgo)) {
          notifications.add(Blog.fromJson(e));
        }
      });

      prefs!.setString('notifications', jsonEncode(notifications));
    }
  }
}
