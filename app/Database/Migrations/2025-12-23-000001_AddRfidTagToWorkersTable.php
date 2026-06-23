<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRfidTagToWorkersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('workers', [
            'rfid_tag_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'unique'     => true,
                'comment'    => 'UHF RFID tag ID from Yanzeo SA810 reader',
                'after'      => 'worker_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('workers', 'rfid_tag_id');
    }
}
