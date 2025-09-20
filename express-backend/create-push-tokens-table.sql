-- Create push_tokens table for managing push notification tokens
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
    -- Unique constraint on device_id when present
    CONSTRAINT unique_device UNIQUE (device_id),
    -- Unique constraint on user_id + platform combination
    CONSTRAINT unique_user_platform UNIQUE (user_id, platform)
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_push_tokens_user_id ON push_tokens(user_id);
CREATE INDEX IF NOT EXISTS idx_push_tokens_device_id ON push_tokens(device_id);
CREATE INDEX IF NOT EXISTS idx_push_tokens_token ON push_tokens(token);
CREATE INDEX IF NOT EXISTS idx_push_tokens_active ON push_tokens(is_active);
CREATE INDEX IF NOT EXISTS idx_push_tokens_platform ON push_tokens(platform);

-- Create trigger for updating updated_at timestamp
CREATE OR REPLACE FUNCTION update_push_tokens_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger
DROP TRIGGER IF EXISTS update_push_tokens_updated_at ON push_tokens;
CREATE TRIGGER update_push_tokens_updated_at
BEFORE UPDATE ON push_tokens
FOR EACH ROW
EXECUTE FUNCTION update_push_tokens_updated_at();

-- Grant permissions
GRANT ALL ON push_tokens TO authenticated;
GRANT ALL ON push_tokens TO anon;
GRANT USAGE ON SEQUENCE push_tokens_id_seq TO authenticated;
GRANT USAGE ON SEQUENCE push_tokens_id_seq TO anon;

-- Insert sample data (optional - comment out in production)
-- INSERT INTO push_tokens (device_id, token, platform, device_info) VALUES
-- ('device_sample_001', 'fcm_sample_token_12345', 'android', '{"model": "Galaxy S23", "os_version": "Android 14"}'),
-- ('device_sample_002', 'apns_sample_token_67890', 'ios', '{"model": "iPhone 15", "os_version": "iOS 17"}');

-- Verify the table was created
SELECT
    column_name,
    data_type,
    column_default,
    is_nullable
FROM information_schema.columns
WHERE table_name = 'push_tokens'
ORDER BY ordinal_position;