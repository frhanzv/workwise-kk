<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationToWorkersTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Check if columns already exist
        $query = $db->query("SHOW COLUMNS FROM workers LIKE 'country_id'");
        if ($query->getNumRows() == 0) {
            $fields = [
                'country_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'address',
                ],
                'state_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'country_id',
                ],
                'city_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'state_id',
                ],
            ];
            
            $this->forge->addColumn('workers', $fields);
        }
        
        // Add foreign keys if they don't exist
        try {
            $db->query('ALTER TABLE workers ADD CONSTRAINT fk_workers_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL ON UPDATE CASCADE');
        } catch (\Exception $e) {
            // Foreign key might already exist
        }
        
        try {
            $db->query('ALTER TABLE workers ADD CONSTRAINT fk_workers_state FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL ON UPDATE CASCADE');
        } catch (\Exception $e) {
            // Foreign key might already exist
        }
        
        try {
            $db->query('ALTER TABLE workers ADD CONSTRAINT fk_workers_city FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL ON UPDATE CASCADE');
        } catch (\Exception $e) {
            // Foreign key might already exist
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query('ALTER TABLE workers DROP FOREIGN KEY fk_workers_country');
        $db->query('ALTER TABLE workers DROP FOREIGN KEY fk_workers_state');
        $db->query('ALTER TABLE workers DROP FOREIGN KEY fk_workers_city');
        
        $this->forge->dropColumn('workers', ['country_id', 'state_id', 'city_id']);
    }
}
