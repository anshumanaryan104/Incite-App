# 🚀 Production Deployment Guide - AI Chatbot with URL Fetching

## Overview

This guide covers deploying the updated AI chatbot feature to AWS EC2 that uses `source_url` to fetch complete articles instead of limited content.

---

## 📋 Pre-Deployment Checklist

### Local Testing Complete ✅
- [x] Express backend sends `source_url`
- [x] AI backend receives and processes `source_url`
- [x] GPT-5 fetches articles from URLs
- [x] End-to-end workflow tested locally

### What's Changed
1. Express Backend: Routes and serializer updated
2. AI Backend: Changed from 'contents' to 'source_url'
3. Environment: Python 3.12 required (not 3.14)

---

## 🔧 Part 1: Express Backend Deployment (EC2)

### Step 1: Connect to EC2

```bash
# SSH into your EC2 instance
ssh -i your-key.pem ubuntu@15.206.148.126

# Or if using different user
ssh -i your-key.pem ec2-user@15.206.148.126
```

### Step 2: Backup Current Code

```bash
# Navigate to express backend
cd /home/ubuntu/express-backend  # Adjust path as needed

# Create backup
cp -r . ../express-backend-backup-$(date +%Y%m%d)

# Or if using PM2
pm2 save
```

### Step 3: Update Files

#### Option A: Using Git (Recommended)

```bash
# If using Git
cd /home/ubuntu/express-backend
git pull origin main

# If you haven't pushed changes yet, do this from local:
# cd /mnt/c/news_app/express-backend
# git add .
# git commit -m "Add source_url support for AI chatbot"
# git push origin main
```

#### Option B: Manual File Upload

Upload these files via SCP from local machine:

```bash
# From your local machine (Windows)
# Open Git Bash or PowerShell

cd /mnt/c/news_app/express-backend

# Upload modified files
scp -i your-key.pem routes/ai-chatbot.js ubuntu@15.206.148.126:/home/ubuntu/express-backend/routes/
scp -i your-key.pem services/article-serializer.js ubuntu@15.206.148.126:/home/ubuntu/express-backend/services/
```

### Step 4: Update Environment Variables

```bash
# On EC2 server
cd /home/ubuntu/express-backend
nano .env
```

**Update these lines:**

```env
# AI Backend URL
# If AI backend is on same server:
AI_API_URL=http://localhost:8000

# If AI backend is on different server or port:
# AI_API_URL=http://your-ai-backend-ip:8000

# Verify these are set:
SUPABASE_URL=https://btqfwegkxkabieqojjjr.supabase.co
SUPABASE_ANON_KEY=your_key_here
OPENAI_API_KEY=your_openai_key  # For AI backend
```

Save: `Ctrl+X`, then `Y`, then `Enter`

### Step 5: Restart Express Backend

```bash
# If using PM2 (most common)
pm2 restart express-backend

# Or restart all
pm2 restart all

# Check status
pm2 status
pm2 logs express-backend --lines 50

# If not using PM2
pkill -f node
npm run dev
```

### Step 6: Verify Express Backend

```bash
# Test from EC2 server
curl http://localhost:3000/api/ai-status

# Expected response:
# {"success":false,"message":"AI service is not available"}
# (This is OK - AI backend not running yet)

# Test from your local machine
curl http://15.206.148.126:3000/api/ai-status
```

---

## 🤖 Part 2: AI Backend Deployment (EC2)

### Option A: Deploy on Same EC2 Instance

#### Step 1: Install Python 3.12

```bash
# Connect to EC2
ssh -i your-key.pem ubuntu@15.206.148.126

# Install Python 3.12
sudo apt update
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:deadsnakes/ppa -y
sudo apt update
sudo apt install python3.12 python3.12-venv python3.12-dev -y

# Verify installation
python3.12 --version
# Should show: Python 3.12.x
```

#### Step 2: Upload AI Backend Code

**From Local Machine:**

