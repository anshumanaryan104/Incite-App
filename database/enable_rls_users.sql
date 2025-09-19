-- Enable RLS on users tables but allow all operations (for prototype)
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_bookmarks ENABLE ROW LEVEL SECURITY;

-- Create permissive policies that allow everything (since no auth)
CREATE POLICY "Allow all operations on users" ON users
    FOR ALL USING (true) WITH CHECK (true);

CREATE POLICY "Allow all operations on user_bookmarks" ON user_bookmarks
    FOR ALL USING (true) WITH CHECK (true);

-- Check status
SELECT tablename, rowsecurity
FROM pg_tables
WHERE schemaname = 'public'
AND tablename IN ('users', 'user_bookmarks');