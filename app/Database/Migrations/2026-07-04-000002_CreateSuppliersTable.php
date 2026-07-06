<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSuppliersTable extends Migration
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
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('suppliers');

        // Seed any supplier names already saved on products.
        if ($this->db->tableExists('products') && $this->db->fieldExists('suppliers', 'products')) {
            $names = [];
            foreach ($this->db->table('products')->select('suppliers')->get()->getResultArray() as $row) {
                $raw = $row['suppliers'] ?? null;
                if (!is_string($raw) || $raw === '') {
                    continue;
                }
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    continue;
                }
                foreach ($decoded as $name) {
                    $name = trim((string) $name);
                    if ($name !== '') {
                        $names[mb_strtolower($name)] = $name;
                    }
                }
            }

            $order = 0;
            $now   = date('Y-m-d H:i:s');
            $rows  = [];
            foreach ($names as $name) {
                $rows[] = [
                    'name'        => $name,
                    'description' => null,
                    'sort_order'  => $order++,
                    'is_active'   => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            if ($rows !== []) {
                $this->db->table('suppliers')->insertBatch($rows);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('suppliers', true);
    }
}
