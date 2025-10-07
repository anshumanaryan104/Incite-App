# How to Use AI Controller in Flutter App

## 📋 Setup Complete!

URLs configured in: `lib/urls/url.dart`
AI Controller created in: `lib/api_controller/ai_controller.dart`

---

## 🚀 Usage Example

### Step 1: Import the Controller

```dart
import 'package:incite/api_controller/ai_controller.dart';
```

### Step 2: Initialize Session (When "Ask AI" button is clicked)

```dart
String userId = "user_12345"; // Get from current user or device ID
bool isSessionInitialized = false;

Future<void> onAskAIButtonPressed(int articleId) async {
  try {
    // Show loading indicator
    setState(() => isLoading = true);

    // Initialize session
    final sessionData = await AIController.initializeSession(
      articleId: articleId,
      userId: userId,
    );

    setState(() {
      isSessionInitialized = true;
      isLoading = false;
    });

    print('Session initialized: ${sessionData['message']}');
    print('Article: ${sessionData['article']['title']}');

    // Now show chat dialog or screen
    _showAIChatDialog();

  } catch (e) {
    setState(() => isLoading = false);
    _showError('Failed to start AI chat: $e');
  }
}
```

### Step 3: Send Questions (When user types and clicks "Send")

```dart
Future<void> onSendQuestion(String question) async {
  if (question.isEmpty) return;

  try {
    // Add user message to chat
    setState(() {
      messages.add({'role': 'user', 'text': question});
      isLoading = true;
    });

    // Get AI answer
    final answer = await AIController.askQuestion(
      question: question,
      userId: userId, // Same userId as init
    );

    // Add AI response to chat
    setState(() {
      messages.add({'role': 'ai', 'text': answer});
      isLoading = false;
    });

  } catch (e) {
    setState(() => isLoading = false);
    _showError('Failed to get answer: $e');
  }
}
```

---

## 💡 Complete Widget Example

```dart
import 'package:flutter/material.dart';
import 'package:incite/api_controller/ai_controller.dart';

class ArticleDetailPage extends StatefulWidget {
  final int articleId;
  final String articleTitle;

  const ArticleDetailPage({
    required this.articleId,
    required this.articleTitle,
  });

  @override
  _ArticleDetailPageState createState() => _ArticleDetailPageState();
}

class _ArticleDetailPageState extends State<ArticleDetailPage> {
  final String userId = "user_12345"; // Replace with actual user ID
  bool isSessionInitialized = false;
  bool isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.articleTitle),
        actions: [
          // Ask AI Button
          IconButton(
            icon: Icon(Icons.smart_toy),
            onPressed: () => _onAskAIButtonPressed(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Article content here
          Text('Article content...'),

          // ... rest of article UI
        ],
      ),
    );
  }

  // When user clicks "Ask AI" button
  Future<void> _onAskAIButtonPressed() async {
    setState(() => isLoading = true);

    try {
      // Initialize AI session
      final sessionData = await AIController.initializeSession(
        articleId: widget.articleId,
        userId: userId,
      );

      setState(() {
        isSessionInitialized = true;
        isLoading = false;
      });

      // Show chat dialog
      _showAIChatDialog();

    } catch (e) {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  // Show chat dialog
  void _showAIChatDialog() {
    showDialog(
      context: context,
      builder: (context) => AIChatDialog(
        userId: userId,
        articleTitle: widget.articleTitle,
      ),
    );
  }
}

// AI Chat Dialog Widget
class AIChatDialog extends StatefulWidget {
  final String userId;
  final String articleTitle;

  const AIChatDialog({
    required this.userId,
    required this.articleTitle,
  });

  @override
  _AIChatDialogState createState() => _AIChatDialogState();
}

class _AIChatDialogState extends State<AIChatDialog> {
  final TextEditingController _questionController = TextEditingController();
  final List<Map<String, String>> _messages = [];
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: Container(
        height: MediaQuery.of(context).size.height * 0.7,
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Ask AI',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                IconButton(
                  icon: Icon(Icons.close),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            Divider(),

            // Messages
            Expanded(
              child: _messages.isEmpty
                  ? Center(
                      child: Text('Ask me anything about this article!'),
                    )
                  : ListView.builder(
                      itemCount: _messages.length,
                      itemBuilder: (context, index) {
                        final message = _messages[index];
                        final isUser = message['role'] == 'user';

                        return Align(
                          alignment: isUser
                              ? Alignment.centerRight
                              : Alignment.centerLeft,
                          child: Container(
                            margin: EdgeInsets.symmetric(vertical: 4),
                            padding: EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: isUser ? Colors.blue : Colors.grey[300],
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              message['text']!,
                              style: TextStyle(
                                color: isUser ? Colors.white : Colors.black,
                              ),
                            ),
                          ),
                        );
                      },
                    ),
            ),

            // Loading indicator
            if (_isLoading)
              Padding(
                padding: EdgeInsets.all(8),
                child: CircularProgressIndicator(),
              ),

            Divider(),

            // Input field
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _questionController,
                    decoration: InputDecoration(
                      hintText: 'Type your question...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                      ),
                    ),
                    enabled: !_isLoading,
                  ),
                ),
                SizedBox(width: 8),
                IconButton(
                  icon: Icon(Icons.send),
                  onPressed: _isLoading ? null : _sendQuestion,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _sendQuestion() async {
    if (_questionController.text.isEmpty) return;

    final question = _questionController.text;
    setState(() {
      _messages.add({'role': 'user', 'text': question});
      _isLoading = true;
    });

    _questionController.clear();

    try {
      // Get AI answer
      final answer = await AIController.askQuestion(
        question: question,
        userId: widget.userId,
      );

      setState(() {
        _messages.add({'role': 'ai', 'text': answer});
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  void dispose() {
    _questionController.dispose();
    super.dispose();
  }
}
```

---

## 🎯 Key Points

1. **Initialize once per article** - Call `initializeSession()` when user clicks "Ask AI"
2. **Use same userId** - Keep userId consistent for init and all questions
3. **Handle errors** - Wrap in try-catch and show user-friendly messages
4. **Session expires** - If user gets "Session not found" error, call init again

---

## 📡 URLs Being Used

- **Init Session:** `http://172.21.144.1:3000/api/ask-ai/init`
- **Send Question:** `http://172.21.144.1:3000/api/ask-ai/query`
- **Check Status:** `http://172.21.144.1:3000/api/ai-status`

---

## ✅ Ready to Test!

Both servers are running:
- ✅ Express Backend: Port 3000
- ✅ AI Backend: Port 8000

Just import `AIController` and start using it! 🚀
