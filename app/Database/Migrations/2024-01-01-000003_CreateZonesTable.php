<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateZonesTable extends Migration
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
            'zone_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => 'location_on',
            ],
            'icon_color' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'blue',
            ],
            'antenna_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'antenna_color' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'purple',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => '45',
            ],
            'port' => [
                'type'       => 'INT',
                'constraint' => 6,
                'default'    => 8080,
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
        $this->forge->addUniqueKey('zone_id');
        $this->forge->createTable('zones');
    }

    public function down()
    {
        $this->forge->dropTable('zones');
    }
}
