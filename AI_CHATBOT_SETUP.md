# AI Chatbot - Complete Setup & Troubleshooting Guide

## ✅ Final Working Configuration (October 2025)

### Architecture Overview
```
Flutter App (Port: varies)
    ↓
Express Backend (Port: 3000)
    ↓
AI Backend - FastAPI (Port: 8000)
    ↓
GPT-5 (OpenAI) + LangGraph MemorySaver
```

---

## 🚀 Quick Start Commands

### 1. Start AI Backend (FastAPI + GPT-5)
```bash
cd /mnt/c/news_app
source .venv/bin/activate
cd ai-backend
uvicorn chatbot_api:app --host 0.0.0.0 --port 8000 --reload
```

**Check if running:**
```bash
curl http://localhost:8000/health
# Should return: {"status":"healthy","service":"ai-chatbot"}
```

---

### 2. Start Express Backend
```bash
cd /mnt/c/news_app/express-backend
npm run dev
```

**Check if running:**
```bash
curl http://localhost:3000/api/ai-status
# Should return: {"success":true,"message":"AI service is running",...}
```

---

### 3. Run Flutter App
```bash
cd /mnt/c/news_app/Flutter-App-Code/incite-3.0
flutter pub get
flutter run
```

---

## 🔧 Critical Files & Their Key Settings

### 1. `/mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/main.dart`

**MUST HAVE GetStorage Initialization:**
```dart
import 'package:get_storage/get_storage.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // ✅ CRITICAL: Initialize GetStorage for AI chat history
  await GetStorage.init();

  // ... rest of code
}
```

**Why:** Without this, userId regenerates on every app restart, breaking chat history persistence.

---

### 2. `/mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/widgets/ask_ai_dialog.dart`

**Key Logic - Session Initialization (Lines 65-116):**
```dart
Future<void> _initializeSession() async {
    setState(() => _isLoading = true);

    try {
      // First, try to load chat history
      final history = await AIController.getChatHistory(
        articleId: widget.articleId,
        userId: _threadId,
      );

      // ✅ CRITICAL: Always initialize session (even if history exists)
      // This ensures Express backend has the session cached
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
        _scrollToBottom();
        return;
      }

      // No history - show greeting message
      setState(() {
        _isSessionInitialized = true;
        _isLoading = false;
        _messages.add({
          'type': 'ai',
          'text': 'Hello! I\'m your AI assistant...',
        });
      });

      _scrollToBottom();
    } catch (e) {
      setState(() => _isLoading = false);
      _showError('Failed to start AI chat. Please try again.');
    }
  }
```

**Why:** Previous code only initialized session when no history existed, causing "Session expired" errors.

---

### 3. `/mnt/c/news_app/express-backend/routes/ai-chatbot.js`

**CRITICAL: Timeout Settings (Lines 95 & 189):**

```javascript
// Init endpoint (line 90-97)
await axios.post(
    `${AI_API_URL}/api/chat`,
    aiRequest,
    {
        headers: { 'Content-Type': 'application/json' },
        timeout: 90000 // ✅ MUST be 90 seconds for GPT-5 with web search
    }
);

// Query endpoint (line 184-191)
const aiResponse = await axios.post(
    `${AI_API_URL}/api/chat`,
    aiRequest,
    {
        headers: { 'Content-Type': 'application/json' },
        timeout: 90000 // ✅ MUST be 90 seconds for GPT-5 with web search
    }
);
```

**Why:** GPT-5 takes 40-60 seconds when doing web search. 30-second timeout causes failures.

---

### 4. `/mnt/c/news_app/ai-backend/chatbot_api.py`

**Current Configuration:**
```python
from langgraph.checkpoint.memory import MemorySaver
from langchain_openai import ChatOpenAI

# Memory storage (in-memory)
memory = MemorySaver()

# GPT-5 Model
llm = ChatOpenAI(
    model="gpt-5",
    temperature=0,
    api_key=os.getenv("OPENAI_API_KEY")
)
```

**Note:** Uses in-memory MemorySaver - chat history stored in RAM, lost on server restart.

---

## 🐛 Common Issues & Solutions

### Issue 1: "Session expired. Please restart Ask AI"
**Symptom:** Error when asking questions after loading history
**Cause:** Express session not initialized when history exists
**Fix:** Always call `initializeSession()` even when history is loaded (already fixed in code above)

