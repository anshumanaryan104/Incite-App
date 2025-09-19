// Debug Supabase Connection
require('dotenv').config();

console.log('\n🔍 Debugging Supabase Configuration\n');
console.log('=====================================\n');

// 1. Check if environment variables are loaded
console.log('1️⃣ Environment Variables Check:');
console.log('--------------------------------');

const url = process.env.SUPABASE_URL;
const anonKey = process.env.SUPABASE_ANON_KEY;

if (!url) {
    console.log('❌ SUPABASE_URL is missing or empty');
} else {
    console.log('✅ SUPABASE_URL found:', url.substring(0, 30) + '...');

    // Check URL format
    if (!url.startsWith('https://')) {
        console.log('⚠️  Warning: URL should start with https://');
    }
    if (!url.includes('.supabase.co')) {
        console.log('⚠️  Warning: URL should contain .supabase.co');
    }
}

if (!anonKey) {
    console.log('❌ SUPABASE_ANON_KEY is missing or empty');
} else {
    console.log('✅ SUPABASE_ANON_KEY found:', anonKey.substring(0, 20) + '...');

    // Check key format
    if (!anonKey.startsWith('eyJ')) {
        console.log('⚠️  Warning: Key should start with "eyJ"');
    }
}

console.log('\n2️⃣ Testing Basic Connection:');
console.log('-----------------------------');

if (url && anonKey) {
    // Test with simple fetch
    const testUrl = `${url}/rest/v1/`;
    console.log('Testing URL:', testUrl);

    fetch(testUrl, {
        headers: {
            'apikey': anonKey,
            'Authorization': `Bearer ${anonKey}`
        }
    })
    .then(response => {
        console.log('✅ Connection successful! Status:', response.status);

        if (response.status === 200) {
            console.log('✅ Supabase is reachable!');
        } else if (response.status === 401) {
            console.log('❌ Authentication failed - Check your ANON KEY');
        } else if (response.status === 404) {
            console.log('⚠️  Project URL might be incorrect');
        }

        return response.text();
    })
    .then(data => {
        console.log('\n3️⃣ Response Preview:');
        console.log('--------------------');
        console.log(data.substring(0, 200));

        console.log('\n4️⃣ Next Steps:');
        console.log('--------------');
        console.log('1. Make sure you have run the schema in Supabase SQL Editor');
        console.log('2. Check if RLS (Row Level Security) is enabled on your tables');
        console.log('3. Try running: node test-supabase.js');
    })
    .catch(error => {
        console.log('❌ Connection failed:', error.message);

        console.log('\n🔧 Troubleshooting:');
        console.log('-------------------');

        if (error.message.includes('fetch')) {
            console.log('• Check your internet connection');
            console.log('• Make sure the Supabase URL is correct');
            console.log('• Verify project is active in Supabase dashboard');
        }

        if (error.message.includes('ENOTFOUND')) {
            console.log('• The URL seems incorrect');
            console.log('• Double-check your project URL in Supabase dashboard');
        }

        console.log('\n📋 Checklist:');
        console.log('1. Is your Supabase project active? (not paused)');
        console.log('2. Did you copy the complete URL? (https://xxxxx.supabase.co)');
        console.log('3. Did you copy the anon/public key? (not service key)');
        console.log('4. Are you connected to internet?');
    });
} else {
    console.log('\n❌ Cannot test connection - Missing credentials');
    console.log('\n📝 Please add to .env file:');
    console.log('SUPABASE_URL=https://your-project.supabase.co');
    console.log('SUPABASE_ANON_KEY=your-anon-key-here');
}