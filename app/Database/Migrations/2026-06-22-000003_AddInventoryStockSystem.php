<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventoryStockSystem extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'quantity_on_hand' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'default' => 0, 'after' => 'unit'],
            'qr_code'          => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'quantity_on_hand'],
        ]);
        $this->forge->addColumn('raw_materials', [
            'quantity_on_hand' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'default' => 0, 'after' => 'unit'],
            'qr_code'          => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'quantity_on_hand'],
        ]);

        $this->db->query('ALTER TABLE products ADD UNIQUE KEY products_qr_code_unique (qr_code)');
        $this->db->query('ALTER TABLE raw_materials ADD UNIQUE KEY raw_materials_qr_code_unique (qr_code)');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'item_type' => ['type' => 'ENUM', 'constraint' => ['product', 'raw_material']],
            'item_id' => ['type' => 'INT', 'unsigned' => true],
            'transaction_type' => ['type' => 'ENUM', 'constraint' => ['stock_in', 'stock_out', 'stock_check_adjust']],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'default' => 0],
            'balance_after' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'default' => 0],
            'scan_method' => ['type' => 'ENUM', 'constraint' => ['uhf', 'qr', 'manual', 'web'], 'default' => 'manual'],
            'scan_reference' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'zone_id' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'stock_check_session_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['item_type', 'item_id']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('inventory_transactions');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'item_type' => ['type' => 'ENUM', 'constraint' => ['product', 'raw_material']],
            'item_id' => ['type' => 'INT', 'unsigned' => true],
            'scan_method' => ['type' => 'ENUM', 'constraint' => ['uhf', 'qr'], 'default' => 'qr'],
            'status' => ['type' => 'ENUM', 'constraint' => ['in_progress', 'completed'], 'default' => 'in_progress'],
            'expected_balance' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'default' => 0],
            'counted_balance' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'variance' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['item_type', 'item_id']);
        $this->forge->createTable('stock_check_sessions');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'INT', 'unsigned' => true],
            'scan_reference' => ['type' => 'VARCHAR', 'constraint' => 150],
            'scan_method' => ['type' => 'ENUM', 'constraint' => ['uhf', 'qr'], 'default' => 'qr'],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_id');
        $this->forge->createTable('stock_check_scans');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'token' => ['type' => 'VARCHAR', 'constraint' => 64],
            'expires_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        $this->forge->addKey('user_id');
        $this->forge->createTable('api_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('api_tokens', true);
        $this->forge->dropTable('stock_check_scans', true);
        $this->forge->dropTable('stock_check_sessions', true);
        $this->forge->dropTable('inventory_transactions', true);
        $this->forge->dropColumn('products', ['quantity_on_hand', 'qr_code']);
        $this->forge->dropColumn('raw_materials', ['quantity_on_hand', 'qr_code']);
    }
}
