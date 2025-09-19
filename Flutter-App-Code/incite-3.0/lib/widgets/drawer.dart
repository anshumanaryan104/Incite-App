import 'dart:io';
import 'dart:math';
import 'dart:ui';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:flutter_tabler_icons/flutter_tabler_icons.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:incite/widgets/dark_mode.dart';
import 'package:incite/widgets/tap.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter_custom_tabs/flutter_custom_tabs.dart' as custom;

import '../api_controller/blog_controller.dart';
import '../model/blog.dart';
import '../model/home.dart';
import '../model/user.dart';
import '../utils/image_util.dart';
import '../utils/theme_util.dart';

enum Availability { loading, available, unavailable }

class DrawerPage extends StatefulWidget {
  const DrawerPage({super.key});

  @override
  State<DrawerPage> createState() => _DrawerPageState();
}

class _DrawerPageState extends State<DrawerPage> {
  bool isDark = false;
  var userProvider = UserProvider();

  List<HomeModel> drawer = [
//  HomeModel(
//   title: allMessages.value.dashboard ?? 'Dashboard',
//    image: SvgImg.dash
//  ),
    HomeModel(title: allMessages.value.myProfile ?? 'My Profile', image: SvgImg.profile),
    HomeModel(title: allMessages.value.myStories ?? 'My Stories', image: SvgImg.fillBook),
    HomeModel(title: allMessages.value.myFeed ?? 'My Feed', image: SvgImg.dash2),
    HomeModel(title: allMessages.value.settings ?? 'Settings', image: SvgImg.setting),
    HomeModel(title: allMessages.value.rateUs ?? 'Rate Us', image: SvgImg.star),
    HomeModel(title: allMessages.value.logout ?? 'Sign Out', image: SvgImg.logout),
    HomeModel(title: allMessages.value.login ?? 'Sign in', image: SvgImg.lock),
    HomeModel(title: allMessages.value.darkMode ?? 'Dark Mode', image: SvgImg.themeMode),
  ];

