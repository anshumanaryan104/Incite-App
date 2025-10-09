# Express Backend Deployment Guide

## 📋 Pre-Deployment Checklist

- [ ] Node.js v16+ installed
- [ ] PM2 installed globally
- [ ] Environment variables configured
- [ ] Supabase database setup complete
- [ ] AI backend deployed and accessible

---

## 🚀 Production-Ready Features

### Security
- ✅ **Helmet.js** - Security headers
- ✅ **Rate Limiting** - DDoS protection (100 req/15min)
- ✅ **CORS** - Configurable origins
- ✅ **Request Size Limits** - 10MB max

### Performance
- ✅ **Compression** - Gzip responses
- ✅ **PM2 Clustering** - Multi-core support
- ✅ **Auto-restart** - On crashes
- ✅ **Memory limits** - 500MB per instance

### Monitoring
- ✅ **Morgan logging** - Request logs
- ✅ **PM2 monitoring** - CPU/Memory tracking
- ✅ **Error logging** - Detailed error logs

---

## 📦 Installation

### Option 1: Same EC2 (Recommended for Testing)

```bash
# Navigate to express-backend
cd ~/Incite-App/express-backend

# Install dependencies
npm install

# Install PM2 globally
sudo npm install -g pm2

# Create logs directory
mkdir -p logs

# Configure environment
cp .env.example .env
nano .env
# Update with your actual values
```

### Option 2: New EC2 Instance (Production)

```bash
# SSH into EC2
ssh -i your-key.pem ubuntu@your-ec2-ip

# Update system
sudo apt update && sudo apt upgrade -y

# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verify installation
node --version
npm --version

# Clone repository
git clone https://github.com/your-username/news_app.git
cd news_app/express-backend

# Install dependencies
npm install

# Install PM2
sudo npm install -g pm2

# Create logs directory
mkdir -p logs
```

---

## ⚙️ Environment Configuration

Create `.env` file:

```env
NODE_ENV=production
PORT=3000

# Supabase
SUPABASE_URL=your_supabase_url
SUPABASE_ANON_KEY=your_anon_key
SUPABASE_SERVICE_KEY=your_service_key

# JWT
JWT_SECRET=your_secure_jwt_secret_minimum_32_chars

# AI Backend
AI_API_URL=http://15.206.148.126:8000

# CORS (comma-separated)
ALLOWED_ORIGINS=*
```

---

## 🎯 Deployment Methods

### Method 1: PM2 (Recommended for Production)

```bash
# Start with PM2 ecosystem
pm2 start ecosystem.config.js --env production

# Check status
pm2 status

# View logs
pm2 logs news-express-backend

# Monitor
pm2 monit

# Setup auto-start on boot
pm2 startup
pm2 save

# Useful commands
pm2 restart news-express-backend
pm2 stop news-express-backend
pm2 delete news-express-backend
```

### Method 2: PM2 Simple Start

```bash
# Start with PM2 (simple)
pm2 start server.js --name news-backend

# View logs
pm2 logs news-backend

# Restart
pm2 restart news-backend
```

### Method 3: Systemd Service

```bash
# Create service file
sudo nano /etc/systemd/system/express-backend.service
```

Add this content:

```ini
[Unit]
Description=News Express Backend
After=network.target

[Service]
User=ubuntu
Group=ubuntu
WorkingDirectory=/home/ubuntu/Incite-App/express-backend
Environment="NODE_ENV=production"
Environment="PORT=3000"
ExecStart=/usr/bin/npm start
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl start express-backend
sudo systemctl enable express-backend
sudo systemctl status express-backend
```

### Method 4: Docker (Advanced)

Create `Dockerfile`:

```dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY . .

EXPOSE 3000

CMD ["npm", "start"]
```

Build and run:

```bash
docker build -t news-express-backend .
docker run -d -p 3000:3000 --env-file .env --name news-backend news-express-backend
```

---

## 🔒 Security Configuration

### 1. Configure EC2 Security Group

Add inbound rules:
- **Port 3000** (TCP) - Express API
- **Port 22** (SSH) - Only from your IP
- **Port 80/443** (HTTP/HTTPS) - If using Nginx reverse proxy

### 2. Update CORS for Production

In `.env`:
```env
ALLOWED_ORIGINS=https://yourdomain.com,https://admin.yourdomain.com
```

### 3. Generate Strong JWT Secret

```bash
node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"
```

---

## 🌐 Nginx Reverse Proxy (Optional)