```bash
# Create tar of ai-backend folder
cd /mnt/c/news_app
tar -czf ai-backend.tar.gz ai-backend/

# Upload to EC2
scp -i your-key.pem ai-backend.tar.gz ubuntu@15.206.148.126:/home/ubuntu/

# Or use Git if repo is set up
```

**On EC2 Server:**

```bash
cd /home/ubuntu
tar -xzf ai-backend.tar.gz
cd ai-backend
```

#### Step 3: Setup Python Environment

```bash
cd /home/ubuntu/ai-backend

# Create virtual environment with Python 3.12
python3.12 -m venv venv

# Activate virtual environment
source venv/bin/activate

# Upgrade pip
pip install --upgrade pip

# Install dependencies
pip install -r requirements.txt

# This will install:
# - fastapi
# - uvicorn[standard]
# - langchain-openai
# - langgraph
# - langchain-core
# - python-dotenv
# - pydantic
# - aiosqlite
```

#### Step 4: Configure Environment

```bash
cd /home/ubuntu/ai-backend

# Create/edit .env file
nano .env
```

**Add:**

```env
# OpenAI Configuration
OPENAI_API_KEY=your_actual_openai_api_key_here

# Environment
ENVIRONMENT=production

# CORS (if needed)
ALLOWED_ORIGINS=*
```

Save: `Ctrl+X`, `Y`, `Enter`

#### Step 5: Test AI Backend

```bash
# Still in venv
cd /home/ubuntu/ai-backend
source venv/bin/activate

# Test run
uvicorn chatbot_api:app --host 0.0.0.0 --port 8000

# In another terminal, test:
curl http://localhost:8000/
# Should return: {"message":"News AI Chatbot API is running","version":"1.0"}

# Stop test (Ctrl+C)
```

#### Step 6: Setup PM2 for AI Backend

```bash
# Install PM2 if not installed
npm install -g pm2

# Create PM2 ecosystem file
cd /home/ubuntu/ai-backend
nano ecosystem.config.js
```

**Add:**

```javascript
module.exports = {
  apps: [{
    name: 'ai-backend',
    script: '/home/ubuntu/ai-backend/venv/bin/uvicorn',
    args: 'chatbot_api:app --host 0.0.0.0 --port 8000',
    cwd: '/home/ubuntu/ai-backend',
    interpreter: '/home/ubuntu/ai-backend/venv/bin/python3.12',
    env: {
      PYTHONPATH: '/home/ubuntu/ai-backend',
    },
    error_file: '/home/ubuntu/ai-backend/logs/error.log',
    out_file: '/home/ubuntu/ai-backend/logs/out.log',
    log_file: '/home/ubuntu/ai-backend/logs/combined.log',
    time: true,
    autorestart: true,
    max_memory_restart: '1G'
  }]
};
```

Save and start:

```bash
# Create logs directory
mkdir -p logs

# Start with PM2
pm2 start ecosystem.config.js

# Check status
pm2 status

# View logs
pm2 logs ai-backend

# Save PM2 configuration
pm2 save

# Setup PM2 to start on system boot
pm2 startup
# Run the command it outputs
```

#### Step 7: Configure Security Group (AWS Console)

1. Go to AWS Console → EC2 → Security Groups
2. Find security group for your EC2 instance
3. Add inbound rule:
   - **Type:** Custom TCP
   - **Port:** 8000
   - **Source:** Custom (0.0.0.0/0 for public, or specific IP)
   - **Description:** AI Backend API

---

### Option B: Deploy on Separate EC2 Instance

If you want AI backend on different server:

1. Launch new EC2 instance (t2.medium recommended for AI workload)
2. Follow same steps as Option A
3. Update Express backend's `.env`:
   ```env
   AI_API_URL=http://new-ai-backend-ip:8000
   ```

---

## 🧪 Part 3: Testing Production Deployment

### Test 1: Check Both Services Running

```bash
# On EC2 server
pm2 status

# Should show:
# express-backend | online
# ai-backend      | online
```

