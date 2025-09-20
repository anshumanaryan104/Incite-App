-- Add created_at column to user_bookmarks table
ALTER TABLE user_bookmarks
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP;

-- Update existing rows to have current timestamp if needed
UPDATE user_bookmarks
SET created_at = CURRENT_TIMESTAMP
WHERE created_at IS NULL;

-- Optionally, add an index on created_at for better query performance
CREATE INDEX IF NOT EXISTS idx_user_bookmarks_created_at
ON user_bookmarks(created_at DESC);

-- Verify the changes
SELECT column_name, data_type, column_default
FROM information_schema.columns
WHERE table_name = 'user_bookmarks'
ORDER BY ordinal_position;