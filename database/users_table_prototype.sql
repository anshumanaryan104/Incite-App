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

-- =====================================================
-- THAT'S IT! SUPER SIMPLE!
-- =====================================================

/*
Quick Test Queries:

1. Add a user:
INSERT INTO users (name, email, preferred_categories)
VALUES ('Your Name', 'your@email.com', ARRAY[1, 2, 3]);

2. Get user's feed:
SELECT * FROM get_user_feed(1);

3. Add a bookmark:
INSERT INTO user_bookmarks (user_id, article_id) VALUES (1, 1);

4. Update user preferences:
UPDATE users
SET preferred_categories = ARRAY[1, 3, 5]
WHERE id = 1;
*/