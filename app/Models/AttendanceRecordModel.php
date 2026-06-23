<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceRecordModel extends Model
{
    protected $table            = 'attendance_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'worker_id',
        'zone_id',
        'check_in_time',
        'check_out_time',
        'date'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Get today's attendance for a specific worker
    public function getTodayAttendance($workerId)
    {
        return $this->where('worker_id', $workerId)
                    ->where('date', date('Y-m-d'))
                    ->findAll();
    }

    // Get active check-in for a worker in a specific zone
    public function getActiveCheckIn($workerId, $zoneId, $date)
    {
        return $this->where('worker_id', $workerId)
                    ->where('zone_id', $zoneId)
                    ->where('date', $date)
                    ->where('check_out_time IS NULL')
                    ->first();
    }

    // Get all attendance records for today
    public function getTodayAllAttendance()
    {
        return $this->where('date', date('Y-m-d'))->findAll();
    }
    
    // Get attendance records for a specific date
    public function getAttendanceByDate($date)
    {
        return $this->where('date', $date)->findAll();
    }
    
    // Get attendance records for a date range
    public function getAttendanceByDateRange($startDate, $endDate): array
    {
        $query = $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE `date` >= ? AND `date` <= ? ORDER BY `date`, `check_in_time`",
            [$startDate, $endDate]
        );
        return $query->getResultArray();
    }

    // Returns ['2026-05-01' => 3, '2026-05-02' => 7, ...] for chart rendering
    public function getDailyCountsByDateRange($startDate, $endDate): array
    {
        $query = $this->db->query(
            "SELECT `date`, COUNT(*) AS check_ins FROM `{$this->table}` WHERE `date` >= ? AND `date` <= ? GROUP BY `date`",
            [$startDate, $endDate]
        );
        $counts = [];
        foreach ($query->getResultArray() as $row) {
            $counts[$row['date']] = (int)$row['check_ins'];
        }
        return $counts;
    }
}