---

### Issue 2: "timeout of 30000ms exceeded" or "timeout of 60000ms exceeded"
**Symptom:** 500 error after 30-60 seconds when asking questions
**Cause:** GPT-5 takes longer (40-60s) for complex questions with web search
**Fix:** Set timeout to 90000ms in express-backend/routes/ai-chatbot.js (lines 95 & 189)

**Verify timeout setting:**
```bash
grep -n "timeout: " /mnt/c/news_app/express-backend/routes/ai-chatbot.js
# Should show: 95:    timeout: 90000
#              189:    timeout: 90000
```

---

### Issue 3: Chat history not persisting across app restarts
**Symptom:** History shows when closing/reopening dialog, but lost on app restart
**Cause:** GetStorage not initialized in main.dart
**Fix:** Add `await GetStorage.init();` in main() function (see above)

**Verify GetStorage is initialized:**
```bash
grep -A2 "GetStorage.init" /mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/main.dart
# Should show the initialization code
```

---

### Issue 4: Express server not reloading after code changes
**Symptom:** Changes to routes/ai-chatbot.js not taking effect
**Cause:** Nodemon not detecting file changes or multiple server instances running

**Fix:**
```bash
# Kill all Express instances
lsof -ti:3000 | xargs kill -9

# Kill all nodemon processes
ps aux | grep nodemon | grep -v grep | awk '{print $2}' | xargs kill -9

# Restart Express
cd /mnt/c/news_app/express-backend
npm run dev
```

---

### Issue 5: AI not answering questions from article content
**Symptom:** AI does web search instead of using article content
**Cause:** Article has no real content (gibberish or empty)

**Example of bad article:**
- Article 17: Content = "dihsjdhjshajhjahfhajkhjhkakhdkhakjdkjjjjkhduiu" (gibberish)

**Example of good article:**
- Article 26: Proper SOAR initiative content

**Check article content:**
```bash
curl -s http://localhost:3000/api/blog-detail/26 | python3 -c "import sys, json; d = json.load(sys.stdin)['data']; print('Title:', d['title'][:80]); print('Content length:', len(d.get('content', '')))"
```

**Fix:** Use articles with actual content, or add proper content to existing articles via admin panel.

---

## 📝 Testing Checklist

### Test 1: Check all servers are running
```bash
# AI Backend (should return health status)
curl http://localhost:8000/health

# Express Backend (should return AI service status)
curl http://localhost:3000/api/ai-status

# Check processes
ps aux | grep "uvicorn\|nodemon" | grep -v grep
```

---

### Test 2: Test full AI flow with curl
```bash
# Step 1: Initialize session (replace articleId with real article)
curl -X POST http://localhost:3000/api/ask-ai/init \
  -H "Content-Type: application/json" \
  -d '{"articleId": 26, "userId": "test_user_123"}' \
  -w "\nTime: %{time_total}s\n"

# Step 2: Ask a question
curl -X POST http://localhost:3000/api/ask-ai/query \
  -H "Content-Type: application/json" \
  -d '{"question": "What is this article about?", "userId": "test_user_123"}' \
  -w "\nTime: %{time_total}s\n" \
  --max-time 95

# Step 3: Get chat history
curl -X POST http://localhost:3000/api/ask-ai/history \
  -H "Content-Type: application/json" \
  -d '{"userId": "test_user_123", "articleId": 26}'
```

---

### Test 3: Verify timeout settings
```bash
# Should show 90000 on lines 95 and 189
grep -n "timeout: [0-9]" /mnt/c/news_app/express-backend/routes/ai-chatbot.js
```

---

### Test 4: Check Flutter GetStorage initialization
```bash
# Should show GetStorage.init() in main function
grep -B2 -A2 "GetStorage.init" /mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/main.dart
```

---

## 🔄 Complete Restart Procedure

**If everything is broken, follow these steps in order:**

### 1. Kill all processes
```bash
# Kill AI backend
lsof -ti:8000 | xargs kill -9

# Kill Express backend
lsof -ti:3000 | xargs kill -9

# Kill nodemon
ps aux | grep nodemon | grep -v grep | awk '{print $2}' | xargs kill -9

# Verify all killed
ps aux | grep "uvicorn\|nodemon\|node server.js" | grep -v grep
```

---

