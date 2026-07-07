<?php

namespace App\Models;

use CodeIgniter\Model;

class StockCheckDiscrepancyModel extends Model
{
    protected $table            = 'stock_check_discrepancies';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'session_id', 'item_type', 'item_id', 'tag_id',
        'epc_no', 'tag_label', 'quantity', 'user_id',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
