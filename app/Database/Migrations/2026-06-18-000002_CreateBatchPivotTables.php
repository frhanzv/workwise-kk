<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBatchPivotTables extends Migration
{
    public function up()
    {
        // batch_raw_materials — raw materials consumed in a batch
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'batch_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned'  => true,
                'null'      => false,
            ],
            'raw_material_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'added_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey('batch_id');
        $this->forge->addKey('raw_material_id');
        $this->forge->addForeignKey('batch_id', 'production_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('raw_material_id', 'raw_materials', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('batch_raw_materials');

        // batch_products — products produced by a batch
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'batch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'added_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addKey('batch_id');
        $this->forge->addKey('product_id');
        $this->forge->addForeignKey('batch_id', 'production_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('batch_products');
    }

    public function down()
    {
        $this->forge->dropTable('batch_products');
        $this->forge->dropTable('batch_raw_materials');
    }
}
