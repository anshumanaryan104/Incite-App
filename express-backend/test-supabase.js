// Test Supabase Connection
require('dotenv').config();
const { supabase } = require('./supabase-client');

async function testConnection() {
    console.log('\n🔍 Testing Supabase Connection...\n');

    try {
        // Test 1: Check if we can connect
        console.log('1️⃣ Testing connection...');
        const { data: settings, error: settingsError } = await supabase
            .from('app_settings')
            .select('*')
            .limit(1);

        if (settingsError) {
            console.error('❌ Connection failed:', settingsError.message);
            console.log('\n📝 Please run the schema first in Supabase SQL Editor!');
            return;
        }

        console.log('✅ Connected to Supabase successfully!\n');

        // Test 2: Check categories
        console.log('2️⃣ Checking categories table...');
        const { data: categories, error: catError } = await supabase
            .from('categories')
            .select('*')
            .limit(5);

        if (catError) {
            console.error('❌ Categories table error:', catError.message);
        } else {
            console.log(`✅ Found ${categories.length} categories`);
            categories.forEach(cat => {
                console.log(`   - ${cat.name} (${cat.color})`);
            });
        }

        // Test 3: Check articles
        console.log('\n3️⃣ Checking articles table...');
        const { data: articles, error: artError } = await supabase
            .from('articles')
            .select('*')
            .limit(5);

        if (artError) {
            console.error('❌ Articles table error:', artError.message);
        } else {
            console.log(`✅ Found ${articles.length} articles`);
            articles.forEach(art => {
                console.log(`   - ${art.title}`);
            });
        }

        console.log('\n🎉 Supabase is ready to use!\n');

    } catch (error) {
        console.error('❌ Unexpected error:', error);
    }
}

testConnection();