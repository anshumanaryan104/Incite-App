import 'dart:async';
import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:incite/api_controller/repository.dart';
import 'package:incite/main.dart';
import 'package:incite/model/home.dart';
import 'package:incite/pages/auth/login.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:incite/widgets/svg_icon.dart';
import 'package:mvc_pattern/mvc_pattern.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:the_apple_sign_in/the_apple_sign_in.dart';
import '../model/blog.dart';
import '../model/cms.dart';
import '../model/lang.dart';
import '../model/messages.dart';
import '../model/settings.dart';
import '../model/user.dart';
import '../splash_screen.dart';
import '../urls/url.dart';
import 'app_provider.dart';
import 'repository.dart' as repository;
import 'package:http/http.dart' as http;
import 'package:firebase_messaging/firebase_messaging.dart' as firebase_messaging;

ValueNotifier<Messages> allMessages = ValueNotifier(Messages());
ValueNotifier<SettingModel> allSettings = ValueNotifier(SettingModel());
ValueNotifier<Users> currentUser = ValueNotifier(Users());
ValueNotifier<List<Blog>> blogAds = ValueNotifier([]);
ValueNotifier<List<SocialMedia>> socialMedia = ValueNotifier([]);
ValueNotifier<double> defaultFontSize = ValueNotifier(16.0);
ValueNotifier<int> defaultAdsFrequency = ValueNotifier(3);
ValueNotifier<ScrollConfig> scrollConfig = ValueNotifier(ScrollConfig());
List<Language> allLanguages = [];
List<CmsModel> allCMS = [];
ValueNotifier<Language> languageCode =
    ValueNotifier(Language(id: 1, name: 'English', language: 'en', pos: "ltr"));
String? emailData;

class UserProvider extends ControllerMVC {
  final bool _isLoggedIn = false;
  Users user = Users();
  bool get isLoggedIn => _isLoggedIn;
  GlobalKey<FormState>? loginFormKey, otpFormKey;
  GlobalKey<FormState>? updateFormKey;
  GlobalKey<FormState>? signupFormKey;
  GlobalKey<FormState>? forgetFormKey;
  GlobalKey<FormState>? resetFormKey;
  GlobalKey<FormState>? changeFormKey;

