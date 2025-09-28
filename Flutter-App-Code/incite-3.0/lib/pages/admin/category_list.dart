import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:http/http.dart' as http;

import '../../splash_screen.dart';
import '../../urls/url.dart';
import 'category_form.dart';

class CategoryListPage extends StatefulWidget {
  const CategoryListPage({super.key});

  @override
  State<CategoryListPage> createState() => _CategoryListPageState();
}

class _CategoryListPageState extends State<CategoryListPage> {
  bool isLoading = true;
  List<dynamic> categories = [];
  String? adminToken;

  @override
  void initState() {
    super.initState();
    adminToken = prefs!.getString('admin_token');
    _fetchCategories();
  }

  Future<void> _fetchCategories() async {
    setState(() {
      isLoading = true;
    });

    try {
      final String url = '${Urls.baseUrl}admin/categories';
      final response = await http.get(
        Uri.parse(url),
        headers: {
          HttpHeaders.contentTypeHeader: 'application/json',
          'Authorization': 'Bearer $adminToken',
        },
      );

      final data = json.decode(response.body);

      if (data['success'] == true) {
        setState(() {
          categories = data['data'] ?? [];
          isLoading = false;
        });
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to fetch categories');
        }
        setState(() {
          isLoading = false;
        });
      }
    } on SocketException {
      if (mounted) {
        showCustomToast(context, 'No Internet Connection');
      }
      setState(() {
        isLoading = false;
      });
    } catch (e) {
      if (mounted) {
        showCustomToast(context, 'Something went wrong');
      }
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _deleteCategory(int categoryId, String name) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Category'),
        content: Text('Are you sure you want to delete "$name"?\n\nNote: Categories with articles cannot be deleted.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final String url = '${Urls.baseUrl}admin/categories/$categoryId';
      final response = await http.delete(
        Uri.parse(url),
        headers: {
          HttpHeaders.contentTypeHeader: 'application/json',
          'Authorization': 'Bearer $adminToken',
        },
      );

      final data = json.decode(response.body);

      if (data['success'] == true) {
        if (mounted) {
          showCustomToast(context, 'Category deleted successfully');
          _fetchCategories();
        }
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to delete category');
        }
      }
    } catch (e) {
      if (mounted) {
        showCustomToast(context, 'Something went wrong');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Manage Categories',
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
      body: RefreshIndicator(
        onRefresh: _fetchCategories,
        child: isLoading
            ? const Center(child: CircularProgressIndicator())
            : categories.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.category_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No categories yet',
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.grey[600],
                            fontFamily: 'Roboto',
                          ),
                        ),
                      ],
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: categories.length,
                    itemBuilder: (context, index) {
                      final category = categories[index];
                      return _buildCategoryCard(category);
                    },
                  ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const CategoryFormPage(),
            ),
          );
          if (result == true) {
            _fetchCategories();
          }
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildCategoryCard(dynamic category) {
    final color = _parseColor(category['color']);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => CategoryFormPage(
                category: category,
                isEdit: true,
              ),
            ),
          );
          if (result == true) {
            _fetchCategories();
          }
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: color,
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          category['name'] ?? 'Unnamed',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            fontFamily: 'Roboto',
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          category['slug'] ?? '',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey[600],
                            fontFamily: 'Roboto',
                          ),
                        ),
                      ],
                    ),
                  ),
                  Icon(
                    Icons.edit,
                    size: 20,
                    color: Colors.grey[600],
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    icon: const Icon(Icons.delete, size: 20, color: Colors.red),
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                    onPressed: () => _deleteCategory(category['id'], category['name']),
                  ),
                ],
              ),
              if (category['description'] != null && category['description'].isNotEmpty) ...[
                const SizedBox(height: 8),
                Text(
                  category['description'],
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[700],
                    fontFamily: 'Roboto',
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Color _parseColor(String? colorString) {
    if (colorString == null || colorString.isEmpty) return Colors.blue;
    try {
      final hexColor = colorString.replaceAll('#', '');
      return Color(int.parse('FF$hexColor', radix: 16));
    } catch (e) {
      return Colors.blue;
    }
  }
}