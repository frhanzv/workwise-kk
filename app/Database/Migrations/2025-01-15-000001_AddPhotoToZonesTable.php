<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToZonesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('zones', [
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'icon_color',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('zones', 'photo');
    }
}
