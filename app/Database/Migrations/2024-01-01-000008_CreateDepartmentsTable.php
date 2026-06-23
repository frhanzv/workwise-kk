<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => true,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('departments');
        
        // Insert default departments
        $data = [
            ['name' => 'Assembly', 'description' => 'Assembly line operations', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Logistics', 'description' => 'Logistics and shipping', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Quality Control', 'description' => 'Quality assurance and control', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Management', 'description' => 'Management and administration', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Maintenance', 'description' => 'Equipment maintenance', 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')],
        ];
        
        $this->db->table('departments')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('departments');
    }
}
