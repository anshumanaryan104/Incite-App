import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/back.dart';
import 'package:incite/widgets/button.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/text_field.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:http/http.dart' as http;

import '../../splash_screen.dart';
import '../../urls/url.dart';
// Signup removed for MVP
import 'package:incite/widgets/app_icons.dart';
import 'admin_dashboard.dart';

class AdminLoginPage extends StatefulWidget {
  const AdminLoginPage({super.key});

  @override
  State<AdminLoginPage> createState() => _AdminLoginPageState();
}

class _AdminLoginPageState extends State<AdminLoginPage> {
  bool isObscure = true;
  bool isLoad = false;

  final _formKey = GlobalKey<FormState>();
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> adminLogin() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      isLoad = true;
    });

    try {
      final String url = '${Urls.baseUrl}admin/login';
      final response = await http.post(
        Uri.parse(url),
        headers: {
          HttpHeaders.contentTypeHeader: 'application/json',
        },
        body: jsonEncode({
          'username': _usernameController.text.trim(),
          'password': _passwordController.text,
        }),
      );

      final data = json.decode(response.body);

      if (data['success'] == true) {
        await prefs!.setString('admin_token', data['data']['token']);
        await prefs!.setString('admin_username', data['data']['admin']['username']);
        await prefs!.setString('admin_role', data['data']['admin']['role']);

        if (mounted) {
          showCustomToast(context, 'Login successful');
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const AdminDashboard()),
          );
        }
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Login failed');
        }
      }
    } on SocketException {
      if (mounted) {
        showCustomToast(context, 'No Internet Connection');
      }
    } catch (e) {
      if (mounted) {
        showCustomToast(context, 'Something went wrong. Try again!');
      }
    } finally {
      if (mounted) {
        setState(() {
          isLoad = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    var size = MediaQuery.of(context).size;
    return CustomLoader(
      isLoading: isLoad,
      child: Scaffold(
        appBar: AppBar(
          backgroundColor: Colors.transparent,
          elevation: 0,
          toolbarHeight: 12,
          automaticallyImplyLeading: false,
          systemOverlayStyle: SystemUiOverlayStyle(
            statusBarIconBrightness: dark(context) ? Brightness.light : Brightness.dark,
            statusBarColor: Colors.transparent,
          ),
          actions: const [SizedBox(width: 12)],
        ),
        resizeToAvoidBottomInset: false,
        body: SafeArea(
          child: Container(
            width: size.width,
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Backbut(),
                      const SizedBox(),
                    ],
                  ),
                  const Spacer(),
                  RectangleAppIcon(width: 130),
                  const SizedBox(height: 20),
                  Text(
                    'Admin Sign In',
                    style: const TextStyle(
                      fontSize: 30,
                      fontFamily: 'Roboto',
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 40),
                  TextFieldWidget(
                    hint: 'Username',
                    pos: 1,
                    controller: _usernameController,
                    onValidate: (v) {
                      if (v!.isEmpty) {
                        return 'Username is required';
                      } else if (v.length < 3) {
                        return 'Username must be at least 3 characters';
                      }
                      return null;
                    },
                    prefix: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 17),
                      child: SvgPicture.asset('assets/svg/name.svg', width: 20, height: 16),
                    ),
                    textAction: TextInputAction.next,
                  ),
                  const SizedBox(height: 20),
                  TextFieldWidget(
                    hint: 'Password',
                    pos: 2,
                    controller: _passwordController,
                    textAction: TextInputAction.go,
                    onValidate: (v) {
                      if (v!.isEmpty) {
                        return 'Password is required';
                      } else if (v.length < 8) {
                        return 'Password must be at least 8 characters';
                      }
                      return null;
                    },
                    onFieldSubmitted: (p0) {
                      adminLogin();
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
                  const Spacer(flex: 4),
                  ElevateButton(
                    style: const TextStyle(
                      fontFamily: 'Poppins',
                      fontSize: 18,
                      color: Colors.white,
                      fontWeight: FontWeight.w500,
                    ),
                    onTap: adminLogin,
                    text: 'Sign in',
                  ),
                  const Spacer(flex: 3),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}