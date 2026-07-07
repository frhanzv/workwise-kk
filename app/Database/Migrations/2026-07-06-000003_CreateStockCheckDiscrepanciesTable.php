<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStockCheckDiscrepanciesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'INT', 'unsigned' => true],
            'item_type'  => ['type' => 'ENUM', 'constraint' => ['product', 'raw_material']],
            'item_id'    => ['type' => 'INT', 'unsigned' => true],
            'tag_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'epc_no'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'tag_label'  => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'quantity'   => ['type' => 'DECIMAL', 'constraint' => '12,3', 'default' => 0],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_id');
        $this->forge->addKey(['item_type', 'item_id']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('stock_check_discrepancies');
    }

    public function down()
    {
        $this->forge->dropTable('stock_check_discrepancies', true);
    }
}
