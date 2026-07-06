<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRawMaterialMasterListFields extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('suppliers', 'raw_materials')) {
            $this->forge->addColumn('raw_materials', [
                'suppliers' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'comment' => 'JSON array of supplier names',
                    'after'   => 'description',
                ],
            ]);
        }

        if (!$this->db->fieldExists('storage_location', 'raw_materials')) {
            $this->forge->addColumn('raw_materials', [
                'storage_location' => [
                    'type'    => 'TEXT',
                    'null'    => true,
                    'comment' => 'JSON array of allowed zone IDs',
                    'after'   => 'suppliers',
                ],
            ]);
        }

        if (!$this->db->fieldExists('expiry_date', 'raw_materials')) {
            $this->forge->addColumn('raw_materials', [
                'expiry_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'shelf_life_months',
                ],
            ]);
        }

        if (!$this->db->fieldExists('cost_price', 'raw_materials')) {
            $this->forge->addColumn('raw_materials', [
                'cost_price' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'null'       => true,
                    'after'      => 'expiry_date',
                ],
            ]);
        }

        if (!$this->db->fieldExists('selling_price', 'raw_materials')) {
            $this->forge->addColumn('raw_materials', [
                'selling_price' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'null'       => true,
                    'after'      => 'cost_price',
                ],
            ]);
        }

        $rows = $this->db->table('raw_materials')
            ->select('id, warehouse_location, supplier_name, suppliers, storage_location')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $update = [];

            $storage = $row['storage_location'] ?? null;
            if (($storage === null || $storage === '') && !empty($row['warehouse_location'])) {
                $update['storage_location'] = json_encode([(string) $row['warehouse_location']]);
            }

            $suppliers = $row['suppliers'] ?? null;
            if (($suppliers === null || $suppliers === '') && !empty($row['supplier_name'])) {
                $update['suppliers'] = json_encode([(string) $row['supplier_name']]);
            }

            if ($update !== []) {
                $this->db->table('raw_materials')->where('id', $row['id'])->update($update);
            }
        }
    }

    public function down()
    {
        foreach (['selling_price', 'cost_price', 'expiry_date', 'storage_location', 'suppliers'] as $col) {
            if ($this->db->fieldExists($col, 'raw_materials')) {
                $this->forge->dropColumn('raw_materials', $col);
            }
        }
    }
}
