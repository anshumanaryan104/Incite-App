// Simple Supabase Connection Test
require('dotenv').config();

console.log('\n📊 Testing Supabase Connection\n');

const url = process.env.SUPABASE_URL;
const key = process.env.SUPABASE_ANON_KEY;

// Check if credentials exist
if (!url || !key) {
    console.log('❌ Missing credentials in .env file');
    process.exit(1);
}

console.log('URL:', url);
console.log('Key (first 50 chars):', key.substring(0, 50) + '...\n');

// Method 1: Test with node-fetch package
async function testWithNodeFetch() {
    try {
        console.log('1️⃣ Testing with node-fetch...');
        const fetch = require('node-fetch');

        const response = await fetch(`${url}/rest/v1/`, {
            headers: {
                'apikey': key,
                'Authorization': `Bearer ${key}`
            }
        });

        console.log('✅ node-fetch: Connected! Status:', response.status);
    } catch (error) {
        console.log('❌ node-fetch failed:', error.message);
    }
}

// Method 2: Test with axios
async function testWithAxios() {
    try {
        console.log('\n2️⃣ Testing with axios...');
        const axios = require('axios');

        const response = await axios.get(`${url}/rest/v1/`, {
            headers: {
                'apikey': key,
                'Authorization': `Bearer ${key}`
            }
        });

        console.log('✅ axios: Connected! Status:', response.status);
    } catch (error) {
        console.log('❌ axios failed:', error.message);
    }
}

// Method 3: Test with native fetch (Node 18+)
async function testWithNativeFetch() {
    try {
        console.log('\n3️⃣ Testing with native fetch...');

        const response = await fetch(`${url}/rest/v1/`, {
            headers: {
                'apikey': key,
                'Authorization': `Bearer ${key}`
            }
        });

        console.log('✅ native fetch: Connected! Status:', response.status);

        // Try to get tables list
        const tablesResponse = await fetch(`${url}/rest/v1/`, {
            headers: {
                'apikey': key,
                'Authorization': `Bearer ${key}`
            }
        });

        const text = await tablesResponse.text();
        console.log('\nAvailable endpoints:', text.substring(0, 200));

    } catch (error) {
        console.log('❌ native fetch failed:', error.message);

        if (error.cause) {
            console.log('Error cause:', error.cause);
        }
    }
}

// Method 4: Test with curl command
async function testWithCurl() {
    console.log('\n4️⃣ Testing with curl command...');
    const { exec } = require('child_process');

    const command = `curl -s -o /dev/null -w "%{http_code}" "${url}/rest/v1/" -H "apikey: ${key}"`;

    exec(command, (error, stdout, stderr) => {
        if (error) {
            console.log('❌ curl failed:', error.message);
        } else {
            console.log('✅ curl: HTTP Status Code:', stdout);
        }
    });
}

// Run all tests
async function runAllTests() {
    // Try with native fetch first
    await testWithNativeFetch();

    // Try with curl
    await testWithCurl();

    console.log('\n📝 If all methods fail, possible issues:');
    console.log('1. Internet/Proxy issues');
    console.log('2. Firewall blocking connections');
    console.log('3. Supabase project might be paused');
    console.log('4. Wrong credentials in .env file');
    console.log('5. WSL network issues (if using WSL)');

    console.log('\n🔧 Try this command to test directly:');
    console.log(`curl "${url}/rest/v1/" -H "apikey: ${key}"`);
}

runAllTests();