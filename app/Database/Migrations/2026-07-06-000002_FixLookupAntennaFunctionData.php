<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixLookupAntennaFunctionData extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('zone_antennas') || !$this->db->tableExists('zones')) {
            return;
        }

        $this->db->query(
            "UPDATE zone_antennas za
             INNER JOIN zones z ON z.zone_id = za.zone_id
             SET za.`function` = 'LOOKUP'
             WHERE za.status = 'active'
               AND z.`function` = 'LOOKUP'
               AND za.`function` <> 'LOOKUP'"
        );
    }

    public function down()
    {
        // No rollback — data repair only.
    }
}
