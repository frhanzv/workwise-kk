<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLookupToZoneAntennaFunction extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('zone_antennas') || !$this->db->fieldExists('function', 'zone_antennas')) {
            return;
        }

        $this->db->query(
            "ALTER TABLE `zone_antennas` MODIFY `function` ENUM('IN', 'OUT', 'IN / OUT', 'LOOKUP') NOT NULL DEFAULT 'IN / OUT'"
        );
    }

    public function down()
    {
        if (!$this->db->tableExists('zone_antennas') || !$this->db->fieldExists('function', 'zone_antennas')) {
            return;
        }

        $this->db->query(
            "UPDATE `zone_antennas` SET `function` = 'IN / OUT' WHERE `function` = 'LOOKUP'"
        );

        $this->db->query(
            "ALTER TABLE `zone_antennas` MODIFY `function` ENUM('IN', 'OUT', 'IN / OUT') NOT NULL DEFAULT 'IN / OUT'"
        );
    }
}
