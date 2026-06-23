-- Add documents column to workers table
ALTER TABLE `workers` ADD COLUMN `documents` TEXT NULL AFTER `profile_photo`;
