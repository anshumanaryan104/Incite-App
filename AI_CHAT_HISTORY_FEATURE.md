# AI Chat History Feature - Implementation Summary

## 🎯 Feature Overview
Chat history ab persist hota hai! Jab user kisi article ke liye "Ask AI" close karta hai aur dobara open karta hai, tab purane chats bhi show honge.

---

## 🏗️ Architecture

### Thread-Based History System
```
User Device
  └─> Persistent User ID (stored in GetStorage)
      └─> Thread ID = userId + "_article_" + articleId
          └─> Chat History per Article
```

**Example:**
- User ID: `user_1728234567890`
- Article ID: `17`
- Thread ID: `user_1728234567890_article_17`

Har article ka alag thread hai, toh different articles ke chats mix nahi honge.

---

## 📡 API Endpoints

### 1. Initialize Session
**POST** `/api/ask-ai/init`
```json
{
  "articleId": 17,
  "userId": "user_1728234567890_article_17"
}
```

### 2. Send Question
**POST** `/api/ask-ai/query`
```json
{
  "question": "What is this article about?",
  "userId": "user_1728234567890_article_17"
}
```

### 3. Get Chat History (NEW!)
**POST** `/api/ask-ai/history`
```json
{
  "articleId": 17,
  "userId": "user_1728234567890_article_17"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "messages": [
      { "type": "user", "text": "What is this about?" },
      { "type": "ai", "text": "This article discusses..." },
      { "type": "user", "text": "Tell me more" },
      { "type": "ai", "text": "Sure! The article..." }
    ],
    "threadId": "user_1728234567890_article_17"
  }
}
```

---

## 🔧 Implementation Details

### Backend Changes

#### 1. **AI Backend (Python/FastAPI)** - `chatbot_api.py`
- Added new endpoint: `POST /api/chat/history`
- Uses `MemorySaver.get()` to retrieve thread state
- Extracts messages from LangGraph state
- Returns formatted message list

#### 2. **Express Backend** - `ai-chatbot.js`
- Added route: `POST /api/ask-ai/history`
- Proxies request to AI backend
- Transforms response to frontend format

### Frontend Changes

#### 3. **Flutter URL Config** - `url.dart`
```dart
static String aiHistory = "${baseUrl}ask-ai/history";
```

#### 4. **AI Controller** - `ai_controller.dart`
New method:
```dart
static Future<List<Map<String, String>>> getChatHistory({
  required int articleId,
  required String userId,
})
```

#### 5. **Ask AI Dialog** - `ask_ai_dialog.dart`
**Major Changes:**

1. **Persistent User ID:**
```dart
String _getOrCreateUserId() {
  String? existingUserId = _storage.read('ai_user_id');
  if (existingUserId != null) return existingUserId;

  String newUserId = 'user_${DateTime.now().millisecondsSinceEpoch}';
  _storage.write('ai_user_id', newUserId);
  return newUserId;
}
```

2. **Thread ID per Article:**
```dart
_threadId = '${_userId}_article_${widget.articleId}';
```

3. **Load History on Init:**
```dart
Future<void> _initializeSession() async {
  // Try to load existing history
  final history = await AIController.getChatHistory(
    articleId: widget.articleId,
    userId: _threadId,
  );

  if (history.isNotEmpty) {
    // Load history
    _messages.addAll(history);
    _isSessionInitialized = true;
  } else {
    // Initialize new session
    await AIController.initializeSession(
      articleId: widget.articleId,
      userId: _threadId,
    );

    // Show greeting message
    _messages.add({
      'type': 'ai',
      'text': 'Hello! I\'m your AI assistant...',
    });
  }
}
```

---

## 🔄 User Flow

### First Time Chat
1. User clicks "Ask AI" on article
2. App generates/retrieves persistent userId
3. Creates threadId: `userId_article_17`
4. Checks for history → Empty
5. Initializes new session
6. Shows greeting message
7. User asks questions
8. All Q&A saved in LangGraph memory

### Returning to Same Article
1. User clicks "Ask AI" again
2. Same userId retrieved from storage
3. Same threadId generated
4. Checks for history → Found!
5. Loads all previous messages
6. User continues conversation
7. New messages append to existing history

### Different Article
1. User clicks "Ask AI" on article ID 25
2. Same userId, but different threadId: `userId_article_25`
3. New thread → No history
4. Fresh session with greeting
5. Separate chat history maintained

---

## 💾 Storage Details

### LangGraph Memory (AI Backend)
- Uses `MemorySaver()` in-memory storage
- Thread-based conversation state
- Persists until server restart
- **Production:** Recommend Redis or PostgreSQL

### GetStorage (Flutter)
- Stores persistent userId
- Survives app restarts
- Location: Device local storage
- Key: `'ai_user_id'`

---

## ✅ Testing Checklist

1. ✅ First chat → Greeting message appears
2. ✅ Send questions → AI responds
3. ✅ Close dialog → Reopen → History loads
4. ✅ Different article → New conversation
5. ✅ Same article → Same history
6. ✅ App restart → Same history (userId persists)
7. ✅ Server restart → History lost (MemorySaver clears)

---

## 🚀 How to Test

1. **Restart both servers:**
```bash
# AI Backend
cd ai-backend
source ../.venv/bin/activate
uvicorn chatbot_api:app --host 0.0.0.0 --port 8000 --reload

# Express Backend
cd express-backend
npm run dev
```

2. **Fully restart Flutter app** (not hot reload):
```bash
flutter run
```

3. **Test Flow:**
   - Open article → Ask AI
   - Send 2-3 questions
   - Close dialog
   - Open Ask AI again
   - ✅ Previous messages should appear!

---

## 🐛 Known Limitations

1. **Memory cleared on server restart**
   - LangGraph uses in-memory storage
   - Production needs Redis/PostgreSQL

2. **UserId tied to device**
   - Different device = Different userId
   - For cross-device sync, need user authentication

3. **No delete history feature**
   - Currently no way to clear chat history
   - Can add later if needed

---

## 🔮 Future Enhancements

1. **Persistent Storage:** Move from in-memory to Redis
2. **User Authentication:** Sync across devices
3. **Clear History Button:** Let users delete conversations
4. **Export Chat:** Download chat as PDF/text
5. **Share Conversation:** Share AI chat with others

---

## 📝 Files Modified

### Backend Files
- `/mnt/c/news_app/ai-backend/chatbot_api.py` - Added history endpoint
- `/mnt/c/news_app/express-backend/routes/ai-chatbot.js` - Added history route

### Frontend Files
- `/mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/urls/url.dart` - Added history URL
- `/mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/api_controller/ai_controller.dart` - Added getChatHistory()
- `/mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/widgets/ask_ai_dialog.dart` - Major refactor for history

---

**Implementation Date:** October 2025
**Status:** ✅ Ready to Test
