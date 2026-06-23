<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCitiesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'state_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'country_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addForeignKey('state_id', 'states', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('country_id', 'countries', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cities');
        
        // Get Malaysia ID
        $db = \Config\Database::connect();
        $malaysia = $db->table('countries')->where('code', 'MY')->get()->getRow();
        
        if ($malaysia) {
            // Get all Malaysian states
            $states = $db->table('states')->where('country_id', $malaysia->id)->get()->getResult();
            $stateMap = [];
            foreach ($states as $state) {
                $stateMap[$state->name] = $state->id;
            }
            
            // Insert Malaysian cities by state
            $cities = [
                // Johor
                ['name' => 'Johor Bahru', 'state' => 'Johor'],
                ['name' => 'Batu Pahat', 'state' => 'Johor'],
                ['name' => 'Muar', 'state' => 'Johor'],
                ['name' => 'Kluang', 'state' => 'Johor'],
                ['name' => 'Pontian', 'state' => 'Johor'],
                ['name' => 'Segamat', 'state' => 'Johor'],
                ['name' => 'Kota Tinggi', 'state' => 'Johor'],
                ['name' => 'Mersing', 'state' => 'Johor'],
                
                // Kedah
                ['name' => 'Alor Setar', 'state' => 'Kedah'],
                ['name' => 'Sungai Petani', 'state' => 'Kedah'],
                ['name' => 'Kulim', 'state' => 'Kedah'],
                ['name' => 'Langkawi', 'state' => 'Kedah'],
                ['name' => 'Baling', 'state' => 'Kedah'],
                ['name' => 'Kuala Kedah', 'state' => 'Kedah'],
                
                // Kelantan
                ['name' => 'Kota Bharu', 'state' => 'Kelantan'],
                ['name' => 'Pasir Mas', 'state' => 'Kelantan'],
                ['name' => 'Tanah Merah', 'state' => 'Kelantan'],
                ['name' => 'Tumpat', 'state' => 'Kelantan'],
                ['name' => 'Gua Musang', 'state' => 'Kelantan'],
                
                // Kuala Lumpur
                ['name' => 'Kuala Lumpur', 'state' => 'Kuala Lumpur'],
                
                // Labuan
                ['name' => 'Victoria', 'state' => 'Labuan'],
                
                // Melaka
                ['name' => 'Melaka City', 'state' => 'Melaka'],
                ['name' => 'Alor Gajah', 'state' => 'Melaka'],
                ['name' => 'Jasin', 'state' => 'Melaka'],
                
                // Negeri Sembilan
                ['name' => 'Seremban', 'state' => 'Negeri Sembilan'],
                ['name' => 'Port Dickson', 'state' => 'Negeri Sembilan'],
                ['name' => 'Nilai', 'state' => 'Negeri Sembilan'],
                ['name' => 'Kuala Pilah', 'state' => 'Negeri Sembilan'],
                ['name' => 'Tampin', 'state' => 'Negeri Sembilan'],
                
                // Pahang
                ['name' => 'Kuantan', 'state' => 'Pahang'],
                ['name' => 'Temerloh', 'state' => 'Pahang'],
                ['name' => 'Bentong', 'state' => 'Pahang'],
                ['name' => 'Raub', 'state' => 'Pahang'],
                ['name' => 'Jerantut', 'state' => 'Pahang'],
                ['name' => 'Pekan', 'state' => 'Pahang'],
                ['name' => 'Kuala Lipis', 'state' => 'Pahang'],
                
                // Penang
                ['name' => 'George Town', 'state' => 'Penang'],
                ['name' => 'Butterworth', 'state' => 'Penang'],
                ['name' => 'Bukit Mertajam', 'state' => 'Penang'],
                ['name' => 'Balik Pulau', 'state' => 'Penang'],
                
                // Perak
                ['name' => 'Ipoh', 'state' => 'Perak'],
                ['name' => 'Taiping', 'state' => 'Perak'],
                ['name' => 'Teluk Intan', 'state' => 'Perak'],
                ['name' => 'Kuala Kangsar', 'state' => 'Perak'],
                ['name' => 'Sitiawan', 'state' => 'Perak'],
                ['name' => 'Lumut', 'state' => 'Perak'],
                ['name' => 'Parit Buntar', 'state' => 'Perak'],
                ['name' => 'Batu Gajah', 'state' => 'Perak'],
                
                // Perlis
                ['name' => 'Kangar', 'state' => 'Perlis'],
                ['name' => 'Arau', 'state' => 'Perlis'],
                
                // Putrajaya
                ['name' => 'Putrajaya', 'state' => 'Putrajaya'],
                
                // Sabah
                ['name' => 'Kota Kinabalu', 'state' => 'Sabah'],
                ['name' => 'Sandakan', 'state' => 'Sabah'],
                ['name' => 'Tawau', 'state' => 'Sabah'],
                ['name' => 'Lahad Datu', 'state' => 'Sabah'],
                ['name' => 'Keningau', 'state' => 'Sabah'],
                ['name' => 'Semporna', 'state' => 'Sabah'],
                
                // Sarawak
                ['name' => 'Kuching', 'state' => 'Sarawak'],
                ['name' => 'Miri', 'state' => 'Sarawak'],
                ['name' => 'Sibu', 'state' => 'Sarawak'],
                ['name' => 'Bintulu', 'state' => 'Sarawak'],
                ['name' => 'Limbang', 'state' => 'Sarawak'],
                ['name' => 'Sarikei', 'state' => 'Sarawak'],
                
                // Selangor
                ['name' => 'Shah Alam', 'state' => 'Selangor'],
                ['name' => 'Petaling Jaya', 'state' => 'Selangor'],
                ['name' => 'Subang Jaya', 'state' => 'Selangor'],
                ['name' => 'Klang', 'state' => 'Selangor'],
                ['name' => 'Ampang', 'state' => 'Selangor'],
                ['name' => 'Sepang', 'state' => 'Selangor'],
                ['name' => 'Kajang', 'state' => 'Selangor'],
                ['name' => 'Selayang', 'state' => 'Selangor'],
                ['name' => 'Rawang', 'state' => 'Selangor'],
                ['name' => 'Kuala Selangor', 'state' => 'Selangor'],
                
                // Terengganu
                ['name' => 'Kuala Terengganu', 'state' => 'Terengganu'],
                ['name' => 'Kemaman', 'state' => 'Terengganu'],
                ['name' => 'Dungun', 'state' => 'Terengganu'],
                ['name' => 'Marang', 'state' => 'Terengganu'],
            ];
            
            $cityData = [];
            foreach ($cities as $city) {
                if (isset($stateMap[$city['state']])) {
                    $cityData[] = [
                        'name' => $city['name'],
                        'state_id' => $stateMap[$city['state']],
                        'country_id' => $malaysia->id,
                        'is_active' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            
            if (!empty($cityData)) {
                $db->table('cities')->insertBatch($cityData);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('cities');
    }
}
