<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProductStorageLocationsMulti extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('storage_location', 'products')) {
            return;
        }

        // Widen column for JSON list of zone IDs.
        $this->forge->modifyColumn('products', [
            'storage_location' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of allowed zone IDs',
            ],
        ]);

        $rows = $this->db->table('products')->select('id, storage_location')->get()->getResultArray();
        foreach ($rows as $row) {
            $raw = $row['storage_location'] ?? null;
            if ($raw === null || $raw === '') {
                continue;
            }

            $decoded = json_decode((string) $raw, true);
            if (is_array($decoded)) {
                continue;
            }

            // Legacy single zone_id string → JSON array.
            $this->db->table('products')->where('id', $row['id'])->update([
                'storage_location' => json_encode([(string) $raw]),
            ]);
        }
    }

    public function down()
    {
        // Keep TEXT; no safe reverse of multi → single.
    }
}
