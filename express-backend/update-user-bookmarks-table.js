const { supabase } = require('./supabase-client');

async function addCreatedAtColumn() {
    console.log('🔧 Adding created_at column to user_bookmarks table...\n');

    try {
        // Note: Direct DDL operations like ALTER TABLE need to be done via Supabase Dashboard
        // or using the SQL Editor in Supabase Dashboard

        // This script shows what needs to be done:
        const sqlCommand = `
-- Add created_at column to user_bookmarks table
ALTER TABLE user_bookmarks
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP;

-- Update existing rows
UPDATE user_bookmarks
SET created_at = CURRENT_TIMESTAMP
WHERE created_at IS NULL;
        `;

        console.log('📋 SQL to execute in Supabase Dashboard:');
        console.log('=' .repeat(50));
        console.log(sqlCommand);
        console.log('=' .repeat(50));

        console.log('\n📌 Instructions:');
        console.log('1. Go to your Supabase Dashboard');
        console.log('2. Navigate to SQL Editor');
        console.log('3. Copy and paste the above SQL');
        console.log('4. Click "Run" to execute');
        console.log('\n✅ After running, the API will automatically use created_at for sorting');

        // Test if the column exists by trying to query it
        const { data, error } = await supabase
            .from('user_bookmarks')
            .select('id, created_at')
            .limit(1);

        if (!error) {
            console.log('\n✅ Good news! created_at column already exists!');
            console.log('Sample data:', data);
        } else if (error.message.includes('created_at')) {
            console.log('\n❌ Column does not exist yet. Please run the SQL above in Supabase Dashboard.');
        }

    } catch (error) {
        console.error('Error:', error);
    }

    process.exit(0);
}

addCreatedAtColumn();