-- Create leave_reasons table
CREATE TABLE IF NOT EXISTS `leave_reasons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('Paid Leave','Medical Leave','Unpaid Leave','Other') NOT NULL DEFAULT 'Paid Leave',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default leave reasons
INSERT INTO `leave_reasons` (`name`, `type`, `description`, `is_active`) VALUES
('Annual Leave', 'Paid Leave', 'Paid annual vacation leave', 1),
('Sick Leave', 'Medical Leave', 'Medical leave for illness', 1),
('Medical Appointment', 'Medical Leave', 'Leave for medical appointments', 1),
('Family Emergency', 'Paid Leave', 'Emergency family matters', 1),
('Personal Leave', 'Unpaid Leave', 'Personal matters requiring time off', 1),
('Maternity Leave', 'Paid Leave', 'Maternity leave for mothers', 1),
('Paternity Leave', 'Paid Leave', 'Paternity leave for fathers', 1),
('Bereavement Leave', 'Paid Leave', 'Leave for funeral or bereavement', 1),
('Training/Conference', 'Paid Leave', 'Attending training or conference', 1),
('Unpaid Personal Leave', 'Unpaid Leave', 'Unpaid leave for personal reasons', 1);

-- Create worker_leave_records table
CREATE TABLE IF NOT EXISTS `worker_leave_records` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `worker_id` varchar(50) NOT NULL,
  `leave_reason_id` int(11) unsigned NOT NULL,
  `leave_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL COMMENT 'User ID who created this record',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_id` (`worker_id`),
  KEY `idx_leave_reason_id` (`leave_reason_id`),
  KEY `idx_leave_date` (`leave_date`),
  CONSTRAINT `fk_worker_leave_worker` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_worker_leave_reason` FOREIGN KEY (`leave_reason_id`) REFERENCES `leave_reasons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
