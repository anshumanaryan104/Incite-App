import 'package:flutter/material.dart';
import 'package:incite/api_controller/repository.dart';
import 'package:incite/pages/setting/widgets/setting_wrap.dart';
import 'package:incite/utils/app_util.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/nav_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/app_bar.dart';
import '../../api_controller/user_controller.dart';
import '../../model/home.dart';
import '../../utils/app_theme.dart';
import '../../utils/image_util.dart';
import '../cms.dart';

class SettingPage extends StatefulWidget {
  const SettingPage({super.key});

  @override
  State<SettingPage> createState() => _SettingPageState();
}

class _SettingPageState extends State<SettingPage> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) {});
    super.initState();
  }

  UserProvider userProvider = UserProvider();

  List<HomeModel> settings = [
    HomeModel(
        title: allMessages.value.notifications ?? 'Notification',
        subtitle: allMessages.value.enableDisablePushNotification ?? 'Enable Disable Push notification',
        image: SvgImg.noti,
        isToggle: true),
    HomeModel(
        title: allMessages.value.selectLanguage ?? 'Select Language',
        subtitle: allMessages.value.selectLanguageSubitle ?? 'Select your preferred language',
        image: SvgImg.lang),
    HomeModel(title: allMessages.value.autoPlay ?? 'Auto Play', image: SvgImg.play, isToggle: true),
    HomeModel(title: allMessages.value.blogFontSize ?? 'Font Size', image: SvgImg.font)
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: CustomScrollView(
        slivers: [
          CommonAppbar(title: allMessages.value.settingsPage ?? 'Settings Page', isPinned: true),
          const SliverToBoxAdapter(
            child: SizedBox(height: 30),
          ),
          SliverPadding(
            padding: const EdgeInsets.only(bottom: 0, left: 24, right: 24),
            sliver: SliverToBoxAdapter(
              child: Column(
                children: [
                  ...List.generate(
                      settings.length,
                      (index) => index == 3
                          ? Builder(builder: (context) {
                              return FontWrap(
                                pos: 2,
                                model: settings[index],
                              );
                            })
                          : ValueListenableBuilder<AppModel>(
                              valueListenable: appThemeModel,
                              builder: (context, appSetting, child) {
                                return ListSettingWrap(
                                    pos: index + 4,
                                    key: ValueKey(index),
                                    model: settings[index],
                                    isOn: index == 0
                                        ? appSetting.isNotificationEnabled.value
                                        : index == 2
                                            ? appSetting.isAutoPlay.value
                                            : false,
                                    onTap: () {
                                      switch (index) {
                                        case 0:
                                          {
                                            // if(currentUser.value.id == null ){
                                            //  }

                                            if (appThemeModel.value.isNotificationEnabled.value == true) {
                                              // OneSignal.User.pushSubscription.optOut();
                                              appThemeModel.value.isNotificationEnabled.value = false;
                                            } else {
                                              // OneSignal.User.pushSubscription.optIn();
                                              appThemeModel.value.isNotificationEnabled.value = true;
                                            }
                                            toggleNotify(appThemeModel.value.isNotificationEnabled.value);
                                            updateToken();
                                            setState(() {});
                                          }
                                          break;

                                        case 1:
                                          Navigator.pushNamed(context, '/LanguageSelection', arguments: true);
                                          break;

                                        case 2:
                                          {
                                            // if(currentUser.value.id == null ){
                                            //  }

                                            if (appSetting.isAutoPlay.value == true) {
                                              // OneSignal.User.pushSubscription.optOut();
                                              appSetting.isAutoPlay.value = false;
                                            } else {
                                              // OneSignal.User.pushSubscription.optIn();
                                              appSetting.isAutoPlay.value = true;
                                            }
                                            toggleAutoPlay(appSetting.isAutoPlay.value);
                                            setState(() {});
                                          }
                                          break;

                                        default:
                                      }
                                    });
                              })),
                  if (currentUser.value.id != null)
                    InkWell(
                      onTap: () {
                        userProvider.logout(context);
                        setState(() {});
                      },
                      radius: 12,
                      child: Container(
                          decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(12),
                              color: dark(context) ? Theme.of(context).cardColor : ColorUtil.whiteGrey),
                          alignment: Alignment.center,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12.5),
                          child: Text(allMessages.value.logout ?? "Sign Out",
                              style: const TextStyle(
                                  fontFamily: 'Roboto',
                                  fontSize: 16,
                                  color: Colors.red,
                                  fontWeight: FontWeight.w500))),
                    )
                ],
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Column(
              children: [
                const SizedBox(height: 16),
                Divider(
                  endIndent: 16,
                  indent: 16,
                  color: Theme.of(context).dividerColor.customOpacity(0.3),
                ),
                ...List.generate(
                    allCMS.length,
                    (index) => RowDot(
                          pos: index + 1,
                          title: allCMS[index].title,
                          onTap: () {
                            Navigator.push(
                                context,
                                PagingTransform(
                                    widget: CmsPage(
                                  cms: allCMS[index],
                                )));
                          },
                        )),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
