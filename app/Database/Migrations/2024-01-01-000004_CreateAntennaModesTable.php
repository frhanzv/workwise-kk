<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAntennaModesTable extends Migration
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
            'mode_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->createTable('antenna_modes');
        
        // Insert default antenna modes
        $data = [
            ['mode_name' => 'RFID-UHF', 'description' => 'Ultra High Frequency RFID', 'is_active' => 1],
            ['mode_name' => 'NFC', 'description' => 'Near Field Communication', 'is_active' => 1],
            ['mode_name' => 'Bluetooth', 'description' => 'Bluetooth Low Energy', 'is_active' => 1],
            ['mode_name' => 'Biometric', 'description' => 'Fingerprint or Facial Recognition', 'is_active' => 1],
            ['mode_name' => 'GPS', 'description' => 'Global Positioning System', 'is_active' => 1],
        ];
        
        $this->db->table('antenna_modes')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('antenna_modes');
    }
}
