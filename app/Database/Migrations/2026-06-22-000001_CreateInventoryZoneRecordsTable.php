<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryZoneRecordsTable extends Migration
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
            'item_type' => [
                'type'       => 'ENUM',
                'constraint' => ['product', 'raw_material'],
                'null'       => false,
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'zone_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'check_in_time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'check_out_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date' => [
                'type' => 'DATE',
                'null' => false,
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
        $this->forge->addKey(['item_type', 'item_id', 'date']);
        $this->forge->addKey(['zone_id', 'date']);
        $this->forge->addKey('check_in_time');
        $this->forge->createTable('inventory_zone_records');
    }

    public function down()
    {
        $this->forge->dropTable('inventory_zone_records');
    }
}
