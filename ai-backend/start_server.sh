#!/bin/bash
cd /mnt/c/news_app
source .venv/bin/activate
cd ai-backend
uvicorn chatbot_api:app --host 0.0.0.0 --port 8000 --reload
