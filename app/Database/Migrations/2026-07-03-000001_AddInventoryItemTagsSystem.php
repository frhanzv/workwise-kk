<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventoryItemTagsSystem extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'tag_mode' => [
                'type'       => 'ENUM',
                'constraint' => ['single', 'multi'],
                'default'    => 'single',
                'after'      => 'epc_no',
            ],
            'qty_per_tag' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,3',
                'default'    => 1,
                'after'      => 'tag_mode',
            ],
        ]);

        $this->forge->addColumn('raw_materials', [
            'tag_mode' => [
                'type'       => 'ENUM',
                'constraint' => ['single', 'multi'],
                'default'    => 'single',
                'after'      => 'epc_no',
            ],
            'qty_per_tag' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,3',
                'default'    => 1,
                'after'      => 'tag_mode',
            ],
        ]);

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
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'epc_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,3',
                'default'    => 1,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'last_seen_zone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'last_seen_at' => [
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
        $this->forge->addUniqueKey('epc_no');
        $this->forge->addKey(['item_type', 'item_id']);
        $this->forge->createTable('inventory_item_tags');

        if ($this->db->fieldExists('tag_id', 'inventory_zone_records')) {
            return;
        }

        $this->forge->addColumn('inventory_zone_records', [
            'tag_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'item_id',
            ],
        ]);

        $this->migrateLegacyEpcTags();
        $this->backfillProductQrCodes();
    }

    public function down()
    {
        $this->forge->dropColumn('inventory_zone_records', 'tag_id');
        $this->forge->dropTable('inventory_item_tags', true);
        $this->forge->dropColumn('products', ['tag_mode', 'qty_per_tag']);
        $this->forge->dropColumn('raw_materials', ['tag_mode', 'qty_per_tag']);
    }

    private function migrateLegacyEpcTags(): void
    {
        $now = date('Y-m-d H:i:s');

        foreach (['products' => 'product', 'raw_materials' => 'raw_material'] as $table => $itemType) {
            if (!$this->db->tableExists($table)) {
                continue;
            }

            $rows = $this->db->table($table)
                ->where('epc_no IS NOT NULL')
                ->where('epc_no !=', '')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $exists = $this->db->table('inventory_item_tags')
                    ->where('epc_no', $row['epc_no'])
                    ->countAllResults();

                if ($exists > 0) {
                    continue;
                }

                $qty = (float) ($row['qty_per_tag'] ?? 1);
                if ($qty <= 0) {
                    $qty = 1;
                }

                $this->db->table('inventory_item_tags')->insert([
                    'item_type'      => $itemType,
                    'item_id'        => (int) $row['id'],
                    'epc_no'         => $row['epc_no'],
                    'quantity'       => $qty,
                    'status'         => 'active',
                    'last_seen_zone' => $row['last_seen_zone'] ?? null,
                    'last_seen_at'   => $row['last_seen_at'] ?? null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }
    }

    private function backfillProductQrCodes(): void
    {
        if (!$this->db->tableExists('products')) {
            return;
        }

        $products = $this->db->table('products')
            ->where('qr_code IS NULL')
            ->orWhere('qr_code', '')
            ->get()
            ->getResultArray();

        foreach ($products as $product) {
            $qr = 'WW|P|' . $product['product_code'] . '|' . ($product['lot_number'] ?? '');
            $this->db->table('products')
                ->where('id', $product['id'])
                ->update(['qr_code' => $qr]);
        }

        if (!$this->db->tableExists('raw_materials')) {
            return;
        }

        $materials = $this->db->table('raw_materials')
            ->where('qr_code IS NULL')
            ->orWhere('qr_code', '')
            ->get()
            ->getResultArray();

        foreach ($materials as $material) {
            $qr = 'WW|RM|' . $material['material_code'];
            $this->db->table('raw_materials')
                ->where('id', $material['id'])
                ->update(['qr_code' => $qr]);
        }
    }
}
