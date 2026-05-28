-- ============================================================================
-- DATABASE SETUP FOR DISTRIBUTED TASK SCHEDULER
-- ============================================================================
-- This script creates the database and user for the Task Scheduler application
-- Run this in MySQL before running migrations
-- ============================================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS task_scheduler CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create a dedicated user (optional - you can use root)
-- Uncomment the lines below if you want a dedicated user
-- CREATE USER IF NOT EXISTS 'task_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT ALL PRIVILEGES ON task_scheduler.* TO 'task_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Use the database
USE task_scheduler;

-- Show confirmation
SELECT 'Database task_scheduler created successfully!' AS Status;

-- ============================================================================
-- NOTES:
-- ============================================================================
-- 1. If using root user, update server/.env with:
--    DB_USERNAME=root
--    DB_PASSWORD=your_root_password
--
-- 2. If creating a dedicated user, uncomment the CREATE USER lines above and update:
--    DB_USERNAME=task_user
--    DB_PASSWORD=your_secure_password
--
-- 3. After running this script, run migrations:
--    cd server
--    php artisan migrate
--    php artisan db:seed --class=AdminUserSeeder
-- ============================================================================
