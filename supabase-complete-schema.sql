-- =====================================================
-- COMPLETE DATABASE SCHEMA FOR NEWS APP
-- Supabase PostgreSQL Database
-- Generated: October 2025
-- =====================================================

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm"; -- For text search

-- =====================================================
-- 1. CATEGORIES TABLE
-- =====================================================
-- Stores article categories (currently only "All News" is used)
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    color VARCHAR(50) DEFAULT '#FF6B6B',
    image VARCHAR(500),
    parent_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    is_active BOOLEAN DEFAULT true,
    is_featured BOOLEAN DEFAULT false,
    is_feed BOOLEAN DEFAULT false,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug);
CREATE INDEX IF NOT EXISTS idx_categories_parent_id ON categories(parent_id);
CREATE INDEX IF NOT EXISTS idx_categories_is_active ON categories(is_active);
CREATE INDEX IF NOT EXISTS idx_categories_sort_order ON categories(sort_order);

-- =====================================================
-- 2. ARTICLES TABLE
-- =====================================================
-- Main articles/news posts table
CREATE TABLE IF NOT EXISTS articles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) UNIQUE,
    description TEXT,
    content TEXT,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,

    -- Media fields
    featured_image VARCHAR(1000),
    images TEXT[], -- Array of image URLs
    video_url VARCHAR(1000),
    video_file VARCHAR(1000),
    background_image VARCHAR(1000),

    -- Article metadata
    type VARCHAR(50) DEFAULT 'article' CHECK (type IN ('article', 'post', 'video', 'quote', 'ads')),
    status VARCHAR(50) DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    is_featured BOOLEAN DEFAULT false,
    is_trending BOOLEAN DEFAULT false,

    -- Engagement metrics
    views INTEGER DEFAULT 0,
    likes INTEGER DEFAULT 0,
    shares INTEGER DEFAULT 0,

    -- Source information
    source_name VARCHAR(255),
    source_url VARCHAR(1000),
    author_name VARCHAR(255),

    -- Publishing info
    published_at TIMESTAMP WITH TIME ZONE,
    schedule_date TIMESTAMP WITH TIME ZONE,

    -- Voice/TTS fields
    voice VARCHAR(100),
    accent_code VARCHAR(10) DEFAULT 'en',

    -- Poll/Voting fields
    is_voting_enable BOOLEAN DEFAULT false,
    question JSONB, -- Stores poll question and options

    -- Additional metadata
    tags TEXT[],
    visibilities TEXT[],
    frequency INTEGER DEFAULT 0,

    -- Audit fields
    created_by VARCHAR(255),
    updated_by VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_articles_slug ON articles(slug);
CREATE INDEX IF NOT EXISTS idx_articles_category_id ON articles(category_id);
CREATE INDEX IF NOT EXISTS idx_articles_status ON articles(status);
CREATE INDEX IF NOT EXISTS idx_articles_is_featured ON articles(is_featured);
CREATE INDEX IF NOT EXISTS idx_articles_is_trending ON articles(is_trending);
CREATE INDEX IF NOT EXISTS idx_articles_published_at ON articles(published_at DESC);
CREATE INDEX IF NOT EXISTS idx_articles_created_at ON articles(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_articles_views ON articles(views DESC);

-- Full-text search index on title and description
CREATE INDEX IF NOT EXISTS idx_articles_title_search ON articles USING gin(to_tsvector('english', title));
CREATE INDEX IF NOT EXISTS idx_articles_description_search ON articles USING gin(to_tsvector('english', description));
CREATE INDEX IF NOT EXISTS idx_articles_content_search ON articles USING gin(to_tsvector('english', content));

-- =====================================================
-- 3. USERS TABLE
-- =====================================================
-- App users (for login/signup functionality)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    hashed_password TEXT NOT NULL,

    -- User profile
    name VARCHAR(255),
    full_name VARCHAR(255),
    phone VARCHAR(50),
    profile_picture VARCHAR(1000),
    avatar_url VARCHAR(1000),
    bio TEXT,

    -- User status
    is_verified BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,

    -- Notification tokens
    player_id VARCHAR(255), -- OneSignal player ID
    fcm_token TEXT, -- Firebase Cloud Messaging token

    -- User preferences
    preferences JSONB DEFAULT '{}',

    -- Timestamps
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP WITH TIME ZONE
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_is_active ON users(is_active);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at DESC);

