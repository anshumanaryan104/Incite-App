import 'dart:convert';
import 'package:http/http.dart' as http;
import '../urls/url.dart';

class AIController {
  /// Initialize AI session when user clicks "Ask AI" button
  ///
  /// Parameters:
  /// - articleId: ID of the article user is reading
  /// - userId: Current user's ID (can use device ID or user account ID)
  ///
  /// Returns:
  /// - Map with session info or throws error
  static Future<Map<String, dynamic>> initializeSession({
    required int articleId,
    required String userId,
  }) async {
    try {
      print('🎬 Initializing AI session for article $articleId');

      final response = await http.post(
        Uri.parse(Urls.aiInitSession),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'articleId': articleId,
          'userId': userId,
        }),
      );

      print('📡 Init Response Status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          print('✅ Session initialized successfully');
          return data['data'];
        } else {
          throw Exception(data['message'] ?? 'Failed to initialize session');
        }
      } else if (response.statusCode == 404) {
        throw Exception('Article not found');
      } else {
        final data = jsonDecode(response.body);
        throw Exception(data['message'] ?? 'Failed to initialize session');
      }
    } catch (e) {
      print('❌ Error initializing session: $e');
      rethrow;
    }
  }

  /// Send question to AI (after session is initialized)
  ///
  /// Parameters:
  /// - question: User's question about the article
  /// - userId: Same userId used in initializeSession
  ///
  /// Returns:
  /// - AI's answer as String or throws error
  static Future<String> askQuestion({
    required String question,
    required String userId,
  }) async {
    try {
      print('💬 Asking question: "$question"');

      final response = await http.post(
        Uri.parse(Urls.aiQuery),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'question': question,
          'userId': userId,
        }),
      );

      print('📡 Query Response Status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          print('✅ Answer received');
          return data['data']['answer'];
        } else {
          throw Exception(data['message'] ?? 'Failed to get answer');
        }
      } else if (response.statusCode == 400) {
        final data = jsonDecode(response.body);
        if (data['error'] == 'SESSION_NOT_FOUND') {
          throw Exception('Session expired. Please restart Ask AI.');
        }
        throw Exception(data['message'] ?? 'Failed to get answer');
      } else {
        final data = jsonDecode(response.body);
        throw Exception(data['message'] ?? 'Failed to get answer');
      }
    } catch (e) {
      print('❌ Error asking question: $e');
      rethrow;
    }
  }

  /// Get chat history for a specific article and user
  ///
  /// Parameters:
  /// - articleId: ID of the article
  /// - userId: Current user's ID
  ///
  /// Returns:
  /// - List of messages with 'type' (user/ai) and 'text'
  static Future<List<Map<String, String>>> getChatHistory({
    required int articleId,
    required String userId,
  }) async {
    try {
      print('📜 Fetching chat history for article $articleId');

      final response = await http.post(
        Uri.parse(Urls.aiHistory),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'articleId': articleId,
          'userId': userId,
        }),
      );

      print('📡 History Response Status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          final messages = data['data']['messages'] as List;
          print('✅ Found ${messages.length} messages in history');

          return messages.map((msg) => {
            'type': msg['type'] as String,
            'text': msg['text'] as String,
          }).toList();
        }
      }

      // Return empty list if no history or error
      return [];
    } catch (e) {
      print('❌ Error fetching history: $e');
      return [];
    }
  }

  /// Check if AI service is available
  static Future<bool> checkStatus() async {
    try {
      final response = await http.get(Uri.parse(Urls.aiStatus));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['success'] == true;
      }
      return false;
    } catch (e) {
      print('❌ Error checking AI status: $e');
      return false;
    }
  }
}