### Test 2: Test Express Backend

```bash
# From your local machine
curl http://15.206.148.126:3000/api/ai-status

# Expected:
# {"success":true,"message":"AI service is running",...}
```

### Test 3: Test AI Init (with Postman or curl)

```bash
# Login first
curl -X POST http://15.206.148.126:3000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"username":"superadmin","password":"admin123"}'

# Copy token from response

# Test AI init
curl -X POST http://15.206.148.126:3000/api/ask-ai/init \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"articleId":134,"userId":"test123"}'

# Should return:
# {"success":true,"data":{...}}
```

### Test 4: Test AI Query

```bash
curl -X POST http://15.206.148.126:3000/api/ask-ai/query \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"question":"What is this article about?","userId":"test123"}'

# Should return AI's answer based on full article
```

### Test 5: Test from Flutter App

Update Flutter app's `lib/urls/url.dart`:

```dart
// Production URL
static const String baseUrl = "http://15.206.148.126:3000";
```

Then test "Ask AI" feature in app.

---

## 📊 Part 4: Monitoring & Logs

### View Logs

```bash
# Express backend logs
pm2 logs express-backend --lines 100

# AI backend logs
pm2 logs ai-backend --lines 100

# All logs
pm2 logs --lines 50

# Follow logs (live)
pm2 logs --lines 0
```

### Monitor Resources

```bash
# CPU and Memory usage
pm2 monit

# Detailed status
pm2 show express-backend
pm2 show ai-backend
```

### Check for Errors

```bash
# Express backend
pm2 logs express-backend --err --lines 50

# AI backend
pm2 logs ai-backend --err --lines 50
```

---

## 🔥 Part 5: Rollback Plan (If Issues)

### Express Backend Rollback

```bash
# Stop current
pm2 stop express-backend

# Restore backup
cd /home/ubuntu
rm -rf express-backend
cp -r express-backend-backup-YYYYMMDD express-backend

# Restart
cd express-backend
pm2 restart express-backend
```

### AI Backend Rollback

```bash
# Simply stop AI backend
pm2 stop ai-backend
pm2 delete ai-backend

# Express backend will fall back to timeout/error handling
```

---

## ⚙️ Part 6: Environment-Specific Configuration

### Production .env (Express Backend)

```env
# Server
NODE_ENV=production
PORT=3000

# Database
SUPABASE_URL=https://btqfwegkxkabieqojjjr.supabase.co
SUPABASE_ANON_KEY=your_production_key

# AI Services
AI_API_URL=http://localhost:8000
# OR
AI_API_URL=http://ai-backend-server-ip:8000

# Security
JWT_SECRET=your_strong_production_secret

# Mediastack
MEDIASTACK_API_KEY=your_mediastack_key

# CORS
ALLOWED_ORIGINS=https://yourdomain.com,https://admin.yourdomain.com
```

### Production .env (AI Backend)

```env
# OpenAI
OPENAI_API_KEY=your_production_openai_key

# Environment
ENVIRONMENT=production

# CORS
ALLOWED_ORIGINS=http://15.206.148.126:3000
```

---

## 🔒 Part 7: Security Checklist

### AWS Security Group Rules

**Express Backend (Port 3000):**
- [x] Allow HTTP from 0.0.0.0/0 (or specific IPs)
- [x] Allow SSH from your IP only

**AI Backend (Port 8000):**
- [x] Allow from Express backend IP only (recommended)
- [x] OR Allow from 0.0.0.0/0 if needed publicly

### Environment Variables

- [x] Never commit `.env` files to Git
- [x] Use strong JWT secrets
- [x] Rotate API keys regularly
- [x] Use AWS Secrets Manager (optional, advanced)

### Server Hardening

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Setup firewall (optional)
sudo ufw allow 22
sudo ufw allow 3000
sudo ufw allow 8000
sudo ufw enable
```

---

## 📈 Part 8: Performance Optimization

### PM2 Process Management

```bash
# Set instance count (cluster mode)
pm2 start ecosystem.config.js -i 2  # 2 instances

