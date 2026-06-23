<?php

namespace App\Controllers;

use App\Models\ZoneModel;

class DebugZones extends BaseController
{
    public function index()
    {
        $zoneModel = new ZoneModel();
        
        // Get all zones
        $zones = $zoneModel->select('*')->findAll();
        
        echo "<h1>Zone Debug Information</h1>";
        echo "<style>
            body { font-family: Arial; padding: 20px; background: #f5f5f5; }
            table { width: 100%; border-collapse: collapse; background: white; }
            th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
            th { background: #333; color: white; }
            img { max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 4px; }
            .yes { color: green; font-weight: bold; }
            .no { color: red; font-weight: bold; }
        </style>";
        
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Zone ID</th>
                <th>Zone Name</th>
                <th>Location Image (DB)</th>
                <th>File Exists?</th>
                <th>Preview</th>
                <th>Full Path</th>
              </tr>";
        
        foreach ($zones as $zone) {
            $hasImage = !empty($zone['location_image']);
            $imagePath = FCPATH . 'uploads/zones/' . ($zone['location_image'] ?? '');
            $fileExists = $hasImage && file_exists($imagePath);
            
            echo "<tr>";
            echo "<td>" . $zone['id'] . "</td>";
            echo "<td>" . $zone['zone_id'] . "</td>";
            echo "<td>" . $zone['zone_name'] . "</td>";
            echo "<td>" . ($zone['location_image'] ?? '<span class="no">NULL</span>') . "</td>";
            echo "<td>" . ($fileExists ? '<span class="yes">YES</span>' : '<span class="no">NO</span>') . "</td>";
            
            if ($fileExists) {
                echo "<td><img src='" . base_url('uploads/zones/' . $zone['location_image']) . "' /></td>";
            } else {
                echo "<td>-</td>";
            }
            
            echo "<td>" . $imagePath . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h2>Upload Directory Info</h2>";
        $uploadDir = FCPATH . 'uploads/zones';
        echo "<ul>";
        echo "<li><strong>FCPATH:</strong> " . FCPATH . "</li>";
        echo "<li><strong>Upload Path:</strong> " . $uploadDir . "</li>";
        echo "<li><strong>Directory Exists:</strong> " . (is_dir($uploadDir) ? '<span class="yes">YES</span>' : '<span class="no">NO</span>') . "</li>";
        echo "<li><strong>Directory Writable:</strong> " . (is_writable($uploadDir) ? '<span class="yes">YES</span>' : '<span class="no">NO</span>') . "</li>";
        
        if (is_dir($uploadDir)) {
            $files = scandir($uploadDir);
            $files = array_diff($files, array('.', '..', 'index.html'));
            echo "<li><strong>Files in directory:</strong> " . count($files) . "</li>";
            if (count($files) > 0) {
                echo "<li><strong>File list:</strong><ul>";
                foreach ($files as $file) {
                    echo "<li>" . $file . "</li>";
                }
                echo "</ul></li>";
            }
        }
        echo "</ul>";
    }
}