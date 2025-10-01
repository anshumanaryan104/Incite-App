import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

import '../../splash_screen.dart';
import '../../urls/url.dart';
import 'article_form.dart';

class AdminDashboard extends StatefulWidget {
  const AdminDashboard({super.key});

  @override
  State<AdminDashboard> createState() => _AdminDashboardState();
}

class _AdminDashboardState extends State<AdminDashboard> {
  bool isLoading = true;
  List<dynamic> articles = [];
  List<dynamic> filteredArticles = [];
  // Categories removed
  String? adminToken;
  String? adminUsername;

  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_filterArticles);
    _loadAdminData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadAdminData() async {
    adminToken = prefs!.getString('admin_token');
    adminUsername = prefs!.getString('admin_username');

    if (adminToken == null) {
      if (mounted) {
        Navigator.pushReplacementNamed(context, '/AdminLogin');
      }
      return;
    }

    // Category fetching removed
    await _fetchArticles();
  }

  void _filterArticles() {
    final query = _searchController.text.toLowerCase();

    setState(() {
      filteredArticles = articles.where((article) {
        final title = (article['title'] ?? '').toLowerCase();
        final description = (article['description'] ?? '').toLowerCase();
        return query.isEmpty || title.contains(query) || description.contains(query);
      }).toList();
    });
  }

  Future<void> _fetchArticles() async {
    setState(() {
      isLoading = true;
    });

    try {
      final String url = '${Urls.baseUrl}admin/articles?page=1&limit=50';
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
          articles = data['data']['articles'] ?? [];
          filteredArticles = articles;
          isLoading = false;
        });
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to fetch articles');
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

  Future<void> _deleteArticle(int articleId, String title) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Article'),
        content: Text('Are you sure you want to delete "$title"?'),
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
      final String url = '${Urls.baseUrl}admin/articles/$articleId';
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
          showCustomToast(context, 'Article deleted successfully');
          _fetchArticles();
        }
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to delete article');
        }
      }
    } catch (e) {
      if (mounted) {
        showCustomToast(context, 'Something went wrong');
      }
    }
  }

  Future<void> _logout() async {
    await prefs!.remove('admin_token');
    await prefs!.remove('admin_username');
    await prefs!.remove('admin_role');

    if (mounted) {
      Navigator.pushNamedAndRemoveUntil(context, '/MainPage', (route) => false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Admin Dashboard',
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
        actions: [
          // Category management removed
          IconButton(
            icon: const Icon(Icons.people),
            tooltip: 'Manage Admins',
            onPressed: () {
              Navigator.pushNamed(context, '/AdminList');
            },
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () {
              showDialog(
                context: context,
                builder: (context) => AlertDialog(
                  title: const Text('Logout'),
                  content: const Text('Are you sure you want to logout?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancel'),
                    ),
                    TextButton(
                      onPressed: () {
                        Navigator.pop(context);
                        _logout();
                      },
                      child: const Text('Logout'),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search articles...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchController.clear();
                            },
                          )
                        : null,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    contentPadding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
                // Category filter removed
              ],
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _fetchArticles,
              child: isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : filteredArticles.isEmpty
                      ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.article_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No articles yet',
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.grey[600],
                            fontFamily: 'Roboto',
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Tap + to create your first article',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[500],
                            fontFamily: 'Roboto',
                          ),
                        ),
                      ],
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: filteredArticles.length,
                    itemBuilder: (context, index) {
                      final article = filteredArticles[index];
                      return _buildArticleCard(article);
                    },
                  ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const ArticleFormPage(isEdit: false),
            ),
          );
          if (result == true) {
            _fetchArticles();
          }
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildArticleCard(dynamic article) {
    final createdAt = DateTime.parse(article['created_at']);
    final dateFormat = DateFormat('MMM d, yyyy');
    final formattedDate = dateFormat.format(createdAt);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => ArticleFormPage(
                article: article,
                isEdit: true,
              ),
            ),
          );
          if (result == true) {
            _fetchArticles();
          }
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      article['title'] ?? 'Untitled',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        fontFamily: 'Roboto',
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 8),
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
                    onPressed: () => _deleteArticle(article['id'], article['title']),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              // Category badge removed
              Row(
                children: [
                  Icon(Icons.person, size: 14, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    article['created_by'] ?? 'Unknown',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                      fontFamily: 'Roboto',
                    ),
                  ),
                  const SizedBox(width: 12),
                  Icon(Icons.access_time, size: 14, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    formattedDate,
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                      fontFamily: 'Roboto',
                    ),
                  ),
                  if (article['is_featured'] == true) ...[
                    const SizedBox(width: 12),
                    Icon(Icons.star, size: 14, color: Colors.amber[700]),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}