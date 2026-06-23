<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatesTable extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'country_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addForeignKey('country_id', 'countries', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('states');
        
        // Get Malaysia ID
        $db = \Config\Database::connect();
        $malaysia = $db->table('countries')->where('code', 'MY')->get()->getRow();
        
        if ($malaysia) {
            // Insert Malaysian states
            $states = [
                ['name' => 'Johor', 'country_id' => $malaysia->id],
                ['name' => 'Kedah', 'country_id' => $malaysia->id],
                ['name' => 'Kelantan', 'country_id' => $malaysia->id],
                ['name' => 'Kuala Lumpur', 'country_id' => $malaysia->id],
                ['name' => 'Labuan', 'country_id' => $malaysia->id],
                ['name' => 'Melaka', 'country_id' => $malaysia->id],
                ['name' => 'Negeri Sembilan', 'country_id' => $malaysia->id],
                ['name' => 'Pahang', 'country_id' => $malaysia->id],
                ['name' => 'Penang', 'country_id' => $malaysia->id],
                ['name' => 'Perak', 'country_id' => $malaysia->id],
                ['name' => 'Perlis', 'country_id' => $malaysia->id],
                ['name' => 'Putrajaya', 'country_id' => $malaysia->id],
                ['name' => 'Sabah', 'country_id' => $malaysia->id],
                ['name' => 'Sarawak', 'country_id' => $malaysia->id],
                ['name' => 'Selangor', 'country_id' => $malaysia->id],
                ['name' => 'Terengganu', 'country_id' => $malaysia->id],
            ];
            
            foreach ($states as &$state) {
                $state['is_active'] = 1;
                $state['created_at'] = date('Y-m-d H:i:s');
            }
            
            $db->table('states')->insertBatch($states);
        }
    }

    public function down()
    {
        $this->forge->dropTable('states');
    }
}
