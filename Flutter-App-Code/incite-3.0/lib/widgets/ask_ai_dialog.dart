import 'package:flutter/material.dart';
import 'package:get_storage/get_storage.dart';
import '../api_controller/ai_controller.dart';

class AskAIDialog extends StatefulWidget {
  final int articleId;
  final String articleTitle;
  final String articleContent;

  const AskAIDialog({
    Key? key,
    required this.articleId,
    required this.articleTitle,
    required this.articleContent,
  }) : super(key: key);

  @override
  State<AskAIDialog> createState() => _AskAIDialogState();
}

class _AskAIDialogState extends State<AskAIDialog> {
  final TextEditingController _questionController = TextEditingController();
  final List<Map<String, String>> _messages = [];
  final ScrollController _scrollController = ScrollController();
  final GetStorage _storage = GetStorage();

  bool _isLoading = false;
  bool _isSessionInitialized = false;
  late String _userId;
  late String _threadId; // Unique thread ID per user + article

  @override
  void initState() {
    super.initState();
    _userId = _getOrCreateUserId();
    _threadId = '${_userId}_article_${widget.articleId}'; // Unique thread per article
    _initializeSession();
  }

  // Get or create persistent user ID for chat history
  String _getOrCreateUserId() {
    // Try to get existing userId from storage
    String? existingUserId = _storage.read('ai_user_id');

    if (existingUserId != null && existingUserId.isNotEmpty) {
      print('📱 Using existing userId: $existingUserId');
      return existingUserId;
    }

    // Create new userId and save it
    String newUserId = 'user_${DateTime.now().millisecondsSinceEpoch}';
    _storage.write('ai_user_id', newUserId);
    print('🆕 Created new userId: $newUserId');
    return newUserId;
  }

