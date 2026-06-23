<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIcNumberToWorkersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('workers', [
            'ic_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'worker_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('workers', 'ic_number');
    }
}
