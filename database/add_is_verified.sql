-- Add is_verified column to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT TRUE;

-- Update existing users to be verified (optional, but good for migration)
UPDATE users SET is_verified = TRUE WHERE is_verified IS NULL;