  firebase_messaging.FirebaseMessaging? _firebaseMessaging;
  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
  );

  UserProvider() {
    loginFormKey = GlobalKey<FormState>();
    updateFormKey = GlobalKey<FormState>();
    signupFormKey = GlobalKey<FormState>();
    forgetFormKey = GlobalKey<FormState>();
    resetFormKey = GlobalKey<FormState>();
    otpFormKey = GlobalKey<FormState>();
    changeFormKey = GlobalKey<FormState>();

    // Disable Firebase Messaging temporarily
    // _firebaseMessaging = firebase_messaging.FirebaseMessaging.instance;
    // _firebaseMessaging?.getToken().then((String? deviceToken) {
    //   user.deviceToken = getOnesignalUserId();
    // }).catchError((e) {
    //   debugPrint('Notification not configured');
    // });
    user.deviceToken = "test-token";
  }

  getLanguageFromServer(BuildContext context) async {
    await repository.getLocalText(context).then((value) {
      if (value != null) {
        allMessages.value = value;
      }
    });
  }

  getAllAvialbleLanguages(BuildContext context) async {
    await repository.getAllLanguages(context).then((value) {
      allLanguages = value;
    });
  }

  socialMediaList() async {
    await repository.socialMediaList().then((value) {
      if (value.isNotEmpty) {
        socialMedia.value = value;
        setState(() {});
      }
    });
  }

  Future<bool?> checkUpdate({String route = 'setting-list', String etag = 'setting-etag'}) async {
    if (prefs!.containsKey('local_data')) {
      prefs = await SharedPreferences.getInstance();
      try {
        final response = await dio.head('${Urls.baseUrl}$route');
        var eTag = response.headers.value('ETag');
        var prefTag = prefs!.containsKey(etag) ? prefs!.getString(etag) : '';

        if ((prefTag != '' || prefTag != null) && eTag != prefTag) {
          prefs!.setString(etag, eTag.toString());
          return false;
        } else if (prefTag == '' || prefTag == null) {
          return false;
        } else {
          return true;
        }
      } catch (e) {
        debugPrint(e.toString());
      }
    } else {
      return false;
    }
    return null;
  }

  Future<void> checkSettingUpdate() async {
    // Send a HEAD request to retrieve the etag header
    //  await checkUpdate().then((etag) async{
    //  try {
    //   if ( true) {
    //       getMessageAndSetting();
    //   } else {
    try {
      final conditionalResponse = await http.get(
        Uri.parse('${Urls.baseUrl}setting-list'),
        headers: {
          HttpHeaders.contentTypeHeader: 'application/json',
          "language-code": languageCode.value.language ?? '',
        },
      );
      var res = json.decode(conditionalResponse.body);
      //  print(res.toString());

      if (res['success'] == true) {
        allSettings.value = SettingModel.fromJson(res['data']);
        prefs!.setBool('maintain', res['data']['enable_maintainance_mode'] == 1 ? true : false);

        if ((allSettings.value.isAndroidForceUpdate == '1' && Platform.isAndroid == true) ||
            (allSettings.value.isIosForceUpdate == '1' && Platform.isIOS == true)) {
          prefs!.remove('update_duration');
          log('----------- Removed update duration ------------- ');
        }
        prefs!.setString('setting', json.encode(res['data']));
      } else {
        if (res['message'] == "Unauthenticated") {
          // ignore: use_build_context_synchronously
          showCustomToast(navkey.currentState!.context, "User Account not found");
          WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
            Navigator.pushAndRemoveUntil(navkey.currentState!.context,
                MaterialPageRoute(builder: (context) => const LoginPage()), (route) => false);
          });
        }
      }
    } on SocketException {
      // showCustomToast(context, allMessages.value.noInternetConnection ?? 'No Internet Connection');
    } on TimeoutException {
      //showCustomToast(context, 'Connection Timeout');
    } on Exception {
      allSettings.value.enableMaintainanceMode = '1';
      allSettings.value.maintainanceTitle = 'Server Under Maintenance';
      allSettings.value.maintainanceShortText = 'Please contact the server administrator at '
          '${allSettings.value.supportMail} to inform them of the time this error occurred.';
      // setState(() {});
    }
  }

  Future googleLogin(BuildContext context, {required ValueChanged onChanged}) async {
    onChanged(true);
    var provider = Provider.of<AppProvider>(context, listen: false);
    try {
      GoogleSignInAccount? googleSignInAccount = await _googleSignIn.signIn();
      repository.googleLogin(googleSignInAccount!, user, context).then((value) async {
        if (value != null) {
          currentUser.value = Users.fromJSON(value!['data']);
          currentUser.value.isPageHome = true;
          if (currentUser.value.isNewUser == true) {
            provider.addUserSession(isSocialSignup: true);
            showCustomToast(context, value['message']);
            Navigator.pushNamedAndRemoveUntil(context, '/SaveInterests', (route) => false, arguments: false);
          } else {
            provider.addUserSession(isSocialSignup: false);
            showCustomToast(context, value['message']);
            provider.getCategory().whenComplete(() {
              onChanged(false);
              Navigator.pushNamedAndRemoveUntil(context, '/MainPage', (route) => false, arguments: 1);
            });
          }
        }
      }).catchError((e) {
        onChanged(false);
      }).whenComplete(() {});
    } catch (e) {
      onChanged(false);
    }
  }

  Future<void> signin(BuildContext context, {required ValueChanged onChanged}) async {
    // if (user.password != "") {
    if (loginFormKey!.currentState!.validate()) {
      loginFormKey!.currentState!.save();
      repository.signin(user, context).then((value) async {
        var provider = Provider.of<AppProvider>(context, listen: false);
        // await provider.getLatestBlog();
        currentUser.value.loginFrom = 'email';
        setState(() {});
        if (value != null) {
          provider.addUserSession(isSignin: true);
          showCustomToast(context, 'You are logged in successfully');
          provider.getCategory().whenComplete(() {
            onChanged(false);
            Navigator.pushNamedAndRemoveUntil(context, '/MainPage', (route) => false, arguments: 1);
          });
        } else {
          onChanged(false);
        }
      }).catchError((e) {
        onChanged(false);
        showCustomToast(context, e.toString());
      }).whenComplete(() {});
    } else {
      onChanged(false);
    }
  }

  void appleLogin(BuildContext context,
      {List<Scope> scopes = const [Scope.email, Scope.fullName], ValueChanged? onChanged}) async {
    onChanged!(true);
    try {
      // 1. perform the sign-in request
      final result = await TheAppleSignIn.performRequests([AppleIdRequest(requestedScopes: scopes)]);
      // 2. check the result
      switch (result.status) {
        case AuthorizationStatus.authorized:
          final appleIdCredential = result.credential;

          Map<String, dynamic> resultData = {
            "name": (appleIdCredential?.fullName?.givenName ?? "") +
                (appleIdCredential?.fullName?.familyName ?? ""),
            "email": appleIdCredential?.email,
            "image": "",
            "apple_token": appleIdCredential?.user,
          };

          repository.appleLogin(resultData, context).then((value) async {
            var provider = Provider.of<AppProvider>(context, listen: false);
            if (value != null && value['success'] == true) {
              if (currentUser.value.isNewUser == true) {
                provider.addUserSession(isSocialSignup: true);
                showCustomToast(context, value['message']);
                Navigator.pushNamedAndRemoveUntil(context, '/SaveInterests', (route) => false,
                    arguments: false);
              } else {
                provider.addUserSession(isSocialSignup: false);
                showCustomToast(context, value['message']);
                provider.getCategory().whenComplete(() {
                  onChanged(false);
                  Navigator.pushNamedAndRemoveUntil(context, '/MainPage', (route) => false, arguments: 1);
                });
              }
            }
          }).catchError((e) {
            onChanged(false);
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
              backgroundColor: Theme.of(context).cardColor,
              content: Text(allMessages.value.emailNotExist.toString()),
            ));
          }).whenComplete(() {});
          break;
        case AuthorizationStatus.error:
          onChanged(false);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            backgroundColor: Theme.of(context).cardColor,
            content:
                Text(result.error.toString(), style: const TextStyle(fontFamily: 'Roboto', fontSize: 16)),
          ));
          break;

        case AuthorizationStatus.cancelled:
          onChanged(false);
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            backgroundColor: Theme.of(context).cardColor,
            content:
                const Text('Sign in aborted by user', style: TextStyle(fontFamily: 'Roboto', fontSize: 16)),
          ));
          break;
      }
    } catch (e) {
      onChanged(false);
    }
  }

  Future adBlogs() async {
    repository.adView().then((value) {
      if (value.isNotEmpty) {
        blogAds.value = value;
        setState(() {});
      }
    }).catchError((e) {});
  }

  Future<void> signup(BuildContext context, {required ValueChanged onChanged}) async {
    if (signupFormKey!.currentState!.validate()) {
      signupFormKey!.currentState!.save();
      repository.register(user, context).then((value) {
        var provider = Provider.of<AppProvider>(context, listen: false);
        if (value != null && value.apiToken != null) {
          provider.clearLists();
          provider.addUserSession(isSignup: true);

          Navigator.pushNamedAndRemoveUntil(context, '/SaveInterests', (route) => false, arguments: false);
        } else {
          onChanged(false);
        }
      }).catchError((e) {
        onChanged(false);
        showCustomToast(context, allMessages.value.emailNotExist.toString());
      }).whenComplete(() {
        onChanged(false);
      });
    } else {
      onChanged(false);
    }
  }

  void forgetPassword(BuildContext context, {required ValueChanged onChanged}) async {
    try {
      if (forgetFormKey!.currentState!.validate()) {
        forgetFormKey!.currentState!.save();
        onChanged(true);
        repository.forgetPassword(user, context).then((value) async {
          onChanged(false);
          if (value == true) {
            showCustomToast(
              context,
              "OTP sent to your email address",
            );
            Navigator.pushNamed(context, '/OTP', arguments: user.email);
          }
        }).whenComplete(() {
          onChanged(false);
        });
      }
    } on SocketException {
      onChanged(false);
    } on Exception catch (e) {
      onChanged(false);
      debugPrint(e.toString());
    }
  }

  getCMS(BuildContext context) async {
    await checkUpdate(route: 'cms-list', etag: 'cms-etag').then((value) async {
      // if (value == false) {
      await repository.getCMS().then((value) {
        allCMS = value;
        setState(() {});
      }).catchError((e) {
        debugPrint(e);
      }).whenComplete(() {});
      // }
    });
  }

  Future resetPass(BuildContext context, String email, {required ValueChanged onChanged}) async {
    if (resetFormKey!.currentState!.validate()) {
      resetFormKey!.currentState!.save();
      onChanged(true);
      repository.resetPassword(user, context, email).then((value) async {
        if (value != null && value == true) {
          onChanged(false);
          showCustomToast(
            context,
            "Your password reset successfully",
          );
          Navigator.pushNamedAndRemoveUntil(context, '/LoginPage', (route) => false);
        } else {
          onChanged(false);
        }
      }).whenComplete(() {
        onChanged(false);
      });
    }
  }

  void profile(BuildContext context, {required ValueChanged onChanged}) async {
    if (updateFormKey!.currentState!.validate()) {
      updateFormKey!.currentState!.save();
      onChanged(true);
      repository.update(user, context).then((value) {
        if (value != null && value.apiToken != null) {
          onChanged(false);
          showCustomToast(
              context, allMessages.value.profileUpdatedSuccessfully ?? 'Profile updated successfully');
          Navigator.pop(context);
        }
      }).catchError((e) {
        onChanged(false);
        showCustomToast(context, 'Something went wrong!!');
      }).whenComplete(() {
        onChanged(false);
      });
    }
  }

  void updateLanguage(BuildContext context) async {
    repository.updateLanguage().then((value) {
      if (value != null && value.apiToken != null) {
        showCustomToast(context, allMessages.value.profileUpdated.toString());
      }
    }).catchError((e) {
      debugPrint(e);
    }).whenComplete(() {});
  }

  void changePassword(BuildContext context,
      {required String conPass,
      required String newPass,
      required String oldPass,
      required ValueChanged onChanged}) async {
    if (changeFormKey!.currentState!.validate()) {
      changeFormKey!.currentState!.save();
      onChanged(true);
      repository.changePassword(context, oldPass: oldPass, newPass: newPass, conPass: conPass).then((value) {
        onChanged(false);
        if (value == true && oldPass.isNotEmpty) {
          showCustomToast(
              context, allMessages.value.changePasswordSuccess ?? 'Password changed successfully');
          Navigator.pop(context);
        } else if (oldPass.isEmpty) {
          showCustomToast(context, 'Old Password is Empty');
        } else if (newPass != conPass) {
          showCustomToast(
              context,
              allMessages.value.passwordAndConfirmPasswordShouldBeSame ??
                  'New Password and confirm passowrd should be same.');
        } else if (newPass == conPass && conPass == oldPass && newPass == oldPass) {
          showCustomToast(
              context,
              allMessages.value.newPasswordOldPasswordNotSame ??
                  'New password can\'t be same as the old password.');
        } else {
          showCustomToast(context, allMessages.value.oldPasswordError ?? 'Old Password is incorrect');
        }
      }).catchError((e) {
        onChanged(false);
        showCustomToast(context, 'Something went wrong!!');
      }).whenComplete(() {
        onChanged(false);
      });
    }
  }

  void logout(BuildContext context) async {
    var provider = Provider.of<AppProvider>(context, listen: false);
    showCustomDialog(
        context: context,
        title: allMessages.value.signOut ?? 'Sign Out',
        text: allMessages.value.doYouWantSigOut ?? 'Do you want to sign out ?',
        isTwoButton: true,
        onTap: () async {
          provider.clearLists();
          provider.logoutUserSession();
          provider.getAnalyticData(isAds: true);
          provider.getAnalyticData(isAds: false);
          prefs!.remove('current_user');
          prefs!.remove('bookmarks');
          prefs!.remove('isBookmark');
          provider.bookmarkIds.clear();
          provider.permanentIds.clear();
          provider.bookmarks.clear();
          prefs!.remove('player_id');
          _googleSignIn.signOut();
          currentUser.value = Users();
          setState(() {});
          await Future.delayed(const Duration(milliseconds: 100));
          Navigator.pushNamedAndRemoveUntil(context, '/LoginPage', (route) => false, arguments: false);
        });
  }
}

