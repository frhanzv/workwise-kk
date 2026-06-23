<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToAttendanceRecords extends Migration
{
    public function up()
    {
        // Add indexes to improve query performance for attendance lookups
        
        // Index for checking active check-ins (used frequently in RFID processing)
        $this->db->query('CREATE INDEX idx_attendance_active_checkin ON attendance_records (worker_id, zone_id, date, check_out_time)');
        
        // Index for date-based queries (used in attendance page)
        $this->db->query('CREATE INDEX idx_attendance_date ON attendance_records (date, worker_id)');
        
        // Index for worker lookups
        $this->db->query('CREATE INDEX idx_attendance_worker ON attendance_records (worker_id, date)');
        
        // Index for check-out time queries (used for recent check-out checks)
        $this->db->query('CREATE INDEX idx_attendance_checkout ON attendance_records (worker_id, zone_id, check_out_time)');
    }

    public function down()
    {
        // Drop the indexes
        $this->db->query('DROP INDEX idx_attendance_active_checkin ON attendance_records');
        $this->db->query('DROP INDEX idx_attendance_date ON attendance_records');
        $this->db->query('DROP INDEX idx_attendance_worker ON attendance_records');
        $this->db->query('DROP INDEX idx_attendance_checkout ON attendance_records');
    }
}