### 2. Verify all critical code is correct
```bash
# Check timeout is 90000
grep "timeout: 90000" /mnt/c/news_app/express-backend/routes/ai-chatbot.js

# Check GetStorage.init exists
grep "GetStorage.init" /mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/main.dart

# Check session initialization logic
grep -A5 "Always initialize session" /mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/widgets/ask_ai_dialog.dart
```

---

### 3. Start AI Backend (FastAPI)
```bash
cd /mnt/c/news_app
source .venv/bin/activate
cd ai-backend
uvicorn chatbot_api:app --host 0.0.0.0 --port 8000 --reload &

# Wait 3 seconds
sleep 3

# Test
curl http://localhost:8000/health
```

---

### 4. Start Express Backend
```bash
cd /mnt/c/news_app/express-backend
npm run dev &

# Wait 3 seconds
sleep 3

# Test
curl http://localhost:3000/api/ai-status
```

---

### 5. Run Flutter App
```bash
cd /mnt/c/news_app/Flutter-App-Code/incite-3.0
flutter run
```

---

### 6. Test the complete flow
1. Open an article with **actual content** (e.g., Article 26 - SOAR Initiative)
2. Click "Ask AI" button
3. Wait for greeting message (or history to load)
4. Ask a question: "What is this article about?"
5. Wait up to 60 seconds for response
6. Close dialog and reopen - history should persist
7. Restart app - history should still persist

---

## 📊 Performance Expectations

### Response Times:
- **Session Init**: 8-15 seconds (GPT-5 context setup)
- **Simple questions** (from article): 5-10 seconds
- **Complex questions** (web search): 40-60 seconds
- **Chat history load**: < 1 second

### Timeout Settings:
- Init endpoint: **90 seconds**
- Query endpoint: **90 seconds**
- History endpoint: **10 seconds**

---

## 🗂️ Thread ID Pattern

**Format:** `{userId}_article_{articleId}`

**Example:** `user_1759788249665_article_26`

**Why:** Creates unique conversation threads per user + article combination, allowing separate histories for different articles.

---

## 🔑 Key Environment Variables

### AI Backend (.env in /mnt/c/news_app/ai-backend/)
```
OPENAI_API_KEY=your_openai_key_here
```

### Express Backend (.env in /mnt/c/news_app/express-backend/)
```
AI_API_URL=http://localhost:8000
SUPABASE_URL=your_supabase_url
SUPABASE_KEY=your_supabase_key
```

---

## 📱 Network Configuration

### For Android Emulator:
- Use: `http://10.0.2.2:3000`

### For Physical Device (WSL):
```bash
# Get WSL IP
ip addr show eth0 | grep "inet " | awk '{print $2}' | cut -d/ -f1

# Use: http://{WSL_IP}:3000
# Example: http://172.21.158.105:3000
```

**Update in Flutter:** `/mnt/c/news_app/Flutter-App-Code/incite-3.0/lib/urls/url.dart`

---

## 🎯 Summary of All Fixes Applied

1. ✅ Added `GetStorage.init()` in main.dart for persistent userId
2. ✅ Modified session initialization to ALWAYS init Express session (even with history)
3. ✅ Increased axios timeout from 30s → 90s in both init and query endpoints
4. ✅ Fixed Express server restart issues by manually killing processes

---

## 📞 Troubleshooting Contacts

**Check logs:**
```bash
# AI Backend logs
tail -f /mnt/c/news_app/ai-backend/server.log

# Express Backend (in terminal where npm run dev is running)
# Logs appear in console output
```

**Common error patterns:**
- `timeout of X ms exceeded` → Increase timeout to 90000ms
- `Session expired` → Check session initialization logic
- `EADDRINUSE` → Port already in use, kill existing process
- `Failed to initialize session` → Check AI backend is running on port 8000

---

## 🚀 Production Deployment Notes

**Current setup uses:**
- In-memory session storage (Express: Map)
- In-memory chat history (LangGraph: MemorySaver)
- Local WSL networking

**For production, consider:**
1. **Redis** for session storage (replace Map)
2. **PostgreSQL** for chat history (replace MemorySaver)
3. **Deploy backends** to Railway.app/Render.com/VPS
4. **Update Flutter URLs** to production endpoints

---

**Last Updated:** October 7, 2025
**Status:** ✅ Fully Working
**Tested With:** Flutter 3.9, Node.js 18, Python 3.11, GPT-5
