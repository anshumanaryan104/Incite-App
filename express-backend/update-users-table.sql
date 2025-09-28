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