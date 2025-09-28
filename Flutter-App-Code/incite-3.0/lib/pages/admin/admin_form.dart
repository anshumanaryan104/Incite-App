import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/button.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/text_field.dart';
import 'package:http/http.dart' as http;

import '../../splash_screen.dart';
import '../../urls/url.dart';

class AdminFormPage extends StatefulWidget {
  const AdminFormPage({super.key});

  @override
  State<AdminFormPage> createState() => _AdminFormPageState();
}

class _AdminFormPageState extends State<AdminFormPage> {
  final _formKey = GlobalKey<FormState>();
  bool isLoading = false;

  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  String selectedRole = 'admin';
  String? adminToken;

  @override
  void initState() {
    super.initState();
    adminToken = prefs!.getString('admin_token');
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _createAdmin() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      isLoading = true;
    });

    try {
      final body = {
        'username': _usernameController.text.trim(),
        'email': _emailController.text.trim(),
        'password': _passwordController.text,
        'role': selectedRole,
      };

      final String url = '${Urls.baseUrl}admin/create-admin';
      final response = await http.post(
        Uri.parse(url),
        headers: {
          HttpHeaders.contentTypeHeader: 'application/json',
          'Authorization': 'Bearer $adminToken',
        },
        body: jsonEncode(body),
      );

      final data = json.decode(response.body);

      if (data['success'] == true) {
        if (mounted) {
          showCustomToast(context, 'Admin created successfully');
          Navigator.pop(context, true);
        }
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to create admin');
        }
      }
    } on SocketException {
      if (mounted) {
        showCustomToast(context, 'No Internet Connection');
      }
    } catch (e) {
      if (mounted) {
        showCustomToast(context, 'Something went wrong: ${e.toString()}');
      }
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return CustomLoader(
      isLoading: isLoading,
      child: Scaffold(
        appBar: AppBar(
          title: const Text(
            'Create Admin',
            style: TextStyle(
              fontFamily: 'Roboto',
              fontSize: 20,
              fontWeight: FontWeight.w600,
            ),
          ),
          systemOverlayStyle: SystemUiOverlayStyle(
            statusBarIconBrightness: dark(context) ? Brightness.light : Brightness.dark,
            statusBarColor: Colors.transparent,
          ),
        ),
        body: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.all(24),
            children: [
              TextFieldWidget(
                hint: 'Username',
                controller: _usernameController,
                textAction: TextInputAction.next,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Username is required';
                  } else if (v.length < 3) {
                    return 'Username must be at least 3 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFieldWidget(
                hint: 'Email',
                controller: _emailController,
                textAction: TextInputAction.next,
                keyboard: TextInputType.emailAddress,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Email is required';
                  } else if (!v.contains('@')) {
                    return 'Enter a valid email';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFieldWidget(
                hint: 'Password',
                controller: _passwordController,
                textAction: TextInputAction.next,
                isObscure: true,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Password is required';
                  } else if (v.length < 8) {
                    return 'Password must be at least 8 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey.shade300),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    isExpanded: true,
                    value: selectedRole,
                    items: const [
                      DropdownMenuItem(
                        value: 'admin',
                        child: Text('Admin'),
                      ),
                      DropdownMenuItem(
                        value: 'editor',
                        child: Text('Editor'),
                      ),
                    ],
                    onChanged: (value) {
                      setState(() {
                        selectedRole = value!;
                      });
                    },
                  ),
                ),
              ),
              const SizedBox(height: 32),
              ElevateButton(
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 18,
                  color: Colors.white,
                  fontWeight: FontWeight.w500,
                ),
                onTap: _createAdmin,
                text: 'Create Admin',
              ),
            ],
          ),
        ),
      ),
    );
  }
}