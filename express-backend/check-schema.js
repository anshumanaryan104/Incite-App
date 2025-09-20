const { supabase } = require('./supabase-client');

async function checkDatabaseSchema() {
    console.log('🔍 Checking Database Schema...\n');

    try {
        // Check bookmarks table structure
        console.log('1. BOOKMARKS TABLE:');
        const { data: bookmarks, error: bErr } = await supabase
            .from('bookmarks')
            .select('*')
            .limit(1);

        if (!bErr && bookmarks) {
            console.log('   ✅ Table exists');
            if (bookmarks.length > 0) {
                console.log('   Sample fields:', Object.keys(bookmarks[0]));
            }
        } else {
            console.log('   ❌ Error:', bErr?.message);
        }

        // Check user_bookmarks table structure
        console.log('\n2. USER_BOOKMARKS TABLE:');
        const { data: userBookmarks, error: ubErr } = await supabase
            .from('user_bookmarks')
            .select('*')
            .limit(1);

        if (!ubErr && userBookmarks) {
            console.log('   ✅ Table exists');
            if (userBookmarks.length > 0) {
                console.log('   Sample fields:', Object.keys(userBookmarks[0]));
            }
        } else if (ubErr?.message.includes('not exist')) {
            console.log('   ⚠️ Table does not exist');
        } else {
            console.log('   ❌ Error:', ubErr?.message);
        }

        // Check interactions table structure
        console.log('\n3. INTERACTIONS TABLE:');
        const { data: interactions, error: iErr } = await supabase
            .from('interactions')
            .select('*')
            .limit(1);

        if (!iErr && interactions) {
            console.log('   ✅ Table exists');
            if (interactions.length > 0) {
                console.log('   Sample fields:', Object.keys(interactions[0]));
            }
        } else if (iErr?.message.includes('not exist')) {
            console.log('   ⚠️ Table does not exist');
        } else {
            console.log('   ❌ Error:', iErr?.message);
        }

        // Try to understand the schema
        console.log('\n📊 SCHEMA ANALYSIS:');

        // Get sample data to understand relationships
        const { data: bookmarksSample } = await supabase
            .from('bookmarks')
            .select('*')
            .limit(2);

        if (bookmarksSample && bookmarksSample.length > 0) {
            console.log('\nBookmarks table sample:');
            console.log(JSON.stringify(bookmarksSample[0], null, 2));
        }

    } catch (error) {
        console.error('Error checking schema:', error);
    }

    process.exit(0);
}

checkDatabaseSchema();