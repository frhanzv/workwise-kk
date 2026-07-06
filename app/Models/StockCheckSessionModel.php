<?php

namespace App\Models;

use CodeIgniter\Model;

class StockCheckSessionModel extends Model
{
    protected $table            = 'stock_check_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'item_type', 'item_id', 'scan_method', 'status',
        'expected_balance', 'counted_balance', 'variance',
        'user_id', 'notes', 'completed_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
