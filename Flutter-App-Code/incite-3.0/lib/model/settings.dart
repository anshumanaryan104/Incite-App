class SettingModel {
  String? homepageTheme;
  String? layout;
  String? googleAnalyticsCode;
  String? appName;
  String? baseImageUrl;
  String? bundleIdAndroid;
  String? bundleIdIos;
  String? isAndroidForceUpdate, isIosForceUpdate;
  String? appLogo;
  String? appSplashScreen;
  String? siteTitle;
  String? enableNotifications;
  String? firebaseMsgKey;
  String? primaryColor;
  String? isShortEnable;
  String? firebaseApiKey;
  String? fromName;
  String? enableMaintainanceMode;
  String? maintainanceTitle;
  String? maintainanceShortText;
  String? pushNotificationEnabled;
  String? dateFormat;
  String? timezone;
  String? blogAccentCode;
  String? liveNewsLogo;
  String? liveNewsStatus;
  String? ePaperLogo;
  String? ePaperStatus;
  String? enableAds;
  String? admobBannerIdAndroid;
  String? admobInterstitialIdAndroid;
  String? admobBannerIdIos;
  String? admobInterstitialIdIos;
  String? fbInterstitialIdIos;
  String? fbInterstitialIdAndroid;
  String? admobFrequency;
  String? enableFbAds;
  String? fbAdsPlacementIdAndroid;
  String? fbAdsPlacementIdIos;
  String? fbAdsAppToken;
  String? fbAdsFrequency;
  String? blogLanguage;
  String? blogAccent;
  String? blogVoice;
  String? rectangualrAppLogo;
  String? signingKeyAndroid;
  String? keyPropertyAndroid;
  String? oneSignalKey;
  String? googleApikey;
  String? secondaryColor, supportMail;
  String? playStoreUrl;
  String? appStoreUrl,
      enableGoogleSignIn,
      categoryIconShape,
      categoryIconColumn,
      enableShareSetting,
      enableAppleSignIn;
  bool? isVoiceEnabled;

  String? enableUnityAds, unityAdsFrequency, unityAndroidGameId, unityIosGameId;
  String? androidSchema,
      enableOsNotifications,
      iosSchema,
      unityAdsBannerIdAndroid,
      unityAdsBannerIdIos,
      unityPlacementAndoidId,
      unityPlacementIosId;

  SettingModel(
      {this.homepageTheme,
      this.enableShareSetting,
      this.layout,
      this.enableOsNotifications,
      this.googleAnalyticsCode,
      this.enableGoogleSignIn,
      this.enableAppleSignIn,
      this.appName,
      this.bundleIdAndroid,
      this.bundleIdIos,
      this.isAndroidForceUpdate,
      this.appLogo,
      this.secondaryColor,
      this.appSplashScreen,
      this.isVoiceEnabled = true,
      this.playStoreUrl,
      this.appStoreUrl,
      this.googleApikey,
      this.siteTitle,
      this.enableNotifications,
      this.firebaseMsgKey,
      this.firebaseApiKey,
      this.fbInterstitialIdAndroid,
      this.fbInterstitialIdIos,
      this.baseImageUrl,
      this.fromName,
      this.enableMaintainanceMode,
      this.maintainanceTitle,
      this.maintainanceShortText,
      this.pushNotificationEnabled,
      this.dateFormat,
      this.timezone,
      this.blogAccentCode,
      this.liveNewsLogo,
      this.liveNewsStatus,
      this.ePaperLogo,
      this.ePaperStatus,
      this.enableAds,
      this.admobBannerIdAndroid,
      this.admobInterstitialIdAndroid,
      this.admobBannerIdIos,
      this.supportMail,
      this.admobInterstitialIdIos,
      this.admobFrequency,
      this.enableFbAds,
      this.fbAdsPlacementIdAndroid,
      this.fbAdsPlacementIdIos,
      this.fbAdsAppToken,
      this.fbAdsFrequency,
      this.blogLanguage,
      this.blogAccent,
      this.blogVoice,
      this.rectangualrAppLogo,
      this.signingKeyAndroid,
      this.androidSchema,
      this.categoryIconShape,
      this.categoryIconColumn,
      this.iosSchema,
      this.oneSignalKey,
      this.keyPropertyAndroid,

      // ---new addtions
      this.enableUnityAds,
      this.unityAdsFrequency,
      this.unityAdsBannerIdAndroid,
      this.unityAdsBannerIdIos,
      this.unityAndroidGameId,
      this.unityIosGameId,
      this.unityPlacementAndoidId,
      this.unityPlacementIosId});

  SettingModel.fromJson(Map<String, dynamic> json) {
    homepageTheme = json['homepage_theme'];
    layout = json['layout'];
    googleAnalyticsCode = json['google_analytics_code'];
    appName = json['app_name'];
    bundleIdAndroid = json['bundle_id_android'];
    bundleIdIos = json['bundle_id_ios'];
    secondaryColor = json['secondary_color'];
    baseImageUrl = json['base_url'];
    isAndroidForceUpdate = json['is_android_app_force_update'];
    appLogo = json['app_logo'];
    enableShareSetting = json['enable_share_setting'];
    enableGoogleSignIn = json["enable_google_login"];
    enableAppleSignIn = json["enable_apple_login"];
    appSplashScreen = json['app_splash_screen'];
    fbInterstitialIdAndroid = json['fb_ads_interstitial_id_android'];
    fbInterstitialIdIos = json['fb_ads_interstitial_id_android'];
    siteTitle = json['site_title'];
    isVoiceEnabled = json['is_voice_enabled'] == '0' ? false : true;
    googleApikey = json['google_api_key'];
    enableNotifications = json['enable_notifications'];
    firebaseMsgKey = json['firebase_msg_key'];
    firebaseApiKey = json['firebase_api_key'];
    fromName = json['from_name'];
    primaryColor = json['primary_color'];
    enableMaintainanceMode = json['enable_maintainance_mode'];
    maintainanceTitle = json['maintainance_title'];
    maintainanceShortText = json['maintainance_short_text'];
    pushNotificationEnabled = json['push_notification_enabled'];
    dateFormat = json['date_format'];
    timezone = json['timezone'];
    enableOsNotifications = json['enable_os_notifications'];
    blogAccentCode = json['blog_accent_code'];
    androidSchema = json['android_schema'];
    iosSchema = json['ios_schema'];
    liveNewsLogo = json['live_news_logo'];
    isShortEnable = json['is_short_video_enable'];
    liveNewsStatus = json['live_news_status'];
    ePaperLogo = json['e_paper_logo'] ?? '0';
    ePaperStatus = json['e_paper_status'] ?? '0';
    enableAds = json['enable_ads'] ?? '0';
    admobBannerIdAndroid = json['admob_banner_id_android'];
    admobInterstitialIdAndroid = json['admob_interstitial_id_android'];
    admobBannerIdIos = json['admob_banner_id_ios'];
    admobInterstitialIdIos = json['admob_interstitial_id_ios'];
    admobFrequency = json['admob_frequency'] ?? '1';
    enableFbAds = json['enable_fb_ads'] ?? '0';
    fbAdsPlacementIdAndroid = json['fb_ads_placement_id_android'];
    fbAdsPlacementIdIos = json['fb_ads_placement_id_ios'];
    fbAdsAppToken = json['fb_ads_app_token'];
    oneSignalKey = json['one_signal_app_id'];
    fbAdsFrequency = json['fb_ads_frequency'];
    blogLanguage = json['blog_language'];
    blogAccent = json['blog_accent'];
    categoryIconShape = json['category_icon_shape'];
    categoryIconColumn = json['category_icon_column'];
    blogVoice = json['blog_voice'];
    rectangualrAppLogo = json['rectangualr_app_logo'];
    signingKeyAndroid = json['signing_key_android'];
    playStoreUrl = json['playstore_url'];
    appStoreUrl = json['appstore_url'];
    isIosForceUpdate = json['is_ios_app_force_update'];
    keyPropertyAndroid = json['key_property_android'];
    supportMail = json["support_email"];
// ---new addition
    enableUnityAds = json['enable_unity_ads'];
    unityAdsFrequency = json['unity_ads_frequency'] ?? "1";
    unityAdsBannerIdAndroid = json['unity_ads_banner_id_android'];
    unityAdsBannerIdIos = json['unity_ads_banner_id_ios'];
    unityAndroidGameId = json['unity_android_game_id'];
    unityIosGameId = json['unity_ios_game_id'];
    unityPlacementAndoidId = json['unity_placement_android_id'];
    unityPlacementIosId = json['unity_placement_ios_id'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['homepage_theme'] = homepageTheme;
    data['layout'] = layout;
    data['google_analytics_code'] = googleAnalyticsCode;
    data['app_name'] = appName;
    data['bundle_id_android'] = bundleIdAndroid;
    data['bundle_id_ios'] = bundleIdIos;
    data['is_android_app_force_update'] = isAndroidForceUpdate;
    data['is_ios_app_force_update'] = isIosForceUpdate;
    data['app_logo'] = appLogo;
    data['app_splash_screen'] = appSplashScreen;
    data["enable_apple_login"] = enableAppleSignIn;
    data["support_email"] = supportMail;
    data['site_title'] = siteTitle;
    data['enable_notifications'] = enableNotifications;
    data['firebase_msg_key'] = firebaseMsgKey;
    data['android_schema'] = androidSchema;
    data['ios_schema'] = iosSchema;
    data['firebase_api_key'] = firebaseApiKey;
    data['category_icon_shape'] = categoryIconShape;
    data['from_name'] = fromName;
    data["enable_google_login"] = enableGoogleSignIn;
    data['enable_maintainance_mode'] = enableMaintainanceMode;
    data['maintainance_title'] = maintainanceTitle;
    data['maintainance_short_text'] = maintainanceShortText;
    data['push_notification_enabled'] = pushNotificationEnabled;
    data['date_format'] = dateFormat;
    data['is_short_video_enable'] = isShortEnable;
    data['timezone'] = timezone;
    data['blog_accent_code'] = blogAccentCode;
    data['live_news_logo'] = liveNewsLogo;
    data['live_news_status'] = liveNewsStatus;
    data['e_paper_logo'] = ePaperLogo;
    data['e_paper_status'] = ePaperStatus;
    data['enable_ads'] = enableAds;
    data['admob_banner_id_android'] = admobBannerIdAndroid;
    data['admob_interstitial_id_android'] = admobInterstitialIdAndroid;
    data['admob_banner_id_ios'] = admobBannerIdIos;
    data['admob_interstitial_id_ios'] = admobInterstitialIdIos;
    data['admob_frequency'] = admobFrequency;
    data['enable_fb_ads'] = enableFbAds;
    data['fb_ads_placement_id_android'] = fbAdsPlacementIdAndroid;
    data['fb_ads_placement_id_ios'] = fbAdsPlacementIdIos;
    data['fb_ads_app_token'] = fbAdsAppToken;
    data['fb_ads_frequency'] = fbAdsFrequency;
    data['blog_language'] = blogLanguage;
    data['playstore_url'] = playStoreUrl;
    data['appstore_url'] = appStoreUrl;
    data['blog_accent'] = blogAccent;
    data['blog_voice'] = blogVoice;
    data['rectangualr_app_logo'] = rectangualrAppLogo;
    data['signing_key_android'] = signingKeyAndroid;
    data['key_property_android'] = keyPropertyAndroid;
    // ---- new additions -----
    data['enable_unity_ads'] = enableUnityAds;
    data['unity_ads_frequency'] = unityAdsFrequency;
    data['enable_os_notifications'] = enableOsNotifications;
    data['unity_ads_banner_id_android'] = unityAdsBannerIdAndroid;
    data['unity_ads_banner_id_ios'] = unityAdsBannerIdIos;
    data['unity_android_game_id'] = unityAndroidGameId;
    data['unity_ios_game_id'] = unityIosGameId;
    data['unity_placement_android_id'] = unityPlacementAndoidId;
    data['unity_placement_ios_id'] = unityPlacementIosId;
    return data;
  }
}