### Install Nginx

```bash
sudo apt install nginx -y
```

### Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/news-api
```

Add:

```nginx
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
```

Enable:

```bash
sudo ln -s /etc/nginx/sites-available/news-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### SSL with Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d your-domain.com
```

---

## 🧪 Testing

### Health Check

```bash
curl http://your-ec2-ip:3000/
```

Expected response:
```json
{
  "message": "Polymath Express Backend",
  "status": "Running"
}
```

### Test API Endpoints

```bash
# Settings
curl http://your-ec2-ip:3000/api/setting-list

# Blogs
curl http://your-ec2-ip:3000/api/blog-list

# AI Status
curl http://your-ec2-ip:3000/api/ai-status
```

---

## 📊 Monitoring & Logging

### PM2 Monitoring

```bash
# Real-time monitoring
pm2 monit

# CPU/Memory stats
pm2 status

# Logs
pm2 logs news-express-backend

# Error logs only
pm2 logs news-express-backend --err

# Last 100 lines
pm2 logs news-express-backend --lines 100
```

### Log Files

Logs are stored in:
- `./logs/pm2-out.log` - Standard output
- `./logs/pm2-error.log` - Error output

View logs:
```bash
tail -f logs/pm2-out.log
tail -f logs/pm2-error.log
```

---

## 🔄 CI/CD Setup (GitHub Actions)

Create `.github/workflows/deploy-express.yml`:

```yaml
name: Deploy Express Backend

on:
  push:
    branches: [main]
    paths:
      - 'express-backend/**'

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Deploy to EC2
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.EC2_HOST }}
          username: ubuntu
          key: ${{ secrets.EC2_SSH_KEY }}
          script: |
            cd ~/Incite-App/express-backend
            git pull origin main
            npm install --production
            pm2 restart news-express-backend
```

---

## 🔧 Troubleshooting

### Server won't start

```bash
# Check PM2 logs
pm2 logs news-express-backend --err

# Check if port is in use
sudo netstat -tulpn | grep 3000

# Kill process on port 3000
sudo kill -9 $(sudo lsof -t -i:3000)
```

### High memory usage

```bash
# Check PM2 status
pm2 status

# Restart specific instance
pm2 restart news-express-backend

# Adjust memory limit in ecosystem.config.js
max_memory_restart: '1G'
```

### Can't connect to Supabase

```bash
# Test connection
node -e "const {createClient} = require('@supabase/supabase-js'); const supabase = createClient(process.env.SUPABASE_URL, process.env.SUPABASE_ANON_KEY); supabase.from('articles').select('count').then(console.log).catch(console.error)"
```

### Rate limit errors

Update in `server.js`:
```javascript
max: IS_PRODUCTION ? 500 : 1000, // Increase limit
```

---

## 💰 Cost Estimation

### AWS EC2

| Instance | vCPU | RAM | Price/Month |
|----------|------|-----|-------------|
| t2.micro | 1 | 1 GB | $8 (Free tier) |
| t2.small | 1 | 2 GB | $17 |
| t2.medium | 2 | 4 GB | $34 |

### Additional Costs
- Supabase: Free tier (500MB database)
- Data Transfer: ~$0.09/GB
- Load Balancer: ~$16/month (if needed)

**Recommended for production:** t2.small or t2.medium

---

## 📝 Post-Deployment

### 1. Update Flutter App

In Flutter app, update API base URL:

```dart
// lib/urls/url.dart
static const String baseUrl = 'http://your-ec2-ip:3000';
```

### 2. Update Admin Web App

```javascript
// admin-web/src/utils/api.js
const API_URL = 'http://your-ec2-ip:3000/api';
```

### 3. Test All Features

- [ ] Article listing
- [ ] Article details
- [ ] User login/signup
- [ ] Ask AI feature
- [ ] Admin panel CRUD
- [ ] Bookmark sync

### 4. Setup Monitoring Alerts

Use AWS CloudWatch or PM2 Plus for alerts on:
- High CPU usage
- Memory leaks
- Error rates
- Downtime

---

## 📚 Additional Resources

- [PM2 Documentation](https://pm2.keymetrics.io/)
- [Express.js Best Practices](https://expressjs.com/en/advanced/best-practice-performance.html)
- [Node.js Security Checklist](https://blog.risingstack.com/node-js-security-checklist/)
- [AWS EC2 User Guide](https://docs.aws.amazon.com/ec2/)

---

Last Updated: October 2025
Project: News App Express Backend Deployment
