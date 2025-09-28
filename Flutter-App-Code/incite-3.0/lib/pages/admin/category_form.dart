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

class CategoryFormPage extends StatefulWidget {
  final Map<String, dynamic>? category;
  final bool isEdit;

  const CategoryFormPage({
    super.key,
    this.category,
    this.isEdit = false,
  });

  @override
  State<CategoryFormPage> createState() => _CategoryFormPageState();
}

class _CategoryFormPageState extends State<CategoryFormPage> {
  final _formKey = GlobalKey<FormState>();
  bool isLoading = false;

  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _slugController = TextEditingController();
  final TextEditingController _colorController = TextEditingController();
  final TextEditingController _descriptionController = TextEditingController();

  String? adminToken;
  List<String> predefinedColors = [
    '#3B82F6', // Blue
    '#EF4444', // Red
    '#10B981', // Green
    '#F59E0B', // Orange
    '#8B5CF6', // Purple
    '#EC4899', // Pink
    '#06B6D4', // Cyan
    '#F97316', // Dark Orange
  ];

  @override
  void initState() {
    super.initState();
    adminToken = prefs!.getString('admin_token');

    if (widget.isEdit && widget.category != null) {
      _nameController.text = widget.category!['name'] ?? '';
      _slugController.text = widget.category!['slug'] ?? '';
      _colorController.text = widget.category!['color'] ?? '#3B82F6';
      _descriptionController.text = widget.category!['description'] ?? '';
    } else {
      _colorController.text = '#3B82F6';
    }

    // Auto-generate slug from name
    _nameController.addListener(() {
      if (!widget.isEdit) {
        final slug = _nameController.text
            .toLowerCase()
            .replaceAll(RegExp(r'[^a-z0-9]+'), '-')
            .replaceAll(RegExp(r'^-+|-+$'), '');
        _slugController.text = slug;
      }
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _slugController.dispose();
    _colorController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _saveCategory() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      isLoading = true;
    });

    try {
      final body = {
        'name': _nameController.text.trim(),
        'slug': _slugController.text.trim(),
        'color': _colorController.text.trim(),
        'description': _descriptionController.text.trim(),
      };

      String url;
      http.Response response;

      if (widget.isEdit) {
        url = '${Urls.baseUrl}admin/categories/${widget.category!['id']}';
        response = await http.put(
          Uri.parse(url),
          headers: {
            HttpHeaders.contentTypeHeader: 'application/json',
            'Authorization': 'Bearer $adminToken',
          },
          body: jsonEncode(body),
        );
      } else {
        url = '${Urls.baseUrl}admin/categories';
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
            widget.isEdit ? 'Category updated successfully' : 'Category created successfully',
          );
          Navigator.pop(context, true);
        }
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to save category');
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

  Color _parseColor(String colorString) {
    try {
      final hexColor = colorString.replaceAll('#', '');
      return Color(int.parse('FF$hexColor', radix: 16));
    } catch (e) {
      return Colors.blue;
    }
  }

  @override
  Widget build(BuildContext context) {
    return CustomLoader(
      isLoading: isLoading,
      child: Scaffold(
        appBar: AppBar(
          title: Text(
            widget.isEdit ? 'Edit Category' : 'Create Category',
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
                hint: 'Category Name',
                controller: _nameController,
                textAction: TextInputAction.next,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Name is required';
                  } else if (v.length < 2) {
                    return 'Name must be at least 2 characters';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFieldWidget(
                hint: 'Slug (URL-friendly name)',
                controller: _slugController,
                textAction: TextInputAction.next,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Slug is required';
                  } else if (!RegExp(r'^[a-z0-9-]+$').hasMatch(v)) {
                    return 'Only lowercase letters, numbers, and hyphens allowed';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _descriptionController,
                decoration: InputDecoration(
                  hintText: 'Description (optional)',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  contentPadding: const EdgeInsets.all(16),
                ),
                maxLines: 3,
                textInputAction: TextInputAction.next,
              ),
              const SizedBox(height: 16),
              const Text(
                'Category Color',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  fontFamily: 'Roboto',
                ),
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 12,
                runSpacing: 12,
                children: predefinedColors.map((colorHex) {
                  final isSelected = _colorController.text == colorHex;
                  return GestureDetector(
                    onTap: () {
                      setState(() {
                        _colorController.text = colorHex;
                      });
                    },
                    child: Container(
                      width: 50,
                      height: 50,
                      decoration: BoxDecoration(
                        color: _parseColor(colorHex),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: isSelected ? Colors.black : Colors.transparent,
                          width: 3,
                        ),
                      ),
                      child: isSelected
                          ? const Icon(Icons.check, color: Colors.white)
                          : null,
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 16),
              TextFieldWidget(
                hint: 'Color Hex Code',
                controller: _colorController,
                textAction: TextInputAction.done,
                onValidate: (v) {
                  if (v!.isEmpty) {
                    return 'Color is required';
                  } else if (!RegExp(r'^#[0-9A-Fa-f]{6}$').hasMatch(v)) {
                    return 'Invalid hex color (e.g., #3B82F6)';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 32),
              ElevateButton(
                style: const TextStyle(
                  fontFamily: 'Poppins',
                  fontSize: 18,
                  color: Colors.white,
                  fontWeight: FontWeight.w500,
                ),
                onTap: _saveCategory,
                text: widget.isEdit ? 'Update Category' : 'Create Category',
              ),
            ],
          ),
        ),
      ),
    );
  }
}