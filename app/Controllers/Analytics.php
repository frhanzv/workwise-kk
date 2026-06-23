<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Analytics extends BaseController
{
    private $apiUrl = 'http://localhost:8001/api/admin';
    
    public function chat()
    {
        $data = [
            'title' => 'Analytics Chat',
            'user' => session()->get('user')
        ];
        
        return view('analytics/chat_interface', $data);
    }
    
    public function query()
    {
        try {
            $this->response->setContentType('application/json');
            
            $json = file_get_contents('php://input');
            $request = json_decode($json, true);
            
            $message = $request['message'] ?? '';
            $conversation_id = $request['conversation_id'] ?? null;
            
            log_message('info', "🔍 Query request - Message: {$message}, Conversation: {$conversation_id}");
            
            if (empty($message)) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'Message is required'
                ]);
            }
            
            // ============================================================
            // FIX #1: DETECT DATA SOURCE FIRST, BEFORE CREATING CONVERSATION
            // ============================================================
            $question_lower = strtolower($message);
            $detected_data_source = $this->detectDataSource($question_lower);
            
            log_message('info', "🤖 Auto-detected data source: {$detected_data_source}");
            
            // Create new conversation if not provided (using DETECTED source)
            if (!$conversation_id) {
                $conversation_id = $this->createConversation($detected_data_source);
            }
            
            // ============================================================
            // FIX #2: USE DETECTED DATA SOURCE FOR EVERYTHING
            // ============================================================
            log_message('info', "📊 Fetching data for source: {$detected_data_source}");
            $data = $this->get_data_for_source($detected_data_source);
            
            log_message('info', "✅ Data fetched: " . count($data) . " records");
            
            if (empty($data)) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => "No data found for '{$detected_data_source}'"
                ]);
            }
            
            // Call Python AI backend
            $python_url = 'http://localhost:8001/api/admin/chat';
            
            $payload = json_encode([
                'message' => $message,
                'data_source' => $detected_data_source,  // Use detected source
                'data' => ['data' => $data],
                'context' => null
            ]);
            
            log_message('info', "🚀 Calling Python API: {$python_url}");
            log_message('info', "📦 Payload size: " . strlen($payload) . " bytes");
            
            $ch = curl_init($python_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($curl_error) {
                log_message('error', "❌ cURL error: {$curl_error}");
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'Failed to connect to AI backend: ' . $curl_error
                ]);
            }
            
            if ($http_code !== 200) {
                log_message('error', "❌ Python API error: HTTP {$http_code} - {$response}");
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'AI backend error (HTTP ' . $http_code . ')',
                    'details' => $response
                ]);
            }
            
            $aiResponse = json_decode($response, true);
            
            // ============================================================
            // FIX #3: SAVE MESSAGE WITH DETECTED DATA SOURCE
            // ============================================================
            if (isset($aiResponse['response'])) {
                $this->saveMessage($conversation_id, $message, $aiResponse['response'], $detected_data_source);
                $this->updateConversationTitle($conversation_id, $message);
            }
            
            $aiResponse['conversation_id'] = $conversation_id;
            
            log_message('info', "✅ Query completed successfully");
            return $this->response->setJSON($aiResponse);
            
        } catch (\Exception $e) {
            log_message('error', "❌ Query exception: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            
            return $this->response->setStatusCode(500)->setJSON([
                'error' => true,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function detectDataSource($question_lower)
    {
        // ============================================================
        // PRIORITY 0: PRODUCTION QUESTIONS (HIGHEST PRIORITY!)
        // This MUST come first to catch all production-related queries
        // ============================================================
        $production_keywords = [
            // Unit-based questions
            'units produced', 'how many units', 'total units',
            'units were', 'units was', 'show me units',
            'unit count', 'count units', 'list units',
            'units made', 'produced units',
            
            // Production-specific questions
            'show production', 'production data', 'who produced',
            'production by shift', 'shift production', 'production records',
            'output', 'total output', 'production summary',
            'most units', 'least units', 'production analysis',
            
            // Time-based production
            'output this week', 'output last week', 'output today',
            'production this month', 'weekly output', 'monthly output',
            'output per', 'production per',
            
            // Standard/performance questions (OLE related but uses production data)
            'exceed standard', 'below standard', 'meet standard',
            'standard output', 'performance standard', 'which workers exceed'
        ];
        
        foreach ($production_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected PRODUCTION question: {$keyword}");
                return 'production';
            }
        }

        // ============================================================
        // PRIORITY 1: OLE QUESTIONS
        // ============================================================
        $ole_keywords = [
            'ole', 'overall labor effectiveness', 'overall effectiveness', 
            'labor effectiveness', 'calculate ole', 'show ole',
            'ole for', 'ole by', 'labor efficiency overall'
        ];
        foreach ($ole_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected OLE question: {$keyword}");
                return 'ole';
            }
        }

        // ============================================================
        // PRIORITY 2: DOWNTIME & LOST TIME QUESTIONS
        // ============================================================
        $downtime_keywords = [
            'downtime', 'breakdown', 'machine failure', 'idle time', 'waiting',
            'material shortage', 'stopped', 'delay', 'machine down', 'not working',
            'time lost', 'lost time', 'lost productivity', 'where is time',
            'time being lost', 'losing time', 'wasted time'
        ];
        
        foreach ($downtime_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected DOWNTIME question: {$keyword}");
                return 'downtime';
            }
        }

        // ============================================================
        // PRIORITY 3: OVERTIME QUESTIONS  
        // ============================================================
        $overtime_keywords = [
            'overtime', 'over time', 'extra hours', 'overtime analysis',
            'overtime rate', 'overtime hours', 'show me overtime',
            'how much overtime', 'overtime cost'
        ];

        foreach ($overtime_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected OVERTIME question: {$keyword}");
                return 'cost_analysis';
            }
        }
        
        // ============================================================
        // PRIORITY 4: PRODUCTIVITY & EFFICIENCY
        // ============================================================
        $productivity_keywords = [
            'productive', 'productivity', 'efficient', 'efficiency', 'utilization',
            'performance', 'units per hour', 'output per hour', 'per hour',
            'most productive', 'productive per', 'best performer', 'worker per hour',
            'hours worked', 'how many hours'
        ];
        
        foreach ($productivity_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected PRODUCTIVITY question: {$keyword}");
                return 'productivity';
            }
        }
        
        // ============================================================
        // PRIORITY 5: COST ANALYSIS 
        // ============================================================
        $cost_keywords = [
            'cost', 'wage', 'salary', 'pay', 'expense', 'labor cost',
            'cost per unit', 'how much', 'spend', 'budget', 'price',
            'expensive', 'cheap', 'affordable', 'cost effective'
        ];
        
        foreach ($cost_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected COST question: {$keyword}");
                return 'cost_analysis';
            }
        }
        
        // ============================================================
        // PRIORITY 6: QUALITY & DEFECTS
        // ============================================================
        $quality_keywords = [
            'quality', 'defect', 'defective', 'reject', 'scrap', 'rework',
            'first pass yield', 'fpy', 'inspection', 'passed', 'failed',
            'defect rate', 'quality rate', 'good units', 'bad units'
        ];
        
        foreach ($quality_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected QUALITY question: {$keyword}");
                return 'quality';
            }
        }
        
        // ============================================================
        // PRIORITY 7: ATTENDANCE & ZONE CHECK-INS
        // ============================================================
        $attendance_keywords = [
            'check in', 'check-in', 'checkin', 'checked in', 'check out', 'checkout',
            'attendance', 'absent', 'present', 'attend', 'who came', 'who went',
            'zone most', 'which zone', 'popular zone', 'busiest zone'
        ];
        
        foreach ($attendance_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected ATTENDANCE question: {$keyword}");
                return 'attendance';
            }
        }

        // Attendance rate and absenteeism
        if (strpos($question_lower, 'attendance rate') !== false || 
            strpos($question_lower, 'absenteeism') !== false) {
            log_message('info', "🎯 Detected ATTENDANCE SUMMARY question");
            return 'attendance_summary';
        }
        
        // ============================================================
        // PRIORITY 8: DEPARTMENT QUESTIONS
        // ============================================================
        if (strpos($question_lower, 'department') !== false) {
            // Questions asking about WORKERS in departments
            $worker_in_dept_keywords = [
                'which worker', 'what worker', 'who in', 'who is in',
                'list worker', 'show worker', 'worker in', 'workers in',
                'worker name', 'name of worker', 'employee in'
            ];
            
            foreach ($worker_in_dept_keywords as $keyword) {
                if (strpos($question_lower, $keyword) !== false) {
                    log_message('info', "🎯 Detected WORKERS IN DEPARTMENT question");
                    return 'workers';
                }
            }
            
            // Questions asking about department STATISTICS
            $dept_stats_keywords = [
                'most worker', 'how many worker', 'which department have',
                'which department has', 'department most', 'count', 
                'total worker', 'number of worker', 'how many people',
                'most people', 'most employee'
            ];
            
            foreach ($dept_stats_keywords as $keyword) {
                if (strpos($question_lower, $keyword) !== false) {
                    log_message('info', "🎯 Detected DEPARTMENT STATS question");
                    return 'workers';
                }
            }
            
            log_message('info', "🎯 Detected DEPARTMENT INFO question");
            return 'departments';
        }
        
        // ============================================================
        // PRIORITY 9: WORKER-SPECIFIC QUESTIONS
        // ============================================================
        $worker_keywords = [
            'worker', 'employee', 'staff', 'personnel', 'ic number', 'ic ', 
            'who is', 'list worker', 'all worker', 'worker detail', 'worker info',
            'how many worker', 'most worker', 'show me worker'
        ];
        
        foreach ($worker_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected WORKER question: {$keyword}");
                return 'workers';
            }
        }
        
        // Check for shift questions
        $shift_keywords = [
            'shift', 'morning shift', 'evening shift', 'night shift', 'afternoon shift'
        ];
        
        foreach ($shift_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                log_message('info', "🎯 Detected SHIFT question: {$keyword}");
                return 'shifts';
            }
        }
        
        // Check for zone questions (non-attendance)
        $zone_keywords = [
            'zone', 'zones', 'area', 'location', 'where is', 'zone name', 'zone info'
        ];
        
        foreach ($zone_keywords as $keyword) {
            if (strpos($question_lower, $keyword) !== false) {
                if (strpos($question_lower, 'most') !== false || 
                    strpos($question_lower, 'how many') !== false ||
                    strpos($question_lower, 'popular') !== false ||
                    strpos($question_lower, 'busiest') !== false) {
                    log_message('info', "🎯 Detected ZONE STATS (attendance) question");
                    return 'attendance';
                }
                log_message('info', "🎯 Detected ZONE INFO question");
                return 'zones';
            }
        }
        
        // Default to workers for general questions
        log_message('info', "🎯 Default to WORKERS for general question");
        return 'workers';
    }

    private function get_data_for_source($data_source)
    {
        try {
            $db = \Config\Database::connect();
            
            log_message('info', "📊 Getting data for source: {$data_source}");
            
            switch ($data_source) {
                case 'workers':
                    $builder = $db->table('workers');
                    $builder->select('workers.*, departments.name as dept_name, departments.description as dept_description');
                    $builder->join('departments', 'workers.department = departments.name', 'left');
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    log_message('info', "✅ Workers: " . count($result) . " records");
                    return $result;
                    
                case 'zones':
                    $query = $db->table('zones')->get();
                    $result = $query->getResultArray();
                    log_message('info', "✅ Zones: " . count($result) . " records");
                    return $result;
                    
                case 'attendance':
                    $builder = $db->table('attendance_records');
                    $builder->select('attendance_records.*, workers.first_name, workers.last_name, CONCAT(workers.first_name, " ", workers.last_name) as worker_name, workers.department');
                    $builder->join('workers', 'attendance_records.worker_id = workers.worker_id', 'left');
                    $builder->orderBy('attendance_records.date', 'DESC');
                    $builder->limit(1000);
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    log_message('info', "✅ Attendance: " . count($result) . " records");
                    return $result;
                    
                case 'departments':
                    $builder = $db->table('departments');
                    $builder->select('departments.*, COUNT(workers.worker_id) as worker_count');
                    $builder->join('workers', 'departments.name = workers.department', 'left');
                    $builder->groupBy('departments.id');
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    log_message('info', "✅ Departments: " . count($result) . " records");
                    return $result;
                    
                case 'shifts':
                    $query = $db->table('shifts')->get();
                    $result = $query->getResultArray();
                    log_message('info', "✅ Shifts: " . count($result) . " records");
                    return $result;
                    
                case 'positions':
                    $query = $db->table('job_positions')->get();
                    $result = $query->getResultArray();
                    log_message('info', "✅ Positions: " . count($result) . " records");
                    return $result;
                    
                case 'reports':
                    $query = $db->table('reports')->get();
                    $result = $query->getResultArray();
                    log_message('info', "✅ Reports: " . count($result) . " records");
                    return $result;

                case 'production':
                    $builder = $db->table('production_records pr');
                    
                    $builder->select('
                        pr.id,
                        pr.worker_id,
                        pr.shift_id,
                        pr.date,
                        pr.units_produced,
                        pr.good_units,
                        pr.defective_units,
                        pr.downtime_minutes,
                        pr.scheduled_hours,
                        pr.standard_output_rate,
                        pr.productive_hours
                    ');
                    
                    // Join with workers and shifts
                    if ($db->tableExists('workers')) {
                        $builder->select('
                            w.first_name,
                            w.last_name,
                            CONCAT(COALESCE(w.first_name, ""), " ", COALESCE(w.last_name, "")) as worker_name,
                            w.department,
                            w.position
                        ', false);
                        $builder->join('workers w', 'pr.worker_id = w.worker_id', 'left');
                    }
                    
                    if ($db->tableExists('shifts')) {
                        $builder->select('s.name as shift_name', false);
                        $builder->join('shifts s', 'pr.shift_id = s.id', 'left');
                    }
                    
                    $builder->orderBy('pr.date', 'DESC');
                    $builder->limit(2000);
                    
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    
                    log_message('info', "🔍 Production query returned: " . count($result) . " records");
                    
                    // Type cast numeric fields
                    foreach ($result as &$row) {
                        $row['units_produced'] = (int)($row['units_produced'] ?? 0);
                        $row['good_units'] = (int)($row['good_units'] ?? 0);
                        $row['defective_units'] = (int)($row['defective_units'] ?? 0);
                        $row['downtime_minutes'] = (int)($row['downtime_minutes'] ?? 0);
                        $row['scheduled_hours'] = (float)($row['scheduled_hours'] ?? 8.0);
                        $row['standard_output_rate'] = (float)($row['standard_output_rate'] ?? 0);
                        $row['productive_hours'] = (float)($row['productive_hours'] ?? 0);
                    }
                    
                    log_message('info', "✅ Production: " . count($result) . " records (type-casted)");
                    return $result;

                case 'quality':
                    $builder = $db->table('production_records');
                    $builder->select('
                        production_records.id,
                        production_records.worker_id,
                        production_records.date,
                        production_records.units_produced,
                        production_records.good_units,
                        production_records.defective_units,
                        workers.first_name,
                        workers.last_name,
                        CONCAT(workers.first_name, " ", workers.last_name) as worker_name,
                        workers.department,
                        shifts.name as shift_name
                    ');
                    $builder->join('workers', 'production_records.worker_id = workers.worker_id', 'left');
                    $builder->join('shifts', 'production_records.shift_id = shifts.id', 'left');
                    $builder->orderBy('production_records.date', 'DESC');
                    $builder->limit(2000);
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    
                    // Type cast numeric fields
                    foreach ($result as &$row) {
                        $row['units_produced'] = (int)($row['units_produced'] ?? 0);
                        $row['good_units'] = (int)($row['good_units'] ?? 0);
                        $row['defective_units'] = (int)($row['defective_units'] ?? 0);
                    }
                    
                    log_message('info', "✅ Quality: " . count($result) . " records");
                    return $result;

                case 'downtime':
                    $builder = $db->table('production_records');
                    $builder->select('
                        production_records.id,
                        production_records.worker_id,
                        production_records.shift_id,
                        production_records.date,
                        production_records.units_produced,
                        production_records.downtime_minutes,
                        production_records.standard_output_rate,
                        workers.first_name,
                        workers.last_name,
                        CONCAT(workers.first_name, " ", workers.last_name) as worker_name,
                        workers.department,
                        workers.position,
                        shifts.name as shift_name
                    ');
                    $builder->join('workers', 'production_records.worker_id = workers.worker_id', 'left');
                    $builder->join('shifts', 'production_records.shift_id = shifts.id', 'left');
                    $builder->orderBy('production_records.date', 'DESC');
                    $builder->limit(2000);
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    
                    // Type cast
                    foreach ($result as &$row) {
                        $row['units_produced'] = (int)($row['units_produced'] ?? 0);
                        $row['downtime_minutes'] = (int)($row['downtime_minutes'] ?? 0);
                        $row['standard_output_rate'] = (float)($row['standard_output_rate'] ?? 0);
                    }
                    
                    log_message('info', "✅ Downtime: " . count($result) . " records");
                    return $result;

                case 'productivity':
                    $builder = $db->table('production_records pr');
                    $builder->select('
                        pr.id,
                        pr.worker_id,
                        pr.shift_id,
                        pr.date,
                        pr.units_produced,
                        pr.standard_output_rate,
                        pr.productive_hours,
                        w.first_name,
                        w.last_name,
                        CONCAT(w.first_name, " ", w.last_name) as worker_name,
                        w.department,
                        s.name as shift_name,
                        ar.check_in_time,
                        ar.check_out_time,
                        TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0 as hours_worked
                    ');
                    $builder->join('workers w', 'pr.worker_id = w.worker_id', 'left');
                    $builder->join('shifts s', 'pr.shift_id = s.id', 'left');
                    $builder->join('attendance_records ar', '
                        pr.worker_id = ar.worker_id 
                        AND DATE(pr.date) = DATE(ar.date)
                    ', 'left');
                    $builder->orderBy('pr.date', 'DESC');
                    $builder->limit(1000);
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    
                    // Type cast
                    foreach ($result as &$row) {
                        $row['units_produced'] = (int)($row['units_produced'] ?? 0);
                        $row['standard_output_rate'] = (float)($row['standard_output_rate'] ?? 0);
                        $row['productive_hours'] = (float)($row['productive_hours'] ?? 0);
                        $row['hours_worked'] = (float)($row['hours_worked'] ?? 0);
                    }
                    
                    log_message('info', "✅ Productivity: " . count($result) . " records");
                    return $result;

                case 'attendance_summary':
                    $builder = $db->table('attendance_records');
                    $builder->select('
                        worker_id,
                        CONCAT(workers.first_name, " ", workers.last_name) as worker_name,
                        workers.department,
                        COUNT(DISTINCT date) as days_present,
                        MIN(date) as first_date,
                        MAX(date) as last_date,
                        DATEDIFF(MAX(date), MIN(date)) + 1 as total_days
                    ');
                    $builder->join('workers', 'attendance_records.worker_id = workers.worker_id', 'left');
                    $builder->groupBy('worker_id');
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    
                    // Calculate attendance rate
                    foreach ($result as &$row) {
                        $row['days_present'] = (int)$row['days_present'];
                        $row['total_days'] = (int)$row['total_days'];
                        $row['attendance_rate'] = ($row['days_present'] / $row['total_days']) * 100;
                        $row['absent_days'] = $row['total_days'] - $row['days_present'];
                    }
                    
                    log_message('info', "✅ Attendance Summary: " . count($result) . " records");
                    return $result;

                case 'cost_analysis':
                    $tables = $db->listTables();
                    $has_labor_costs = in_array('labor_costs', $tables);
                    
                    $builder = $db->table('production_records pr');
                    
                    if ($has_labor_costs) {
                        $builder->select('
                            pr.id,
                            pr.worker_id,
                            pr.date,
                            pr.units_produced,
                            w.first_name,
                            w.last_name,
                            CONCAT(w.first_name, " ", w.last_name) as worker_name,
                            w.department,
                            w.position,
                            lc.hourly_rate,
                            lc.overtime_rate as overtime_multiplier,
                            ar.check_in_time,
                            ar.check_out_time,
                            TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0 as total_hours,
                            GREATEST(0, TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0 - 8.0) as overtime_hours,
                            LEAST(8.0, TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0) as regular_hours
                        ');
                        $builder->join('workers w', 'pr.worker_id = w.worker_id', 'left');
                        $builder->join('labor_costs lc', 'pr.worker_id = lc.worker_id AND lc.is_active = 1', 'left');
                    } else {
                        $builder->select('
                            pr.id,
                            pr.worker_id,
                            pr.date,
                            pr.units_produced,
                            w.first_name,
                            w.last_name,
                            CONCAT(w.first_name, " ", w.last_name) as worker_name,
                            w.department,
                            w.position,
                            CASE 
                                WHEN w.position = "Manager" THEN 25.00
                                WHEN w.position = "Supervisor" THEN 20.00
                                WHEN w.position = "Senior Technician" THEN 18.00
                                WHEN w.position = "Technician" THEN 15.00
                                WHEN w.position = "Operator" THEN 12.00
                                ELSE 14.00
                            END as hourly_rate,
                            1.5 as overtime_multiplier,
                            ar.check_in_time,
                            ar.check_out_time,
                            TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0 as total_hours,
                            GREATEST(0, TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0 - 8.0) as overtime_hours,
                            LEAST(8.0, TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0) as regular_hours
                        ');
                        $builder->join('workers w', 'pr.worker_id = w.worker_id', 'left');
                    }
                    
                    $builder->join('attendance_records ar', '
                        pr.worker_id = ar.worker_id 
                        AND DATE(pr.date) = DATE(ar.date)
                    ', 'left');
                    $builder->orderBy('pr.date', 'DESC');
                    $builder->limit(1000);
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    
                    // Type cast
                    foreach ($result as &$row) {
                        $row['units_produced'] = (int)($row['units_produced'] ?? 0);
                        $row['hourly_rate'] = (float)($row['hourly_rate'] ?? 0);
                        $row['overtime_multiplier'] = (float)($row['overtime_multiplier'] ?? 1.5);
                        $row['total_hours'] = (float)($row['total_hours'] ?? 0);
                        $row['overtime_hours'] = (float)($row['overtime_hours'] ?? 0);
                        $row['regular_hours'] = (float)($row['regular_hours'] ?? 0);
                    }
                    
                    log_message('info', "✅ Cost Analysis: " . count($result) . " records");
                    return $result;

                case 'ole':
                    $builder = $db->table('production_records pr');
                    $builder->select('
                        pr.id,
                        pr.worker_id,
                        pr.shift_id,
                        pr.date,
                        pr.units_produced,
                        pr.good_units,
                        pr.defective_units,
                        pr.downtime_minutes,
                        pr.scheduled_hours,
                        pr.standard_output_rate,
                        w.first_name,
                        w.last_name,
                        CONCAT(w.first_name, " ", w.last_name) as worker_name,
                        w.department,
                        s.name as shift_name,
                        ar.check_in_time,
                        ar.check_out_time,
                        TIMESTAMPDIFF(MINUTE, ar.check_in_time, ar.check_out_time) / 60.0 as actual_work_time
                    ');
                    $builder->join('workers w', 'pr.worker_id = w.worker_id', 'left');
                    $builder->join('shifts s', 'pr.shift_id = s.id', 'left');
                    $builder->join('attendance_records ar', '
                        pr.worker_id = ar.worker_id 
                        AND DATE(pr.date) = DATE(ar.date)
                    ', 'left');
                    $builder->orderBy('pr.date', 'DESC');
                    $builder->limit(1000);
                    $query = $builder->get();
                    $result = $query->getResultArray();
                    
                    // Type cast
                    foreach ($result as &$row) {
                        $row['units_produced'] = (int)($row['units_produced'] ?? 0);
                        $row['good_units'] = (int)($row['good_units'] ?? 0);
                        $row['defective_units'] = (int)($row['defective_units'] ?? 0);
                        $row['downtime_minutes'] = (int)($row['downtime_minutes'] ?? 0);
                        $row['scheduled_hours'] = (float)($row['scheduled_hours'] ?? 8.0);
                        $row['standard_output_rate'] = (float)($row['standard_output_rate'] ?? 0);
                        $row['actual_work_time'] = (float)($row['actual_work_time'] ?? 0);
                    }
                    
                    log_message('info', "✅ OLE: " . count($result) . " records");
                    return $result;
                    
                default:
                    log_message('warning', "⚠️ Unknown data source: {$data_source}");
                    return [];
            }
        } catch (\Exception $e) {
            log_message('error', "❌ get_data_for_source error: " . $e->getMessage());
            log_message('error', "Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    private function createConversation($data_source)
    {
        try {
            $db = \Config\Database::connect();
            $userId = session()->get('user')['id'] ?? null;
            
            $data = [
                'user_id' => $userId,
                'title' => 'New Chat',
                'data_source' => $data_source,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->table('analytics_conversations')->insert($data);
            return $db->insertID();
            
        } catch (\Exception $e) {
            log_message('error', '❌ Create Conversation Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function updateConversationTitle($conversation_id, $firstMessage)
    {
        try {
            $db = \Config\Database::connect();
            
            $conversation = $db->table('analytics_conversations')
                ->where('id', $conversation_id)
                ->get()
                ->getRowArray();
            
            if ($conversation && $conversation['title'] === 'New Chat') {
                $title = strlen($firstMessage) > 50 
                    ? substr($firstMessage, 0, 50) . '...' 
                    : $firstMessage;
                
                $db->table('analytics_conversations')
                    ->where('id', $conversation_id)
                    ->update([
                        'title' => $title,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', '❌ Update Conversation Title Error: ' . $e->getMessage());
        }
    }
    
    private function saveMessage($conversation_id, $question, $response, $dataSource)
    {
        try {
            $db = \Config\Database::connect();
            
            $data = [
                'conversation_id' => $conversation_id,
                'question' => $question,
                'response' => $response,
                'data_source' => $dataSource,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->table('analytics_messages')->insert($data);
            
            $db->table('analytics_conversations')
                ->where('id', $conversation_id)
                ->update(['updated_at' => date('Y-m-d H:i:s')]);
            
        } catch (\Exception $e) {
            log_message('error', '❌ Save Message Error: ' . $e->getMessage());
        }
    }
    
    public function getConversations()
    {
        try {
            $db = \Config\Database::connect();
            $userId = session()->get('user')['id'] ?? null;
            
            $builder = $db->table('analytics_conversations');
            $builder->select('*');
            
            if ($userId) {
                $builder->where('user_id', $userId);
            }
            
            $builder->orderBy('updated_at', 'DESC');
            $builder->limit(100);
            
            $results = $builder->get()->getResultArray();
            
            log_message('info', 'Conversations loaded: ' . count($results));
            
            return $this->response->setJSON([
                'success' => true,
                'conversations' => $results
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get Conversations Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'conversations' => []
            ]);
        }
    }
    
    public function getConversationMessages($conversation_id)
    {
        try {
            $db = \Config\Database::connect();
            $userId = session()->get('user')['id'] ?? null;
            
            $conversation = $db->table('analytics_conversations')
                ->where('id', $conversation_id)
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();
            
            if (!$conversation) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Conversation not found'
                ]);
            }
            
            $messages = $db->table('analytics_messages')
                ->where('conversation_id', $conversation_id)
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();
            
            return $this->response->setJSON([
                'success' => true,
                'conversation' => $conversation,
                'messages' => $messages
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get Conversation Messages Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function deleteConversation($conversation_id)
    {
        try {
            $db = \Config\Database::connect();
            $userId = session()->get('user')['id'] ?? null;
            
            $conversation = $db->table('analytics_conversations')
                ->where('id', $conversation_id)
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();
            
            if (!$conversation) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Conversation not found'
                ]);
            }
            
            $db->table('analytics_messages')
                ->where('conversation_id', $conversation_id)
                ->delete();
            
            $db->table('analytics_conversations')
                ->where('id', $conversation_id)
                ->delete();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Conversation deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Delete Conversation Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function renameConversation()
    {
        try {
            $request = $this->request->getJSON();
            $conversation_id = $request->conversation_id ?? null;
            $new_title = $request->title ?? '';
            
            if (!$conversation_id || !$new_title) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Conversation ID and title are required'
                ]);
            }
            
            $db = \Config\Database::connect();
            $userId = session()->get('user')['id'] ?? null;
            
            $conversation = $db->table('analytics_conversations')
                ->where('id', $conversation_id)
                ->where('user_id', $userId)
                ->get()
                ->getRowArray();
            
            if (!$conversation) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Conversation not found'
                ]);
            }
            
            $db->table('analytics_conversations')
                ->where('id', $conversation_id)
                ->update([
                    'title' => $new_title,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Conversation renamed successfully'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Rename Conversation Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}