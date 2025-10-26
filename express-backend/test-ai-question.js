/**
 * Test AI chatbot with a real question
 */

const axios = require('axios');

async function testAIQuestion() {
    try {
        console.log('🧪 Testing AI Question Endpoint...\n');

        // Step 1: Initialize session
        console.log('Step 1: Initializing session...');
        const initResponse = await axios.post(
            'http://localhost:3000/api/ask-ai/init',
            {
                articleId: 134,
                userId: 'test123'
            },
            {
                headers: { 'Content-Type': 'application/json' },
                timeout: 30000
            }
        );

        if (initResponse.data.success) {
            console.log('✅ Session initialized\n');
            console.log('Article:', initResponse.data.data.article.title);
            console.log('\n---\n');
        }

        // Step 2: Ask a question
        console.log('Step 2: Asking question...');
        const queryResponse = await axios.post(
            'http://localhost:3000/api/ask-ai/query',
            {
                question: 'What is this article about? Give me a brief summary.',
                userId: 'test123'
            },
            {
                headers: { 'Content-Type': 'application/json' },
                timeout: 60000 // 60 seconds for AI to fetch URL and respond
            }
        );

        console.log('\n✅ AI Response:\n');
        console.log('Question:', queryResponse.data.data.question);
        console.log('\nAnswer:', queryResponse.data.data.answer);
        console.log('\n---\n');

        // Step 3: Ask follow-up question
        console.log('Step 3: Asking follow-up question...');
        const followupResponse = await axios.post(
            'http://localhost:3000/api/ask-ai/query',
            {
                question: 'Where will the storm hit?',
                userId: 'test123'
            },
            {
                headers: { 'Content-Type': 'application/json' },
                timeout: 60000
            }
        );

        console.log('\n✅ AI Response:\n');
        console.log('Question:', followupResponse.data.data.question);
        console.log('\nAnswer:', followupResponse.data.data.answer);
        console.log('\n---\n');

        console.log('🎉 Test Complete! Check backend logs to verify:');
        console.log('1. Express backend sent source_url to AI');
        console.log('2. AI backend fetched article from URL');
        console.log('3. AI answered based on full article content');

    } catch (error) {
        if (error.response) {
            console.log('❌ Response Status:', error.response.status);
            console.log('❌ Response Data:', error.response.data);
        } else if (error.code === 'ECONNREFUSED') {
            console.log('❌ Backend is not running');
        } else if (error.code === 'ETIMEDOUT') {
            console.log('❌ Request timeout - AI is taking too long (check AI backend logs)');
        } else {
            console.log('❌ Error:', error.message);
        }
    }
}

console.log('═══════════════════════════════════════');
console.log('  COMPLETE WORKFLOW TEST');
console.log('  Express → AI → Web Scraping → Answer');
console.log('═══════════════════════════════════════\n');

testAIQuestion();
