const { supabase } = require('./supabase-client');

async function analyzeTableStructure() {
    console.log('📊 DATABASE TABLE ANALYSIS\n');
    console.log('=' .repeat(50));

    try {
        // 1. BOOKMARKS TABLE
        console.log('\n📚 1. BOOKMARKS TABLE (Anonymous/Device-based)');
        console.log('-'.repeat(40));

        const { data: bookmarksData, error: bErr } = await supabase
            .from('bookmarks')
            .select('*')
            .limit(2);

        if (bookmarksData && bookmarksData.length > 0) {
            console.log('Structure:', Object.keys(bookmarksData[0]));
            console.log('\nSample Entry:');
            console.log(JSON.stringify(bookmarksData[0], null, 2));
            console.log('\nPurpose: Stores bookmarks for anonymous users (device-based)');
            console.log('Key Fields:');
            console.log('  - device_id: Unique device identifier');
            console.log('  - article_id: Reference to articles table');
            console.log('  - created_at: When bookmark was created');
        } else {
            console.log('No data available or error:', bErr?.message);
        }

        // 2. USER_BOOKMARKS TABLE
        console.log('\n👤 2. USER_BOOKMARKS TABLE (Authenticated Users)');
        console.log('-'.repeat(40));

        const { data: userBookmarksData, error: ubErr } = await supabase
            .from('user_bookmarks')
            .select('*')
            .limit(2);

        if (userBookmarksData) {
            if (userBookmarksData.length > 0) {
                console.log('Structure:', Object.keys(userBookmarksData[0]));
                console.log('\nSample Entry:');
                console.log(JSON.stringify(userBookmarksData[0], null, 2));
            } else {
                console.log('Table exists but is empty');
            }
            console.log('\nPurpose: Stores bookmarks for registered/logged-in users');
            console.log('Key Fields:');
            console.log('  - user_id: Reference to users table');
            console.log('  - article_id: Reference to articles table');
            console.log('  - created_at: When bookmark was created');
        } else if (ubErr) {
            console.log('Error accessing table:', ubErr.message);
        }

        // 3. INTERACTIONS TABLE
        console.log('\n📈 3. INTERACTIONS TABLE (User Engagement)');
        console.log('-'.repeat(40));

        const { data: interactionsData, error: iErr } = await supabase
            .from('interactions')
            .select('*')
            .limit(2);

        if (interactionsData) {
            if (interactionsData.length > 0) {
                console.log('Structure:', Object.keys(interactionsData[0]));
                console.log('\nSample Entry:');
                console.log(JSON.stringify(interactionsData[0], null, 2));
            } else {
                console.log('Table exists but is empty');
            }
            console.log('\nPurpose: Tracks user interactions with articles');
            console.log('Key Types:');
            console.log('  - view: Article was viewed');
            console.log('  - like: Article was liked');
            console.log('  - share: Article was shared');
            console.log('  - comment: User commented on article');
            console.log('Key Fields:');
            console.log('  - user_id/device_id: User or device identifier');
            console.log('  - article_id: Reference to articles table');
            console.log('  - interaction_type: Type of interaction');
            console.log('  - created_at: When interaction occurred');
        } else if (iErr) {
            console.log('Error accessing table:', iErr.message);
        }

        console.log('\n' + '='.repeat(50));
        console.log('\n📝 SUMMARY:');
        console.log('-'.repeat(40));
        console.log('1. BOOKMARKS: For anonymous users (device-based)');
        console.log('2. USER_BOOKMARKS: For registered users (user account-based)');
        console.log('3. INTERACTIONS: Tracks all user engagement (views, likes, shares)');
        console.log('\n💡 Use Cases:');
        console.log('- Anonymous users use device_id for bookmarks');
        console.log('- Logged-in users have persistent bookmarks across devices');
        console.log('- Interactions help track article popularity and engagement');

    } catch (error) {
        console.error('Error analyzing tables:', error);
    }

    process.exit(0);
}

analyzeTableStructure();