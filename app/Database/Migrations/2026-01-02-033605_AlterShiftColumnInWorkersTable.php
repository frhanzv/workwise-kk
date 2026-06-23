<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterShiftColumnInWorkersTable extends Migration
{
    public function up()
    {
        // Change shift column from ENUM to VARCHAR to support dynamic shifts from shifts table
        $this->forge->modifyColumn('workers', [
            'shift' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => false,
            ],
        ]);
    }

    public function down()
    {
        // Revert back to ENUM with original values
        $this->forge->modifyColumn('workers', [
            'shift' => [
                'type'           => 'ENUM',
                'constraint'     => ['morning', 'afternoon', 'night'],
                'default'        => 'morning',
            ],
        ]);
    }
}