  @override
  void dispose() {
    _questionController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  // Initialize AI session when dialog opens
  Future<void> _initializeSession() async {
    setState(() => _isLoading = true);

    try {
      // First, try to load chat history
      print('📜 Attempting to load chat history for thread: $_threadId');
      final history = await AIController.getChatHistory(
        articleId: widget.articleId,
        userId: _threadId, // Use threadId instead of userId
      );

      // Always initialize session (even if history exists)
      // This ensures Express backend has the session cached
      print('🔄 Initializing Express session...');
      await AIController.initializeSession(
        articleId: widget.articleId,
        userId: _threadId,
      );

      // If history exists, load it (without greeting message)
      if (history.isNotEmpty) {
        setState(() {
          _messages.addAll(history);
          _isSessionInitialized = true;
          _isLoading = false;
        });
        print('✅ Loaded ${history.length} messages from history');
        _scrollToBottom();
        return;
      }

      // No history - show greeting message
      setState(() {
        _isSessionInitialized = true;
        _isLoading = false;

        // Add greeting message only for new sessions
        _messages.add({
          'type': 'ai',
          'text': 'Hello! I\'m your AI assistant. I can help answer any questions you have about this article. What would you like to know?',
        });
      });

      print('✅ AI Session initialized for article ${widget.articleId}');

      // Auto-scroll to show greeting
      _scrollToBottom();
    } catch (e) {
      setState(() => _isLoading = false);
      print('❌ Failed to initialize session: $e');
      _showError('Failed to start AI chat. Please try again.');
    }
  }

  // Send question to AI
  Future<void> _sendQuestion() async {
    if (_questionController.text.trim().isEmpty) return;
    if (!_isSessionInitialized) {
      _showError('Please wait for AI to initialize...');
      return;
    }

    final question = _questionController.text.trim();

    setState(() {
      _messages.add({
        'type': 'user',
        'text': question,
      });
      _isLoading = true;
    });

    _questionController.clear();
    _scrollToBottom();

    try {
      // Call AI API with threadId
      final answer = await AIController.askQuestion(
        question: question,
        userId: _threadId, // Use threadId instead of userId
      );

      setState(() {
        _messages.add({
          'type': 'ai',
          'text': answer,
        });
        _isLoading = false;
      });

      _scrollToBottom();
    } catch (e) {
      setState(() => _isLoading = false);
      print('❌ Failed to get answer: $e');

      setState(() {
        _messages.add({
          'type': 'ai',
          'text': 'Sorry, I couldn\'t process your question. Please try again.',
        });
      });

      _scrollToBottom();
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: EdgeInsets.zero,
      alignment: Alignment.bottomCenter,
      child: Container(
        width: MediaQuery.of(context).size.width,
        margin: const EdgeInsets.only(top: 100),
        decoration: BoxDecoration(
          color: const Color(0xFF2D2D2D), // Dark background like screenshot
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(20),
            topRight: Radius.circular(20),
          ),
        ),
        child: Column(
          children: [
            // Header - Exact like screenshot
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: const BoxDecoration(
                color: Color(0xFF3D3D3D),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(20),
                  topRight: Radius.circular(20),
                ),
              ),
              child: Row(
                children: [
                  const Text(
                    '✨',
                    style: TextStyle(fontSize: 20),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Ask AI',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                        Text(
                          'for ${widget.articleTitle}',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey[500],
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white),
                    onPressed: () => Navigator.pop(context),
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                ],
              ),
            ),

            // Messages Area - Dark background
            Expanded(
              child: Container(
                color: const Color(0xFF2D2D2D),
                child: _messages.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            if (_isLoading && !_isSessionInitialized)
                              const CircularProgressIndicator(color: Color(0xFFFF8B7B))
                            else
                              Icon(
                                Icons.chat_bubble_outline,
                                size: 60,
                                color: Colors.grey[600],
                              ),
                            const SizedBox(height: 16),
                            Text(
                              _isLoading && !_isSessionInitialized
                                  ? 'Initializing AI...'
                                  : 'Ask me anything about this article',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.grey[500],
                              ),
                            ),
                          ],
                        ),
                      )
                    : Column(
                        children: [
                          Expanded(
                            child: ListView.builder(
                              controller: _scrollController,
                              padding: const EdgeInsets.all(16),
                              itemCount: _messages.length,
                              itemBuilder: (context, index) {
                                final message = _messages[index];
                                final isUser = message['type'] == 'user';

                                return Align(
                                  alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 12),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 16,
                                      vertical: 12,
                                    ),
                                    constraints: BoxConstraints(
                                      maxWidth: MediaQuery.of(context).size.width * 0.65,
                                    ),
                                    decoration: BoxDecoration(
                                      color: isUser
                                          ? const Color(0xFFFF8B7B) // Pink/salmon like screenshot
                                          : Colors.white,
                                      borderRadius: BorderRadius.circular(18),
                                    ),
                                    child: Text(
                                      message['text'] ?? '',
                                      style: TextStyle(
                                        color: isUser ? Colors.white : Colors.black87,
                                        fontSize: 14,
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
                          if (_isLoading && _isSessionInitialized)
                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Row(
                                children: [
                                  SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Color(0xFFFF8B7B),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Text(
                                    'AI is thinking...',
                                    style: TextStyle(
                                      color: Colors.grey[400],
                                      fontSize: 13,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
              ),
            ),

            // Input Bar (Bottom) - Exactly like screenshot
            Container(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
              decoration: const BoxDecoration(
                color: Color(0xFF1F1F1F), // Darker bottom bar
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: TextField(
                        controller: _questionController,
                        style: const TextStyle(
                          color: Colors.black,
                          fontSize: 15,
                        ),
                        decoration: const InputDecoration(
                          hintText: 'Ask a question...',
                          hintStyle: TextStyle(
                            color: Colors.grey,
                            fontSize: 15,
                          ),
                          border: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 12,
                          ),
                        ),
                        maxLines: null,
                        textInputAction: TextInputAction.send,
                        onSubmitted: (_) => _sendQuestion(),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Container(
                    width: 48,
                    height: 48,
                    decoration: const BoxDecoration(
                      color: Color(0xFFFF8B7B), // Pink send button
                      shape: BoxShape.circle,
                    ),
                    child: IconButton(
                      icon: const Icon(Icons.arrow_forward, color: Colors.white, size: 22),
                      onPressed: _sendQuestion,
                      padding: EdgeInsets.zero,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