  @override
  Widget build(BuildContext context) {
    var provider = Provider.of<AppProvider>(context, listen: false);
    return Drawer(
        width: size(context).width * 0.75,
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.only(
          topRight: languageCode.value.pos == 'rtl' ? Radius.zero : const Radius.circular(20),
          bottomRight: languageCode.value.pos == 'rtl' ? Radius.zero : const Radius.circular(20),
          topLeft: languageCode.value.pos == 'rtl' ? const Radius.circular(20) : Radius.zero,
          bottomLeft: languageCode.value.pos == 'rtl' ? const Radius.circular(20) : Radius.zero,
        )),
        child: Container(
          decoration: const BoxDecoration(
            boxShadow: [
              BoxShadow(offset: Offset(20, 24), blurRadius: 50, color: Color.fromRGBO(6, 0, 45, 0.25))
            ],
          ),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 5, sigmaY: 5), // Adjust blur radius as needed
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.only(
                  topRight: languageCode.value.pos == 'rtl' ? Radius.zero : const Radius.circular(20),
                  bottomRight: languageCode.value.pos == 'rtl' ? Radius.zero : const Radius.circular(20),
                  topLeft: languageCode.value.pos == 'rtl' ? const Radius.circular(20) : Radius.zero,
                  bottomLeft: languageCode.value.pos == 'rtl' ? const Radius.circular(20) : Radius.zero,
                ),
                color: dark(context)
                    ? Theme.of(context).scaffoldBackgroundColor
                    : Colors.white, // Set desired drawer color and opacity
              ),
              padding: const EdgeInsets.only(left: 24, top: 16, bottom: 16, right: 20),
              child: SingleChildScrollView(
                child: Column(
                  children: [
                    const SizedBox(height: 40),
                    Row(
                      children: [
                        Padding(
                          padding: EdgeInsets.only(right: languageCode.value.pos == 'rtl' ? 10.0 : 0),
                          child: const Hero(
                            tag: 'Drawer',
                            child: ProfileWidget(
                              radius: 30,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            AnimationFadeSlide(
                              dx: 0.35,
                              duration: 300,
                              child: Container(
                                width: size(context).width / 2.35,
                                padding: EdgeInsets.only(left: languageCode.value.pos == 'rtl' ? 17.0 : 0),
                                child: Text(
                                    currentUser.value.id == null
                                        ? allMessages.value.signIn ?? ""
                                        : currentUser.value.name ?? 'Rahul',
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(
                                        fontFamily: 'Roboto',
                                        fontSize: 20,
                                        fontWeight: FontWeight.w600,
                                        color: dark(context) ? ColorUtil.white : ColorUtil.textblack)),
                              ),
                            ),
                            currentUser.value.id == null ? const SizedBox() : const SizedBox(height: 4),
                            currentUser.value.id == null
                                ? const SizedBox()
                                : Container(
                                    width: size(context).width / 2.35,
                                    padding: EdgeInsets.only(
                                      left: languageCode.value.pos == 'rtl' ? 12.0 : 0,
                                      right: languageCode.value.pos == 'ltr' ? 12.0 : 0,
                                    ),
                                    child: AnimationFadeSlide(
                                      dx: 0,
                                      isFade: true,
                                      duration: 500,
                                      child: Text(currentUser.value.email ?? "Guest",
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                              fontFamily: 'Roboto',
                                              fontSize: 13,
                                              color:
                                                  dark(context) ? ColorUtil.lightGrey : ColorUtil.textgrey)),
                                    ),
                                  ),
                          ],
                        )
                      ],
                    ),
                    SizedBox(
                      height: 40,
                      child: Divider(
                          height: 0,
                          thickness: 0.5,
                          color: dark(context) ? Theme.of(context).dividerColor : ColorUtil.textgrey),
                    ),
                    ...drawer
                        .asMap()
                        .entries
                        .map((e) => (e.key == 2 || e.key == 0 || e.key == 1) && currentUser.value.id == null
                            ? const SizedBox()
                            : e.key == 5 && currentUser.value.id == null
                                ? const SizedBox()
                                : e.key == 6 && currentUser.value.id != null
                                    ? const SizedBox()
                                    : DrawWrap(
                                        pos: e.key,
                                        title: e.value.title,
                                        image: e.value.image,
                                        onTap: () {
                                          switch (e.key) {
                                            case 7:
                                              showDialog(
                                                  context: context,
                                                  barrierColor: dark(context)
                                                      ? Colors.black.customOpacity(0.9)
                                                      : Colors.black.customOpacity(0.75),
                                                  builder: (builder) {
                                                    return const Material(
                                                        color: Colors.transparent, child: DarkModeToggle());
                                                  });
                                              break;

                                            case 0:
                                              Navigator.pushNamed(context, '/UserProfile', arguments: true);
                                              break;
                                            case 2:
                                              Navigator.pushNamed(context, '/SaveInterests', arguments: true)
                                                  .then((value) {
                                                provider.selectedInterests(provider.selectedFeed);
                                              });
                                              break;
                                            case 1:
                                              blogListHolder2.clearList();
                                              blogListHolder2.setList(DataModel(blogs: provider.bookmarks));
                                              blogListHolder2.setBlogType(BlogType.bookmarks);
                                              setState(() {});
                                              Navigator.pushNamed(context, '/SavedPage').then((value) {});
                                              break;
                                            case 3:
                                              Navigator.pushNamed(context, '/SettingPage');
                                              break;
                                            case 4:
                                              redirectToPlayStore();
                                              //_inAppReview.requestReview();
                                              break;
                                            case 6:
                                              Navigator.pushNamed(context, '/LoginPage');
                                              break;
                                            case 5:
                                              userProvider.logout(context);
                                              setState(() {});
                                              break;
                                            default:
                                          }
                                        })),
                    const SizedBox(height: 20),
                    ValueListenableBuilder<List<SocialMedia>>(
                        valueListenable: socialMedia,
                        builder: (context, value, child) {
                          return Wrap(
                            spacing: 12,
                            runSpacing: 16,
                            children: [
                              ...List.generate(
                                  value.length,
                                  (index) => InkResponse(
                                        onTap: () {
                                          openWhatsApp(value[index].url!.split('=').last);
                                        },
                                        child: SizedBox(
                                          width: 30,
                                          height: 30,
                                          child: Icon(getTabIcons(value[index].name.toString()), size: 28),
                                        ),
                                      ))
                            ],
                          );
                        }),
                    const SizedBox(height: 20)
                  ],
                ),
              ),
            ),
          ),
        ));
  }

  IconData? getTabIcons(String icon) {
    IconData? iconData;

    switch (icon.toLowerCase()) {
      case "facebook":
        iconData = TablerIcons.brand_facebook;
        return iconData;
      case "fb":
        iconData = TablerIcons.brand_facebook;
        return iconData;
      case "instagram":
        iconData = TablerIcons.brand_instagram;
        return iconData;
      case "youtube":
        iconData = TablerIcons.brand_youtube;
        return iconData;
      case "pintrest":
        iconData = TablerIcons.brand_pinterest;
        return iconData;
      case "pinterest":
        iconData = TablerIcons.brand_pinterest;
        return iconData;
      case "linkedin":
        iconData = TablerIcons.brand_linkedin;
        return iconData;
      case "snapchat":
        iconData = TablerIcons.brand_snapchat;
        return iconData;
      case "twitter":
        iconData = TablerIcons.brand_twitter;
        return iconData;
      case "skype":
        iconData = TablerIcons.brand_skype;
        return iconData;
      case "whatsapp":
        iconData = TablerIcons.brand_whatsapp;
        return iconData;
      case "telegram":
        iconData = TablerIcons.brand_telegram;
        return iconData;
      case "reddit":
        iconData = TablerIcons.brand_reddit;
        return iconData;
      case "tiktok":
        iconData = TablerIcons.brand_tiktok;
        return iconData;
      case "github":
        iconData = TablerIcons.brand_github;
        return iconData;
      case "discord":
        iconData = TablerIcons.brand_discord;
        return iconData;

      default:
        // Handle unknown cases or provide a default icon
        break;
    }
    return null;
  }

  IconData? getIcons(String icon) {
    switch (icon) {
      case "&#xec74;":
        return TablerIcons.brand_whatsapp;
      case "&#xec20;":
        return TablerIcons.brand_instagram;
      case "&#xec1a;":
        return TablerIcons.brand_facebook;
      case "&#xec8c;":
        return TablerIcons.brand_linkedin;
      case "&#xec26;":
        return TablerIcons.brand_telegram;
      case "&#xeae5;":
        return TablerIcons.mail;
      case "&#xec25;":
        return TablerIcons.brand_snapchat;
      case "&#xec1c;":
        return TablerIcons.brand_github;
      case "&#xf7e7;":
        return TablerIcons.brand_github_filled;
      case "brand-youtube":
        return TablerIcons.brand_youtube;
      default:
    }
    return null;
  }

  void openWhatsApp(String text) async {
    final url = text;
    try {
      await custom.launchUrl(Uri.parse(text));
    } catch (e) {
      if (await canLaunchUrl(Uri.parse(url))) {
        await launchUrl(Uri.parse(url));
      } else {
        throw 'Could not launch $url';
      }
    }
  }

  void redirectToPlayStore() async {
    // Create the Play Store deep link URL
    final String url = Platform.isAndroid
        ? allSettings.value.playStoreUrl.toString()
        : allSettings.value.appStoreUrl.toString();

    if (await canLaunchUrl(Uri.parse(url))) {
      await launchUrl(Uri.parse(url));
    } else {
      CustomToast(message: 'Could not redirect to $url', onDismiss: () {});
      throw 'Could not launch $url';
    }
  }
}

