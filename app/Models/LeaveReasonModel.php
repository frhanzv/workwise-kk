<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveReasonModel extends Model
{
    protected $table = 'leave_reasons';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'type', 'description', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'type' => 'required|in_list[Paid Leave,Medical Leave,Unpaid Leave,Other]',
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Leave reason name is required',
            'min_length' => 'Leave reason name must be at least 2 characters',
        ],
        'type' => [
            'required' => 'Leave type is required',
            'in_list' => 'Please select a valid leave type',
        ],
    ];

    /**
     * Get all active leave reasons
     */
    public function getActiveReasons()
    {
        return $this->where('is_active', 1)->orderBy('type', 'ASC')->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Get leave reasons by type
     */
    public function getReasonsByType($type)
    {
        return $this->where('type', $type)->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
    }
}
