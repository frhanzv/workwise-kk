<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffShiftAllocationModel extends Model
{
    protected $table = 'staff_shift_allocation';
    protected $primaryKey = 'id';
    protected $allowedFields = ['group_id', 'allocation_date', 'shift_code', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'group_id' => 'required',
        'allocation_date' => 'required|valid_date',
        'shift_code' => 'required',
    ];
    
    protected $validationMessages = [
        'group_id' => [
            'required' => 'Group is required',
        ],
        'allocation_date' => [
            'required' => 'Date is required',
            'valid_date' => 'Please provide a valid date',
        ],
        'shift_code' => [
            'required' => 'Shift code is required',
        ],
    ];
    
    public function getAllocationsByGroupAndDateRange($groupId, $startDate, $endDate)
    {
        return $this->where('group_id', $groupId)
                    ->where('allocation_date >=', $startDate)
                    ->where('allocation_date <=', $endDate)
                    ->findAll();
    }
    
    public function deleteByGroupAndDateRange($groupId, $startDate, $endDate)
    {
        return $this->where('group_id', $groupId)
                    ->where('allocation_date >=', $startDate)
                    ->where('allocation_date <=', $endDate)
                    ->delete();
    }
}
