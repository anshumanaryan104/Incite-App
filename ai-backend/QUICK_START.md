# AI Chatbot - Quick Start Guide

## 🚀 5 Minutes Setup

### 1️⃣ Start AI Backend (Port 8000)
```bash
cd /mnt/c/news_app/ai-backend
./start_server.sh
```

### 2️⃣ Start Express Backend (Port 3000)
```bash
cd /mnt/c/news_app/express-backend
npm run dev
```

### 3️⃣ Test It!
```bash
curl http://localhost:8000/health
curl http://localhost:3000/api/ai-status
```

---

## 📡 API Endpoints

### For Frontend/Mobile App

**Ask AI about an article:**
```
POST http://localhost:3000/api/ask-ai
```

**Request:**
```json
{
  "articleId": "123",
  "title": "Article Title",
  "summary": "Summary",
  "contents": "Full content",
  "question": "Your question?",
  "userId": "user_id"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "answer": "AI's answer here",
    "thread_id": "user_id",
    "article_id": "123",
    "question": "Your question?"
  }
}
```

---

## 🔧 Configuration Files

### ai-backend/.env
```env
OPENAI_API_KEY=your_key_here
```

### express-backend/.env
```env
AI_API_URL=http://localhost:8000
```

---

## 🧪 Testing

**Test AI directly:**
```bash
cd /mnt/c/news_app
source .venv/bin/activate
python ai-backend/test_api.py
```

**Test via Express:**
```bash
curl -X POST http://localhost:3000/api/ask-ai \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","contents":"Content","question":"What?","userId":"1"}'
```

---

## 📁 Important Files

```
ai-backend/
├── chatbot_api.py      ← Main AI server
├── start_server.sh     ← Easy startup
├── .env                ← OpenAI key here
└── README.md           ← Full docs

express-backend/
├── routes/ai-chatbot.js  ← AI routes
└── .env                  ← Add AI_API_URL
```

---

## 🆘 Quick Fixes

**AI won't start?**
```bash
source .venv/bin/activate
pip install fastapi uvicorn langchain-openai langgraph
```

**Express can't connect?**
```bash
# Check AI is running
curl http://localhost:8000/health
```

**OpenAI errors?**
- Check API key in `ai-backend/.env`
- Verify you have credits

---

## 📊 Ports

- **8000** - AI Backend (FastAPI)
- **3000** - Express Backend
- **5173** - Admin Web Panel
- **Flutter** - Uses Express (3000)

---

For detailed docs, see:
- `ai-backend/README.md`
- `AI_INTEGRATION_SUMMARY.md`
