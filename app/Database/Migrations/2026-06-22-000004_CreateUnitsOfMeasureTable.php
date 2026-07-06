<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnitsOfMeasureTable extends Migration
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
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('units_of_measure');

        $defaults = config('Inventory')->defaultUnits;
        $rows     = [];
        $order    = 0;

        foreach ($defaults as $unit) {
            $rows[] = [
                'code'        => $unit['code'],
                'label'       => $unit['label'],
                'description' => null,
                'sort_order'  => $order++,
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }

        if ($rows !== []) {
            $this->db->table('units_of_measure')->insertBatch($rows);
        }
    }

    public function down()
    {
        $this->forge->dropTable('units_of_measure');
    }
}
