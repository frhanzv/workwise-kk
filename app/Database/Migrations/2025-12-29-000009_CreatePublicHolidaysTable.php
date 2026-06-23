<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePublicHolidaysTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'holiday_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['Federal', 'State'],
                'null' => false,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('public_holidays');
    }

    public function down()
    {
        $this->forge->dropTable('public_holidays');
    }
}
