<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConsumedStatusToRawMaterials extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `raw_materials` MODIFY `status` ENUM('active','inactive','consumed') NOT NULL DEFAULT 'active'");
    }

    public function down()
    {
        // Revert consumed items to inactive before removing the enum value
        $this->db->query("UPDATE `raw_materials` SET `status` = 'inactive' WHERE `status` = 'consumed'");
        $this->db->query("ALTER TABLE `raw_materials` MODIFY `status` ENUM('active','inactive') NOT NULL DEFAULT 'active'");
    }
}
