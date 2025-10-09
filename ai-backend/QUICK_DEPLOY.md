# Quick Deploy Guide - AI Backend to AWS

## 🚀 Fastest Way to Deploy

### Method 1: AWS Elastic Beanstalk (5 minutes)

#### 1. Install EB CLI
```bash
pip install awsebcli
```

#### 2. Configure AWS Credentials
```bash
aws configure
# Enter your AWS Access Key ID
# Enter your AWS Secret Access Key
# Enter region: us-east-1
```

#### 3. Initialize EB in ai-backend folder
```bash
cd /mnt/c/news_app/ai-backend
eb init -p python-3.12 news-ai-chatbot --region us-east-1
```

#### 4. Set Environment Variables
```bash
eb setenv OPENAI_API_KEY=your_openai_api_key_here
eb setenv ENVIRONMENT=production
eb setenv ALLOWED_ORIGINS=*
```

#### 5. Create and Deploy
```bash
eb create news-ai-chatbot-env
```

#### 6. Get Your URL
```bash
eb status
# Look for CNAME - that's your API URL
```

#### 7. Test
```bash
eb open
# Or manually visit: http://your-app-name.elasticbeanstalk.com/health
```

---

## ✅ Verify Deployment

```bash
# Check health
curl http://your-app-url/health

# Expected response:
# {"status":"healthy","service":"ai-chatbot","version":"1.0.0","environment":"production"}
```

---

## 🔄 Update After Changes

```bash
cd /mnt/c/news_app/ai-backend
eb deploy
```

---

## 🔧 Troubleshooting

### View Logs
```bash
eb logs
```

### SSH into Instance
```bash
eb ssh
```

### Check Environment Status
```bash
eb health
```

### Terminate Environment (if needed)
```bash
eb terminate news-ai-chatbot-env
```

---

## 📝 Update Express Backend

After deployment, update your Express backend:

**File:** `express-backend/.env`
```env
AI_API_URL=http://your-app-name.elasticbeanstalk.com
```

---

## 💡 Pro Tips

1. **Custom Domain:** Add your domain in AWS Route 53
2. **HTTPS:** Enable SSL in EB console
3. **Auto Scaling:** Configure in EB console
4. **Monitoring:** Enable CloudWatch logs

---

## 📊 Default Configuration

- **Instance Type:** t2.small
- **Port:** 8000
- **Health Check:** /health endpoint
- **Auto Scaling:** Enabled (1-4 instances)
- **Python Version:** 3.12

---

## ⚠️ Important Notes

- Keep your OPENAI_API_KEY secret
- For production, set specific ALLOWED_ORIGINS
- Monitor costs in AWS Billing Dashboard
- First deployment takes 5-10 minutes

---

## 🎯 Next Steps

1. Test all API endpoints
2. Update mobile app configuration
3. Set up monitoring alerts
4. Configure custom domain (optional)

---

**Need detailed instructions?** See `AWS_DEPLOYMENT.md`
