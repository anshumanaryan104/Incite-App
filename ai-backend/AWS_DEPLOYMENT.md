# AWS Deployment Guide - AI Chatbot Backend

## 📋 Pre-Deployment Checklist

- [ ] AWS Account with appropriate permissions
- [ ] AWS CLI installed and configured
- [ ] OpenAI API key
- [ ] All dependencies listed in requirements.txt
- [ ] Environment variables configured

---

## 🚀 Deployment Options

### Option 1: AWS Elastic Beanstalk (Recommended)

#### Step 1: Install EB CLI
```bash
pip install awsebcli
```

#### Step 2: Initialize Elastic Beanstalk
```bash
cd /path/to/ai-backend
eb init -p python-3.12 news-ai-chatbot --region us-east-1
```

#### Step 3: Create Environment Configuration
Create `.ebextensions/python.config`:
```yaml
option_settings:
  aws:elasticbeanstalk:application:environment:
    PYTHONPATH: "/var/app/current:$PYTHONPATH"
  aws:elasticbeanstalk:container:python:
    WSGIPath: chatbot_api:app
```

#### Step 4: Create Procfile
```
web: uvicorn chatbot_api:app --host 0.0.0.0 --port 8000
```

#### Step 5: Set Environment Variables
```bash
eb setenv OPENAI_API_KEY=your_openai_api_key_here
eb setenv ENVIRONMENT=production
eb setenv ALLOWED_ORIGINS=https://yourdomain.com
eb setenv PORT=8000
```

#### Step 6: Deploy
```bash
eb create news-ai-chatbot-env
eb deploy
```

#### Step 7: Check Status
```bash
eb status
eb health
eb logs
```

---

### Option 2: AWS EC2 (Manual Setup)

#### Step 1: Launch EC2 Instance
1. Go to AWS EC2 Dashboard
2. Launch Instance:
   - AMI: Ubuntu Server 22.04 LTS
   - Instance Type: t2.micro (Free tier) or t2.small
   - Security Group: Allow ports 22 (SSH), 8000 (API), 80 (HTTP), 443 (HTTPS)

#### Step 2: Connect to EC2
```bash
ssh -i your-key.pem ubuntu@your-ec2-ip
```

#### Step 3: Install Dependencies
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Python 3.12
sudo apt install python3.12 python3-pip python3-venv -y

# Install nginx (optional, for reverse proxy)
sudo apt install nginx -y
```

#### Step 4: Setup Application
```bash
# Clone or upload your code
cd /home/ubuntu
mkdir ai-backend
cd ai-backend

# Upload files using scp or git
# scp -i your-key.pem -r /local/path/ai-backend/* ubuntu@your-ec2-ip:/home/ubuntu/ai-backend/

# Create virtual environment
python3 -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
```

#### Step 5: Configure Environment
```bash
# Create .env file
nano .env

# Add your configuration:
OPENAI_API_KEY=your_key_here
ENVIRONMENT=production
PORT=8000
ALLOWED_ORIGINS=*
```

#### Step 6: Setup Systemd Service
```bash
sudo nano /etc/systemd/system/ai-chatbot.service
```

Add this content:
```ini
[Unit]
Description=AI Chatbot FastAPI Service
After=network.target

[Service]
User=ubuntu
Group=ubuntu
WorkingDirectory=/home/ubuntu/ai-backend
Environment="PATH=/home/ubuntu/ai-backend/venv/bin"
ExecStart=/home/ubuntu/ai-backend/venv/bin/uvicorn chatbot_api:app --host 0.0.0.0 --port 8000
Restart=always

[Install]
WantedBy=multi-user.target
```

#### Step 7: Start Service
```bash
sudo systemctl daemon-reload
sudo systemctl start ai-chatbot
sudo systemctl enable ai-chatbot
sudo systemctl status ai-chatbot
```

#### Step 8: Configure Nginx (Optional)
```bash
sudo nano /etc/nginx/sites-available/ai-chatbot
```

Add this configuration:
```nginx
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/ai-chatbot /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

### Option 3: AWS Lambda + API Gateway (Serverless)

#### Step 1: Install Mangum
Add to requirements.txt:
```
mangum==0.17.0
```

#### Step 2: Modify chatbot_api.py
Add at the end of the file:
```python
from mangum import Mangum

handler = Mangum(app)
```

#### Step 3: Create Deployment Package
```bash
cd ai-backend
pip install -r requirements.txt -t package/
cp chatbot_api.py package/
cd package
zip -r ../deployment-package.zip .
```

