const express = require('express');
const router = express.Router();
const axios = require('axios');
const { supabase } = require('../supabase-client');

// AI Chatbot API endpoint
const AI_API_URL = process.env.AI_API_URL || 'http://localhost:8000';

// In-memory session storage (article context per thread)
// In production, use Redis or database
const sessionStore = new Map();

/**
 * POST /api/ask-ai/init
 * Initialize AI chat session with article context
 * Called when user first clicks "Ask AI" button
 *
 * Request Body:
 * {
 *   "articleId": 17,         // REQUIRED - Article ID to fetch from database
 *   "userId": "user123"      // REQUIRED - User ID for session
 * }
 *
 * Response:
 * {
 *   "success": true,
 *   "data": {
 *     "threadId": "user123",
 *     "article": { id, title, image },
 *     "message": "Session initialized"
 *   }
 * }
 */
router.post('/ask-ai/init', async (req, res) => {
    try {
        const { articleId, userId } = req.body;

        // Validation
        if (!articleId || !userId) {
            return res.status(400).json({
                success: false,
                message: 'Article ID and User ID are required',
                error: 'MISSING_FIELDS'
            });
        }

        console.log(`🎬 Initializing AI session for user ${userId} with article ${articleId}`);

        // Fetch article from Supabase database (with source_url for web scraping)
        const { data: article, error: dbError } = await supabase
            .from('articles')
            .select('id, title, description, featured_image, source_url')
            .eq('id', articleId)
            .eq('status', 'published')
            .single();

        if (dbError || !article) {
            return res.status(404).json({
                success: false,
                message: 'Article not found',
                error: 'ARTICLE_NOT_FOUND'
            });
        }

        console.log(`✅ Article found: "${article.title}"`);
        console.log(`🔗 Source URL: ${article.source_url || 'N/A'}`);

        // Store article context in session
        sessionStore.set(userId, {
            articleId: article.id,
            title: article.title,
            summary: article.description || '',
            source_url: article.source_url || '',
            image: article.featured_image,
            createdAt: Date.now()
        });

        console.log(`💾 Session stored for user ${userId}`);

        // Call AI backend to initialize thread with article context
        // AI will use source_url to fetch full article content via web scraping
        const aiRequest = {
            title: article.title,
            summary: article.description || '',
            source_url: article.source_url || '',
            question: "Initialize session", // Dummy question to set context
            thread_id: userId
        };

        console.log('📡 Initializing AI thread with article context...');

        await axios.post(
            `${AI_API_URL}/api/chat`,
            aiRequest,
            {
                headers: { 'Content-Type': 'application/json' },
                timeout: 90000 // Increased to 90 seconds for GPT-5 with web search
            }
        );

        console.log('✅ AI thread initialized');

        // Return session info
        res.json({
            success: true,
            data: {
                threadId: userId,
                article: {
                    id: article.id,
                    title: article.title,
                    image: article.featured_image
                },
                message: 'Session initialized. You can now ask questions.'
            }
        });

    } catch (error) {
        console.error('❌ Init Error:', error.message);
        res.status(500).json({
            success: false,
            message: 'Failed to initialize AI session',
            error: error.message
        });
    }
});

/**
 * POST /api/ask-ai/query
 * Send question to AI (session must be initialized first)
 * Called when user clicks "Send" button with question
 *
 * Request Body:
 * {
 *   "question": "User's question",
 *   "userId": "user123"      // REQUIRED - Same userId from init
 * }
 *
 * Response:
 * {
 *   "success": true,
 *   "data": {
 *     "answer": "AI response",
 *     "question": "User's question",
 *     "threadId": "user123"
 *   }
 * }
 */
