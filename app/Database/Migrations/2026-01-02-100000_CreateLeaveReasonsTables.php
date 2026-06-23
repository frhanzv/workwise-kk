<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeaveReasonsTables extends Migration
{
    public function up()
    {
        // Create leave_reasons table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['Paid Leave', 'Medical Leave', 'Unpaid Leave', 'Other'],
                'default' => 'Paid Leave',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('type');
        $this->forge->addKey('is_active');
        $this->forge->createTable('leave_reasons');

        // Insert default leave reasons
        $data = [
            ['name' => 'Annual Leave', 'type' => 'Paid Leave', 'description' => 'Paid annual vacation leave', 'is_active' => 1],
            ['name' => 'Sick Leave', 'type' => 'Medical Leave', 'description' => 'Medical leave for illness', 'is_active' => 1],
            ['name' => 'Medical Appointment', 'type' => 'Medical Leave', 'description' => 'Leave for medical appointments', 'is_active' => 1],
            ['name' => 'Family Emergency', 'type' => 'Paid Leave', 'description' => 'Emergency family matters', 'is_active' => 1],
            ['name' => 'Personal Leave', 'type' => 'Unpaid Leave', 'description' => 'Personal matters requiring time off', 'is_active' => 1],
            ['name' => 'Maternity Leave', 'type' => 'Paid Leave', 'description' => 'Maternity leave for mothers', 'is_active' => 1],
            ['name' => 'Paternity Leave', 'type' => 'Paid Leave', 'description' => 'Paternity leave for fathers', 'is_active' => 1],
            ['name' => 'Bereavement Leave', 'type' => 'Paid Leave', 'description' => 'Leave for funeral or bereavement', 'is_active' => 1],
            ['name' => 'Training/Conference', 'type' => 'Paid Leave', 'description' => 'Attending training or conference', 'is_active' => 1],
            ['name' => 'Unpaid Personal Leave', 'type' => 'Unpaid Leave', 'description' => 'Unpaid leave for personal reasons', 'is_active' => 1],
        ];
        $this->db->table('leave_reasons')->insertBatch($data);

        // Create worker_leave_records table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'worker_id' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'leave_reason_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'leave_date' => [
                'type' => 'DATE',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User ID who created this record',
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
        $this->forge->addKey('worker_id');
        $this->forge->addKey('leave_reason_id');
        $this->forge->addKey('leave_date');
        $this->forge->addForeignKey('worker_id', 'workers', 'worker_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('leave_reason_id', 'leave_reasons', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('worker_leave_records');
    }

    public function down()
    {
        $this->forge->dropTable('worker_leave_records', true);
        $this->forge->dropTable('leave_reasons', true);
    }
}
