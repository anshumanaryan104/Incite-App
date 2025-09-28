-- Ultra Simple Users Table for Prototype Testing
-- Focus on core functionality only

-- =====================================================
-- USERS TABLE (Prototype Version - No Timestamps)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,

    -- Essential Information Only
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,

    -- User Preferences (What categories they like)
    preferred_categories INTEGER[] -- Array of category IDs they're interested in
);

-- =====================================================
-- USER_BOOKMARKS TABLE (Simple bookmarks)
-- =====================================================
CREATE TABLE IF NOT EXISTS user_bookmarks (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    article_id INTEGER REFERENCES articles(id) ON DELETE CASCADE,
    UNIQUE(user_id, article_id)
);

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_user_bookmarks_user ON user_bookmarks(user_id);

-- =====================================================
-- SAMPLE TEST USERS
-- =====================================================
INSERT INTO users (name, email, preferred_categories) VALUES
('Test User 1', 'user1@test.com', ARRAY[1, 2]),        -- Likes: Technology, Sports
('Test User 2', 'user2@test.com', ARRAY[3, 4]),        -- Likes: Politics, Entertainment
('Test User 3', 'user3@test.com', ARRAY[1, 5]),        -- Likes: Technology, Health
('Demo User', 'demo@test.com', ARRAY[1, 2, 3, 4, 5]);  -- Likes: Everything

-- =====================================================
-- SIMPLE FUNCTION - Get User's Personalized Feed
-- =====================================================
CREATE OR REPLACE FUNCTION get_user_feed(user_id INTEGER)
RETURNS TABLE (
    article_id INTEGER,
    title VARCHAR,
    description TEXT,
    featured_image TEXT,
    category_name VARCHAR
) AS $$
DECLARE
    user_categories INTEGER[];
BEGIN
    -- Get user's preferred categories
    SELECT preferred_categories INTO user_categories
    FROM users WHERE id = user_id;

    -- Return articles from preferred categories
    RETURN QUERY
    SELECT
        a.id,
        a.title,
        a.description,
        a.featured_image,
        c.name as category_name
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    WHERE
        a.status = 'published'
        AND a.category_id = ANY(user_categories)
    ORDER BY a.published_at DESC
    LIMIT 50;
END;
$$ LANGUAGE plpgsql;

-- 1. Create admin_users table
CREATE TABLE admin_users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    hashed_password TEXT NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    is_active BOOLEAN DEFAULT true,
    last_login_at TIMESTAMP,
    last_login_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

  -- 2. Create audit_logs table
 CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    table_name VARCHAR(50) NOT NULL,
    record_id INTEGER NOT NULL,
    action VARCHAR(20) NOT NULL,
    admin_username VARCHAR(50) NOT NULL,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

  -- 3. Update articles table (add audit columns)
ALTER TABLE articles
ADD COLUMN IF NOT EXISTS created_by VARCHAR(50),
ADD COLUMN IF NOT EXISTS updated_by VARCHAR(50),
ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50),
ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

-- 4. Create indexes for performance
  CREATE INDEX idx_audit_logs_username ON audit_logs(admin_username);
  CREATE INDEX idx_audit_logs_table_record ON audit_logs(table_name, record_id);
  CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at DESC);
  CREATE INDEX idx_articles_created_by ON articles(created_by);


  -- Add username column and hashed_password to users table
ALTER TABLE users
ADD COLUMN IF NOT EXISTS username TEXT UNIQUE,
ADD COLUMN IF NOT EXISTS hashed_password TEXT;

-- Delete all existing users (as requested)
DELETE FROM users;

-- Make username required (NOT NULL)
ALTER TABLE users
ALTER COLUMN username SET NOT NULL;

-- Create index on username for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Update email to be optional
ALTER TABLE users
ALTER COLUMN email DROP NOT NULL;