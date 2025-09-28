import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

import '../../splash_screen.dart';
import '../../urls/url.dart';
import 'admin_form.dart';

class AdminListPage extends StatefulWidget {
  const AdminListPage({super.key});

  @override
  State<AdminListPage> createState() => _AdminListPageState();
}

class _AdminListPageState extends State<AdminListPage> {
  bool isLoading = true;
  List<dynamic> admins = [];
  String? adminToken;
  String? currentAdminRole;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    adminToken = prefs!.getString('admin_token');
    currentAdminRole = prefs!.getString('admin_role');

    if (currentAdminRole != 'super_admin') {
      if (mounted) {
        showCustomToast(context, 'Access denied: Super admin only');
        Navigator.pop(context);
      }
      return;
    }

    await _fetchAdmins();
  }

  Future<void> _fetchAdmins() async {
    setState(() {
      isLoading = true;
    });

    try {
      final String url = '${Urls.baseUrl}admin/list';
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
          admins = data['data'] ?? [];
          isLoading = false;
        });
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to fetch admins');
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

  Future<void> _deleteAdmin(String adminId, String username) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Admin'),
        content: Text('Are you sure you want to delete admin "$username"?'),
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
      final String url = '${Urls.baseUrl}admin/$adminId';
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
          showCustomToast(context, 'Admin deleted successfully');
          _fetchAdmins();
        }
      } else {
        if (mounted) {
          showCustomToast(context, data['message'] ?? 'Failed to delete admin');
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
          'Manage Admins',
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
        onRefresh: _fetchAdmins,
        child: isLoading
            ? const Center(child: CircularProgressIndicator())
            : admins.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.people_outline,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No admins found',
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
                    itemCount: admins.length,
                    itemBuilder: (context, index) {
                      final admin = admins[index];
                      return _buildAdminCard(admin);
                    },
                  ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const AdminFormPage(),
            ),
          );
          if (result == true) {
            _fetchAdmins();
          }
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildAdminCard(dynamic admin) {
    final createdAt = DateTime.parse(admin['created_at']);
    final dateFormat = DateFormat('MMM d, yyyy');
    final formattedDate = dateFormat.format(createdAt);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        admin['username'] ?? 'Unknown',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          fontFamily: 'Roboto',
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        admin['email'] ?? '',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey[600],
                          fontFamily: 'Roboto',
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.delete, color: Colors.red),
                  onPressed: () => _deleteAdmin(admin['id'], admin['username']),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: admin['role'] == 'super_admin'
                        ? Colors.purple
                        : (admin['role'] == 'editor' ? Colors.orange : Colors.blue),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    admin['role'] == 'super_admin'
                        ? 'Super Admin'
                        : (admin['role'] == 'editor' ? 'Editor' : 'Admin'),
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.white,
                      fontFamily: 'Roboto',
                    ),
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
              ],
            ),
          ],
        ),
      ),
    );
  }
}