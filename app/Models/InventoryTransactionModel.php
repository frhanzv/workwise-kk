<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryTransactionModel extends Model
{
    protected $table            = 'inventory_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'item_type', 'item_id', 'transaction_type', 'quantity', 'balance_after',
        'scan_method', 'scan_reference', 'zone_id', 'user_id', 'notes', 'stock_check_session_id',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
