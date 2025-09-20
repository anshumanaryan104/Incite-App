const axios = require('axios');

// Configure the API base URL
const PORT = process.env.PORT || 3000;
const BASE_URL = `http://localhost:${PORT}/api`;

// Test data
const testData = {
    // For device-based (anonymous user)
    deviceFeed: {
        device_id: 'test-device-123',
        category_ids: [1, 2, 3, 5],
        category_names: ['Technology', 'Sports', 'Entertainment', 'Politics'],
        preferences: {
            notification_enabled: true,
            daily_digest: false
        }
    },

    // For authenticated user
    userFeed: {
        user_id: 1,
        category_ids: [2, 4, 6],
        category_names: ['Sports', 'Business', 'Health'],
        preferences: {
            notification_enabled: true,
            morning_brief: true,
            breaking_news: true
        }
    }
};

// Test POST /api/add-feed - Add feed preferences for device
async function testAddFeedDevice() {
    console.log('\n📝 Testing POST /api/add-feed (Device-based)...');
    try {
        const response = await axios.post(`${BASE_URL}/add-feed`, testData.deviceFeed);
        console.log('✅ Success:', JSON.stringify(response.data, null, 2));
        return response.data;
    } catch (error) {
        if (error.code === 'ECONNREFUSED') {
            console.error('❌ Error: Cannot connect to server. Make sure the Express server is running on port 8000');
            console.error('   Run: npm start (or node server.js) in another terminal');
        } else {
            console.error('❌ Error:', error.response?.data || error.message);
            if (error.response?.status) {
                console.error('   Status:', error.response.status);
            }
        }
    }
}

// Test POST /api/add-feed - Add feed preferences for user
async function testAddFeedUser() {
    console.log('\n📝 Testing POST /api/add-feed (User-based)...');
    try {
        const response = await axios.post(`${BASE_URL}/add-feed`, testData.userFeed);
        console.log('✅ Success:', response.data);
        return response.data;
    } catch (error) {
        console.error('❌ Error:', error.response?.data || error.message);
    }
}

// Test GET /api/get-feed - Get feed preferences
async function testGetFeed(identifier) {
    console.log('\n📖 Testing GET /api/get-feed...');
    try {
        const params = identifier.user_id
            ? { user_id: identifier.user_id }
            : { device_id: identifier.device_id };

        const response = await axios.get(`${BASE_URL}/get-feed`, { params });
        console.log('✅ Success:', response.data);
        return response.data;
    } catch (error) {
        console.error('❌ Error:', error.response?.data || error.message);
    }
}

// Test DELETE /api/remove-feed - Remove feed preferences
async function testRemoveFeed(identifier) {
    console.log('\n🗑️ Testing DELETE /api/remove-feed...');
    try {
        const response = await axios.delete(`${BASE_URL}/remove-feed`, {
            data: identifier
        });
        console.log('✅ Success:', response.data);
        return response.data;
    } catch (error) {
        console.error('❌ Error:', error.response?.data || error.message);
    }
}

// Test update feed - Update existing feed preferences
async function testUpdateFeed() {
    console.log('\n🔄 Testing Update Feed (changing categories)...');
    try {
        const updatedData = {
            device_id: 'test-device-123',
            category_ids: [1, 4, 7], // Changed categories
            category_names: ['Technology', 'Business', 'Science'],
            preferences: {
                notification_enabled: false, // Changed preference
                daily_digest: true
            }
        };

        const response = await axios.post(`${BASE_URL}/add-feed`, updatedData);
        console.log('✅ Success:', response.data);
        return response.data;
    } catch (error) {
        console.error('❌ Error:', error.response?.data || error.message);
    }
}

// Test with invalid data
async function testInvalidRequests() {
    console.log('\n⚠️ Testing Invalid Requests...');

    // Test without category_ids
    console.log('\n1. Missing category_ids:');
    try {
        await axios.post(`${BASE_URL}/add-feed`, {
            device_id: 'test-device-456'
        });
    } catch (error) {
        console.log('Expected error:', error.response?.data?.message || error.message);
    }

    // Test without identifier
    console.log('\n2. Missing user_id and device_id:');
    try {
        await axios.post(`${BASE_URL}/add-feed`, {
            category_ids: [1, 2, 3]
        });
    } catch (error) {
        console.log('Expected error:', error.response?.data?.message || error.message);
    }

    // Test with empty category array
    console.log('\n3. Empty category array:');
    try {
        await axios.post(`${BASE_URL}/add-feed`, {
            device_id: 'test-device-789',
            category_ids: []
        });
    } catch (error) {
        console.log('Expected error:', error.response?.data?.message || error.message);
    }
}

// Main test runner
async function runTests() {
    console.log('========================================');
    console.log('🚀 Starting Feed API Tests');
    console.log('========================================');

    // Test device-based feed
    console.log('\n--- Device-Based Feed Tests ---');
    await testAddFeedDevice();
    await testGetFeed({ device_id: 'test-device-123' });
    await testUpdateFeed();
    await testGetFeed({ device_id: 'test-device-123' });

    // Test user-based feed
    console.log('\n--- User-Based Feed Tests ---');
    await testAddFeedUser();
    await testGetFeed({ user_id: 1 });

    // Test invalid requests
    await testInvalidRequests();

    // Clean up - remove test feeds
    console.log('\n--- Cleanup ---');
    await testRemoveFeed({ device_id: 'test-device-123' });
    await testRemoveFeed({ user_id: 1 });

    // Verify removal
    console.log('\n--- Verify Removal ---');
    await testGetFeed({ device_id: 'test-device-123' });

    console.log('\n========================================');
    console.log('✅ Tests Completed');
    console.log('========================================');
}

// Run tests if this file is executed directly
if (require.main === module) {
    runTests().catch(console.error);
}

module.exports = {
    testAddFeedDevice,
    testAddFeedUser,
    testGetFeed,
    testRemoveFeed,
    testUpdateFeed,
    testInvalidRequests
};