router.post('/ask-ai/query', async (req, res) => {
    try {
        const { question, userId } = req.body;

        // Validation
        if (!question || !userId) {
            return res.status(400).json({
                success: false,
                message: 'Question and User ID are required',
                error: 'MISSING_FIELDS'
            });
        }

        // Check if session exists
        const session = sessionStore.get(userId);
        if (!session) {
            return res.status(400).json({
                success: false,
                message: 'Session not initialized. Please call /api/ask-ai/init first.',
                error: 'SESSION_NOT_FOUND'
            });
        }

        console.log(`💬 User ${userId} asking: "${question}"`);
        console.log(`📄 Using cached article: "${session.title}"`);
        console.log(`🔗 Article URL: ${session.source_url || 'N/A'}`);

        // Prepare request for AI API using cached session data
        // AI will use source_url to fetch full article content via web scraping
        const aiRequest = {
            title: session.title,
            summary: session.summary,
            source_url: session.source_url || '',
            question: question,
            thread_id: userId
        };

        console.log('📡 Calling AI API...');

        // Call FastAPI AI service
        const aiResponse = await axios.post(
            `${AI_API_URL}/api/chat`,
            aiRequest,
            {
                headers: { 'Content-Type': 'application/json' },
                timeout: 90000 // Increased to 90 seconds for GPT-5 with web search
            }
        );

        console.log('✅ AI Response received');

        // Return AI response
        res.json({
            success: true,
            data: {
                answer: aiResponse.data.answer,
                question: question,
                threadId: userId
            }
        });

    } catch (error) {
        console.error('❌ Query Error:', error.message);

        // Handle different error types
        if (error.code === 'ECONNREFUSED') {
            return res.status(503).json({
                success: false,
                message: 'AI service is not available.',
                error: 'SERVICE_UNAVAILABLE'
            });
        }

        if (error.response) {
            return res.status(error.response.status).json({
                success: false,
                message: error.response.data.detail || 'AI service error',
                error: 'AI_SERVICE_ERROR'
            });
        }

        res.status(500).json({
            success: false,
            message: 'Failed to process question',
            error: error.message
        });
    }
});

/**
 * POST /api/ask-ai/history
 * Get chat history for a user + article combination
 *
 * Request Body:
 * {
 *   "userId": "user123",
 *   "articleId": 17
 * }
 *
 * Response:
 * {
 *   "success": true,
 *   "data": {
 *     "messages": [
 *       { "role": "user", "text": "question" },
 *       { "role": "ai", "text": "answer" }
 *     ]
 *   }
 * }
 */
router.post('/ask-ai/history', async (req, res) => {
    try {
        const { userId, articleId } = req.body;

        if (!userId || !articleId) {
            return res.status(400).json({
                success: false,
                message: 'User ID and Article ID are required',
                error: 'MISSING_FIELDS'
            });
        }

        console.log(`📜 Fetching chat history for user ${userId}, article ${articleId}`);

        // Call AI backend to get history
        const aiResponse = await axios.post(
            `${AI_API_URL}/api/chat/history`,
            { thread_id: userId },
            {
                headers: { 'Content-Type': 'application/json' },
                timeout: 10000
            }
        );

        const messages = aiResponse.data.messages || [];

        // Transform messages to frontend format
        const formattedMessages = messages.map(msg => ({
            type: msg.role === 'user' ? 'user' : 'ai',
            text: msg.content
        }));

        console.log(`✅ Found ${formattedMessages.length} messages`);

        res.json({
            success: true,
            data: {
                messages: formattedMessages,
                threadId: userId
            }
        });

    } catch (error) {
        console.error('❌ History Error:', error.message);

        // Return empty history on error
        res.json({
            success: true,
            data: {
                messages: [],
                threadId: req.body.userId || ''
            }
        });
    }
});

/**
 * GET /api/ai-status
 * Check AI service status
 */
router.get('/ai-status', async (req, res) => {
    try {
        const response = await axios.get(`${AI_API_URL}/health`, {
            timeout: 5000
        });

        res.json({
            success: true,
            message: 'AI service is running',
            data: response.data
        });
    } catch (error) {
        res.status(503).json({
            success: false,
            message: 'AI service is not available',
            error: error.message
        });
    }
});

module.exports = router;
