<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SetEmptyStorageToAllZones extends Migration
{
    public function up()
    {
        $allZones = json_encode(['*']);

        foreach (['products', 'raw_materials'] as $table) {
            if (!$this->db->tableExists($table) || !$this->db->fieldExists('storage_location', $table)) {
                continue;
            }

            $this->db->table($table)
                ->groupStart()
                    ->where('storage_location', null)
                    ->orWhere('storage_location', '')
                    ->orWhere('storage_location', '[]')
                    ->orWhere('storage_location', 'null')
                ->groupEnd()
                ->update(['storage_location' => $allZones]);
        }
    }

    public function down()
    {
        // No reverse — empty previously meant all zones.
    }
}
