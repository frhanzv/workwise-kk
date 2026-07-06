<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductMasterListFields extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('suppliers', 'products')) {
            $this->forge->addColumn('products', [
                'suppliers' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'JSON array of supplier names',
                    'after' => 'description',
                ],
            ]);
        }

        if (!$this->db->fieldExists('storage_location', 'products')) {
            $this->forge->addColumn('products', [
                'storage_location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'comment'    => 'Zone ID for storage location',
                    'after'      => 'suppliers',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('storage_location', 'products')) {
            $this->forge->dropColumn('products', 'storage_location');
        }
        if ($this->db->fieldExists('suppliers', 'products')) {
            $this->forge->dropColumn('products', 'suppliers');
        }
    }
}
