<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStaffShiftAllocationTable extends Migration
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
            'group_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'allocation_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'shift_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
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
        $this->forge->addKey(['group_id', 'allocation_date']);
        $this->forge->createTable('staff_shift_allocation');
    }

    public function down()
    {
        $this->forge->dropTable('staff_shift_allocation');
    }
}
