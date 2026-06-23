<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateZoneAntennasTable extends Migration
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
            'zone_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'antenna_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => '45',
            ],
            'port' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 49152,
            ],
            'antenna_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'function' => [
                'type'       => 'ENUM',
                'constraint' => ['IN', 'OUT', 'IN / OUT'],
                'default'    => 'IN / OUT',
            ],
            'power_level' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 30,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
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
        $this->forge->addKey('zone_id');
        $this->forge->addForeignKey('zone_id', 'zones', 'zone_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('zone_antennas');
    }

    public function down()
    {
        $this->forge->dropTable('zone_antennas');
    }
}