# Enable memory limits
# Already in ecosystem.config.js:
# max_memory_restart: '1G'
```

### Nginx Reverse Proxy (Optional)

For better performance, add Nginx:

```bash
sudo apt install nginx -y

# Configure Nginx
sudo nano /etc/nginx/sites-available/express-backend
```

```nginx
server {
    listen 80;
    server_name 15.206.148.126;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    location /ai/ {
        proxy_pass http://localhost:8000/;
        proxy_read_timeout 90s;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/express-backend /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🎯 Part 9: Deployment Checklist

### Pre-Deployment
- [x] Local testing complete
- [x] Code committed to Git
- [x] Backup created on server
- [x] .env files configured

### Deployment
- [x] Express backend updated
- [x] AI backend deployed
- [x] PM2 processes started
- [x] Security groups configured

### Post-Deployment
- [x] Services running (pm2 status)
- [x] API endpoints responding
- [x] Logs checked for errors
- [x] Flutter app tested
- [x] Rollback plan ready

---

## 🆘 Part 10: Troubleshooting

### Issue 1: AI Backend Not Starting

```bash
# Check Python version
python3.12 --version

# Check virtual environment
source /home/ubuntu/ai-backend/venv/bin/activate
which python
# Should show: /home/ubuntu/ai-backend/venv/bin/python

# Check dependencies
pip list | grep langchain

# Check logs
pm2 logs ai-backend --err
```

### Issue 2: Express Can't Connect to AI

```bash
# Test AI backend directly
curl http://localhost:8000/

# Check AI_API_URL in .env
cat /home/ubuntu/express-backend/.env | grep AI_API_URL

# Check if port 8000 is listening
netstat -tlnp | grep 8000
```

### Issue 3: 422 Errors from AI

```bash
# Check AI backend logs for validation errors
pm2 logs ai-backend --lines 100

# Verify chatbot_api.py has source_url (not contents)
grep "source_url" /home/ubuntu/ai-backend/chatbot_api.py
```

### Issue 4: High Memory Usage

```bash
# Check memory
free -h

# Restart AI backend
pm2 restart ai-backend

# Reduce PM2 instances if needed
pm2 scale ai-backend 1
```

---

## 📞 Support Commands

### Useful PM2 Commands

```bash
pm2 list                    # List all processes
pm2 restart all             # Restart all
pm2 stop all                # Stop all
pm2 delete all              # Delete all
pm2 logs                    # View all logs
pm2 monit                   # Monitor dashboard
pm2 save                    # Save current config
pm2 startup                 # Auto-start on boot
```

### Useful System Commands

```bash
# Disk space
df -h

# Memory usage
free -h

# CPU info
htop

# Network connections
netstat -tlnp

# Process list
ps aux | grep node
ps aux | grep python
```

---

## 🎉 Success Criteria

Deployment is successful when:

1. ✅ `pm2 status` shows both services online
2. ✅ `curl http://localhost:3000/api/ai-status` returns success
3. ✅ AI init endpoint works (returns 200)
4. ✅ AI query endpoint works (returns answer)
5. ✅ Flutter app "Ask AI" feature works
6. ✅ Logs show no critical errors
7. ✅ Article URL is being fetched by GPT-5

---

## 📝 Post-Deployment Notes

### Update Flutter App URL

```dart
// lib/urls/url.dart
static const String baseUrl = "http://15.206.148.126:3000";
```

### Monitor for 24 Hours

- Check logs regularly
- Monitor memory usage
- Test multiple articles
- Check response times

### Optimize as Needed

- Adjust PM2 instances
- Configure caching
- Setup CDN if needed
- Add monitoring (CloudWatch)

---

**Deployment Guide Complete!** 🚀

For issues or questions, check logs first:
```bash
pm2 logs --lines 100
```

**Last Updated:** October 26, 2025
