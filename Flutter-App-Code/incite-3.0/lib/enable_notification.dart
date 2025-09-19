import 'package:incite/api_controller/user_controller.dart';

enum NotificationType { firebase, onesignal }

class PushNotification {
  static NotificationType notificationType =
      allSettings.value.enableOsNotifications == '1' ? NotificationType.onesignal : NotificationType.firebase;
  // --------------
  // ---------------
  // --
  // If you want to switch just replace name from
  //  "NotificationType.onesignal"
  //   to
  //  "NotificationType.firebase"
  // --------------
  // --------------
}
