# AI Chatbot Backend

FastAPI-based backend for LangGraph AI chatbot that answers questions about news articles.

## 🚀 Quick Start

### 1. Setup Environment

```bash
# Navigate to project root
cd /mnt/c/news_app

# Activate virtual environment
source .venv/bin/activate

# Install dependencies (if not already installed)
pip install fastapi uvicorn langchain-openai langgraph python-dotenv
```

### 2. Configure Environment Variables

Make sure `.env` file in `ai-backend/` folder has your OpenAI API key:

```env
OPENAI_API_KEY=your_openai_api_key_here
```

### 3. Start the Server

**Option 1: Using the start script (Recommended)**
```bash
cd ai-backend
./start_server.sh
```

**Option 2: Using uvicorn directly**
```bash
cd /mnt/c/news_app
source .venv/bin/activate
cd ai-backend
uvicorn chatbot_api:app --host 0.0.0.0 --port 8000 --reload
```

**Option 3: Using python directly**
```bash
cd /mnt/c/news_app
source .venv/bin/activate
python ai-backend/chatbot_api.py
```

Server will start on: **http://localhost:8000**

---

## 📡 API Endpoints

### 1. Health Check
```http
GET /health
```

**Response:**
```json
{
  "status": "healthy",
  "service": "ai-chatbot"
}
```

### 2. Chat with AI
```http
POST /api/chat
```

**Request Body:**
```json
{
  "title": "Article Title",
  "summary": "Article Summary",
  "contents": "Full article content",
  "question": "User's question about the article",
  "thread_id": "unique-user-id"
}
```

**Response:**
```json
{
  "answer": "AI generated answer based on article content",
  "thread_id": "unique-user-id"
}
```

**Example cURL:**
```bash
curl -X POST "http://localhost:8000/api/chat" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "India Launches Chandrayaan-4",
    "summary": "ISRO successfully launched Chandrayaan-4 mission",
    "contents": "The Indian Space Research Organisation (ISRO) achieved a major milestone today...",
    "question": "When was the mission launched?",
    "thread_id": "user123"
  }'
```

---

## 🔗 Integration with Express Backend

The Express backend already has AI integration built-in. Check `express-backend/routes/ai-chatbot.js`.

### Using from Express API

**Endpoint:** `POST /api/ask-ai`

**Request Body:**
```json
{
  "articleId": "123",
  "title": "Article Title",
  "summary": "Article Summary",
  "contents": "Full article content",
  "question": "User's question",
  "userId": "user123"
}
```

**Example from Flutter/Mobile App:**
```dart
final response = await http.post(
  Uri.parse('http://10.0.2.2:3000/api/ask-ai'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'articleId': '123',
    'title': article.title,
    'summary': article.description,
    'contents': article.content,
    'question': 'What is this article about?',
    'userId': currentUserId,
  }),
);
```

### Check AI Service Status

**Endpoint:** `GET /api/ai-status`

**Response:**
```json
{
  "success": true,
  "message": "AI service is running",
  "data": {
    "status": "healthy",
    "service": "ai-chatbot"
  }
}
```

---

## 🧪 Testing

### Test the AI Backend Directly

```bash
# Make sure server is running first
cd /mnt/c/news_app
source .venv/bin/activate
python ai-backend/test_api.py
```

### Test via Express Backend

```bash
curl -X POST "http://localhost:3000/api/ask-ai" \
  -H "Content-Type: application/json" \
  -d '{
    "articleId": "1",
    "title": "Breaking News",
    "summary": "Important news update",
    "contents": "Full article content here",
    "question": "What is this about?",
    "userId": "test_user"
  }'
```

---

## 🏗️ Architecture

```
┌─────────────────┐
│  Flutter App    │
│  (Mobile)       │
└────────┬────────┘
         │
         │ HTTP Request
         ▼
┌─────────────────┐
│ Express Backend │
│   (Port 3000)   │
└────────┬────────┘
         │
         │ HTTP Request
         ▼
┌─────────────────┐
│  FastAPI        │
│ AI Chatbot      │
│  (Port 8000)    │
└────────┬────────┘
         │
         │ API Call
         ▼
┌─────────────────┐
│   OpenAI        │
│     GPT-5       │
└─────────────────┘
```

---

## 📝 Features

### AI Capabilities
- ✅ Answer questions about article content
- ✅ Web search integration (when article doesn't have enough info)
- ✅ Context-aware responses
- ✅ Conversation memory (per user thread)
- ✅ Concise and factual answers (2-3 sentences max)
- ✅ Article relevance checking

### LangGraph Features
- Uses **StateGraph** for workflow management
- **MemorySaver** for conversation history
- **Tool binding** for web search capability
- **Thread-based memory** for multi-user support

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|-----------|
| API Framework | FastAPI |
| AI Framework | LangGraph + LangChain |
| LLM | OpenAI GPT-5 |
| State Management | LangGraph StateGraph |
| Memory | MemorySaver (in-memory) |
| Server | Uvicorn (ASGI) |

---

## 🔧 Troubleshooting

### AI Backend not starting
```bash
# Check if port 8000 is already in use
lsof -i :8000

# Kill the process if needed
kill -9 <PID>
```

### Express can't connect to AI Backend
```bash
# Check AI backend status
curl http://localhost:8000/health

# Check environment variable in Express
cat express-backend/.env | grep AI_API_URL
```

### OpenAI API errors
- Check your API key in `.env` file
- Verify API key has credits
- Check OpenAI service status

---

## 📚 Additional Resources

- [FastAPI Documentation](https://fastapi.tiangolo.com/)
- [LangGraph Documentation](https://langchain-ai.github.io/langgraph/)
- [LangChain Documentation](https://python.langchain.com/)
- [OpenAI API Documentation](https://platform.openai.com/docs/)

---

## 🚦 Running Everything Together

### Terminal 1: AI Backend
```bash
cd /mnt/c/news_app/ai-backend
./start_server.sh
```

### Terminal 2: Express Backend
```bash
cd /mnt/c/news_app/express-backend
npm run dev
```

### Terminal 3: Admin Web Panel
```bash
cd /mnt/c/news_app/admin-web
npm run dev
```

### Terminal 4: Flutter App
```bash
cd /mnt/c/news_app/Flutter-App-Code/incite-3.0
flutter run
```

---

Last Updated: October 2025
Project: News App AI Integration
