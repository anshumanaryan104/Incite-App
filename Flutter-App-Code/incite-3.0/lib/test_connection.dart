import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class TestConnection extends StatefulWidget {
  const TestConnection({super.key});

  @override
  State<TestConnection> createState() => _TestConnectionState();
}

class _TestConnectionState extends State<TestConnection> {
  String status = "Testing connection...";

  @override
  void initState() {
    super.initState();
    testConnection();
  }

  Future<void> testConnection() async {
    try {
      print("🔍 Testing connection to: http://10.0.2.2:3000/api/blog-list");

      final response = await http.get(
        Uri.parse("http://10.0.2.2:3000/api/blog-list"),
        headers: {
          "content-type": "application/json",
        },
      ).timeout(const Duration(seconds: 10));

      print("✅ Response received: ${response.statusCode}");
      print("📦 Response body: ${response.body.substring(0, 200)}");

      setState(() {
        status = "Connected! Status: ${response.statusCode}\n${response.body.substring(0, 200)}...";
      });
    } catch (e) {
      print("❌ Connection failed: $e");
      setState(() {
        status = "Connection failed: $e";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Connection Test")),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(status, textAlign: TextAlign.center),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: testConnection,
              child: const Text("Test Again"),
            ),
          ],
        ),
      ),
    );
  }
}