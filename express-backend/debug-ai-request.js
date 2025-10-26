/**
 * Debug: See exactly what Express is sending to AI backend
 */

const axios = require('axios');

async function debugAIRequest() {
    try {
        console.log('🔍 Debugging: What Express sends to AI backend\n');

        // This is exactly what Express backend sends to AI
        const testPayload = {
            title: "Test Article",
            summary: "Test summary",
            source_url: "https://example.com/test",
            question: "Test question",
            thread_id: "debug123"
        };

        console.log('📤 Sending to AI backend:');
        console.log(JSON.stringify(testPayload, null, 2));
        console.log('\n---\n');

        // Direct call to AI backend (bypass Express)
        const response = await axios.post(
            'http://localhost:8000/api/chat',
            testPayload,
            {
                headers: { 'Content-Type': 'application/json' },
                timeout: 30000
            }
        );

        console.log('✅ AI Backend Response:');
        console.log(JSON.stringify(response.data, null, 2));

    } catch (error) {
        if (error.response) {
            console.log('❌ Status:', error.response.status);
            console.log('❌ Error Response:');
            console.log(JSON.stringify(error.response.data, null, 2));
        } else {
            console.log('❌ Error:', error.message);
        }
    }
}

debugAIRequest();
