<?php

namespace App\Controllers;

class Reports extends BaseController
{
    public function index()
    {
        $workerModel = new \App\Models\WorkerModel();
        $zoneModel = new \App\Models\ZoneModel();
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        
        // Get date filter from request
        $filterType = $this->request->getGet('filter') ?? 'today';
        $customStartDate = $this->request->getGet('start_date');
        $customEndDate = $this->request->getGet('end_date');
        
        // Determine date range
        $today = date('Y-m-d');
        $startDate = $today;
        $endDate = $today;
        $filterLabel = 'Today';
        
        if ($customStartDate && $customEndDate) {
            // Custom date range selected
            $startDate = $customStartDate;
            $endDate = $customEndDate;
            $filterLabel = date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate));
            $filterType = 'custom';
        } else {
            switch ($filterType) {
                case 'yesterday':
                    $startDate = $endDate = date('Y-m-d', strtotime('-1 day'));
                    $filterLabel = 'Yesterday';
                    break;
                case 'week':
                    $startDate = date('Y-m-d', strtotime('monday this week'));
                    $endDate = date('Y-m-d', strtotime('sunday this week'));
                    $filterLabel = 'This Week';
                    break;
                case 'last_week':
                    $startDate = date('Y-m-d', strtotime('monday last week'));
                    $endDate = date('Y-m-d', strtotime('sunday last week'));
                    $filterLabel = 'Last Week';
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = date('Y-m-t');
                    $filterLabel = 'This Month';
                    break;
                case 'last_month':
                    $startDate = date('Y-m-01', strtotime('first day of last month'));
                    $endDate = date('Y-m-t', strtotime('last day of last month'));
                    $filterLabel = 'Last Month';
                    break;
                default: // today
                    $startDate = $endDate = $today;
                    $filterLabel = 'Today';
            }
        }
        
        // Get all active zones
        $allZones = $zoneModel->where('status', 'active')->findAll();
        $totalZones = count($allZones);
        
        // Get attendance records for selected date range
        if ($startDate === $endDate) {
            $records = $attendanceModel->getAttendanceByDate($startDate);
        } else {
            $records = $attendanceModel->getAttendanceByDateRange($startDate, $endDate);
        }
        
        // Get all workers
        $allWorkers = $workerModel->getAllWorkersFormatted();
        $totalWorkers = count($allWorkers);
        
        // ATTENDANCE SUMMARY
        $totalCheckIns = count($records);
        $completedVisits = 0;
        $activeVisits = 0;
        $totalDurationMinutes = 0;
        
        foreach ($records as $record) {
            if ($record['check_in_time'] && $record['check_out_time']) {
                $duration = strtotime($record['check_out_time']) - strtotime($record['check_in_time']);
                $totalDurationMinutes += $duration / 60;
                $completedVisits++;
            } else {
                $activeVisits++;
            }
        }
        
        $avgDuration = $completedVisits > 0 ? round($totalDurationMinutes / $completedVisits) : 0;
        
        // ZONE ANALYTICS
        $zoneStats = [];
        foreach ($allZones as $zone) {
            $zoneRecords = array_filter($records, function($record) use ($zone) {
                return $record['zone_id'] == $zone['zone_id'];
            });
            
            $zoneCheckIns = count($zoneRecords);
            $zoneDuration = 0;
            $zoneCompleted = 0;
            
            foreach ($zoneRecords as $record) {
                if ($record['check_in_time'] && $record['check_out_time']) {
                    $duration = strtotime($record['check_out_time']) - strtotime($record['check_in_time']);
                    $zoneDuration += $duration / 60;
                    $zoneCompleted++;
                }
            }
            
            $zoneAvgDuration = $zoneCompleted > 0 ? round($zoneDuration / $zoneCompleted) : 0;
            
            $zoneStats[] = [
                'zone_name' => $zone['zone_name'],
                'zone_id' => $zone['zone_id'],
                'location' => $zone['location'] ?? '-',
                'total_visits' => $zoneCheckIns,
                'completed_visits' => $zoneCompleted,
                'avg_duration' => $this->formatMinutes($zoneAvgDuration),
                'avg_duration_minutes' => $zoneAvgDuration
            ];
        }
        
        // Sort by total visits
        usort($zoneStats, function($a, $b) {
            return $b['total_visits'] - $a['total_visits'];
        });
        
        // WORKER PRODUCTIVITY
        $workerStats = [];
        foreach ($allWorkers as $worker) {
            $workerRecords = array_filter($records, function($record) use ($worker) {
                return $record['worker_id'] == $worker['worker_id'];
            });
            
            if (empty($workerRecords)) continue;
            
            $workerCheckIns = count($workerRecords);
            $workerDuration = 0;
            $workerCompleted = 0;
            $uniqueZones = [];
            
            foreach ($workerRecords as $record) {
                $uniqueZones[$record['zone_id']] = true;
                
                if ($record['check_in_time'] && $record['check_out_time']) {
                    $duration = strtotime($record['check_out_time']) - strtotime($record['check_in_time']);
                    $workerDuration += $duration / 60;
                    $workerCompleted++;
                }
            }
            
            $workerAvgDuration = $workerCompleted > 0 ? round($workerDuration / $workerCompleted) : 0;
            
            $workerStats[] = [
                'worker_name' => $worker['full_name'],
                'worker_id' => $worker['worker_id'],
                'department' => ucfirst($worker['department']),
                'position' => $worker['position'],
                'total_visits' => $workerCheckIns,
                'completed_visits' => $workerCompleted,
                'zones_visited' => count($uniqueZones),
                'total_duration' => $this->formatMinutes($workerDuration),
                'avg_duration' => $this->formatMinutes($workerAvgDuration),
                'total_duration_minutes' => $workerDuration,
                'avg_duration_minutes' => $workerAvgDuration
            ];
        }
        
        // Sort by total visits
        usort($workerStats, function($a, $b) {
            return $b['total_visits'] - $a['total_visits'];
        });
        
        // DAILY ACTIVITY CHART DATA (for line chart) — single GROUP BY query
        $dailyCounts  = $attendanceModel->getDailyCountsByDateRange($startDate, $endDate);
        $dailyActivity = [];
        $currentDate  = strtotime($startDate);
        $endDateTime  = strtotime($endDate);

        while ($currentDate <= $endDateTime) {
            $dateStr = date('Y-m-d', $currentDate);
            $dailyActivity[] = [
                'date'      => $dateStr,
                'label'     => date('M d', $currentDate),
                'check_ins' => $dailyCounts[$dateStr] ?? 0,
            ];
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        // Get unique workers who checked in during this period
        $activeWorkers = [];
        foreach ($records as $record) {
            $activeWorkers[$record['worker_id']] = true;
        }
        $activeWorkersCount = count($activeWorkers);
        
        $data = [
            'title' => 'Reports',
            'user' => $this->getLoggedInUser(),
            'filter_type' => $filterType,
            'filter_label' => $filterLabel,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => [
                'total_workers' => $totalWorkers,
                'active_workers' => $activeWorkersCount,
                'total_zones' => $totalZones,
                'total_check_ins' => $totalCheckIns,
                'completed_visits' => $completedVisits,
                'active_visits' => $activeVisits,
                'avg_duration' => $this->formatMinutes($avgDuration)
            ],
            'zone_stats' => $zoneStats,
            'worker_stats' => $workerStats,
            'daily_activity' => $dailyActivity
        ];

        return view('reports/index', $data);
    }
    
    public function exportPdf()
    {
        // Get the same data as index method
        $workerModel = new \App\Models\WorkerModel();
        $zoneModel = new \App\Models\ZoneModel();
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        
        // Get date filter from request
        $filterType = $this->request->getGet('filter') ?? 'today';
        $customStartDate = $this->request->getGet('start_date');
        $customEndDate = $this->request->getGet('end_date');
        
        // Determine date range
        $today = date('Y-m-d');
        $startDate = $today;
        $endDate = $today;
        $filterLabel = 'Today';
        
        if ($customStartDate && $customEndDate) {
            $startDate = $customStartDate;
            $endDate = $customEndDate;
            $filterLabel = date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate));
            $filterType = 'custom';
        } else {
            switch ($filterType) {
                case 'yesterday':
                    $startDate = $endDate = date('Y-m-d', strtotime('-1 day'));
                    $filterLabel = 'Yesterday';
                    break;
                case 'week':
                    $startDate = date('Y-m-d', strtotime('monday this week'));
                    $endDate = date('Y-m-d', strtotime('sunday this week'));
                    $filterLabel = 'This Week';
                    break;
                case 'last_week':
                    $startDate = date('Y-m-d', strtotime('monday last week'));
                    $endDate = date('Y-m-d', strtotime('sunday last week'));
                    $filterLabel = 'Last Week';
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = date('Y-m-t');
                    $filterLabel = 'This Month';
                    break;
                case 'last_month':
                    $startDate = date('Y-m-01', strtotime('first day of last month'));
                    $endDate = date('Y-m-t', strtotime('last day of last month'));
                    $filterLabel = 'Last Month';
                    break;
                default:
                    $startDate = $endDate = $today;
                    $filterLabel = 'Today';
            }
        }
        
        $allZones = $zoneModel->where('status', 'active')->findAll();
        $totalZones = count($allZones);
        
        if ($startDate === $endDate) {
            $records = $attendanceModel->getAttendanceByDate($startDate);
        } else {
            $records = $attendanceModel->getAttendanceByDateRange($startDate, $endDate);
        }
        
        $allWorkers = $workerModel->getAllWorkersFormatted();
        $totalWorkers = count($allWorkers);
        
        $totalCheckIns = count($records);
        $completedVisits = 0;
        $activeVisits = 0;
        $totalDurationMinutes = 0;
        
        foreach ($records as $record) {
            if ($record['check_in_time'] && $record['check_out_time']) {
                $duration = strtotime($record['check_out_time']) - strtotime($record['check_in_time']);
                $totalDurationMinutes += $duration / 60;
                $completedVisits++;
            } else {
                $activeVisits++;
            }
        }
        
        $avgDuration = $completedVisits > 0 ? round($totalDurationMinutes / $completedVisits) : 0;
        
        $zoneStats = [];
        foreach ($allZones as $zone) {
            $zoneRecords = array_filter($records, function($record) use ($zone) {
                return $record['zone_id'] == $zone['zone_id'];
            });
            
            $zoneCheckIns = count($zoneRecords);
            $zoneDuration = 0;
            $zoneCompleted = 0;
            
            foreach ($zoneRecords as $record) {
                if ($record['check_in_time'] && $record['check_out_time']) {
                    $duration = strtotime($record['check_out_time']) - strtotime($record['check_in_time']);
                    $zoneDuration += $duration / 60;
                    $zoneCompleted++;
                }
            }
            
            $zoneAvgDuration = $zoneCompleted > 0 ? round($zoneDuration / $zoneCompleted) : 0;
            
            $zoneStats[] = [
                'zone_name' => $zone['zone_name'],
                'zone_id' => $zone['zone_id'],
                'location' => $zone['location'] ?? '-',
                'total_visits' => $zoneCheckIns,
                'completed_visits' => $zoneCompleted,
                'avg_duration' => $this->formatMinutes($zoneAvgDuration)
            ];
        }
        
        usort($zoneStats, function($a, $b) {
            return $b['total_visits'] - $a['total_visits'];
        });
        
        $workerStats = [];
        foreach ($allWorkers as $worker) {
            $workerRecords = array_filter($records, function($record) use ($worker) {
                return $record['worker_id'] == $worker['worker_id'];
            });
            
            if (empty($workerRecords)) continue;
            
            $workerCheckIns = count($workerRecords);
            $workerDuration = 0;
            $workerCompleted = 0;
            $uniqueZones = [];
            
            foreach ($workerRecords as $record) {
                $uniqueZones[$record['zone_id']] = true;
                
                if ($record['check_in_time'] && $record['check_out_time']) {
                    $duration = strtotime($record['check_out_time']) - strtotime($record['check_in_time']);
                    $workerDuration += $duration / 60;
                    $workerCompleted++;
                }
            }
            
            $workerAvgDuration = $workerCompleted > 0 ? round($workerDuration / $workerCompleted) : 0;
            
            $workerStats[] = [
                'worker_name' => $worker['full_name'],
                'worker_id' => $worker['worker_id'],
                'department' => ucfirst($worker['department']),
                'position' => $worker['position'],
                'total_visits' => $workerCheckIns,
                'completed_visits' => $workerCompleted,
                'zones_visited' => count($uniqueZones),
                'total_duration' => $this->formatMinutes($workerDuration),
                'avg_duration' => $this->formatMinutes($workerAvgDuration)
            ];
        }
        
        usort($workerStats, function($a, $b) {
            return $b['total_visits'] - $a['total_visits'];
        });
        
        $activeWorkers = [];
        foreach ($records as $record) {
            $activeWorkers[$record['worker_id']] = true;
        }
        $activeWorkersCount = count($activeWorkers);
        
        $data = [
            'filter_label' => $filterLabel,
            'generated_date' => date('F d, Y \a\t h:i A'),
            'summary' => [
                'total_workers' => $totalWorkers,
                'active_workers' => $activeWorkersCount,
                'total_zones' => $totalZones,
                'total_check_ins' => $totalCheckIns,
                'completed_visits' => $completedVisits,
                'active_visits' => $activeVisits,
                'avg_duration' => $this->formatMinutes($avgDuration)
            ],
            'zone_stats' => $zoneStats,
            'worker_stats' => $workerStats
        ];
        
        // Generate PDF
        $html = view('reports/pdf', $data);
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = 'WorkWise_Report_' . date('Y-m-d_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }
    
    private function formatMinutes($minutes)
    {
        if ($minutes < 60) {
            return round($minutes) . 'm';
        }
        
        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);
        
        if ($mins == 0) {
            return $hours . 'h';
        }
        
        return $hours . 'h ' . $mins . 'm';
    }
}
