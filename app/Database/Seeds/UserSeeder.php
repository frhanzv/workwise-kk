<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username'  => 'admin',
            'email'     => 'admin@workwise.com',
            'password'  => password_hash('admin123', PASSWORD_DEFAULT),
            'full_name' => 'System Administrator',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Insert user
        $this->db->table('users')->insert($data);
    }
}
