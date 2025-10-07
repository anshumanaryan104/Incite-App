import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/repository.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/model/blog.dart';
// Signup removed for MVP - admin login only
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/back.dart';
import 'package:incite/widgets/button.dart';
import 'package:incite/widgets/gradient.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/text_field.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../splash_screen.dart';
import '../../utils/image_util.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key, this.isFromInitial = false, this.isFromHome = true, this.blog});

  final bool isFromHome, isFromInitial;
  final Blog? blog;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  bool isObscure = true;
  UserProvider userProvider = UserProvider();

  bool isLoad = false;
  UserProvider user = UserProvider();

  @override
  void initState() {
    prefs!.setString('is_first_time_open', 'yes');
    prefs!.remove('id');
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      if (widget.isFromHome == false) {
        if (!prefs!.containsKey('player_id')) {
          await updateToken();
        }
        var provide = Provider.of<AppProvider>(context, listen: false);
        prefs!.remove('isBookmark');
        provide.getCategory();

        provide.setAnalyticData();
        getAllLanguages(context);
      }
    });
    super.initState();
  }

  @override
  void didChangeDependencies() {
    if (prefs!.containsKey('data')) {
      isLoad = true;
      setState(() {});
      prefs!.remove('data');
    }
    super.didChangeDependencies();
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    var size = MediaQuery.of(context).size;
    return ValueListenableBuilder(
      valueListenable: allSettings,
      builder: (context, value, child) {
        return CustomLoader(
          isLoading: isLoad,
          child: Scaffold(
            appBar: AppBar(
              backgroundColor: Colors.transparent,
              elevation: 0,
              //  leadingWidth: 80,
              toolbarHeight: 12,
              automaticallyImplyLeading: false,
              systemOverlayStyle: SystemUiOverlayStyle(
                statusBarIconBrightness: dark(context) ? Brightness.light : Brightness.dark,
                statusBarColor: Colors.transparent,
              ),
              actions: const [SizedBox(width: 12)],
            ),
            resizeToAvoidBottomInset: false,
            body: value.enableMaintainanceMode == '1'
                ? PopScope(
                    canPop: false,
                    onPopInvokedWithResult: (val, result) async {
                      if (val) {
                        return;
                      }
                    },
                    child: SizedBox(
                      width: size.width,
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 24),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            // Image.asset(Img.logo,width: 100, height: 100),
                            Stack(
                              children: [
                                Image.asset('assets/images/maintain.png', width: 200, height: 200),
                                Positioned(
                                  top: kToolbarHeight,
                                  right: 50,
                                  child: Image.asset(Img.logo, width: 30, height: 30),
                                ),
                              ],
                            ),

                            Text(
                              value.maintainanceTitle ?? 'Server Under Maintenance',
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                fontFamily: 'Roboto',
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              value.maintainanceShortText.toString(),
                              textAlign: TextAlign.center,
                              style: const TextStyle(fontFamily: 'Roboto', fontSize: 14, height: 1.4),
                            ),

                            const SizedBox(height: 12),
                            GestureDetector(
                              onTap: () async {
                                await launchUrl(Uri.parse("mailto:${allSettings.value.supportMail}"));
                              },
                              child: ElevateButton(
                                onTap: () {},
                                text: "${allSettings.value.supportMail}",
                                style: TextStyle(
                                  fontFamily: 'Roboto',
                                  fontSize: 16,
                                  color: Theme.of(context).primaryColor,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  )
                : SafeArea(
                    child: Container(
                      width: size.width,
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Form(
                        key: userProvider.loginFormKey,
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                widget.isFromHome ? const Backbut() : const SizedBox(),
                                InkWell(
                                  onTap: () async {
                                    // Load categories and navigate to BlogWrap
                                    var provider = Provider.of<AppProvider>(context, listen: false);
                                    await provider.getCategory();

                                    if (context.mounted) {
                                      Navigator.pushNamedAndRemoveUntil(
                                        context,
                                        '/BlogWrap',
                                        (route) => false,
                                        arguments: [0, false, null],
                                      );
                                    }
                                  },
                                  child: Padding(
                                    padding: const EdgeInsets.all(8.0),
                                    child: Text(
                                      allMessages.value.skip ?? 'Skip',
                                      style: TextStyle(
                                        fontFamily: 'Roboto',
                                        fontSize: 16,
                                        fontWeight: FontWeight.w400,
                                        color: dark(context) ? ColorUtil.white : ColorUtil.textgrey,
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const Spacer(),
                            // Polymath text logo with gradient
                            ShaderMask(
                              shaderCallback: (bounds) => LinearGradient(
                                colors: [
                                  Color(0xFFFF6B6B), // Coral
                                  Color(0xFFB8A4D4), // Purple
                                ],
                                begin: Alignment.centerLeft,
                                end: Alignment.centerRight,
                              ).createShader(bounds),
                              child: Text(
                                'Polymath',
                                style: TextStyle(
                                  fontSize: 48,
                                  fontFamily: 'Roboto',
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                  letterSpacing: -1.5,
                                  decoration: TextDecoration.none,
                                ),
                              ),
                            ),
                            const SizedBox(height: 20),
                            Text(
                              allMessages.value.signIn ?? 'Sign in',
                              style: const TextStyle(
                                fontSize: 30,
                                fontFamily: 'Roboto',
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 40),

                            TextFieldWidget(
                              hint: 'Username',
                              pos: 2,
                              onValidate: (v) {
                                if (v!.isEmpty) {
                                  return "Username is required";
                                } else if (v.length < 3) {
                                  return "Username must be at least 3 characters";
                                }
                                return null;
                              },
                              onSaved: (v) {
                                setState(() {
                                  userProvider.user.email = v;
                                });
                              },
                              prefix: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 17),
                                child: Icon(Icons.person, size: 20),
                              ),
                              textAction: TextInputAction.next,
                            ),
                            const SizedBox(height: 20),
                            TextFieldWidget(
                              hint: allMessages.value.password ?? 'Password',
                              pos: 3,
                              textAction: TextInputAction.go,
                              onValidate: (v) {
                                if (v!.isEmpty) {
                                  return '${allMessages.value.password} ${allMessages.value.isRequired}';
                                } else if (v.length < 6) {
                                  return 'Password must be at least 6 characters';
                                }
                                return null;
                              },
                              onFieldSubmitted: (p0) {
                                isLoad = true;
                                setState(() {});
                                userProvider.signin(
                                  context,
                                  onChanged: (value) {
                                    isLoad = false;
                                    setState(() {});
                                  },
                                );
                              },
                              onSaved: (v) {
                                setState(() {
                                  userProvider.user.password = v;
                                });
                              },
                              suffix: InkResponse(
                                onTap: () {
                                  isObscure = !isObscure;
                                  setState(() {});
                                },
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 0, horizontal: 17),
                                  child: Icon(
                                    isObscure == false ? Icons.visibility_off : Icons.visibility,
                                    color: dark(context) ? ColorUtil.white : ColorUtil.textgrey2,
                                  ),
                                ),
                              ),
                              isObscure: isObscure,
                              prefix: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 17),
                                child: SvgPicture.asset('assets/svg/password.svg', width: 22, height: 12),
                              ),
                            ),
                            const SizedBox(height: 22),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.end,
                              children: [
                                InkWell(
                                  onTap: () {
                                    Navigator.pushNamed(context, '/ForgotPage');
                                  },
                                  child: Text(
                                    allMessages.value.forgotPassword ?? 'Forgot Password?',
                                    style: const TextStyle(fontSize: 14),
                                  ),
                                ),
                              ],
                            ),
                            const Spacer(flex: 2),
                            ElevateButton(
                              style: const TextStyle(
                                fontFamily: 'Poppins',
                                fontSize: 18,
                                color: Colors.white,
                                fontWeight: FontWeight.w500,
                              ),
                              onTap: () {
                                isLoad = true;
                                setState(() {});
                                userProvider.signin(
                                  context,
                                  onChanged: (value) {
                                    isLoad = false;
                                    setState(() {});
                                  },
                                );
                              },
                              text: allMessages.value.signIn ?? 'Sign in',
                            ),
                            // Admin login only - no social sign-in or signup
                            const SizedBox(height: 16),
                          ],
                        ),
                      ),
                    ),
                  ),
          ),
        );
      },
    );
  }
}

// BottomText2 widget removed - no signup for MVP
