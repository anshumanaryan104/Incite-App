import 'package:flutter/material.dart';

class AppModel {
  ValueNotifier<ThemeMode> isDarkModeEnabled = ValueNotifier(ThemeMode.system);
  ValueNotifier<bool> isUserLoggedIn = ValueNotifier(false);
  ValueNotifier<bool> isNotificationEnabled = ValueNotifier(true);
  ValueNotifier<bool> isAutoPlay = ValueNotifier(true);
  AppModel();

  AppModel.fromMap(Map data) {
    isDarkModeEnabled.value = data['isDarkModeEnabled'] == "system"
        ? ThemeMode.system
        : data['isDarkModeEnabled'] == "light"
            ? ThemeMode.light
            : ThemeMode.dark;
    isUserLoggedIn.value = data['isUserLoggedIn'] as bool;
    isNotificationEnabled.value = data['is_notification_enabled'] as bool;
    isAutoPlay.value = data['is_auto_play'] ?? true;
  }

  Map toMap() {
    return {
      'isDarkModeEnabled': isDarkModeEnabled.value == ThemeMode.system
          ? "system"
          : isDarkModeEnabled.value == ThemeMode.light
              ? "light"
              : "dark",
      "isUserLoggedIn": isUserLoggedIn.value,
      'is_notification_enabled': isNotificationEnabled.value,
      'is_auto_play': isAutoPlay.value
    };
  }
}