#### Step 4: Create Lambda Function
1. Go to AWS Lambda Console
2. Create Function:
   - Runtime: Python 3.12
   - Upload deployment-package.zip
   - Set handler: chatbot_api.handler
   - Memory: 1024 MB
   - Timeout: 30 seconds

#### Step 5: Set Environment Variables
In Lambda configuration, add:
- OPENAI_API_KEY
- ENVIRONMENT=production
- ALLOWED_ORIGINS

#### Step 6: Create API Gateway
1. Create HTTP API
2. Add integration to Lambda function
3. Create routes for all endpoints
4. Deploy API

---

## 🔒 Security Best Practices

### 1. Environment Variables
```bash
# Never commit .env file
echo ".env" >> .gitignore

# Use AWS Secrets Manager for production
aws secretsmanager create-secret \
    --name news-ai-chatbot/openai-key \
    --secret-string "your_api_key_here"
```

### 2. CORS Configuration
Update ALLOWED_ORIGINS in production:
```bash
export ALLOWED_ORIGINS=https://yourdomain.com,https://admin.yourdomain.com
```

### 3. HTTPS/SSL
- Use AWS Certificate Manager for SSL certificates
- Enable HTTPS on load balancer or CloudFront

### 4. API Rate Limiting
Add rate limiting middleware in production

---

## 📊 Monitoring & Logging

### CloudWatch Logs (AWS)
```bash
# View logs in Elastic Beanstalk
eb logs

# View logs in EC2
sudo journalctl -u ai-chatbot -f

# View Lambda logs
aws logs tail /aws/lambda/news-ai-chatbot --follow
```

### Health Check Endpoint
```bash
curl http://your-domain.com/health
```

Expected Response:
```json
{
  "status": "healthy",
  "service": "ai-chatbot",
  "version": "1.0.0",
  "environment": "production"
}
```

---

## 🔧 Troubleshooting

### Issue: Service won't start
```bash
# Check logs
sudo journalctl -u ai-chatbot -n 50

# Check if port is in use
sudo netstat -tulpn | grep 8000

# Restart service
sudo systemctl restart ai-chatbot
```

### Issue: OpenAI API errors
- Verify API key is correct
- Check OpenAI account has credits
- Review rate limits

### Issue: High memory usage
- Increase EC2 instance size
- Adjust Lambda memory allocation
- Monitor with CloudWatch

---

## 💰 Cost Estimation

### Elastic Beanstalk
- t2.micro: ~$8/month (Free tier eligible)
- t2.small: ~$17/month

### EC2
- t2.micro: ~$8/month (Free tier eligible)
- t2.small: ~$17/month
- Additional: EBS storage, data transfer

### Lambda
- Free tier: 1M requests/month
- After: $0.20 per 1M requests
- Compute: $0.0000166667 per GB-second

### Additional Costs
- OpenAI API: Variable based on usage
- Data Transfer: ~$0.09/GB
- Load Balancer: ~$16/month (if used)

---

## 🔄 CI/CD Setup (GitHub Actions)

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to AWS

on:
  push:
    branches: [ main ]
    paths:
      - 'ai-backend/**'

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v2

    - name: Setup Python
      uses: actions/setup-python@v2
      with:
        python-version: '3.12'

    - name: Install EB CLI
      run: pip install awsebcli

    - name: Deploy to EB
      env:
        AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
        AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
      run: |
        cd ai-backend
        eb deploy news-ai-chatbot-env
```

---

## 📝 Post-Deployment

### 1. Update Express Backend
Update `express-backend/.env`:
```env
AI_API_URL=https://your-deployed-api-url.com
```

### 2. Test Endpoints
```bash
# Health check
curl https://your-api-url.com/health

# Test chat endpoint
curl -X POST https://your-api-url.com/api/chat \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Article",
    "summary": "Test summary",
    "contents": "Test content",
    "question": "What is this about?",
    "thread_id": "test123"
  }'
```

### 3. Monitor Performance
- Set up CloudWatch alarms
- Monitor API latency
- Track error rates
- Monitor OpenAI API usage

---

## 📚 Additional Resources

- [AWS Elastic Beanstalk Python Guide](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/create-deploy-python-apps.html)
- [FastAPI Deployment](https://fastapi.tiangolo.com/deployment/)
- [AWS Lambda with Python](https://docs.aws.amazon.com/lambda/latest/dg/python-handler.html)
- [OpenAI API Best Practices](https://platform.openai.com/docs/guides/production-best-practices)

---

Last Updated: October 2025
Project: News App AI Backend Deployment
