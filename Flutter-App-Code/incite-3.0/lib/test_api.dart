import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() => runApp(TestApp());

class TestApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: TestScreen(),
    );
  }
}

class TestScreen extends StatefulWidget {
  @override
  _TestScreenState createState() => _TestScreenState();
}

class _TestScreenState extends State<TestScreen> {
  String result = "Testing...";

  @override
  void initState() {
    super.initState();
    testAPI();
  }

  Future<void> testAPI() async {
    try {
      final url = 'http://10.0.2.2:3000/api/blog-list';
      print('Testing URL: $url');

      final response = await http.get(Uri.parse(url));
      print('Status Code: ${response.statusCode}');
      print('Response Body: ${response.body.substring(0, 200)}');

      setState(() {
        if (response.body.startsWith('<!DOCTYPE')) {
          result = "ERROR: Getting HTML instead of JSON\nURL: $url";
        } else {
          final data = json.decode(response.body);
          result = "SUCCESS!\nData: ${data.toString()}";
        }
      });
    } catch (e) {
      setState(() {
        result = "ERROR: $e";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('API Test')),
      body: Center(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: Text(result),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: testAPI,
        child: Icon(Icons.refresh),
      ),
    );
  }
}