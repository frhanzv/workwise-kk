<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkerLeaveRecordModel extends Model
{
    protected $table = 'worker_leave_records';
    protected $primaryKey = 'id';
    protected $allowedFields = ['worker_id', 'leave_reason_id', 'leave_date', 'notes', 'created_by'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'worker_id' => 'required',
        'leave_reason_id' => 'required|integer',
        'leave_date' => 'required|valid_date',
    ];
    
    protected $validationMessages = [
        'worker_id' => [
            'required' => 'Worker ID is required',
        ],
        'leave_reason_id' => [
            'required' => 'Leave reason is required',
        ],
        'leave_date' => [
            'required' => 'Leave date is required',
            'valid_date' => 'Please enter a valid date',
        ],
    ];

    /**
     * Get leave records with reason details for a worker
     */
    public function getWorkerLeaveRecords($workerId, $startDate = null, $endDate = null)
    {
        $builder = $this->db->table($this->table)
            ->select('worker_leave_records.*, leave_reasons.name as reason_name, leave_reasons.type as reason_type')
            ->join('leave_reasons', 'leave_reasons.id = worker_leave_records.leave_reason_id')
            ->where('worker_leave_records.worker_id', $workerId);
        
        if ($startDate) {
            $builder->where('worker_leave_records.leave_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('worker_leave_records.leave_date <=', $endDate);
        }
        
        return $builder->orderBy('worker_leave_records.leave_date', 'DESC')->get()->getResultArray();
    }

    /**
     * Check if leave already exists for a worker on a specific date
     */
    public function leaveExistsForDate($workerId, $leaveDate)
    {
        return $this->where('worker_id', $workerId)
                    ->where('leave_date', $leaveDate)
                    ->first() !== null;
    }

    /**
     * Delete leave record for a specific date
     */
    public function deleteByWorkerAndDate($workerId, $leaveDate)
    {
        return $this->where('worker_id', $workerId)
                    ->where('leave_date', $leaveDate)
                    ->delete();
    }
}