Color generateRandomColor() {
  Random random = Random();
  int r = random.nextInt(256);
  int g = random.nextInt(256);
  int b = random.nextInt(256);
  return Color.fromRGBO(r, g, b, 1);
}

class ProfileWidget extends StatelessWidget {
  const ProfileWidget({
    super.key,
    this.onTap,
    this.radius,
    this.size,
  });
  final VoidCallback? onTap;
  final double? radius, size;

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder(
        valueListenable: currentUser,
        builder: (context, val, child) {
          return TapInk(
            onTap: val.id == null
                ? onTap ??
                    () {
                      Navigator.pushNamed(context, '/LoginPage');
                    }
                : onTap ??
                    () {
                      Navigator.pushNamed(context, '/UserProfile');
                    },
            child: val.id != null && val.photo != ''
                ? CircleAvatar(
                    radius: radius ?? 35,
                    backgroundImage: CachedNetworkImageProvider(val.photo),
                  )
                : val.id != null
                    ? CircleAvatar(
                        radius: radius ?? 35,
                        backgroundColor: Theme.of(context).primaryColor.customOpacity(0.3),
                        child: Text(val.name != '' ? val.name!.split(' ').first[0] : 'G',
                            style: TextStyle(
                                fontFamily: 'Roboto',
                                fontSize: size ?? 28,
                                color: isBlack(Theme.of(context).primaryColor)
                                    ? Colors.white
                                    : Theme.of(context).primaryColor,
                                fontWeight: FontWeight.w600)),
                      )
                    : FittedBox(
                        fit: BoxFit.cover,
                        child: CircleAvatar(
                          radius: radius ?? 35,
                          backgroundColor: const Color.fromRGBO(158, 158, 158, 1),
                          child: SvgPicture.asset(
                            SvgImg.profileCircle,
                            fit: BoxFit.cover,
                            width: double.infinity,
                            height: double.infinity,
                          ),
                        )),
          );
        });
  }
}

