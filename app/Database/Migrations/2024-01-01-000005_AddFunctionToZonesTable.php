<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFunctionToZonesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('zones', [
            'function' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'after' => 'power_level'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('zones', 'function');
    }
}
