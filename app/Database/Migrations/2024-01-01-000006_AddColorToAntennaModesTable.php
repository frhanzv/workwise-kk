<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColorToAntennaModesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('antenna_modes', [
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'after' => 'mode_name'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('antenna_modes', 'color');
    }
}
