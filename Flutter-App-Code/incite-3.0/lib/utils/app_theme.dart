import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/lang.dart';
import 'package:incite/model/messages.dart';
import 'package:incite/model/settings.dart';
import '../model/cms.dart';
import '../splash_screen.dart';
import 'app_util.dart';
import 'color_util.dart';

//* <------------- App Theme [Theme Manager] ------------->

ValueNotifier<AppModel> appThemeModel = ValueNotifier(AppModel());

toggleDarkMode(ThemeMode value) {
  AppModel model = appThemeModel.value;
  appThemeModel.value = AppModel.fromMap({
    'isDarkModeEnabled': value == ThemeMode.system
        ? "system"
        : value == ThemeMode.light
            ? 'light'
            : 'dark',
    'isUserLoggedIn': model.isUserLoggedIn.value,
    'is_notification_enabled': model.isNotificationEnabled.value,
    'is_auto_play': model.isAutoPlay.value
  });
  saveDataToSharedPrefs(appThemeModel.value);
}

toggleSignInOut(bool value) {
  AppModel model = appThemeModel.value;
  appThemeModel.value = AppModel.fromMap({
    'isDarkModeEnabled': model.isDarkModeEnabled.value == ThemeMode.system
        ? "system"
        : model.isDarkModeEnabled.value == ThemeMode.light
            ? 'light'
            : 'dark',
    'isUserLoggedIn': value,
    'is_notification_enabled': model.isNotificationEnabled.value,
    'is_auto_play': model.isAutoPlay.value
  });
  saveDataToSharedPrefs(appThemeModel.value);
}

toggleNotify(bool value) async {
  AppModel model = appThemeModel.value;
  appThemeModel.value = AppModel.fromMap({
    'isDarkModeEnabled': model.isDarkModeEnabled.value == ThemeMode.system
        ? "system"
        : model.isDarkModeEnabled.value == ThemeMode.light
            ? 'light'
            : 'dark',
    'isUserLoggedIn': model.isUserLoggedIn.value,
    'is_notification_enabled': value,
    'is_auto_play': model.isAutoPlay.value
  });
  saveDataToSharedPrefs(appThemeModel.value);
}

toggleAutoPlay(bool value) async {
  AppModel model = appThemeModel.value;
  appThemeModel.value = AppModel.fromMap({
    'isDarkModeEnabled': model.isDarkModeEnabled.value == ThemeMode.system
        ? "system"
        : model.isDarkModeEnabled.value == ThemeMode.light
            ? 'light'
            : 'dark',
    'isUserLoggedIn': model.isUserLoggedIn.value,
    'is_notification_enabled': model.isNotificationEnabled.value,
    'is_auto_play': value
  });
  saveDataToSharedPrefs(appThemeModel.value);
}

saveDataToSharedPrefs(AppModel model) async {
  try {
    prefs?.setString('app_data', json.encode(model.toMap()));
    // ignore: empty_catches
  } catch (e) {}
}

getDataFromSharedPrefs() async {
  if (prefs!.containsKey('update_duration')) {
    Duration daysago =
        DateTime.now().difference(DateTime.parse(prefs!.getString('update_duration').toString()));

    if (daysago.inDays >= 3) {
      prefs!.remove('update_duration');
    }
  }

  if (prefs!.containsKey('app_data')) {
    AppModel model = AppModel.fromMap(json.decode(prefs!.getString('app_data').toString()));
    appThemeModel.value = model;
  } else {
    // initializing app_data in sharedPreferences with default values
    saveDataToSharedPrefs(AppModel());
  }
}

getMessageAndSetting() {
  if (prefs!.containsKey('local_data')) {
    allMessages.value = Messages.fromJson(json.decode(prefs!.getString('local_data').toString()));
  }
  if (prefs!.containsKey('setting')) {
    allSettings.value = SettingModel.fromJson(json.decode(prefs!.getString('setting').toString()));
  }
  if (prefs!.containsKey('languages')) {
    allLanguages = [];
    json.decode(prefs!.getString("languages").toString()).forEach((lang) {
      allLanguages.add(Language.fromJson(lang));
    });
  }
  if (prefs!.containsKey('defalut_language')) {
    String lng = prefs!.getString("defalut_language").toString();
    languageCode.value = Language.fromJson(json.decode(lng));
  }
  if (prefs!.containsKey('OffAds')) {
    allCMS = [];
    final ads = prefs!.getString('OffAds').toString();
    json.decode(ads)['data'].forEach((language) {
      allCMS.add(CmsModel.fromJson(language));
    });
  }
}

//* ThemeData according to brightness i.e Dark or Light mode

