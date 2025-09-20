-- Create user_feeds table to store user's selected interests/categories
CREATE TABLE IF NOT EXISTS user_feeds (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    device_id VARCHAR(255),
    category_ids INTEGER[] NOT NULL, -- Array of category IDs user is interested in
    category_names TEXT[], -- Array of category names for quick reference
    preferences JSONB DEFAULT '{}', -- Additional preferences like notification settings per category
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Ensure either user_id or device_id is present
    CONSTRAINT has_identifier CHECK (user_id IS NOT NULL OR device_id IS NOT NULL),
    -- Unique constraint on device_id when present
    CONSTRAINT unique_device_feed UNIQUE (device_id),
    -- Unique constraint on user_id when present
    CONSTRAINT unique_user_feed UNIQUE (user_id)
);

-- Create indexes for better performance
CREATE INDEX idx_user_feeds_user_id ON user_feeds(user_id);
CREATE INDEX idx_user_feeds_device_id ON user_feeds(device_id);
CREATE INDEX idx_user_feeds_category_ids ON user_feeds USING GIN(category_ids);
CREATE INDEX idx_user_feeds_active ON user_feeds(is_active);

-- Grant permissions
GRANT ALL ON user_feeds TO authenticated;
GRANT ALL ON user_feeds TO anon;
GRANT USAGE ON SEQUENCE user_feeds_id_seq TO authenticated;
GRANT USAGE ON SEQUENCE user_feeds_id_seq TO anon;

-- Create a function to update the updated_at timestamp
CREATE OR REPLACE FUNCTION update_user_feeds_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to automatically update updated_at
CREATE TRIGGER update_user_feeds_updated_at_trigger
BEFORE UPDATE ON user_feeds
FOR EACH ROW
EXECUTE FUNCTION update_user_feeds_updated_at();

-- Add comments for documentation
COMMENT ON TABLE user_feeds IS 'Stores user selected interests/categories for personalized feed';
COMMENT ON COLUMN user_feeds.user_id IS 'Reference to registered user ID';
COMMENT ON COLUMN user_feeds.device_id IS 'Device identifier for anonymous users';
COMMENT ON COLUMN user_feeds.category_ids IS 'Array of category IDs the user is interested in';
COMMENT ON COLUMN user_feeds.category_names IS 'Cached category names for quick display';
COMMENT ON COLUMN user_feeds.preferences IS 'JSON object storing additional preferences per category';