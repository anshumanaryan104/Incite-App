import 'dart:async';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'api_controller/app_provider.dart';
import 'test_connection.dart';

SharedPreferences? prefs;

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key, this.isNotificationClick = false});
  final bool isNotificationClick;

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  UserProvider user = UserProvider();

  void showSnack(String text) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(text)));
  }

  @override
  void initState() {
    // Always call startCall regardless of notification status
    startCall();
    super.initState();
  }

  Future startCall() async {
    // Immediately navigate after a short delay
    Future.delayed(const Duration(seconds: 1), () {
      if (mounted) {
        switchToPage();
      }
    });
  }

  FutureOr<void> switchToPage() {
    // Skip login check - go directly to main page
    Navigator.pushNamedAndRemoveUntil(context, '/MainPage', arguments: 1, (route) => false);
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      child: ValueListenableBuilder(
          valueListenable: allSettings,
          key: ValueKey(allSettings.value.enableMaintainanceMode),
          builder: (context, value, child) {
            return AnnotatedRegion(
              value: SystemUiOverlayStyle(
                  statusBarIconBrightness: dark(context) ? Brightness.light : Brightness.dark,
                  statusBarColor: Colors.transparent),
              child: Scaffold(
                body: SafeArea(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Center(
                        child: AnimationFadeScale(
                            duration: 600,
                            child: AnimationFadeSlide(
                                duration: 700,
                                dy: 0.5,
                                child: Column(
                                  children: [
                                    value.appSplashScreen == null
                                        ? Image.asset(Img.logo, width: 100, height: 100)
                                        : CachedNetworkImage(
                                            imageUrl:
                                                "${value.baseImageUrl}/${value.appSplashScreen.toString()}",
                                            width: 100,
                                            height: 100,
                                            placeholder: (context, url) {
                                              return Image.asset(Img.logo, width: 100, height: 100);
                                            },
                                            errorWidget: (context, url, error) {
                                              return Image.asset(Img.logo, width: 100, height: 100);
                                            }),
                                    if (value.appName != null) const SizedBox(height: 12),
                                    if (value.appName != null)
                                      Text(value.appName ?? '',
                                          style: Theme.of(context)
                                              .textTheme
                                              .titleMedium!
                                              .copyWith(fontSize: 24, fontWeight: FontWeight.bold))
                                  ],
                                ))),
                      )
                    ],
                  ),
                ),
              ),
            );
          }),
    );
  }
}