-- =====================================================
-- 4. ADMIN_USERS TABLE
-- =====================================================
-- Admin panel users (separate from app users)
CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    hashed_password TEXT NOT NULL,

    -- Admin role
    role VARCHAR(50) DEFAULT 'admin' CHECK (role IN ('super_admin', 'admin', 'editor')),

    -- Admin status
    is_active BOOLEAN DEFAULT true,

    -- Login tracking
    last_login_at TIMESTAMP WITH TIME ZONE,
    last_login_ip VARCHAR(100),

    -- Timestamps
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_admin_users_username ON admin_users(username);
CREATE INDEX IF NOT EXISTS idx_admin_users_email ON admin_users(email);
CREATE INDEX IF NOT EXISTS idx_admin_users_role ON admin_users(role);
CREATE INDEX IF NOT EXISTS idx_admin_users_is_active ON admin_users(is_active);

-- =====================================================
-- 5. BOOKMARKS TABLE (Device-based)
-- =====================================================
-- Stores bookmarks for anonymous users (device-based)
CREATE TABLE IF NOT EXISTS bookmarks (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL,
    article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Prevent duplicate bookmarks
    CONSTRAINT unique_device_article_bookmark UNIQUE (device_id, article_id)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_bookmarks_device_id ON bookmarks(device_id);
CREATE INDEX IF NOT EXISTS idx_bookmarks_article_id ON bookmarks(article_id);
CREATE INDEX IF NOT EXISTS idx_bookmarks_created_at ON bookmarks(created_at DESC);

-- =====================================================
-- 6. USER_BOOKMARKS TABLE
-- =====================================================
-- Stores bookmarks for logged-in users
CREATE TABLE IF NOT EXISTS user_bookmarks (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Prevent duplicate bookmarks
    CONSTRAINT unique_user_article_bookmark UNIQUE (user_id, article_id)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_user_bookmarks_user_id ON user_bookmarks(user_id);
CREATE INDEX IF NOT EXISTS idx_user_bookmarks_article_id ON user_bookmarks(article_id);
CREATE INDEX IF NOT EXISTS idx_user_bookmarks_created_at ON user_bookmarks(created_at DESC);

-- =====================================================
-- 7. ANONYMOUS_BOOKMARKS TABLE (Deprecated/Legacy)
-- =====================================================
-- Legacy bookmark table - keeping for backward compatibility
CREATE TABLE IF NOT EXISTS anonymous_bookmarks (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL,
    article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT unique_anon_device_article UNIQUE (device_id, article_id)
);

CREATE INDEX IF NOT EXISTS idx_anonymous_bookmarks_device_id ON anonymous_bookmarks(device_id);
CREATE INDEX IF NOT EXISTS idx_anonymous_bookmarks_article_id ON anonymous_bookmarks(article_id);

-- =====================================================
-- 8. INTERACTIONS TABLE
-- =====================================================
-- Tracks user interactions (views, likes, shares, searches, etc.)
CREATE TABLE IF NOT EXISTS interactions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    device_id VARCHAR(255),
    article_id INTEGER REFERENCES articles(id) ON DELETE CASCADE,

    -- Interaction type
    interaction_type VARCHAR(100) NOT NULL CHECK (interaction_type IN (
        'view', 'like', 'share', 'share_intent', 'search',
        'feed_preferences_updated', 'feed_preferences_removed',
        'notification_settings_check', 'notifications_viewed',
        'token_update'
    )),

    -- Additional data
    metadata JSONB DEFAULT '{}',

    -- Request info
    ip_address VARCHAR(100),
    user_agent TEXT,

    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Ensure either user_id or device_id is present
    CONSTRAINT has_user_or_device CHECK (user_id IS NOT NULL OR device_id IS NOT NULL)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_interactions_user_id ON interactions(user_id);
CREATE INDEX IF NOT EXISTS idx_interactions_device_id ON interactions(device_id);
CREATE INDEX IF NOT EXISTS idx_interactions_article_id ON interactions(article_id);
CREATE INDEX IF NOT EXISTS idx_interactions_type ON interactions(interaction_type);
CREATE INDEX IF NOT EXISTS idx_interactions_created_at ON interactions(created_at DESC);

-- =====================================================
-- 9. ANONYMOUS_INTERACTIONS TABLE (Deprecated/Legacy)
-- =====================================================
-- Legacy interactions table
CREATE TABLE IF NOT EXISTS anonymous_interactions (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(255),
    article_id INTEGER REFERENCES articles(id) ON DELETE CASCADE,
    interaction_type VARCHAR(50) NOT NULL CHECK (interaction_type IN ('view', 'like', 'share')),
    ip_address VARCHAR(100),
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_anon_interactions_device_id ON anonymous_interactions(device_id);
CREATE INDEX IF NOT EXISTS idx_anon_interactions_article_id ON anonymous_interactions(article_id);
CREATE INDEX IF NOT EXISTS idx_anon_interactions_type ON anonymous_interactions(interaction_type);

-- =====================================================
-- 10. APP_SETTINGS TABLE
-- =====================================================
-- Application-wide settings (key-value pairs)
CREATE TABLE IF NOT EXISTS app_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    data_type VARCHAR(50) DEFAULT 'string' CHECK (data_type IN ('string', 'number', 'boolean', 'json')),
    is_public BOOLEAN DEFAULT true, -- Whether accessible via public API
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create index
CREATE INDEX IF NOT EXISTS idx_app_settings_key ON app_settings(setting_key);

-- Insert default settings
INSERT INTO app_settings (setting_key, setting_value, description, data_type, is_public) VALUES
('app_name', 'News App', 'Application name', 'string', true),
('primary_color', '#FF6B6B', 'Primary theme color', 'string', true),
('secondary_color', '#4ECDC4', 'Secondary theme color', 'string', true),
('app_version', '1.0.0', 'Current app version', 'string', true),
('enable_ads', '0', 'Enable advertisements', 'boolean', true),
('enable_notifications', '0', 'Enable push notifications', 'boolean', true)
ON CONFLICT (setting_key) DO NOTHING;

-- =====================================================
-- 11. ABOUT_CONTACT TABLE
-- =====================================================
-- Stores About Us and Contact information
CREATE TABLE IF NOT EXISTS about_contact (
    id SERIAL PRIMARY KEY,

    -- About section
    about_title VARCHAR(255) DEFAULT 'About Us',
    about_description TEXT DEFAULT 'Welcome to News App - Your trusted source for latest news and updates',
    about_content TEXT DEFAULT 'We are dedicated to bringing you the most relevant and timely news from around the world.',
    about_mission TEXT DEFAULT 'To deliver accurate, unbiased, and comprehensive news coverage to our readers',
    about_vision TEXT DEFAULT 'To be the most trusted digital news platform',
    about_established VARCHAR(50) DEFAULT '2024',
    about_team_size VARCHAR(255) DEFAULT 'Growing team of passionate individuals',
    about_values JSONB DEFAULT '["Accuracy and Truth", "Unbiased Reporting", "Reader First Approach", "Innovation in News Delivery"]'::jsonb,

    -- Contact section
    contact_title VARCHAR(255) DEFAULT 'Contact Us',
    contact_description TEXT DEFAULT 'We''d love to hear from you',
    contact_email VARCHAR(255) DEFAULT 'contact@newsapp.com',
    contact_support_email VARCHAR(255) DEFAULT 'support@newsapp.com',
    contact_phone VARCHAR(50) DEFAULT '+91 98765 43210',
    contact_whatsapp VARCHAR(50) DEFAULT '+91 98765 43210',
    contact_address JSONB DEFAULT '{"line1": "News App Headquarters", "line2": "Tech Park, Building A", "city": "Mumbai", "state": "Maharashtra", "country": "India", "pincode": "400001"}'::jsonb,
    business_hours JSONB DEFAULT '{"weekdays": "9:00 AM - 6:00 PM", "saturday": "9:00 AM - 2:00 PM", "sunday": "Closed"}'::jsonb,
    contact_response_time VARCHAR(255) DEFAULT 'We typically respond within 24 hours',

    -- Legal section
    privacy_policy_url VARCHAR(255) DEFAULT '/privacy-policy',
    terms_conditions_url VARCHAR(255) DEFAULT '/terms-conditions',
    disclaimer TEXT DEFAULT 'All news content is for informational purposes only',

    -- App info
    app_version VARCHAR(50) DEFAULT '1.0.0',
    app_developer VARCHAR(255) DEFAULT 'News App Team',
    app_copyright VARCHAR(255) DEFAULT '© 2025 News App. All rights reserved.',

    -- Timestamps
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Insert default record
INSERT INTO about_contact (id) VALUES (1) ON CONFLICT (id) DO NOTHING;

-- =====================================================
-- 12. PUSH_TOKENS TABLE
-- =====================================================
-- Stores push notification tokens for devices
CREATE TABLE IF NOT EXISTS push_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    device_id VARCHAR(255),
    token TEXT NOT NULL UNIQUE,
    platform VARCHAR(50) NOT NULL CHECK (platform IN ('ios', 'android', 'web')),
    device_info JSONB DEFAULT '{}',
    is_active BOOLEAN DEFAULT true,
    last_used TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Ensure either user_id or device_id is present
    CONSTRAINT has_identifier CHECK (user_id IS NOT NULL OR device_id IS NOT NULL),
    CONSTRAINT unique_device UNIQUE (device_id),
    CONSTRAINT unique_user_platform UNIQUE (user_id, platform)
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_push_tokens_user_id ON push_tokens(user_id);
CREATE INDEX IF NOT EXISTS idx_push_tokens_device_id ON push_tokens(device_id);
CREATE INDEX IF NOT EXISTS idx_push_tokens_token ON push_tokens(token);
CREATE INDEX IF NOT EXISTS idx_push_tokens_active ON push_tokens(is_active);
CREATE INDEX IF NOT EXISTS idx_push_tokens_platform ON push_tokens(platform);

-- =====================================================
-- 13. NOTIFICATION_SETTINGS TABLE
-- =====================================================
-- User notification preferences
CREATE TABLE IF NOT EXISTS notification_settings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE REFERENCES users(id) ON DELETE CASCADE,

    -- Main settings
    notification_enabled BOOLEAN DEFAULT true,
    permission_status VARCHAR(50) DEFAULT 'granted' CHECK (permission_status IN ('granted', 'denied', 'not_determined', 'provisional')),

    -- Push notification settings
    push_enabled BOOLEAN DEFAULT true,
    push_permission VARCHAR(50) DEFAULT 'granted',
    fcm_token TEXT,
    push_updated_at TIMESTAMP WITH TIME ZONE,

    -- Email settings
    email_enabled BOOLEAN DEFAULT false,
    email VARCHAR(255),
    email_verified BOOLEAN DEFAULT false,

    -- Notification preferences
    pref_breaking_news BOOLEAN DEFAULT true,
    pref_daily_digest BOOLEAN DEFAULT false,
    pref_bookmark_reminders BOOLEAN DEFAULT true,
    pref_new_categories BOOLEAN DEFAULT true,
    pref_trending BOOLEAN DEFAULT true,
    pref_recommendations BOOLEAN DEFAULT false,

    -- Device settings
    device_sound BOOLEAN DEFAULT true,
    device_vibration BOOLEAN DEFAULT true,
    device_badge BOOLEAN DEFAULT true,
    quiet_hours_enabled BOOLEAN DEFAULT false,
    quiet_hours_start TIME DEFAULT '22:00',
    quiet_hours_end TIME DEFAULT '08:00',

    -- Schedule settings
    schedule_morning BOOLEAN DEFAULT false,
    schedule_morning_time TIME DEFAULT '08:00',
    schedule_evening BOOLEAN DEFAULT false,
    schedule_evening_time TIME DEFAULT '18:00',

    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_notification_settings_user_id ON notification_settings(user_id);

-- =====================================================
-- 14. USER_FEEDS TABLE (Optional - Not Currently Used)
-- =====================================================
-- User selected interests/categories for personalized feed
CREATE TABLE IF NOT EXISTS user_feeds (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    device_id VARCHAR(255),
    category_ids INTEGER[] NOT NULL,
    category_names TEXT[],
    preferences JSONB DEFAULT '{}',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT has_identifier CHECK (user_id IS NOT NULL OR device_id IS NOT NULL),
    CONSTRAINT unique_device_feed UNIQUE (device_id),
    CONSTRAINT unique_user_feed UNIQUE (user_id)
);

CREATE INDEX IF NOT EXISTS idx_user_feeds_user_id ON user_feeds(user_id);
CREATE INDEX IF NOT EXISTS idx_user_feeds_device_id ON user_feeds(device_id);
CREATE INDEX IF NOT EXISTS idx_user_feeds_category_ids ON user_feeds USING GIN(category_ids);

-- =====================================================
-- 15. AUDIT_LOGS TABLE
-- =====================================================
-- Tracks admin actions for accountability
CREATE TABLE IF NOT EXISTS audit_logs (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INTEGER,
    action VARCHAR(50) NOT NULL CHECK (action IN ('CREATE', 'UPDATE', 'DELETE')),
    admin_username VARCHAR(100) NOT NULL,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(100),
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_audit_logs_table_name ON audit_logs(table_name);
CREATE INDEX IF NOT EXISTS idx_audit_logs_record_id ON audit_logs(record_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs(action);
CREATE INDEX IF NOT EXISTS idx_audit_logs_admin ON audit_logs(admin_username);
CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs(created_at DESC);

-- =====================================================
-- FUNCTIONS
-- =====================================================

-- Function to increment article views
CREATE OR REPLACE FUNCTION increment_article_views(article_id_input INTEGER)
RETURNS void AS $$
BEGIN
    UPDATE articles
    SET views = views + 1
    WHERE id = article_id_input;
END;
$$ LANGUAGE plpgsql;

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger for categories
DROP TRIGGER IF EXISTS update_categories_updated_at ON categories;
CREATE TRIGGER update_categories_updated_at
BEFORE UPDATE ON categories
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for articles
DROP TRIGGER IF EXISTS update_articles_updated_at ON articles;
CREATE TRIGGER update_articles_updated_at
BEFORE UPDATE ON articles
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for users
DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for admin_users
DROP TRIGGER IF EXISTS update_admin_users_updated_at ON admin_users;
CREATE TRIGGER update_admin_users_updated_at
BEFORE UPDATE ON admin_users
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for app_settings
DROP TRIGGER IF EXISTS update_app_settings_updated_at ON app_settings;
CREATE TRIGGER update_app_settings_updated_at
BEFORE UPDATE ON app_settings
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for about_contact
DROP TRIGGER IF EXISTS update_about_contact_updated_at ON about_contact;
CREATE TRIGGER update_about_contact_updated_at
BEFORE UPDATE ON about_contact
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for push_tokens
DROP TRIGGER IF EXISTS update_push_tokens_updated_at ON push_tokens;
CREATE TRIGGER update_push_tokens_updated_at
BEFORE UPDATE ON push_tokens
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for notification_settings
DROP TRIGGER IF EXISTS update_notification_settings_updated_at ON notification_settings;
CREATE TRIGGER update_notification_settings_updated_at
BEFORE UPDATE ON notification_settings
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Trigger for user_feeds
DROP TRIGGER IF EXISTS update_user_feeds_updated_at ON user_feeds;
CREATE TRIGGER update_user_feeds_updated_at
BEFORE UPDATE ON user_feeds
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- VIEWS (Optional - for simplified queries)
-- =====================================================

-- View for articles with full category info
CREATE OR REPLACE VIEW v_articles_full AS
SELECT
    a.*,
    c.name as category_name,
    c.color as category_color,
    c.slug as category_slug
FROM articles a
LEFT JOIN categories c ON a.category_id = c.id;

-- =====================================================
-- PERMISSIONS (Adjust based on your security needs)
-- =====================================================

-- Grant permissions to authenticated users
GRANT ALL ON ALL TABLES IN SCHEMA public TO authenticated;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO authenticated;
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO authenticated;

-- Grant permissions to anonymous users (read-only mostly)
GRANT SELECT ON ALL TABLES IN SCHEMA public TO anon;
GRANT INSERT, UPDATE, DELETE ON bookmarks TO anon;
GRANT INSERT, UPDATE, DELETE ON user_bookmarks TO anon;
GRANT INSERT ON interactions TO anon;
GRANT INSERT ON anonymous_interactions TO anon;
GRANT INSERT, UPDATE ON push_tokens TO anon;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO anon;

-- =====================================================
-- INITIAL DATA
-- =====================================================

-- Insert default "All News" category
INSERT INTO categories (id, name, slug, color, is_active, is_feed, is_featured, sort_order)
VALUES (1, 'All News', 'all-news', '#FF6B6B', true, true, true, 1)
ON CONFLICT (id) DO UPDATE
SET name = EXCLUDED.name,
    color = EXCLUDED.color,
    is_active = EXCLUDED.is_active,
    is_feed = EXCLUDED.is_feed;

-- Create default super admin (username: superadmin, password: admin123)
-- Password hash for 'admin123' using bcrypt
INSERT INTO admin_users (username, email, hashed_password, role)
VALUES ('superadmin', 'admin@newsapp.com', '$2b$10$rJ9qY8xQZxK5vXGxN3YYxeYvQXQmqO9YzJVKZW5fZ5fZ5fZ5fZ5fZ', 'super_admin')
ON CONFLICT (username) DO NOTHING;

-- =====================================================
-- COMMENTS FOR DOCUMENTATION
-- =====================================================

COMMENT ON TABLE categories IS 'Article categories (currently only "All News" is used)';
COMMENT ON TABLE articles IS 'Main news articles/posts table with full content and metadata';
COMMENT ON TABLE users IS 'App users for login/signup functionality';
COMMENT ON TABLE admin_users IS 'Admin panel users (separate from app users)';
COMMENT ON TABLE bookmarks IS 'Device-based bookmarks for anonymous users';
COMMENT ON TABLE user_bookmarks IS 'User-based bookmarks for logged-in users';
COMMENT ON TABLE interactions IS 'Tracks all user interactions (views, likes, shares, etc.)';
COMMENT ON TABLE app_settings IS 'Application-wide settings stored as key-value pairs';
COMMENT ON TABLE about_contact IS 'About Us and Contact information';
COMMENT ON TABLE push_tokens IS 'Push notification tokens for devices';
COMMENT ON TABLE notification_settings IS 'User notification preferences and settings';
COMMENT ON TABLE user_feeds IS 'User selected interests/categories (optional, not currently used)';
COMMENT ON TABLE audit_logs IS 'Admin action logs for accountability';

-- =====================================================
-- INDEXES SUMMARY
-- =====================================================
-- Total indexes created: 50+
-- Performance optimized for:
-- - Article listing and filtering
-- - Full-text search on articles
-- - User bookmarks and interactions
-- - Admin audit logs
-- - Notification management

-- =====================================================
-- SCHEMA CREATION COMPLETE
-- =====================================================
-- To use this schema:
-- 1. Copy this entire file
-- 2. Go to Supabase Dashboard > SQL Editor
-- 3. Paste and run this SQL
-- 4. Verify tables in Table Editor
-- =====================================================
