<?php

namespace App\Models;

use CodeIgniter\Model;

class StockCheckScanModel extends Model
{
    protected $table            = 'stock_check_scans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['session_id', 'scan_reference', 'scan_method', 'quantity'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = null;
}
