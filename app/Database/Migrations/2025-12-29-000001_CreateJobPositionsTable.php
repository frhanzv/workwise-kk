<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJobPositionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addUniqueKey('title');
        $this->forge->createTable('job_positions');
        
        // Insert some default job positions
        $data = [
            [
                'title' => 'Line Operator',
                'description' => 'Factory line operator',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Supervisor',
                'description' => 'Line supervisor',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Manager',
                'description' => 'Department manager',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Quality Controller',
                'description' => 'Quality control inspector',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Maintenance Technician',
                'description' => 'Equipment maintenance',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        
        $this->db->table('job_positions')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('job_positions');
    }
}