showCustomDialog(
    {required BuildContext context,
    String? title,
    String? text,
    VoidCallback? onNoTap,
    VoidCallback? onTap,
    dismissible = true,
    isTwoButton = false}) async {
  Platform.isIOS
      ? showCupertinoDialog(
          context: context,
          builder: (context) => CupertinoAlertDialog(
                title: Text(title ?? ""),
                content: Text(text.toString(),
                    textAlign: TextAlign.center,
                    style:
                        const TextStyle(fontFamily: 'Roboto', fontSize: 16.0, fontWeight: FontWeight.w500)),
                actions: [
                  if (isTwoButton == true)
                    CupertinoDialogAction(
                      onPressed: onNoTap ?? () => Navigator.of(context).pop(false),
                      child: Text(allMessages.value.no ?? 'No',
                          style: TextStyle(color: Theme.of(context).disabledColor)),
                    ),
                  CupertinoDialogAction(
                    isDestructiveAction: true,
                    onPressed: onTap,
                    textStyle: TextStyle(
                        fontFamily: 'Roboto',
                        fontSize: 16.0,
                        color: isTwoButton ? Colors.red : Theme.of(context).primaryColor,
                        fontWeight: FontWeight.w600),
                    child: Text(isTwoButton == false ? 'Ok' : allMessages.value.yes ?? 'Yes'),
                  ),
                ],
              ))
      : await showDialog(
          context: context,
          barrierDismissible: dismissible,
          builder: (context) => AlertDialog(
            backgroundColor: Theme.of(context).canvasColor,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: title != null
                ? Row(
                    children: [
                      SvgIcon(SvgImg.logout, color: Theme.of(context).disabledColor),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          title.toString(),
                          style: Theme.of(context).textTheme.bodyLarge?.merge(
                                TextStyle(
                                    color: isBlack(Theme.of(context).primaryColor) && dark(context)
                                        ? Colors.white
                                        : Theme.of(context).primaryColor,
                                    fontFamily: 'Roboto',
                                    fontSize: 20.0,
                                    fontWeight: FontWeight.w600),
                              ),
                        ),
                      ),
                    ],
                  )
                : null,
            content: Text(text.toString(),
                textAlign: TextAlign.center,
                style: const TextStyle(fontFamily: 'Roboto', fontSize: 16.0, fontWeight: FontWeight.w500)),
            actionsAlignment: MainAxisAlignment.center,
            actions: <Widget>[
              isTwoButton == false
                  ? const SizedBox()
                  : TextButton(
                      style: TextButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          padding: const EdgeInsets.symmetric(horizontal: 16)),
                      onPressed: onNoTap ??
                          () {
                            Navigator.pop(context);
                          },
                      child: Text(allMessages.value.no ?? 'No',
                          style: const TextStyle(
                              color: ColorUtil.textgrey,
                              fontFamily: 'Roboto',
                              fontSize: 14.0,
                              fontWeight: FontWeight.w500)),
                    ),
              TextButton(
                style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    backgroundColor: Theme.of(context).primaryColor),
                onPressed: onTap,
                child: Text(isTwoButton == false ? 'Ok' : allMessages.value.yes ?? 'Yes',
                    style: const TextStyle(
                        color: ColorUtil.white,
                        fontFamily: 'Roboto',
                        fontSize: 14.0,
                        fontWeight: FontWeight.w500)),
              ),
            ],
          ),
        );
}
