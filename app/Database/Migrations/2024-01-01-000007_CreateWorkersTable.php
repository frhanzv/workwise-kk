<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWorkersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'worker_id' => [
                'type'           => 'VARCHAR',
                'constraint'     => '50',
            ],
            'first_name' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
            ],
            'last_name' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
            ],
            'email' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'unique'         => true,
            ],
            'phone' => [
                'type'           => 'VARCHAR',
                'constraint'     => '20',
                'null'           => true,
            ],
            'address' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'department' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
            ],
            'position' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
            ],
            'start_date' => [
                'type'           => 'DATE',
            ],
            'shift' => [
                'type'           => 'ENUM',
                'constraint'     => ['morning', 'afternoon', 'night'],
                'default'        => 'morning',
            ],
            'status' => [
                'type'           => 'ENUM',
                'constraint'     => ['active', 'inactive', 'on_break', 'offline'],
                'default'        => 'active',
            ],
            'profile_photo' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'null'           => true,
            ],
            'assigned_zones' => [
                'type'           => 'TEXT',
                'null'           => true,
                'comment'        => 'JSON array of zone IDs',
            ],
            'last_active' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'created_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'updated_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
        ]);
        
        $this->forge->addKey('worker_id', true);
        $this->forge->createTable('workers');
    }

    public function down()
    {
        $this->forge->dropTable('workers');
    }
}
