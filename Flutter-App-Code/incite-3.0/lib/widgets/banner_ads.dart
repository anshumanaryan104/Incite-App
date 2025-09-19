import 'dart:developer';

import 'package:easy_audience_network/easy_audience_network.dart' as facebook;
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart' as google;
import 'package:incite/api_controller/user_controller.dart';
import 'package:unity_ads_plugin/unity_ads_plugin.dart';

class AdManager {
  static String get gameId {
    if (defaultTargetPlatform == TargetPlatform.android) {
      return allSettings.value.unityAndroidGameId ?? "";
    }
    if (defaultTargetPlatform == TargetPlatform.iOS) {
      return allSettings.value.unityIosGameId ?? "";
    }
    return '';
  }

  static String get bannerAdPlacementId {
    if (defaultTargetPlatform == TargetPlatform.android) {
      return allSettings.value.unityAdsBannerIdAndroid ?? "";
    } else {
      return allSettings.value.unityAdsBannerIdIos ?? "";
    }
  }

  static String get interstitialVideoAdPlacementId {
    if (defaultTargetPlatform == TargetPlatform.android) {
      return allSettings.value.unityPlacementAndoidId ?? "";
    } else {
      return allSettings.value.unityPlacementIosId ?? "";
    }
  }

  static String get rewardedVideoAdPlacementId {
    return 'your_rewarded_video_ad_placement_id';
  }
}

class BannerAds extends StatefulWidget {
  const BannerAds({super.key, this.adUnitId = ''});

  final String adUnitId;

  @override
  State<BannerAds> createState() => _BannerAdsState();
}

class _BannerAdsState extends State<BannerAds> {
  late google.BannerAd myBanner;
  bool isBannerLoaded = false;

  @override
  void initState() {
    myBannerAd();

    super.initState();
  }

  void myBannerAd() async {
    myBanner = google.BannerAd(
      adUnitId: widget.adUnitId != '' ? widget.adUnitId : '',
      size: google.AdSize.banner,
      request: const google.AdRequest(),
      listener: google.BannerAdListener(onAdLoaded: (ad) {
        myBanner = ad as google.BannerAd;
        isBannerLoaded = true;
        setState(() {});
      }, onAdFailedToLoad: (ad, error) {
        setState(() {
          isBannerLoaded = false;
        });
        ad.dispose();
      }),
    );
    await myBanner.load();
  }

  @override
  void dispose() {
    myBanner.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    var size2 = MediaQuery.of(context).size;
    // var isDark = Theme.of(context).brightness == Brightness.dark;
    return isBannerLoaded
        ? Container(
            alignment: Alignment.center,
            width: size2.width,
            height: myBanner.size.height.toDouble(),
            color: Theme.of(context).cardColor,
            child: google.AdWidget(ad: myBanner))
        : Container(
            width: size2.width,
            color: Theme.of(context).cardColor,
            alignment: Alignment.center,
            height: 40,
            child: const SizedBox(),
          );
  }
}

class FacebookAd extends StatefulWidget {
  const FacebookAd({super.key, this.adUnitId = ''});

  final String adUnitId;

  @override
  State<FacebookAd> createState() => _FacebookAdState();
}

class _FacebookAdState extends State<FacebookAd> {
  late Widget facebookAd;
  bool isBannerLoaded = false;

  @override
  void initState() {
    facebookAds();
    super.initState();
  }

  facebookAds() {
    facebookAd = facebook.BannerAd(
      placementId: "IMG_16_9_APP_INSTALL#${widget.adUnitId}", //testid
      bannerSize: facebook.BannerSize.STANDARD,
      keepAlive: true,
      listener: facebook.BannerAdListener(
        onError: (code, message) => print('banner ad error\ncode: $code\nmessage:$message'),
        onLoaded: () {
          isBannerLoaded = true;
          setState(() {});
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    var size2 = MediaQuery.of(context).size;
    // var isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      alignment: Alignment.center,
      width: size2.width,
      height: 50,
      color: Theme.of(context).cardColor,
      child: isBannerLoaded
          ? facebookAd
          : const SizedBox(
              child: Text('Facebook Ads', style: TextStyle(fontSize: 16)),
            ),
    );
    // : Container(
    //   width: size2.width,
    //   color: Theme.of(context).cardColor,
    //   alignment: Alignment.center,
    //   height: 40,
    //   child: const SizedBox(),
    // );
  }
}

class UnityInAppAd extends StatelessWidget {
  const UnityInAppAd({super.key});

  @override
  Widget build(BuildContext context) {
    return UnityBannerAd(
      placementId: AdManager.bannerAdPlacementId,
      onLoad: (placementId) => log('Banner loaded: $placementId'),
      onClick: (placementId) => log('Banner clicked: $placementId'),
      onShown: (placementId) => log('Banner shown: $placementId'),
      onFailed: (placementId, error, message) => log('Banner Ad $placementId failed: $error $message'),
    );
  }
}

class UnityAppAdsInBlogs extends ChangeNotifier {
  Map<String, bool> placements = {
    AdManager.interstitialVideoAdPlacementId: false,
    // AdManager.rewardedVideoAdPlacementId: false
  };
  void loadAds() {
    for (var placementId in placements.keys) {
      loadAd(placementId);
    }
  }

  static void loadAd(String placementId) {
    UnityAds.load(
      placementId: placementId,
      onComplete: (placementId) {
        print('Load Complete $placementId');
      },
      onFailed: (placementId, error, message) => print('Load Failed $placementId: $error $message'),
    );
  }

  static void showAd(String placementId) {
    UnityAds.showVideoAd(
      placementId: placementId,
      onComplete: (placementId) {
        print('Video Ad $placementId completed');
        // loadAd(placementId);
      },
      onFailed: (placementId, error, message) {
        print('Video Ad $placementId failed: $error $message');
        loadAd(placementId);
      },
      onStart: (placementId) => print('Video Ad $placementId started'),
      onClick: (placementId) => print('Video Ad $placementId click'),
      onSkipped: (placementId) {
        print('Video Ad $placementId skipped');
        loadAd(placementId);
      },
    );
  }
}