ThemeData getLightThemeData(Color primary, Color secondary) {
  return ThemeData(
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: FadeUpwardsPageTransitionsBuilder(),
          TargetPlatform.iOS: ZoomPageTransitionsBuilder(),
        },
      ),
      primaryColor: primary,
      appBarTheme: const AppBarTheme(backgroundColor: Colors.white),
      brightness: Brightness.light,
      scaffoldBackgroundColor: const Color.fromRGBO(255, 255, 255, 1.0),
      dividerColor: Colors.grey.shade200,
      primaryIconTheme: const IconThemeData(color: Colors.black),
      textTheme: getLightTextTheme(),
      colorScheme: ColorScheme.fromSeed(
        seedColor: primary,
        primary: primary,
        secondary: secondary,
      ));
}

ThemeData getDarkThemeData(Color primary, Color secondary) {
  return ThemeData(
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: FadeUpwardsPageTransitionsBuilder(),
          TargetPlatform.iOS: ZoomPageTransitionsBuilder(),
        },
      ),
      primaryColor: primary,
      scaffoldBackgroundColor: const Color.fromRGBO(18, 18, 18, 1),
      brightness: Brightness.dark,
      appBarTheme: const AppBarTheme(backgroundColor: Color.fromARGB(255, 20, 19, 19)),
      cardColor: const Color.fromRGBO(40, 40, 40, 1),
      canvasColor: const Color.fromRGBO(20, 20, 20, 1),
      primaryIconTheme: const IconThemeData(color: Colors.white),
      textTheme: getDarkTextTheme(),
      colorScheme:
          ColorScheme.fromSeed(seedColor: primary, secondary: secondary, brightness: Brightness.dark));
}

//* Text Themes according to brightness i.e Dark or Light mode

TextTheme getLightTextTheme() {
  return const TextTheme(
      displayLarge: TextStyle(fontFamily: 'Roboto', fontSize: 30, color: ColorUtil.textblack),
      displayMedium: TextStyle(
          fontFamily: 'Roboto', fontSize: 28, color: ColorUtil.textblack, fontWeight: FontWeight.w700),
      displaySmall: TextStyle(
          color: ColorUtil.textblack, fontFamily: 'Roboto', fontSize: 18, fontWeight: FontWeight.w700),
      headlineLarge: TextStyle(fontFamily: 'Roboto', fontSize: 17, color: ColorUtil.textblack),
      headlineMedium: TextStyle(
          fontFamily: 'Roboto', fontSize: 16, color: ColorUtil.textblack, fontWeight: FontWeight.w600),
      headlineSmall: TextStyle(
          fontFamily: 'Roboto', fontSize: 14, color: ColorUtil.textblack, fontWeight: FontWeight.w600),
      titleLarge: TextStyle(fontFamily: 'Roboto', fontSize: 14, color: ColorUtil.textblack),
      titleMedium: TextStyle(color: ColorUtil.textblack, fontFamily: 'Roboto', fontSize: 12),
      bodyLarge: TextStyle(fontWeight: FontWeight.w500, fontFamily: 'Roboto', fontSize: 15),
      bodyMedium: TextStyle(fontFamily: 'Roboto', fontSize: 14, color: ColorUtil.textblack));
}

TextTheme getDarkTextTheme() {
  return const TextTheme(
      displayLarge: TextStyle(fontFamily: 'Roboto', fontSize: 30, color: Colors.white),
      displayMedium:
          TextStyle(fontFamily: 'Roboto', fontSize: 28, color: Colors.white, fontWeight: FontWeight.w700),
      displaySmall:
          TextStyle(color: Colors.white, fontFamily: 'Roboto', fontSize: 18, fontWeight: FontWeight.w700),
      headlineLarge: TextStyle(fontFamily: 'Roboto', fontSize: 17, color: Colors.white),
      headlineMedium:
          TextStyle(fontFamily: 'Roboto', fontSize: 16, color: Colors.white, fontWeight: FontWeight.w600),
      headlineSmall:
          TextStyle(fontFamily: 'Roboto', fontSize: 14, color: Colors.white, fontWeight: FontWeight.w600),
      titleLarge: TextStyle(fontFamily: 'Roboto', fontSize: 14, color: Colors.white),
      titleMedium: TextStyle(color: Colors.white, fontFamily: 'Roboto', fontSize: 12),
      bodyLarge: TextStyle(fontWeight: FontWeight.w500, fontFamily: 'Roboto', fontSize: 15),
      bodyMedium: TextStyle(fontFamily: 'Roboto', fontSize: 14, color: Colors.white));
}

extension CustomOpacity on Color {
  Color customOpacity(double opacity) {
    // Ensure opacity is between 0.0 and 1.0
    double clampedOpacity = opacity.clamp(0.0, 1.0);

    // Calculate the new alpha value by scaling the current alpha with the opacity
    double newAlpha = (a * clampedOpacity);

    // Return a new color with the adjusted alpha value
    return withValues(alpha: newAlpha);
  }
}
