import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

void main() => runApp(DebugApp());

class DebugApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(home: DebugScreen());
  }
}

class DebugScreen extends StatefulWidget {
  @override
  _DebugScreenState createState() => _DebugScreenState();
}

class _DebugScreenState extends State<DebugScreen> {
  String result = "Testing...";

  @override
  void initState() {
    super.initState();
    testAllEndpoints();
  }

  Future<void> testAllEndpoints() async {
    StringBuffer sb = StringBuffer();

    // Test different URLs
    List<String> urls = [
      'http://10.0.2.2:3000',
      'http://10.0.2.2:3000/api/blog-list',
      'http://10.0.2.2:3000/api/setting-list',
      'http://10.0.2.2:8000/api/blog-list',
    ];

    for (String url in urls) {
      try {
        sb.writeln('\nTesting: $url');
        final response = await http.get(Uri.parse(url)).timeout(Duration(seconds: 3));
        sb.writeln('Status: ${response.statusCode}');

        String body = response.body;
        if (body.length > 100) body = body.substring(0, 100);

        if (body.startsWith('<!DOCTYPE')) {
          sb.writeln('Response: HTML (Error!)');
        } else if (body.startsWith('{')) {
          sb.writeln('Response: JSON (Good!)');
        } else {
          sb.writeln('Response: Unknown');
        }
        sb.writeln('Body: $body...\n');

      } catch (e) {
        sb.writeln('Error: $e\n');
      }
    }

    setState(() {
      result = sb.toString();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('API Debug')),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(10),
        child: Text(
          result,
          style: TextStyle(fontSize: 12, fontFamily: 'monospace'),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: testAllEndpoints,
        child: Icon(Icons.refresh),
      ),
    );
  }
}