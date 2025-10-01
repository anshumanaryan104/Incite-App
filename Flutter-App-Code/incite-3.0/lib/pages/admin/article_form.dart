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

class ArticleFormPage extends StatefulWidget {
  final Map<String, dynamic>? article;
  final bool isEdit;

  const ArticleFormPage({
    super.key,
    this.article,
    this.isEdit = false,
  });

  @override
  State<ArticleFormPage> createState() => _ArticleFormPageState();
}

class _ArticleFormPageState extends State<ArticleFormPage> {
  final _formKey = GlobalKey<FormState>();
  bool isLoading = false;
  bool isFeatured = false;

  final TextEditingController _titleController = TextEditingController();
  final TextEditingController _descriptionController = TextEditingController();
  final TextEditingController _contentController = TextEditingController();
  final TextEditingController _imageController = TextEditingController();

  // Categories removed
  String? adminToken;

  @override
  void initState() {
    super.initState();
    adminToken = prefs!.getString('admin_token');

    if (widget.isEdit && widget.article != null) {
      _titleController.text = widget.article!['title'] ?? '';
      _descriptionController.text = widget.article!['description'] ?? '';
      _contentController.text = widget.article!['content'] ?? '';
      _imageController.text = widget.article!['featured_image'] ?? '';
      isFeatured = widget.article!['is_featured'] ?? false;
    }

    // Category fetching removed
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _contentController.dispose();
    _imageController.dispose();
    super.dispose();
  }

  Future<void> _saveArticle() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      isLoading = true;
    });

    try {
      final body = {
        'title': _titleController.text.trim(),
        'description': _descriptionController.text.trim(),
        'content': _contentController.text.trim(),
        // category_id removed
        'featured_image': _imageController.text.trim(),
        'is_featured': isFeatured,
      };

      String url;
      http.Response response;

      if (widget.isEdit) {
        url = '${Urls.baseUrl}admin/articles/${widget.article!['id']}';
        response = await http.put(
          Uri.parse(url),
          headers: {
            HttpHeaders.contentTypeHeader: 'application/json',
            'Authorization': 'Bearer $adminToken',
          },
          body: jsonEncode(body),
        );
      } else {
        url = '${Urls.baseUrl}admin/articles';
        response = await http.post(
          Uri.parse(url),
          headers: {
            HttpHeaders.contentTypeHeader: 'application/json',
            'Authorization': 'Bearer $adminToken',
          },
          body: jsonEncode(body),
        );
      }

      final data = json.decode(response.body);

      if (data['success'] == true) {
        if (mounted) {
          showCustomToast(
            context,
            widget.isEdit ? 'Article updated successfully' : 'Article created successfully',
          );
          Navigator.pop(context, true);
        }
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to save article');
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
          title: Text(
            widget.isEdit ? 'Edit Article' : 'Create Article',
            style: const TextStyle(
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
                hint: 'Title',
                controller: _titleController,
                textAction: TextInputAction.next,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Title is required';
                  } else if (v.length < 5) {
                    return 'Title must be at least 5 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _descriptionController,
                decoration: InputDecoration(
                  hintText: 'Short Description',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  contentPadding: const EdgeInsets.all(16),
                ),
                maxLines: 3,
                textInputAction: TextInputAction.next,
                validator: (v) {
                  if (v!.isEmpty) {
                    return 'Description is required';
                  } else if (v.length < 10) {
                    return 'Description must be at least 10 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _contentController,
                decoration: InputDecoration(
                  hintText: 'Full Content (Article body)',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  contentPadding: const EdgeInsets.all(16),
                ),
                maxLines: 10,
                textInputAction: TextInputAction.newline,
                validator: (v) {
                  if (v!.isEmpty) {
                    return 'Content is required';
                  } else if (v.length < 20) {
                    return 'Content must be at least 20 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFieldWidget(
                hint: 'Featured Image URL',
                controller: _imageController,
                textAction: TextInputAction.next,
                keyboard: TextInputType.url,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Image URL is required';
                  } else if (!v.startsWith('http')) {
                    return 'Enter a valid URL';
                  }
                  return null;
                },
              ),
              // Category dropdown removed
              const SizedBox(height: 16),
              CheckboxListTile(
                title: const Text(
                  'Mark as Featured',
                  style: TextStyle(
                    fontFamily: 'Roboto',
                    fontSize: 14,
                  ),
                ),
                value: isFeatured,
                onChanged: (value) {
                  setState(() {
                    isFeatured = value ?? false;
                  });
                },
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
              ),
              const SizedBox(height: 32),
              ElevateButton(
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 18,
                  color: Colors.white,
                  fontWeight: FontWeight.w500,
                ),
                onTap: _saveArticle,
                text: widget.isEdit ? 'Update Article' : 'Create Article',
              ),
            ],
          ),
        ),
      ),
    );
  }
}