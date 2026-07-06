<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTagDefaultQuantity extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('default_quantity', 'inventory_item_tags')) {
            $this->forge->addColumn('inventory_item_tags', [
                'default_quantity' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,3',
                    'default'    => 0,
                    'after'      => 'quantity',
                ],
            ]);
        }

        $this->db->query(
            'UPDATE inventory_item_tags SET default_quantity = quantity WHERE default_quantity IS NULL OR default_quantity = 0'
        );
    }

    public function down()
    {
        if ($this->db->fieldExists('default_quantity', 'inventory_item_tags')) {
            $this->forge->dropColumn('inventory_item_tags', 'default_quantity');
        }
    }
}
