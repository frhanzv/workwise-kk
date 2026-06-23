<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWpmsFieldsToInventoryTables extends Migration
{
    public function up()
    {
        $this->forge->addColumn('raw_materials', [
            'sap_code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'material_name'],
            'appearance' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'chemical_formula' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ph_range' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'assay_content' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'specific_gravity' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'shelf_life_months' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'warehouse_location' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'min_stock' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'expiry_alert_days' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'sample_test' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'pre_sample_test' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'k_test' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'supplier_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'manufacturer_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'supplier_shelf_life_months' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);

        $this->forge->addColumn('products', [
            'sap_code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'product_name'],
            'entry_date' => ['type' => 'DATE', 'null' => true],
            'lot_number' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'shelf_life_months' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'analysis_date' => ['type' => 'DATE', 'null' => true],
            'manufacturing_date' => ['type' => 'DATE', 'null' => true],
            'expiry_date' => ['type' => 'DATE', 'null' => true],
            'customer_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'ph_level_target' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'purity_grade' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'density_20c' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'viscosity' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'pricing_start_date' => ['type' => 'DATE', 'null' => true],
            'cost_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'selling_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'color_description' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'qc_status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'qc_quantity' => ['type' => 'DECIMAL', 'constraint' => '12,3', 'null' => true],
            'nsf_certified' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'halal_certified' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
    }

    public function down()
    {
        $rmFields = [
            'sap_code', 'appearance', 'chemical_formula', 'ph_range', 'assay_content',
            'specific_gravity', 'shelf_life_months', 'warehouse_location', 'min_stock',
            'expiry_alert_days', 'sample_test', 'pre_sample_test', 'k_test',
            'supplier_name', 'manufacturer_name', 'supplier_shelf_life_months',
        ];
        $productFields = [
            'sap_code', 'entry_date', 'lot_number', 'shelf_life_months', 'analysis_date',
            'manufacturing_date', 'expiry_date', 'customer_name', 'ph_level_target',
            'purity_grade', 'density_20c', 'viscosity', 'pricing_start_date', 'cost_price',
            'selling_price', 'color_description', 'qc_status', 'qc_quantity',
            'nsf_certified', 'halal_certified',
        ];

        $this->forge->dropColumn('raw_materials', $rmFields);
        $this->forge->dropColumn('products', $productFields);
    }
}
