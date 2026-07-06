<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiTokenModel extends Model
{
    protected $table            = 'api_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['user_id', 'token', 'expires_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function createForUser(int $userId, int $ttlDays = 30): string
    {
        $token = bin2hex(random_bytes(32));
        $this->where('user_id', $userId)->delete();
        $this->insert([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$ttlDays} days")),
        ]);
        return $token;
    }

    public function findValid(string $token): ?array
    {
        $row = $this->where('token', $token)->first();
        if (!$row) {
            return null;
        }
        if (strtotime($row['expires_at']) < time()) {
            return null;
        }
        return $row;
    }
}