class ToggleButton extends StatefulWidget {
  const ToggleButton({
    super.key,
    required this.isON,
    this.isNativeToggle = true,
    this.onNormalToggleTap,
    this.isNotification = false,
  });

  final bool isON, isNotification, isNativeToggle;
  final VoidCallback? onNormalToggleTap;

  @override
  State<ToggleButton> createState() => _ToggleButtonState();
}

class _ToggleButtonState extends State<ToggleButton> {
  @override
  Widget build(BuildContext context) {
    if (widget.isNativeToggle == true) {
      return IgnorePointer(
        child: Switch.adaptive(value: widget.isON, onChanged: (val) {}),
      );
    }
    return GestureDetector(
      onTap: widget.onNormalToggleTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        curve: Curves.easeIn,
        width: 44,
        height: 24,
        alignment: widget.isON == false ? Alignment.centerLeft : Alignment.centerRight,
        padding: const EdgeInsets.symmetric(vertical: 2, horizontal: 1),
        decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            color: widget.isON == true
                ? ColorUtil.autoPlayRed
                : widget.isON
                    ? dark(context)
                        ? Colors.black
                        : Colors.white
                    : dark(context)
                        ? Colors.black.customOpacity(0.4)
                        : ColorUtil.lightGrey),
        child: Container(
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.white,
            ),
            padding: const EdgeInsets.only(top: 2),
            child: SvgPicture.asset(SvgImg.play)),
      ),
    );
  }
}

class DrawWrap extends StatelessWidget {
  const DrawWrap({
    super.key,
    this.title,
    this.isSignOut = false,
    required this.onTap,
    this.image,
    this.pos = 1,
    this.suffix,
  });

  final String? title, image;
  final VoidCallback onTap;
  final bool isSignOut;
  final int pos;
  final Widget? suffix;

  @override
  Widget build(BuildContext context) {
    return AnimationFadeSlide(
      dx: 0,
      isFade: true,
      duration: 200 * pos,
      child: Container(
        margin: const EdgeInsets.only(bottom: 15),
        child: Stack(
          children: [
            Container(
              decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(isSignOut ? 12 : 50),
                  color: dark(context) ? Theme.of(context).cardColor : ColorUtil.whiteGrey),
              alignment: isSignOut ? Alignment.center : null,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12.5),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  image == ''
                      ? const SizedBox()
                      : SvgPicture.asset(image ?? SvgImg.dash,
                          width: 24,
                          height: 24,
                          colorFilter: ColorFilter.mode(
                              dark(context) ? ColorUtil.white : ColorUtil.textblack, BlendMode.srcIn)),
                  image == '' ? const SizedBox() : const SizedBox(width: 15),
                  Text(title ?? 'data',
                      style: TextStyle(
                          fontFamily: 'Roboto',
                          fontSize: 16,
                          color: isSignOut ? Colors.red : null,
                          fontWeight: FontWeight.w500)),
                  image == '' ? const Spacer() : const SizedBox(),
                  suffix ?? const SizedBox(),
                ],
              ),
            ),
            Positioned.fill(
                child: TapInk(
                    onTap: onTap,
                    splash: Theme.of(context).primaryColor,
                    radius: 50,
                    child: const SizedBox()))
          ],
        ),
      ),
    );
  }
}
