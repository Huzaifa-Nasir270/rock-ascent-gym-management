-- Upgrade Notifications Table for Read Tracking
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS read_at TIMESTAMP NULL;
