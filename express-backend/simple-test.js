// Simplest Supabase Test with node-fetch
require('dotenv').config();
const fetch = require('node-fetch');

const url = process.env.SUPABASE_URL;
const key = process.env.SUPABASE_ANON_KEY;

console.log('Testing Supabase connection...\n');

// First, let's just try to reach Supabase
fetch(`${url}/rest/v1/`, {
    headers: {
        'apikey': key,
        'Authorization': `Bearer ${key}`
    }
})
.then(response => {
    console.log('✅ Connected to Supabase!');
    console.log('Status:', response.status);
    console.log('Status Text:', response.statusText);

    if (response.status === 200) {
        console.log('\n✅ Authentication successful!');
        console.log('Now you can run: node server.js');
    } else if (response.status === 401) {
        console.log('\n❌ Authentication failed - Check your API key');
    } else if (response.status === 404) {
        console.log('\n❌ URL might be wrong');
    }

    return response.text();
})
.then(text => {
    console.log('\nResponse preview:', text.substring(0, 100));
})
.catch(error => {
    console.log('❌ Connection failed:', error.message);

    // More detailed error info
    if (error.code === 'ENOTFOUND') {
        console.log('\n📝 The Supabase URL is incorrect or project doesn\'t exist');
        console.log('Check: Settings -> API -> Project URL in Supabase dashboard');
    } else if (error.code === 'ETIMEDOUT') {
        console.log('\n📝 Connection timed out - possible network/firewall issue');
    }

    console.log('\n🔍 Debug Info:');
    console.log('URL being tested:', `${url}/rest/v1/`);
    console.log('Error code:', error.code);
    console.log('Error type:', error.type);
});