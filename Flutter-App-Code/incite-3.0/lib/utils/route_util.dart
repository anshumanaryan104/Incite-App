import 'dart:developer';

import 'package:flutter/cupertino.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/pages/auth/forgot_password.dart';
import 'package:incite/pages/auth/otp.dart';
import 'package:incite/pages/auth/reset_password.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/e_news.dart';
import 'package:incite/pages/interests/save_insterests.dart';
import 'package:incite/pages/main/blog_wrap.dart';
import 'package:incite/pages/main/web_view.dart';
import 'package:incite/pages/main/widgets/quotes.dart';
import 'package:incite/utils/loader_util.dart';
import 'package:incite/utils/nav_util.dart';
import '../pages/auth/login.dart';
import '../pages/bookmark/bookmark.dart';
import '../pages/language/language_select.dart';
import '../pages/main/blog.dart';
import '../pages/main/dashboard.dart';
import '../pages/main/live_news.dart';
import '../pages/main/widgets/blog_ad.dart';
import '../pages/profile/user_profile.dart';
import '../pages/search/search_page.dart';
import '../pages/setting/settings.dart';
import '../splash_screen.dart';

class RouteGenerator {
  static Route<dynamic>? generateRoute(RouteSettings settings) {
    dynamic args = settings.arguments;
    switch (settings.name) {
      case '/':
        return PagingTransform(widget: const SplashScreen());

      case '/SignUpPage':
        return PagingTransform(widget: const SignUpPage());

      case '/LanguageSelection':
        return PagingTransform(
          widget: LanguagePage(isSetting: args ?? false),
        );

      case '/MainPage':
        return PagingTransform(widget: DashboardPage(index: args ?? 0));

      case '/SaveInterests':
        return PagingTransform(widget: SaveInterest(isDrawer: args));

      case '/ReadBlog':
        return PagingTransform(
            widget: BlogPage(
          model: args[0],
          currIndex: 0,
          onTap: args[1],
          isSingle: args.length > 2 ? args[2] : false,
        ));
      // case '/BlogWrap':
      // return PagingTransform(widget: BlogWrapPage());
      case '/LoginPage':
        return PagingTransform(widget: LoginPage(isFromHome: args ?? true));

      case '/UserProfile':
        return PagingTransform(
            widget: UserProfile(
          isDash: args ?? false,
        ));

      case '/weburl':
        return PagingTransform(widget: CustomWebView(url: args));

      case '/OTP':
        return PagingTransform(widget: OtpScreen(mail: args));

      case '/SearchPage':
        return PagingTransform(slideUp: true, dy: 0.1, dx: 0, widget: const SearchPage());

      case '/SettingPage':
        return PagingTransform(widget: const SettingPage());

      case '/SavedPage':
        return PagingTransform(widget: const BookmarkPage());

      case '/ResetPage':
        return PagingTransform(widget: ResetPassword(isChange: args[0], mail: args[1]));

      case '/BlogPage':
        return PagingTransform(widget: BlogPage(model: args[0] as Blog, currIndex: 0, initial: args[1]));

      case '/QuotePage':
        return PagingTransform(widget: QuotePage(model: args[0] as Blog, initial: args[1], type: args[2]));

      case '/BlogWrap':
        return PagingTransform(
            widget: BlogWrapPage(
                key: ValueKey(args.length > 3 ? args[3] : 0),
                index: args[0],
                onChanged: (value) {},
                isBookmark: args[1],
                type: args.length > 4 ? args[4] : null,
                preloadPageController: args[2],
                isBack: args.length == 6 ? args[5] : false));

      case '/ForgotPage':
        return PagingTransform(widget: const ForgotPassword());

      case '/LiveNews':
        return PagingTransform(widget: const LiveNews());

      case '/ENews':
        return PagingTransform(widget: const EnewsPage());

      case '/Ads':
        return PagingTransform(
            widget: BlogAd(
          onTap: () {},
        ));

      default:
        {
          if (settings.name != null &&
              (settings.name!.contains('/blog/') || settings.name!.contains('/quote/')) &&
              !settings.name!.contains('null')) {
            var id = int.parse(settings.name!.split('/').last);

            return PagingTransform(widget: Loader(blog: Blog(id: int.parse(id.toString()))));
          } else if (settings.name != null && (settings.name!.contains('/e-news/'))) {
            var id = int.parse(settings.name!.split('/').last);

            return PagingTransform(widget: EnewsPage(id: id));
          } else if (settings.name != null && (settings.name!.contains('/live-news/'))) {
            var id = int.parse(settings.name!.split('/').last);
            log(settings.name!.toString());
            return PagingTransform(widget: LiveNews(id: id));
          }
          if (settings.name != null && (settings.name!.contains('/shorts/'))) {
            var id = int.parse(settings.name!.split('/').last);

            return PagingTransform(widget: Loader(blog: Blog(id: int.parse(id.toString())), type: 'shorts'));
          } else {
            if (!prefs!.containsKey('setNotification')) {
              return PagingTransform(widget: const SplashScreen());
            }
          }
        }
    }
    return null;
  }
}
