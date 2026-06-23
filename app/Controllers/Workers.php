<?php

namespace App\Controllers;

class Workers extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url('workers/list'));
    }

    public function tracking($workerId)
    {
        $workerModel     = new \App\Models\WorkerModel();
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        $zoneModel       = new \App\Models\ZoneModel();

        $worker = $workerModel->where('worker_id', $workerId)->first();
        if (!$worker) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Worker not found.');
        }

        // Date selection
        $selectedDate = $this->request->getGet('date') ?? date('Y-m-d');
        $today = date('Y-m-d');

        // All attendance records for this worker on the selected date
        $records = $attendanceModel
            ->where('worker_id', $workerId)
            ->where('date', $selectedDate)
            ->orderBy('check_in_time', 'ASC')
            ->findAll();

        // Enrich records with zone info and per-record duration
        $zoneVisits = [];
        $zoneTotals = []; // keyed by zone_id: ['name' => ..., 'seconds' => ...]
        $totalInZoneSec = 0;

        foreach ($records as $rec) {
            $zone = $zoneModel->where('zone_id', $rec['zone_id'])->first();
            $zoneName = $zone ? $zone['zone_name'] : $rec['zone_id'];

            $durationSec = null;
            if ($rec['check_in_time'] && $rec['check_out_time']) {
                $durationSec = strtotime($rec['check_out_time']) - strtotime($rec['check_in_time']);
                $totalInZoneSec += $durationSec;
            }

            $zoneVisits[] = [
                'zone_id'      => $rec['zone_id'],
                'zone_name'    => $zoneName,
                'entry_time'   => $rec['check_in_time'],
                'exit_time'    => $rec['check_out_time'],
                'duration_sec' => $durationSec,
                'duration_fmt' => $durationSec !== null ? $this->fmtDur($durationSec) : null,
                'is_active'    => !$rec['check_out_time'],
            ];

            // Accumulate zone totals
            if (!isset($zoneTotals[$rec['zone_id']])) {
                $zoneTotals[$rec['zone_id']] = ['name' => $zoneName, 'seconds' => 0, 'visits' => 0];
            }
            $zoneTotals[$rec['zone_id']]['visits']++;
            if ($durationSec !== null) {
                $zoneTotals[$rec['zone_id']]['seconds'] += $durationSec;
            }
        }

        // Total observed window = first check-in to last check-out (or now)
        $firstIn  = null;
        $lastOut  = null;
        $totalObservedSec = 0;
        $notInAreaSec = 0;

        if (!empty($records)) {
            $inTimes  = array_filter(array_column($records, 'check_in_time'));
            $outTimes = array_filter(array_column($records, 'check_out_time'));
            $firstIn  = !empty($inTimes) ? min($inTimes) : null;
            $lastRef  = !empty($outTimes) ? max($outTimes) : date('Y-m-d H:i:s');
            $lastOut  = !empty($outTimes) ? max($outTimes) : null;

            if ($firstIn) {
                $totalObservedSec = strtotime($lastRef) - strtotime($firstIn);
                // Time gaps between zones = observed - in-zone
                $notInAreaSec = max(0, $totalObservedSec - $totalInZoneSec);
            }
        }

        // Format zone totals
        $zoneSummary = [];
        foreach ($zoneTotals as $zid => $zt) {
            $zoneSummary[] = [
                'name'    => $zt['name'],
                'visits'  => $zt['visits'],
                'seconds' => $zt['seconds'],
                'fmt'     => $zt['seconds'] > 0 ? $this->fmtDur($zt['seconds']) : '-',
            ];
        }

        return view('workers/tracking', [
            'title'            => 'Worker Tracking — ' . $worker['first_name'] . ' ' . $worker['last_name'],
            'user'             => $this->getLoggedInUser(),
            'worker'           => $worker,
            'selected_date'    => $selectedDate,
            'today'            => $today,
            'zone_visits'      => $zoneVisits,
            'zone_summary'     => $zoneSummary,
            'first_in'         => $firstIn,
            'last_out'         => $lastOut,
            'total_in_zone'    => $this->fmtDur($totalInZoneSec),
            'total_not_in_zone' => $this->fmtDur($notInAreaSec),
            'total_observed'   => $totalObservedSec > 0 ? $this->fmtDur($totalObservedSec) : '-',
            'total_in_zone_sec' => $totalInZoneSec,
            'total_not_in_zone_sec' => $notInAreaSec,
            'total_observed_sec' => $totalObservedSec,
        ]);
    }

    private function fmtDur(int $seconds): string
    {
        if ($seconds <= 0) return '0m';
        $m = round($seconds / 60);
        if ($m < 60) return $m . 'm';
        $h = floor($m / 60);
        $rem = $m % 60;
        return $rem === 0 ? $h . 'h' : $h . 'h ' . $rem . 'm';
    }

    private function resolveShiftEndTime(array $shiftEndTimes, string $shift, string $default = '14:00:00'): string
    {
        if (isset($shiftEndTimes[$shift])) {
            return $shiftEndTimes[$shift];
        }
        $lower = strtolower($shift);
        if (isset($shiftEndTimes[$lower])) {
            return $shiftEndTimes[$lower];
        }
        $capital = ucfirst($lower);
        if (isset($shiftEndTimes[$capital])) {
            return $shiftEndTimes[$capital];
        }
        return $default;
    }

    /**
     * Staff who left before shift end and are not currently inside any zone.
     * Uses the latest transaction for display; re-entering a zone removes them from the list.
     */
    private function buildEarlyStaffList(array $workers, string $date, array $shiftEndTimes, ?string $shiftFilter = null): array
    {
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        $earlyStaff      = [];

        foreach ($workers as $worker) {
            $shift = $worker['shift'] ?? 'morning';

            if ($shiftFilter && strtolower($shift) !== strtolower($shiftFilter)) {
                continue;
            }

            $shiftEndTime = $this->resolveShiftEndTime($shiftEndTimes, $shift);

            $records = $attendanceModel->where('worker_id', $worker['worker_id'])
                ->where('date', $date)
                ->orderBy('check_in_time', 'DESC')
                ->findAll();

            if (empty($records)) {
                continue;
            }

            // Still inside any zone — not early
            $hasActive = !empty(array_filter($records, fn($r) => empty($r['check_out_time'])));
            if ($hasActive) {
                continue;
            }

            $lastTxn = $records[0];
            if (empty($lastTxn['check_out_time'])) {
                continue;
            }

            if (strtolower($shift) === 'night') {
                $shiftEndDateTime = strtotime($date . ' +1 day ' . $shiftEndTime);
            } else {
                $shiftEndDateTime = strtotime($date . ' ' . $shiftEndTime);
            }
            $checkOutDateTime = strtotime($lastTxn['check_out_time']);

            if ($checkOutDateTime >= $shiftEndDateTime) {
                continue;
            }

            $earlyMinutes = round(($shiftEndDateTime - $checkOutDateTime) / 60);
            $entry        = [
                'worker_id'       => $worker['worker_id'],
                'full_name'       => $worker['full_name'],
                'ic_number'       => $worker['ic_number'] ?? '-',
                'check_out_time'  => date('d/m/Y H:i:s', strtotime($lastTxn['check_out_time'])),
                'shift'           => ucfirst($shift),
                'shift_end'       => date('h:i A', strtotime($shiftEndTime)),
                'early_minutes'   => $earlyMinutes,
            ];

            if (isset($worker['profile_photo'])) {
                $entry['profile_photo'] = $worker['profile_photo'];
            }
            if (isset($worker['initials'])) {
                $entry['initials'] = $worker['initials'];
            }

            $earlyStaff[] = $entry;
        }

        return $earlyStaff;
    }

    public function workerList()
    {
        $workerModel = new \App\Models\WorkerModel();
        $workers = $workerModel->getAllWorkersFormatted();
        
        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->getActiveDepartments();

        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        
        // Calculate stats
        $totalWorkers = count($workers);
        $activeToday = 0;
        $inactive = 0;
        
        // Format workers for view
        $formattedWorkers = [];
        $colors = ['blue', 'purple', 'yellow', 'pink', 'cyan', 'green', 'red', 'indigo', 'orange', 'teal'];
        $colorIndex = 0;
        
        foreach ($workers as $worker) {
            // Count status
            if ($worker['status'] === 'active') {
                $activeToday++;
            } elseif ($worker['status'] === 'inactive') {
                $inactive++;
            }
            
            // Map status colors
            $statusColor = 'gray';
            $statusLabel = ucfirst($worker['status']);
            switch ($worker['status']) {
                case 'active':
                    $statusColor = 'green';
                    $statusLabel = 'Active';
                    break;
                case 'inactive':
                    $statusColor = 'red';
                    $statusLabel = 'Inactive';
                    break;
                case 'on_break':
                    $statusColor = 'yellow';
                    $statusLabel = 'On Break';
                    break;
                case 'offline':
                    $statusColor = 'gray';
                    $statusLabel = 'Offline';
                    break;
            }
            
            // Format last active
            $lastActive = 'Never';
            if (!empty($worker['last_active'])) {
                $lastActiveTime = strtotime($worker['last_active']);
                $diff = time() - $lastActiveTime;
                if ($diff < 60) {
                    $lastActive = 'Just now';
                } elseif ($diff < 3600) {
                    $lastActive = floor($diff / 60) . 'm ago';
                } elseif ($diff < 86400) {
                    $lastActive = floor($diff / 3600) . 'h ago';
                } else {
                    $lastActive = floor($diff / 86400) . 'd ago';
                }
            }
            
            $formattedWorkers[] = [
                'name' => $worker['full_name'],
                'email' => $worker['email'],
                'initials' => $worker['initials'],
                'color' => $colors[$colorIndex % count($colors)],
                'id_number' => $worker['worker_id'],
                'department' => ucfirst($worker['department']),
                'role' => $worker['position'],
                'total_zones' => $worker['total_zones'],
                'status' => $statusLabel,
                'status_color' => $statusColor,
                'last_active' => $lastActive,
                'shift' => $worker['shift'],
                'profile_photo' => $worker['profile_photo'] ?? null,
                'rfid_tag_id' => $worker['rfid_tag_id'] ?? ''
            ];
            
            $colorIndex++;
        }
        
        $data = [
            'title' => 'Worker List',
            'user' => $this->getLoggedInUser(),
            'stats' => [
                'total_workers' => $totalWorkers,
                'active_today' => $activeToday,
                'inactive' => $inactive
            ],
            'workers' => $formattedWorkers,
            'departments' => $departments,
            'shifts' => $shifts
        ];

        return view('workers/list', $data);
    }

    public function attendance()
    {
        $workerModel = new \App\Models\WorkerModel();
        // Get only active workers for attendance tracking
        $allWorkers = $workerModel->getAllWorkersFormatted();
        $workers = array_filter($allWorkers, function($worker) {
            return $worker['status'] === 'active';
        });
        
        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->getActiveDepartments();
        
        $shiftModel = new \App\Models\ShiftModel();
        $activeShifts = $shiftModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        
        $zoneModel = new \App\Models\ZoneModel();
        $allZones = $zoneModel->where('status', 'active')->findAll();
        
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        
        // Get date filter from request
        $filterType = $this->request->getGet('filter') ?? 'today';
        $customDate = $this->request->getGet('date');
        
        // Determine date range based on filter
        $currentDate = date('Y-m-d');
        $startDate = $currentDate;
        $endDate = $currentDate;
        $filterLabel = 'Today';
        
        if ($customDate) {
            $startDate = $endDate = $customDate;
            $filterLabel = date('M d, Y', strtotime($customDate));
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
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = date('Y-m-t');
                    $filterLabel = 'This Month';
                    break;
                default: // today
                    $startDate = $endDate = $currentDate;
                    $filterLabel = 'Today';
            }
        }
        
        // Get attendance records for date range
        if ($startDate === $endDate) {
            $records = $attendanceModel->getAttendanceByDate($startDate);
        } else {
            $records = $attendanceModel->getAttendanceByDateRange($startDate, $endDate);
        }
        
        // Build shift times map from database shifts
        $shiftTimes = [];
        foreach ($activeShifts as $shiftData) {
            $shiftTimes[$shiftData['name']] = $shiftData['start_time'];
        }
        
        // Get current time
        $currentTime = date('H:i:s');
        
        // Format workers for attendance view with their assigned zones details
        $attendance = [];
        foreach ($workers as $worker) {
            // Get zone details for assigned zones
            $workerZones = [];
            if (!empty($worker['zones_array'])) {
                foreach ($worker['zones_array'] as $zoneId) {
                    // Find zone details
                    foreach ($allZones as $zone) {
                        if ($zone['zone_id'] == $zoneId) {
                            $workerZones[] = [
                                'zone_id' => $zone['zone_id'],
                                'zone_name' => $zone['zone_name'],
                                'location' => $zone['location'] ?? ''
                            ];
                            break;
                        }
                    }
                }
            }
            
            // Get attendance records for this worker in date range
            $workerRecords = array_filter($records, function($record) use ($worker) {
                return $record['worker_id'] === $worker['worker_id'];
            });
            
            // Determine first check-in and last check-out
            $firstCheckIn = null;
            $lastCheckOut = null;
            $zoneVisits = [];
            
            foreach ($workerRecords as $record) {
                if ($record['check_in_time']) {
                    if (!$firstCheckIn || $record['check_in_time'] < $firstCheckIn) {
                        $firstCheckIn = $record['check_in_time'];
                    }
                }
                
                if ($record['check_out_time']) {
                    if (!$lastCheckOut || $record['check_out_time'] > $lastCheckOut) {
                        $lastCheckOut = $record['check_out_time'];
                    }
                }
                
                // Build zone visits
                $zoneName = '';
                foreach ($allZones as $zone) {
                    if ($zone['zone_id'] == $record['zone_id']) {
                        $zoneName = $zone['zone_name'];
                        break;
                    }
                }
                
                $zoneVisits[] = [
                    'name' => $zoneName,
                    'entry' => $record['check_in_time'] ? date('g:i A', strtotime($record['check_in_time'])) : '-',
                    'exit' => $record['check_out_time'] ? date('g:i A', strtotime($record['check_out_time'])) : null,
                    'duration' => $this->calculateDuration($record['check_in_time'], $record['check_out_time'])
                ];
            }
            
            // Calculate total work hours
            $workHours = null;
            if ($firstCheckIn && $lastCheckOut) {
                $start = strtotime($firstCheckIn);
                $end = strtotime($lastCheckOut);
                $totalMinutes = ($end - $start) / 60;
                
                if ($totalMinutes < 60) {
                    // Show minutes if less than 1 hour
                    $workHours = round($totalMinutes) . 'm';
                } else {
                    // Show hours if 1 hour or more
                    $hours = $totalMinutes / 60;
                    $workHours = number_format($hours, 1) . 'h';
                }
            }
            
            // Determine attendance status based on shift
            $shift = $worker['shift'] ?? 'morning';
            $shiftStartTime = $shiftTimes[$shift] ?? '06:00:00';
            
            $attendance[] = [
                'worker_id' => $worker['worker_id'],
                'name' => $worker['full_name'],
                'id_tag' => $worker['worker_id'],
                'department' => $worker['department'],
                'shift' => ucfirst($shift),
                'shift_start' => date('g:i A', strtotime($shiftStartTime)),
                'time_in' => $firstCheckIn ? date('g:i A', strtotime($firstCheckIn)) : null,
                'time_out' => $lastCheckOut ? date('g:i A', strtotime($lastCheckOut)) : null,
                'work_hours' => $workHours,
                'zones' => $zoneVisits,
                'assigned_zones' => $workerZones, // Available zones for this worker
                'status' => $worker['status'] ?? 'active', // Worker status for inactive/on leave check
                'profile_photo' => $worker['profile_photo'] ?? null,
                'initials' => $worker['initials'] ?? strtoupper(substr($worker['full_name'], 0, 2))
            ];
        }

        $data = [
            'title' => 'Attendance',
            'user' => $this->getLoggedInUser(),
            'attendance' => $attendance,
            'workers' => $workers, // For the modal dropdown
            'all_zones' => $allZones,
            'departments' => $departments,
            'shifts' => $activeShifts,
            'shift_times' => $shiftTimes,
            'filter_label' => $filterLabel,
            'filter_type' => $filterType,
            'custom_date' => $customDate,
            'is_today' => ($endDate === date('Y-m-d')),
            'viewing_date' => $endDate
        ];

        return view('workers/attendance', $data);
    }
    
    public function attendanceData()
    {
        $workerModel = new \App\Models\WorkerModel();
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        $zoneModel = new \App\Models\ZoneModel();
        
        // Get date filter
        $filterType = $this->request->getGet('filter') ?? 'today';
        $customDate = $this->request->getGet('date');
        
        $currentDate = date('Y-m-d');
        $startDate = $currentDate;
        $endDate = $currentDate;
        
        if ($customDate) {
            $startDate = $endDate = $customDate;
        } else {
            switch ($filterType) {
                case 'yesterday':
                    $startDate = $endDate = date('Y-m-d', strtotime('-1 day'));
                    break;
                case 'today':
                default:
                    $startDate = $endDate = $currentDate;
            }
        }
        
        $isToday = ($startDate === $currentDate);
        
        // Get workers and attendance records
        $workers = $workerModel->getAllWorkersFormatted();
        $allZones = $zoneModel->where('status', 'active')->findAll();
        
        // Load shifts
        $shiftModel = new \App\Models\ShiftModel();
        $activeShifts = $shiftModel->where('is_active', 1)->findAll();
        
        // Get attendance records
        if ($startDate === $endDate) {
            $records = $attendanceModel->getAttendanceByDate($startDate);
        } else {
            $records = $attendanceModel->getAttendanceByDateRange($startDate, $endDate);
        }
        
        // Build shift times map
        $shiftTimes = [];
        foreach ($activeShifts as $shiftData) {
            $shiftTimes[$shiftData['name']] = $shiftData['start_time'];
        }
        
        $currentTime = date('H:i:s');
        
        // Format attendance data
        $attendance = [];
        foreach ($workers as $worker) {
            $workerZones = [];
            if (!empty($worker['zones_array'])) {
                foreach ($worker['zones_array'] as $zoneId) {
                    foreach ($allZones as $zone) {
                        if ($zone['zone_id'] == $zoneId) {
                            $workerZones[] = [
                                'zone_id' => $zone['zone_id'],
                                'zone_name' => $zone['zone_name']
                            ];
                            break;
                        }
                    }
                }
            }
            
            $workerAttendance = [
                'worker_id' => $worker['worker_id'],
                'name' => $worker['full_name'],
                'initials' => $worker['initials'],
                'id_tag' => $worker['worker_id'],
                'department' => ucfirst($worker['department']),
                'shift' => ucfirst($worker['shift']),
                'shift_start' => $shiftTimes[$worker['shift']] ?? '06:00:00',
                'time_in' => null,
                'time_out' => null,
                'work_hours' => null,
                'zones' => [],
                'assigned_zones' => $workerZones,
                'profile_photo' => $worker['profile_photo'] ?? null,
                'status' => $worker['status'] ?? 'active'
            ];
            
            // Find records for this worker
            $workerRecords = array_filter($records, function($r) use ($worker) {
                return $r['worker_id'] == $worker['worker_id'];
            });
            
            if (!empty($workerRecords)) {
                usort($workerRecords, function($a, $b) {
                    return strtotime($a['check_in_time']) - strtotime($b['check_in_time']);
                });
                
                $firstRecord = reset($workerRecords);
                $lastRecord = end($workerRecords);
                
                $workerAttendance['time_in'] = date('h:i A', strtotime($firstRecord['check_in_time']));
                
                if ($lastRecord['check_out_time']) {
                    $workerAttendance['time_out'] = date('h:i A', strtotime($lastRecord['check_out_time']));
                    
                    $timeIn = strtotime($firstRecord['check_in_time']);
                    $timeOut = strtotime($lastRecord['check_out_time']);
                    $diff = $timeOut - $timeIn;
                    $hours = floor($diff / 3600);
                    $minutes = floor(($diff % 3600) / 60);
                    $workerAttendance['work_hours'] = $hours . '.' . round(($minutes / 60) * 10) . 'h';
                }
                
                // Zone details
                foreach ($workerRecords as $record) {
                    $zoneName = 'Unknown Zone';
                    foreach ($allZones as $zone) {
                        if ($zone['zone_id'] == $record['zone_id']) {
                            $zoneName = $zone['zone_name'];
                            break;
                        }
                    }
                    
                    $duration = null;
                    if ($record['check_out_time']) {
                        $entryTime = strtotime($record['check_in_time']);
                        $exitTime = strtotime($record['check_out_time']);
                        $diff = $exitTime - $entryTime;
                        $hours = floor($diff / 3600);
                        $minutes = floor(($diff % 3600) / 60);
                        $duration = ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm';
                    }
                    
                    $workerAttendance['zones'][] = [
                        'name' => $zoneName,
                        'entry' => date('h:i A', strtotime($record['check_in_time'])),
                        'exit' => $record['check_out_time'] ? date('h:i A', strtotime($record['check_out_time'])) : null,
                        'duration' => $duration
                    ];
                }
            }
            
            $attendance[] = $workerAttendance;
        }
        
        // Calculate stats
        $presentCount = 0;
        $absentCount = 0;
        $totalHours = 0;
        $totalMinutes = 0;
        $workHoursCount = 0;
        
        foreach ($attendance as $record) {
            if ($record['time_in']) {
                $presentCount++;
                
                if ($record['work_hours']) {
                    $hours = (float)str_replace('h', '', $record['work_hours']);
                    $totalHours += $hours;
                    $workHoursCount++;
                }
                
                $time = strtotime($record['time_in']);
                $hours = (int)date('H', $time);
                $minutes = (int)date('i', $time);
                $totalMinutes += ($hours * 60) + $minutes;
            } else {
                // Only count as absent if shift has started and viewing today
                if ($isToday) {
                    $shiftName = $record['shift'];
                    $shiftLower = strtolower($shiftName);
                    $shiftCapital = ucfirst($shiftLower);
                    
                    // Try to find shift start time with multiple variations
                    $shiftStartTime = '06:00:00';
                    if (isset($shiftTimes[$shiftName])) {
                        $shiftStartTime = $shiftTimes[$shiftName];
                    } elseif (isset($shiftTimes[$shiftLower])) {
                        $shiftStartTime = $shiftTimes[$shiftLower];
                    } elseif (isset($shiftTimes[$shiftCapital])) {
                        $shiftStartTime = $shiftTimes[$shiftCapital];
                    }
                    
                    // Only count as absent if shift has started and worker is not inactive
                    if ($currentTime > $shiftStartTime && $record['status'] !== 'inactive') {
                        $absentCount++;
                    }
                }
            }
        }
        
        $avgTimeIn = '-';
        if ($presentCount > 0) {
            $avgMinutes = round($totalMinutes / $presentCount);
            $avgHours = floor($avgMinutes / 60);
            $avgMins = $avgMinutes % 60;
            $avgTimeIn = sprintf('%d:%02d', $avgHours, $avgMins);
        }
        
        $avgWorkHours = '-';
        if ($workHoursCount > 0) {
            $avgWorkHours = number_format($totalHours / $workHoursCount, 1) . 'h';
        }
        
        // Sanitize all string data to ensure valid UTF-8
        $attendance = array_map(function($record) {
            return array_map(function($value) {
                if (is_string($value)) {
                    return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
                return $value;
            }, $record);
        }, $attendance);
        
        return $this->response->setJSON([
            'success' => true,
            'attendance' => $attendance,
            'stats' => [
                'present' => $presentCount,
                'absent' => $absentCount,
                'avg_time_in' => $avgTimeIn,
                'avg_work_hours' => $avgWorkHours
            ],
            'is_today' => $isToday,
            'current_time' => $currentTime,
            'shift_times' => $shiftTimes
        ]);
    }
    
    public function lateList()
    {
        $workerModel = new \App\Models\WorkerModel();
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        
        $workers = $workerModel->getAllWorkersFormatted();
        
        // Load shifts from database
        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->findAll();
        
        // Build shift times map from database shifts
        $shiftTimes = [];
        foreach ($shifts as $shiftData) {
            $shiftTimes[$shiftData['name']] = $shiftData['start_time'];
        }
        
        // Handle date filtering
        $filter = $this->request->getGet('filter');
        $customDate = $this->request->getGet('date');
        
        if ($customDate) {
            $date = $customDate;
            $dateLabel = date('M d, Y', strtotime($date));
        } elseif ($filter === 'today') {
            $date = date('Y-m-d');
            $dateLabel = 'Today';
        } elseif ($filter === 'yesterday') {
            $date = date('Y-m-d', strtotime('-1 day'));
            $dateLabel = 'Yesterday';
        } else {
            $date = date('Y-m-d');
            $dateLabel = 'Today';
        }
        
        $lateStaff = [];
        
        foreach ($workers as $worker) {
            $shift = $worker['shift'] ?? 'morning';
            $shiftStartTime = $shiftTimes[$shift] ?? '06:00:00';
            
            // Get attendance records for selected date
            $records = $attendanceModel->where('worker_id', $worker['worker_id'])
                                      ->where('date', $date)
                                      ->orderBy('check_in_time', 'ASC')
                                      ->findAll();
            
            if (!empty($records)) {
                $firstCheckIn = $records[0];
                $checkInTime = date('H:i:s', strtotime($firstCheckIn['check_in_time']));
                
                // Check if worker is late
                if ($checkInTime > $shiftStartTime) {
                    $lateStaff[] = [
                        'worker_id' => $worker['worker_id'],
                        'full_name' => $worker['full_name'],
                        'ic_number' => $worker['ic_number'] ?? '-',
                        'check_in_time' => date('d/m/Y H:i:s', strtotime($firstCheckIn['check_in_time'])),
                        'shift' => ucfirst($shift),
                        'shift_start' => date('h:i A', strtotime($shiftStartTime)),
                        'profile_photo' => $worker['profile_photo'] ?? null,
                        'initials' => $worker['initials']
                    ];
                }
            }
        }
        
        $data = [
            'title' => 'Staff Late List',
            'user' => $this->getLoggedInUser(),
            'lateStaff' => $lateStaff,
            'date' => $date,
            'dateLabel' => $dateLabel,
            'shifts' => $shifts
        ];
        
        return view('workers/late_list', $data);
    }
    
    public function exportLateList()
    {
        $workerModel = new \App\Models\WorkerModel();
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        
        $workers = $workerModel->getAllWorkersFormatted();
        
        // Load shifts from database
        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->findAll();
        
        // Build shift times map from database shifts
        $shiftTimes = [];
        foreach ($shifts as $shiftData) {
            $shiftTimes[$shiftData['name']] = $shiftData['start_time'];
        }
        
        // Handle date filtering
        $filter = $this->request->getGet('filter');
        $customDate = $this->request->getGet('date');
        $shiftFilter = $this->request->getGet('shift');
        
        if ($customDate) {
            $date = $customDate;
        } elseif ($filter === 'today') {
            $date = date('Y-m-d');
        } elseif ($filter === 'yesterday') {
            $date = date('Y-m-d', strtotime('-1 day'));
        } else {
            $date = date('Y-m-d');
        }
        
        $lateStaff = [];
        
        foreach ($workers as $worker) {
            $shift = $worker['shift'] ?? 'morning';
            
            // Apply shift filter
            if ($shiftFilter && strtolower($shift) !== strtolower($shiftFilter)) {
                continue;
            }
            
            $shiftStartTime = $shiftTimes[$shift] ?? '06:00:00';
            
            // Get attendance records for selected date
            $records = $attendanceModel->where('worker_id', $worker['worker_id'])
                                      ->where('date', $date)
                                      ->orderBy('check_in_time', 'ASC')
                                      ->findAll();
            
            if (!empty($records)) {
                $firstCheckIn = $records[0];
                $checkInTime = date('H:i:s', strtotime($firstCheckIn['check_in_time']));
                
                // Check if worker is late
                if ($checkInTime > $shiftStartTime) {
                    // Calculate how many minutes late
                    $shiftStartDateTime = strtotime($date . ' ' . $shiftStartTime);
                    $checkInDateTime = strtotime($firstCheckIn['check_in_time']);
                    $lateMinutes = round(($checkInDateTime - $shiftStartDateTime) / 60);
                    
                    $lateStaff[] = [
                        'worker_id' => $worker['worker_id'],
                        'full_name' => $worker['full_name'],
                        'ic_number' => $worker['ic_number'] ?? '-',
                        'check_in_time' => date('d/m/Y H:i:s', strtotime($firstCheckIn['check_in_time'])),
                        'shift' => ucfirst($shift),
                        'shift_start' => date('h:i A', strtotime($shiftStartTime)),
                        'late_minutes' => $lateMinutes
                    ];
                }
            }
        }
        
        // Generate CSV
        $filename = 'late_staff_' . date('Y-m-d', strtotime($date)) . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, ['No', 'Full Name', 'IC No/Passport No', 'Staff No', 'Shift', 'Shift Start', 'Check In Time', 'Late By']);
        
        // CSV Data
        foreach ($lateStaff as $index => $staff) {
            $totalMinutes = $staff['late_minutes'];
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            
            if ($hours > 0) {
                $lateBy = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            } else {
                $lateBy = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            }
            
            fputcsv($output, [
                $index + 1,
                $staff['full_name'],
                "\t" . $staff['ic_number'], // Add tab prefix to preserve leading zeros in Excel
                $staff['worker_id'],
                $staff['shift'],
                $staff['shift_start'],
                $staff['check_in_time'],
                $lateBy
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function earlyList()
    {
        $workerModel = new \App\Models\WorkerModel();
        
        $workers = $workerModel->getAllWorkersFormatted();
        
        // Load shifts from database
        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->findAll();
        
        // Build shift end times map from database shifts
        $shiftEndTimes = [];
        foreach ($shifts as $shiftData) {
            $shiftEndTimes[$shiftData['name']] = $shiftData['end_time'];
        }
        
        // Handle date filtering
        $filter = $this->request->getGet('filter');
        $customDate = $this->request->getGet('date');
        
        if ($customDate) {
            $date = $customDate;
            $dateLabel = date('M d, Y', strtotime($date));
        } elseif ($filter === 'today') {
            $date = date('Y-m-d');
            $dateLabel = 'Today';
        } elseif ($filter === 'yesterday') {
            $date = date('Y-m-d', strtotime('-1 day'));
            $dateLabel = 'Yesterday';
        } else {
            $date = date('Y-m-d');
            $dateLabel = 'Today';
        }
        
        $earlyStaff = $this->buildEarlyStaffList($workers, $date, $shiftEndTimes);
        
        $data = [
            'title' => 'Staff Early List',
            'user' => $this->getLoggedInUser(),
            'earlyStaff' => $earlyStaff,
            'date' => $date,
            'dateLabel' => $dateLabel,
            'shifts' => $shifts
        ];
        
        return view('workers/early_list', $data);
    }
    
    public function exportEarlyList()
    {
        $workerModel = new \App\Models\WorkerModel();
        
        $workers = $workerModel->getAllWorkersFormatted();
        
        // Load shifts from database
        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->findAll();
        
        // Build shift end times map from database shifts
        $shiftEndTimes = [];
        foreach ($shifts as $shiftData) {
            $shiftEndTimes[$shiftData['name']] = $shiftData['end_time'];
        }
        
        // Handle date filtering
        $filter = $this->request->getGet('filter');
        $customDate = $this->request->getGet('date');
        $shiftFilter = $this->request->getGet('shift');
        
        if ($customDate) {
            $date = $customDate;
        } elseif ($filter === 'today') {
            $date = date('Y-m-d');
        } elseif ($filter === 'yesterday') {
            $date = date('Y-m-d', strtotime('-1 day'));
        } else {
            $date = date('Y-m-d');
        }
        
        $earlyStaff = $this->buildEarlyStaffList($workers, $date, $shiftEndTimes, $shiftFilter);
        
        // Generate CSV
        $filename = 'early_staff_' . date('Y-m-d', strtotime($date)) . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, ['No', 'Full Name', 'IC No/Passport No', 'Staff No', 'Shift', 'Shift End', 'Check Out Time', 'Early By']);
        
        // CSV Data
        foreach ($earlyStaff as $index => $staff) {
            $totalMinutes = $staff['early_minutes'];
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            
            if ($hours > 0) {
                $earlyBy = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            } else {
                $earlyBy = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            }
            
            fputcsv($output, [
                $index + 1,
                $staff['full_name'],
                "\t" . $staff['ic_number'],
                $staff['worker_id'],
                $staff['shift'],
                $staff['shift_end'],
                $staff['check_out_time'],
                $earlyBy
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function activityLogs()
    {
        $workerModel = new \App\Models\WorkerModel();
        $zoneModel = new \App\Models\ZoneModel();
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        
        // Get date filter from request (default to today)
        $customDate = $this->request->getGet('date');
        
        if ($customDate) {
            $date = $customDate;
            // Check if it's today or yesterday
            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            if ($date === $today) {
                $dateLabel = 'Today';
            } elseif ($date === $yesterday) {
                $dateLabel = 'Yesterday';
            } else {
                $dateLabel = date('M d, Y', strtotime($date));
            }
        } else {
            $date = date('Y-m-d');
            $dateLabel = 'Today';
        }
        
        // Get all zones
        $allZones = $zoneModel->where('status', 'active')->findAll();
        
        // Get attendance records for selected date
        $todayRecords = $attendanceModel->getAttendanceByDate($date);
        
        // Get worker activity for the selected date
        $workers = $workerModel->getAllWorkersFormatted();
        $workerActivity = [];
        
        // Load shifts from database
        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->findAll();
        
        // Build shift times map from database shifts
        $shiftTimes = [];
        foreach ($shifts as $shiftData) {
            $shiftTimes[$shiftData['name']] = $shiftData['start_time'];
        }
        
        foreach ($workers as $worker) {
            $workerRecords = array_filter($todayRecords, function($record) use ($worker) {
                return $record['worker_id'] == $worker['worker_id'];
            });
            
            if (empty($workerRecords)) continue;
            
            // Get first check-in and last check-out
            $checkInTimes = array_column($workerRecords, 'check_in_time');
            $checkOutTimes = array_filter(array_column($workerRecords, 'check_out_time'));
            
            $firstCheckIn = !empty($checkInTimes) ? min($checkInTimes) : null;
            $lastCheckOut = !empty($checkOutTimes) ? max($checkOutTimes) : null;
            
            // Check if worker was late
            $shift = $worker['shift'] ?? 'morning';
            $shiftStartTime = $shiftTimes[$shift] ?? '06:00:00';
            $isLate = false;
            
            if ($firstCheckIn) {
                $checkInTime = date('H:i:s', strtotime($firstCheckIn));
                $isLate = ($checkInTime > $shiftStartTime);
            }
            
            // Get zone visits
            $zones = [];
            foreach ($workerRecords as $record) {
                $zone = $zoneModel->where('zone_id', $record['zone_id'])->where('status', 'active')->first();
                if ($zone) {
                    $duration = null;
                    if ($record['check_in_time'] && $record['check_out_time']) {
                        $durationSec = strtotime($record['check_out_time']) - strtotime($record['check_in_time']);
                        $duration = $this->formatDuration($durationSec);
                    }
                    
                    $zones[] = [
                        'name' => $zone['zone_name'],
                        'entry' => $record['check_in_time'] ? date('H:i', strtotime($record['check_in_time'])) : '-',
                        'exit' => $record['check_out_time'] ? date('H:i', strtotime($record['check_out_time'])) : 'Active',
                        'duration' => $duration ?? 'In Progress'
                    ];
                }
            }
            
            if (!empty($zones)) {
                $workerActivity[] = [
                    'name' => $worker['full_name'],
                    'department' => ucwords($worker['department']),
                    'id_tag' => $worker['worker_id'],
                    'time_in' => $firstCheckIn ? date('H:i', strtotime($firstCheckIn)) : '-',
                    'time_out' => $lastCheckOut ? date('H:i', strtotime($lastCheckOut)) : '-',
                    'is_late' => $isLate,
                    'zones' => $zones
                ];
            }
        }
        
        $data = [
            'title' => 'Worker Activity Logs',
            'user' => $this->getLoggedInUser(),
            'worker_activity' => $workerActivity,
            'date' => $date,
            'date_label' => $dateLabel
        ];
        
        return view('workers/activity_logs', $data);
    }
    
    private function formatDuration($seconds)
    {
        $minutes = round($seconds / 60);
        
        if ($minutes < 60) {
            return $minutes . 'm';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($mins == 0) {
            return $hours . 'h';
        }
        
        return $hours . 'h ' . $mins . 'm';
    }
    
    private function calculateDuration($checkIn, $checkOut)
    {
        if (!$checkIn) return '-';
        if (!$checkOut) return '-';
        
        $start = strtotime($checkIn);
        $end = strtotime($checkOut);
        $minutes = ($end - $start) / 60;
        
        if ($minutes < 60) {
            return round($minutes) . 'm';
        } else {
            $hours = floor($minutes / 60);
            $mins = round($minutes % 60);
            return $hours . 'h ' . $mins . 'm';
        }
    }
    
    public function recordAttendance()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        $data = $this->request->getJSON(true);
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        
        $workerId = $data['worker_id'] ?? null;
        $zoneId = $data['zone_id'] ?? null;
        $time = $data['time'] ?? null;
        $action = $data['action'] ?? null;
        $date = date('Y-m-d');
        
        if (!$workerId || !$zoneId || !$time || !$action) {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing required fields']);
        }
        
        // Combine date and time
        $datetime = $date . ' ' . $time . ':00';
        
        if ($action === 'in') {
            // Check if there's already an active check-in for this zone
            $existing = $attendanceModel->getActiveCheckIn($workerId, $zoneId, $date);
            if ($existing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Worker is already checked in to this zone']);
            }
            
            // Check if there was a recent check-out from this zone (within 1 minute)
            $recentCheckOut = $attendanceModel
                ->where('worker_id', $workerId)
                ->where('zone_id', $zoneId)
                ->where('date', $date)
                ->where('check_out_time IS NOT NULL')
                ->orderBy('check_out_time', 'DESC')
                ->first();
            
            if ($recentCheckOut) {
                $config = config('RFIDReader');
                $requiredInterval = $config->checkOutToCheckInInterval;
                
                $lastCheckOutTime = strtotime($recentCheckOut['check_out_time']);
                $checkInTime = strtotime($datetime);
                $timeSinceCheckOut = $checkInTime - $lastCheckOutTime; // in seconds
                
                // If less than configured interval since last check-out, reject
                if ($timeSinceCheckOut < $requiredInterval) {
                    $timeRemaining = $requiredInterval - $timeSinceCheckOut;
                    return $this->response->setJSON([
                        'success' => false, 
                        'message' => "Please wait at least {$requiredInterval} seconds after check-out before checking in again. Wait {$timeRemaining} more seconds."
                    ]);
                }
            }
            
            // Create new check-in record
            $attendanceModel->insert([
                'worker_id' => $workerId,
                'zone_id' => $zoneId,
                'check_in_time' => $datetime,
                'date' => $date
            ]);
            
            return $this->response->setJSON(['success' => true, 'message' => 'Check-in recorded successfully']);
        } else {
            // Find active check-in for this zone
            $existing = $attendanceModel->getActiveCheckIn($workerId, $zoneId, $date);
            if (!$existing) {
                return $this->response->setJSON(['success' => false, 'message' => 'No active check-in found for this zone']);
            }
            
            // Check if enough time has passed since check-in (minimum from config)
            $config = config('RFIDReader');
            $requiredInterval = $config->checkInToCheckOutInterval;
            
            $checkInTime = strtotime($existing['check_in_time']);
            $checkOutTime = strtotime($datetime);
            $timeDifference = $checkOutTime - $checkInTime; // in seconds
            
            // If less than configured interval, reject the check-out
            if ($timeDifference < $requiredInterval) {
                $timeRemaining = $requiredInterval - $timeDifference;
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => "Please wait at least {$requiredInterval} seconds before checking out. Wait {$timeRemaining} more seconds."
                ]);
            }
            
            // Update with check-out time
            $attendanceModel->update($existing['id'], [
                'check_out_time' => $datetime
            ]);
            
            return $this->response->setJSON(['success' => true, 'message' => 'Check-out recorded successfully']);
        }
    }

    /**
     * Determine icon and color based on zone name keywords
     */
    private function getIconFromZoneName($zoneName)
    {
        $zoneName = strtolower($zoneName);
        
        // Check for keywords and assign appropriate icon
        if (stripos($zoneName, 'main') !== false || stripos($zoneName, 'entrance') !== false || stripos($zoneName, 'gate') !== false) {
            return ['icon' => 'home_pin', 'color' => 'blue'];
        }
        
        if (stripos($zoneName, 'warehouse') !== false || stripos($zoneName, 'storage') !== false || stripos($zoneName, 'loading') !== false) {
            return ['icon' => 'warehouse', 'color' => 'amber'];
        }
        
        if (stripos($zoneName, 'server') !== false || stripos($zoneName, 'data') !== false || stripos($zoneName, 'network') !== false) {
            return ['icon' => 'dns', 'color' => 'red'];
        }
        
        if (stripos($zoneName, 'cafeteria') !== false || stripos($zoneName, 'restaurant') !== false || stripos($zoneName, 'dining') !== false || stripos($zoneName, 'kitchen') !== false) {
            return ['icon' => 'restaurant', 'color' => 'teal'];
        }
        
        if (stripos($zoneName, 'office') !== false || stripos($zoneName, 'desk') !== false || stripos($zoneName, 'work') !== false) {
            return ['icon' => 'business_center', 'color' => 'blue'];
        }
        
        if (stripos($zoneName, 'parking') !== false || stripos($zoneName, 'garage') !== false) {
            return ['icon' => 'local_parking', 'color' => 'indigo'];
        }
        
        if (stripos($zoneName, 'lobby') !== false || stripos($zoneName, 'reception') !== false) {
            return ['icon' => 'meeting_room', 'color' => 'purple'];
        }
        
        if (stripos($zoneName, 'lab') !== false || stripos($zoneName, 'research') !== false) {
            return ['icon' => 'science', 'color' => 'green'];
        }
        
        if (stripos($zoneName, 'security') !== false || stripos($zoneName, 'guard') !== false) {
            return ['icon' => 'shield', 'color' => 'red'];
        }
        
        if (stripos($zoneName, 'production') !== false || stripos($zoneName, 'manufacturing') !== false || stripos($zoneName, 'factory') !== false) {
            return ['icon' => 'precision_manufacturing', 'color' => 'orange'];
        }
        
        // Default icon
        return ['icon' => 'location_on', 'color' => 'blue'];
    }

    public function add()
    {
        $zoneModel = new \App\Models\ZoneModel();
        $zones = $zoneModel->where('status', 'active')->findAll();
        
        // Add icon and color data to zones
        $formattedZones = [];
        foreach ($zones as $zone) {
            $iconData = $this->getIconFromZoneName($zone['zone_name']);
            $formattedZones[] = array_merge($zone, [
                'icon' => $iconData['icon'],
                'icon_color' => $iconData['color']
            ]);
        }
        
        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->getActiveDepartments();
        
        $jobPositionModel = new \App\Models\JobPositionModel();
        $positions = $jobPositionModel->getActivePositions();
        
        $countryModel = new \App\Models\CountryModel();
        $countries = $countryModel->getActiveCountries();
        
        $stateModel = new \App\Models\StateModel();
        $states = $stateModel->getActiveStates();
        
        $cityModel = new \App\Models\CityModel();
        $cities = $cityModel->getActiveCities();
        
        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        
        $data = [
            'title' => 'Register New Worker',
            'user' => $this->getLoggedInUser(),
            'zones' => $formattedZones,
            'departments' => $departments,
            'positions' => $positions,
            'countries' => $countries,
            'states' => $states,
            'cities' => $cities,
            'shifts' => $shifts
        ];

        return view('workers/add', $data);
    }

    public function store()
    {
        $workerModel = new \App\Models\WorkerModel();
        
        // Get zones from hidden inputs (populated by JavaScript)
        $assignedZones = $this->request->getPost('assigned_zones');
        $zonesArray = [];
        if (!empty($assignedZones)) {
            $zonesArray = explode(',', $assignedZones);
        }
        
        // Prepare data
        $data = [
            'worker_id'      => $this->request->getPost('worker_id'),
            'ic_number'      => $this->request->getPost('ic_number'),
            'first_name'     => $this->request->getPost('first_name'),
            'last_name'      => $this->request->getPost('last_name'),
            'email'          => $this->request->getPost('email'),
            'phone'          => $this->request->getPost('phone'),
            'address'        => $this->request->getPost('address'),
            'country_id'     => $this->request->getPost('country_id') ?: null,
            'state_id'       => $this->request->getPost('state_id') ?: null,
            'city_id'        => $this->request->getPost('city_id') ?: null,
            'department'     => $this->request->getPost('department'),
            'position'       => $this->request->getPost('position'),
            'start_date'     => $this->request->getPost('start_date'),
            'shift'          => $this->request->getPost('shift'),
            'status'         => $this->request->getPost('status') ?: 'active',
            'assigned_zones' => !empty($zonesArray) ? json_encode($zonesArray) : null,
            'last_active'    => date('Y-m-d H:i:s')
        ];
        
        // Handle profile photo upload
        $profilePhoto = $this->request->getFile('profile_photo');
        if ($profilePhoto && $profilePhoto->isValid() && !$profilePhoto->hasMoved()) {
            $newName = $profilePhoto->getRandomName();
            $profilePhoto->move(FCPATH . 'uploads/profiles', $newName);
            $data['profile_photo'] = $newName;
        }
        
        // Handle multiple document uploads
        $documents = $this->request->getFileMultiple('documents');
        $documentNames = [];
        if ($documents) {
            foreach ($documents as $document) {
                if ($document->isValid() && !$document->hasMoved()) {
                    $docName = $document->getRandomName();
                    $document->move(FCPATH . 'uploads/documents', $docName);
                    $documentNames[] = $docName;
                }
            }
        }
        if (!empty($documentNames)) {
            $data['documents'] = json_encode($documentNames);
        }
        
        // Validate and save
        if ($workerModel->save($data)) {
            return redirect()->to(base_url('workers/list'))->with('success', 'Worker registered successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to register worker: ' . implode(', ', $workerModel->errors()));
        }
    }

    public function edit($workerId)
    {
        $workerModel = new \App\Models\WorkerModel();
        $zoneModel = new \App\Models\ZoneModel();
        
        $worker = $workerModel->find($workerId);
        
        if (!$worker) {
            return redirect()->to(base_url('workers/list'))->with('error', 'Worker not found');
        }
        
        // Decode assigned zones
        $assignedZones = [];
        if (!empty($worker['assigned_zones'])) {
            $assignedZones = json_decode($worker['assigned_zones'], true) ?: [];
        }
        
        $zones = $zoneModel->where('status', 'active')->findAll();
        
        // Add icon and color data to zones
        $formattedZones = [];
        foreach ($zones as $zone) {
            $iconData = $this->getIconFromZoneName($zone['zone_name']);
            $formattedZones[] = array_merge($zone, [
                'icon' => $iconData['icon'],
                'icon_color' => $iconData['color']
            ]);
        }
        
        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->getActiveDepartments();
        
        $jobPositionModel = new \App\Models\JobPositionModel();
        $positions = $jobPositionModel->getActivePositions();
        
        $countryModel = new \App\Models\CountryModel();
        $countries = $countryModel->getActiveCountries();
        
        $stateModel = new \App\Models\StateModel();
        $states = $stateModel->getActiveStates();
        
        $cityModel = new \App\Models\CityModel();
        $cities = $cityModel->getActiveCities();
        
        $shiftModel = new \App\Models\ShiftModel();
        $shifts = $shiftModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        
        $assetModel = new \App\Models\AssetModel();
        $workerAssets = $assetModel->getAssetsByWorker($worker['worker_id']);
        
        $data = [
            'title' => 'Edit Worker',
            'user' => $this->getLoggedInUser(),
            'worker' => $worker,
            'zones' => $formattedZones,
            'assignedZones' => $assignedZones,
            'departments' => $departments,
            'positions' => $positions,
            'countries' => $countries,
            'states' => $states,
            'cities' => $cities,
            'shifts' => $shifts,
            'workerAssets' => $workerAssets
        ];
        
        return view('workers/edit', $data);
    }

    public function update($workerId)
    {
        $workerModel = new \App\Models\WorkerModel();
        
        $worker = $workerModel->find($workerId);
        if (!$worker) {
            return redirect()->to(base_url('workers/list'))->with('error', 'Worker not found');
        }
        
        // Get zones from hidden inputs
        $assignedZones = $this->request->getPost('assigned_zones');
        $zonesArray = [];
        if (!empty($assignedZones)) {
            $zonesArray = explode(',', $assignedZones);
        }
        
        // Prepare data
        $data = [
            'ic_number'      => $this->request->getPost('ic_number'),
            'first_name'     => $this->request->getPost('first_name'),
            'last_name'      => $this->request->getPost('last_name'),
            'email'          => $this->request->getPost('email'),
            'phone'          => $this->request->getPost('phone'),
            'address'        => $this->request->getPost('address'),
            'country_id'     => $this->request->getPost('country_id') ?: null,
            'state_id'       => $this->request->getPost('state_id') ?: null,
            'city_id'        => $this->request->getPost('city_id') ?: null,
            'department'     => $this->request->getPost('department'),
            'position'       => $this->request->getPost('position'),
            'start_date'     => $this->request->getPost('start_date'),
            'shift'          => $this->request->getPost('shift'),
            'status'         => $this->request->getPost('status') ?: 'active',
            'assigned_zones' => !empty($zonesArray) ? json_encode($zonesArray) : null,
        ];
        
        // Handle RFID tag ID
        $rfidTagId = trim($this->request->getPost('rfid_tag_id') ?? '');
        if (!empty($rfidTagId)) {
            // Check if tag is already assigned to another worker
            if ($workerModel->isRfidTagRegistered($rfidTagId, $workerId)) {
                return redirect()->back()->withInput()->with('error', 'This RFID tag is already assigned to another worker');
            }
            $data['rfid_tag_id'] = strtoupper($rfidTagId);
        } else {
            $data['rfid_tag_id'] = null;
        }
        
        // Set custom validation rules for update (exclude current worker from unique checks)
        $workerModel->setValidationRules([
            'first_name'  => 'required|max_length[100]',
            'last_name'   => 'required|max_length[100]',
            'email'       => "required|valid_email|is_unique[workers.email,worker_id,{$workerId}]",
            'department'  => 'required|max_length[100]',
            'position'    => 'required|max_length[100]',
            'start_date'  => 'required|valid_date',
            'shift'       => 'required|max_length[100]',
            'status'      => 'in_list[active,inactive,on_break,offline]',
        ]);
        
        // Handle profile photo upload
        $profilePhoto = $this->request->getFile('profile_photo');
        if ($profilePhoto && $profilePhoto->isValid() && !$profilePhoto->hasMoved()) {
            // Delete old photo if exists
            if (!empty($worker['profile_photo']) && file_exists(FCPATH . 'uploads/profiles/' . $worker['profile_photo'])) {
                unlink(FCPATH . 'uploads/profiles/' . $worker['profile_photo']);
            }
            
            $newName = $profilePhoto->getRandomName();
            $profilePhoto->move(FCPATH . 'uploads/profiles', $newName);
            $data['profile_photo'] = $newName;
        }
        
        // Handle multiple document uploads
        $documents = $this->request->getFileMultiple('documents');
        $documentNames = [];
        
        // Get existing documents that should be kept (from hidden field)
        $existingDocsJson = $this->request->getPost('existing_documents');
        if (!empty($existingDocsJson)) {
            $existingDocs = json_decode($existingDocsJson, true);
            if (is_array($existingDocs)) {
                $documentNames = $existingDocs;
            }
        }
        
        // Delete removed documents from server
        if (!empty($worker['documents'])) {
            $oldDocs = json_decode($worker['documents'], true) ?: [];
            foreach ($oldDocs as $oldDoc) {
                if (!in_array($oldDoc, $documentNames)) {
                    // Document was removed, delete from server
                    $filePath = FCPATH . 'uploads/documents/' . $oldDoc;
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                        log_message('info', 'Deleted document: ' . $filePath);
                    }
                }
            }
        }
        
        // Add new documents
        if ($documents) {
            foreach ($documents as $document) {
                if ($document->isValid() && !$document->hasMoved()) {
                    $docName = $document->getRandomName();
                    $document->move(FCPATH . 'uploads/documents', $docName);
                    $documentNames[] = $docName;
                }
            }
        }
        
        $data['documents'] = !empty($documentNames) ? json_encode($documentNames) : null;
        
        // Update worker
        if ($workerModel->update($workerId, $data)) {
            return redirect()->to(base_url('workers/list'))->with('success', 'Worker updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update worker: ' . implode(', ', $workerModel->errors()));
        }
    }

    public function view($workerId)
    {
        $workerModel = new \App\Models\WorkerModel();
        $zoneModel = new \App\Models\ZoneModel();
        $antennaModeModel = new \App\Models\AntennaModeModel();
        $countryModel = new \App\Models\CountryModel();
        $stateModel = new \App\Models\StateModel();
        $cityModel = new \App\Models\CityModel();
        $publicHolidayModel = new \App\Models\PublicHolidayModel();
        $attendanceRecordModel = new \App\Models\AttendanceRecordModel();
        $leaveReasonModel = new \App\Models\LeaveReasonModel();
        $workerLeaveModel = new \App\Models\WorkerLeaveRecordModel();
        
        $worker = $workerModel->find($workerId);
        
        if (!$worker) {
            return redirect()->to(base_url('workers/list'))->with('error', 'Worker not found');
        }
        
        // Get current month/year for calendar
        $currentMonth = $this->request->getGet('month') ?? date('n');
        $currentYear = $this->request->getGet('year') ?? date('Y');
        $currentDay = $this->request->getGet('day') ?? date('d');
        $view = $this->request->getGet('view') ?? 'month';
        
        // Determine date range based on view
        if ($view === 'day') {
            $firstDay = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $currentDay);
            $lastDay = $firstDay;
        } elseif ($view === 'week') {
            $currentDate = mktime(0, 0, 0, $currentMonth, $currentDay, $currentYear);
            $weekStart = strtotime('last sunday', strtotime('+1 day', $currentDate));
            if (date('w', $currentDate) == 0) {
                $weekStart = $currentDate;
            }
            $weekEnd = strtotime('+6 days', $weekStart);
            $firstDay = date('Y-m-d', $weekStart);
            $lastDay = date('Y-m-d', $weekEnd);
        } else {
            $firstDay = sprintf('%04d-%02d-01', $currentYear, $currentMonth);
            $lastDay = date('Y-m-t', strtotime($firstDay));
        }
        
        $publicHolidays = $publicHolidayModel
            ->where('holiday_date >=', $firstDay)
            ->where('holiday_date <=', $lastDay)
            ->where('is_active', 1)
            ->findAll();
        
        // Get leave records for the worker in the date range
        $leaveRecords = $workerLeaveModel->getWorkerLeaveRecords($workerId, $firstDay, $lastDay);
        
        // Get attendance records for the current month
        $attendanceRecords = [];
        $attendanceData = $attendanceRecordModel
            ->where('worker_id', $workerId)
            ->where('DATE(check_in_time) >=', $firstDay)
            ->where('DATE(check_in_time) <=', $lastDay)
            ->findAll();
        
        // Create a map of public holidays for easy lookup
        $holidayDates = [];
        foreach ($publicHolidays as $holiday) {
            $holidayDates[] = $holiday['holiday_date'];
        }
        
        // Mark attendance records
        foreach ($attendanceData as $record) {
            $date = date('Y-m-d', strtotime($record['check_in_time']));
            $attendanceRecords[$date] = 'PRESENT';
        }
        
        // Mark leave records
        foreach ($leaveRecords as $leave) {
            $attendanceRecords[$leave['leave_date']] = strtoupper($leave['reason_type']);
        }
        
        // Mark working days without attendance as ABSENT
        // Only for active workers and dates after their start date
        if ($worker['status'] !== 'inactive') {
            $start = new \DateTime($firstDay);
            $end = new \DateTime($lastDay);
            $end->modify('+1 day');
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, $end);
            
            // Get worker start date
            $workerStartDate = $worker['start_date'] ?? null;
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $dayOfWeek = $date->format('w');
                
                // Skip if already has attendance record
                if (isset($attendanceRecords[$dateStr])) {
                    continue;
                }
                
                // Skip dates before worker's start date
                if ($workerStartDate && $dateStr < $workerStartDate) {
                    continue;
                }
                
                // Skip weekends (Saturday = 6, Sunday = 0)
                if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                    continue;
                }
                
                // Skip public holidays
                if (in_array($dateStr, $holidayDates)) {
                    continue;
                }
                
                // Skip future dates
                if ($dateStr > date('Y-m-d')) {
                    continue;
                }
                
                // Mark as ABSENT
                $attendanceRecords[$dateStr] = 'ABSENT';
            }
        }
        
        // Calculate attendance stats for the month
        $attendanceStats = [
            'present' => count(array_filter($attendanceRecords, function($status) { return $status === 'PRESENT'; })),
            'paid_leave' => count(array_filter($attendanceRecords, function($status) { return $status === 'PAID LEAVE'; })),
            'medical_leave' => count(array_filter($attendanceRecords, function($status) { return $status === 'MEDICAL LEAVE'; })),
            'absent' => count(array_filter($attendanceRecords, function($status) { return $status === 'ABSENT'; })),
        ];
        
        // Get location details
        if (!empty($worker['country_id'])) {
            $country = $countryModel->find($worker['country_id']);
            $worker['country_name'] = $country['name'] ?? null;
        }
        
        if (!empty($worker['state_id'])) {
            $state = $stateModel->find($worker['state_id']);
            $worker['state_name'] = $state['name'] ?? null;
        }
        
        if (!empty($worker['city_id'])) {
            $city = $cityModel->find($worker['city_id']);
            $worker['city_name'] = $city['name'] ?? null;
        }
        
        // Get all antenna modes for color mapping
        $antennaModes = $antennaModeModel->findAll();
        $modeColorMap = [];
        foreach ($antennaModes as $mode) {
            $modeColorMap[$mode['mode_name']] = $mode['color'] ?? 'purple';
        }
        
        // Decode assigned zones
        $assignedZones = [];
        $assignedZoneDetails = [];
        if (!empty($worker['assigned_zones'])) {
            $assignedZones = json_decode($worker['assigned_zones'], true) ?: [];
            
            // Get zone details with icons
            foreach ($assignedZones as $zoneId) {
                $zone = $zoneModel->where('zone_id', $zoneId)->first();
                if ($zone) {
                    $iconData = $this->getIconFromZoneName($zone['zone_name']);
                    $antennaColor = $modeColorMap[$zone['antenna_mode']] ?? 'purple';
                    $assignedZoneDetails[] = array_merge($zone, [
                        'icon' => $iconData['icon'],
                        'icon_color' => $iconData['color'],
                        'antenna_color' => $antennaColor
                    ]);
                }
            }
        }
        
        // Check if the worker being viewed is in HR department
        // If worker is in HR, anyone can mark leave for them
        $isHRWorker = (stripos($worker['department'], 'human resource') !== false);
        
        // Get active leave reasons if this is an HR worker
        $leaveReasons = [];
        if ($isHRWorker) {
            $leaveReasons = $leaveReasonModel->getActiveReasons();
        }
        
        $currentUser = $this->getLoggedInUser();
        
        $data = [
            'title' => 'View Worker',
            'user' => $currentUser,
            'worker' => $worker,
            'assignedZones' => $assignedZoneDetails,
            'publicHolidays' => $publicHolidays,
            'attendanceRecords' => $attendanceRecords,
            'attendanceStats' => $attendanceStats,
            'isHR' => $isHRWorker,
            'leaveReasons' => $leaveReasons,
            'leaveRecords' => $leaveRecords
        ];
        
        return view('workers/view', $data);
    }

    public function delete($workerId)
    {
        $workerModel = new \App\Models\WorkerModel();
        
        $worker = $workerModel->find($workerId);
        if (!$worker) {
            return redirect()->to(base_url('workers/list'))->with('error', 'Worker not found');
        }
        
        // Delete profile photo if exists
        if (!empty($worker['profile_photo']) && file_exists(FCPATH . 'uploads/profiles/' . $worker['profile_photo'])) {
            unlink(FCPATH . 'uploads/profiles/' . $worker['profile_photo']);
        }
        
        // Delete worker
        if ($workerModel->delete($workerId)) {
            return redirect()->to(base_url('workers/list'))->with('success', 'Worker deleted successfully!');
        } else {
            return redirect()->to(base_url('workers/list'))->with('error', 'Failed to delete worker');
        }
    }
    
    public function batchUpload()
    {
        // Set JSON response header
        header('Content-Type: application/json');
        
        $workerModel = new \App\Models\WorkerModel();
        
        // Validate file upload
        $file = $this->request->getFile('csv_file');
        
        if (!$file || !$file->isValid()) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or file is invalid']);
            return;
        }
        
        if ($file->getExtension() !== 'csv') {
            echo json_encode(['success' => false, 'message' => 'Only CSV files are allowed']);
            return;
        }
        
        // Read CSV file
        $csvData = array_map('str_getcsv', file($file->getTempName()));
        
        if (empty($csvData)) {
            echo json_encode(['success' => false, 'message' => 'CSV file is empty']);
            return;
        }
        
        // Get headers
        $headers = array_shift($csvData);
        
        // Validate headers
        $requiredHeaders = ['full_name', 'email', 'worker_id', 'department', 'position', 'phone', 'shift', 'status'];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        
        if (!empty($missingHeaders)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Missing required columns: ' . implode(', ', $missingHeaders)
            ]);
            return;
        }
        
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        
        // Process each row
        foreach ($csvData as $index => $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Map CSV columns to array
            $data = array_combine($headers, $row);
            
            // Validate required fields
            if (empty($data['full_name']) || empty($data['email']) || empty($data['worker_id'])) {
                $errors[] = "Row " . ($index + 2) . ": Missing required fields";
                $skipped++;
                continue;
            }
            
            // Check if worker already exists
            $existing = $workerModel->where('worker_id', $data['worker_id'])->first();
            if ($existing) {
                $errors[] = "Row " . ($index + 2) . ": Worker ID {$data['worker_id']} already exists";
                $skipped++;
                continue;
            }
            
            // Check email uniqueness
            $existingEmail = $workerModel->where('email', $data['email'])->first();
            if ($existingEmail) {
                $errors[] = "Row " . ($index + 2) . ": Email {$data['email']} already exists";
                $skipped++;
                continue;
            }
            
            // Prepare data for insertion
            $workerData = [
                'full_name' => trim($data['full_name']),
                'email' => trim($data['email']),
                'worker_id' => trim($data['worker_id']),
                'department' => !empty($data['department']) ? trim($data['department']) : 'operations',
                'position' => !empty($data['position']) ? trim($data['position']) : 'worker',
                'phone' => !empty($data['phone']) ? trim($data['phone']) : '',
                'shift' => !empty($data['shift']) ? trim($data['shift']) : 'day',
                'status' => !empty($data['status']) ? trim($data['status']) : 'active',
                'password' => password_hash('password123', PASSWORD_DEFAULT), // Default password
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Calculate initials
            $nameParts = explode(' ', $workerData['full_name']);
            $workerData['initials'] = strtoupper(
                (isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : '') .
                (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : '')
            );
            
            // Insert worker
            try {
                if ($workerModel->insert($workerData)) {
                    $inserted++;
                } else {
                    $errors[] = "Row " . ($index + 2) . ": Failed to insert worker";
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                $skipped++;
            }
        }
        
        echo json_encode([
            'success' => true,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    }
    
    /**
     * Update RFID tag for a worker
     * AJAX endpoint
     */
    public function updateRfidTag()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        $data = $this->request->getJSON(true);
        $workerId = $data['worker_id'] ?? null;
        $rfidTagId = trim($data['rfid_tag_id'] ?? '');
        
        if (!$workerId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Worker ID is required']);
        }
        
        if (empty($rfidTagId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'RFID Tag ID is required']);
        }
        
        // Validate tag ID length
        if (strlen($rfidTagId) < 4) {
            return $this->response->setJSON(['success' => false, 'message' => 'RFID Tag ID must be at least 4 characters']);
        }
        
        $workerModel = new \App\Models\WorkerModel();
        
        // Check if worker exists
        $worker = $workerModel->find($workerId);
        if (!$worker) {
            return $this->response->setJSON(['success' => false, 'message' => 'Worker not found']);
        }
        
        // Check if tag is already assigned to another worker
        if ($workerModel->isRfidTagRegistered($rfidTagId, $workerId)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'This RFID tag is already assigned to another worker'
            ]);
        }
        
        // Update worker with RFID tag
        $updated = $workerModel->update($workerId, ['rfid_tag_id' => strtoupper($rfidTagId)]);
        
        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'RFID card assigned successfully!'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update worker. Please try again.'
            ]);
        }
    }
    
    public function checkRfidCard()
    {
        $rfidTagId = trim($this->request->getGet('rfid_tag_id') ?? '');
        $currentWorkerId = $this->request->getGet('current_worker_id') ?? null;
        
        if (empty($rfidTagId)) {
            return $this->response->setJSON(['available' => true, 'message' => '']);
        }
        
        // Validate tag ID length
        if (strlen($rfidTagId) < 4) {
            return $this->response->setJSON([
                'available' => false, 
                'message' => 'RFID Tag ID must be at least 4 characters'
            ]);
        }
        
        $workerModel = new \App\Models\WorkerModel();
        
        // Check if tag is already assigned to another worker
        $existingWorker = $workerModel->where('rfid_tag_id', strtoupper($rfidTagId))->first();
        
        if ($existingWorker) {
            // If it's the same worker (editing), it's okay
            if ($currentWorkerId && $existingWorker['worker_id'] === $currentWorkerId) {
                return $this->response->setJSON([
                    'available' => true, 
                    'message' => 'Current card'
                ]);
            }
            
            return $this->response->setJSON([
                'available' => false, 
                'message' => 'This RFID card is already assigned to another worker'
            ]);
        }
        
        return $this->response->setJSON([
            'available' => true, 
            'message' => 'Card is available'
        ]);
    }
    
    // Leave Management Methods
    public function markLeave()
    {
        $session = session();
        $workerLeaveModel = new \App\Models\WorkerLeaveRecordModel();
        
        // Get JSON data from request body
        $json = $this->request->getJSON(true);
        
        $workerId = $json['worker_id'] ?? null;
        $leaveReasonId = $json['leave_reason_id'] ?? null;
        $leaveDate = $json['leave_date'] ?? null;
        $notes = $json['notes'] ?? '';
        
        // Check if leave already exists for this date
        if ($workerLeaveModel->leaveExistsForDate($workerId, $leaveDate)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Leave already marked for this date'
            ]);
        }
        
        $data = [
            'worker_id' => $workerId,
            'leave_reason_id' => $leaveReasonId,
            'leave_date' => $leaveDate,
            'notes' => $notes,
            'created_by' => $session->get('id')
        ];
        
        if ($workerLeaveModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Leave marked successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to mark leave: ' . implode(', ', $workerLeaveModel->errors())
            ]);
        }
    }

    public function shiftPreview()
{
    try {
        $staffShiftAllocationModel = new \App\Models\StaffShiftAllocationModel();
        $staffGroupModel = new \App\Models\StaffGroupModel();
        $workerModel = new \App\Models\WorkerModel();
        
        // Get date range for preview
        $year = $this->request->getGet('year') ?? date('Y');
        $month = $this->request->getGet('month') ?? date('m');
        
        // Calculate first and last day of the month
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $lastDay = date('t', strtotime($startDate));
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);
        
        // Get all staff groups
        $staffGroups = $staffGroupModel->where('is_active', 1)->findAll();
        
        // Get all shift allocations for the date range
        $allocations = $staffShiftAllocationModel
            ->where('allocation_date <=', $endDate)
            ->where('allocation_date >=', $startDate)
            ->orderBy('allocation_date', 'ASC')
            ->findAll();
        
        // Debug: Log what we found
        log_message('info', 'Found ' . count($allocations) . ' allocations for period ' . $startDate . ' to ' . $endDate);
        
        // Organize allocations by group and date
        $allocationMap = [];
        foreach ($allocations as $allocation) {
            $groupId = $allocation['group_id'];
            $date = $allocation['allocation_date'];
            
            if (!isset($allocationMap[$groupId])) {
                $allocationMap[$groupId] = [];
            }
            $allocationMap[$groupId][$date] = $allocation['shift_code'];
        }
        
        // Generate dates for the preview
        $dates = [];
        $currentDate = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        
        while ($currentDate <= $endTimestamp) {
            $dates[] = [
                'date' => date('Y-m-d', $currentDate),
                'day' => date('d', $currentDate),
                'day_name' => date('D', $currentDate),
                'month' => date('M', $currentDate)
            ];
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        // Build preview data
        $shiftPreview = [];
        
        // For each staff group that has allocations
        foreach ($allocationMap as $groupId => $groupAllocations) {
            // Find the group details
            $group = null;
            foreach ($staffGroups as $g) {
                if ($g['id'] == $groupId) {
                    $group = $g;
                    break;
                }
            }
            
            if (!$group) {
                continue;
            }
            
            // Get workers in this group
            $groupWorkers = $workerModel
                ->where('staff_group_id', $groupId)
                ->where('status', 'active')
                ->orderBy('first_name', 'ASC')
                ->findAll();
            
            // If no workers have this group_id, try to show the group anyway with placeholder
            if (empty($groupWorkers)) {
                // Create a placeholder row showing the group name
                $row = [
                    'worker_id' => $group['group_name'],
                    'name' => 'Group: ' . $group['group_name'],
                    'department' => 'All Departments',
                    'position' => 'Multiple Positions',
                    'days' => []
                ];
                
                // Fill in shift codes for each date
                foreach ($dates as $dateInfo) {
                    $date = $dateInfo['date'];
                    $shiftCode = $groupAllocations[$date] ?? 'OD';
                    $row['days'][] = $shiftCode;
                }
                
                $shiftPreview[] = $row;
            } else {
                // Add a row for each worker in the group
                foreach ($groupWorkers as $worker) {
                    $row = [
                        'worker_id' => $worker['worker_id'],
                        'name' => $worker['first_name'] . ' ' . $worker['last_name'],
                        'department' => $worker['department'] ?? 'N/A',
                        'position' => $worker['position'] ?? 'N/A',
                        'days' => []
                    ];
                    
                    // Fill in shift codes for each date
                    foreach ($dates as $dateInfo) {
                        $date = $dateInfo['date'];
                        $shiftCode = $groupAllocations[$date] ?? 'OD';
                        $row['days'][] = $shiftCode;
                    }
                    
                    $shiftPreview[] = $row;
                }
            }
        }
        
        // Get month name for display
        $monthName = date('F Y', strtotime($startDate));
        
        $data = [
            'title' => 'Shift Allocation Preview',
            'user' => $this->getLoggedInUser(),
            'shiftPreview' => $shiftPreview,
            'dates' => $dates,
            'monthName' => $monthName,
            'currentYear' => $year,
            'currentMonth' => $month,
            'totalAllocations' => count($allocations),
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        
        return view('workers/shift_preview', $data);
        
    } catch (\Exception $e) {
        log_message('error', 'Shift Preview Error: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        
        // Return empty preview on error with debug info
        $data = [
            'title' => 'Shift Allocation Preview',
            'user' => $this->getLoggedInUser(),
            'shiftPreview' => [],
            'dates' => [],
            'monthName' => date('F Y'),
            'currentYear' => date('Y'),
            'currentMonth' => date('m'),
            'error' => $e->getMessage()
        ];
        
        return view('workers/shift_preview', $data);
    }
}

public function shiftPreviewDebug()
{
    $staffShiftAllocationModel = new \App\Models\StaffShiftAllocationModel();
    $staffGroupModel = new \App\Models\StaffGroupModel();
    $workerModel = new \App\Models\WorkerModel();
    
    // Get date range
    $year = $this->request->getGet('year') ?? date('Y');
    $month = $this->request->getGet('month') ?? date('m');
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $lastDay = date('t', strtotime($startDate));
    $endDate = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);
    
    // Get all data
    $allGroups = $staffGroupModel->findAll();
    $allAllocations = $staffShiftAllocationModel
        ->where('allocation_date >=', $startDate)
        ->where('allocation_date <=', $endDate)
        ->findAll();
    $allWorkers = $workerModel->findAll();
    
    // Check workers table structure
    $db = \Config\Database::connect();
    $workersFields = $db->getFieldNames('workers');
    
    echo "<h1>Debug Information</h1>";
    echo "<h2>Date Range: {$startDate} to {$endDate}</h2>";
    
    echo "<h3>Staff Groups Found: " . count($allGroups) . "</h3>";
    echo "<pre>" . print_r($allGroups, true) . "</pre>";
    
    echo "<h3>Shift Allocations Found: " . count($allAllocations) . "</h3>";
    echo "<pre>" . print_r($allAllocations, true) . "</pre>";
    
    echo "<h3>Workers Table Structure:</h3>";
    echo "<pre>" . print_r($workersFields, true) . "</pre>";
    
    echo "<h3>Sample Worker (first 3):</h3>";
    echo "<pre>" . print_r(array_slice($allWorkers, 0, 3), true) . "</pre>";
    
    die();
}
    
    public function removeLeave()
    {
        $session = session();
        
        $workerLeaveModel = new \App\Models\WorkerLeaveRecordModel();
        
        // Get JSON data from request body
        $json = $this->request->getJSON(true);
        
        $workerId = $json['worker_id'] ?? null;
        $leaveDate = $json['leave_date'] ?? null;
        
        if ($workerLeaveModel->deleteByWorkerAndDate($workerId, $leaveDate)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Leave removed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to remove leave'
            ]);
        }
    }
    
    public function monitoring()
    {
        $attendanceModel = new \App\Models\AttendanceRecordModel();
        $workerModel = new \App\Models\WorkerModel();
        $zoneModel = new \App\Models\ZoneModel();
        $departmentModel = new \App\Models\DepartmentModel();
        
        // Get selected zone from session (use zone_id varchar for attendance queries)
        $selectedZoneId = session()->get('selected_zone'); // This is like "Z-1001"
        $selectedZoneName = session()->get('selected_zone_name');
        
        // Get today's date
        $today = date('Y-m-d');
        
        // Pagination
        $perPage = 10;
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $perPage;
        
        // Get check-in/out stats (filtered by selected zone) - use fresh model instances
        $checkedInQuery = (new \App\Models\AttendanceRecordModel())
            ->where('date', $today);
        
        if ($selectedZoneId) {
            $checkedInQuery->where('zone_id', $selectedZoneId);
        }
        
        $checkedInCount = $checkedInQuery->countAllResults();
            
        $checkedOutQuery = (new \App\Models\AttendanceRecordModel())
            ->where('date', $today)
            ->where('check_out_time IS NOT NULL');
        
        if ($selectedZoneId) {
            $checkedOutQuery->where('zone_id', $selectedZoneId);
        }
        
        $checkedOutCount = $checkedOutQuery->countAllResults();
        
        // Get total count for pagination (filtered by selected zone)
        $totalRecordsQuery = (new \App\Models\AttendanceRecordModel())
            ->where('date', $today);
        
        if ($selectedZoneId) {
            $totalRecordsQuery->where('zone_id', $selectedZoneId);
        }
        
        $totalRecords = $totalRecordsQuery->countAllResults();
        
        // Get recent attendance records (paginated and filtered by selected zone)
        // Order by most recent activity (check-out time if exists, otherwise check-in time)
        $recentActivityQuery = (new \App\Models\AttendanceRecordModel())
            ->select('attendance_records.*, CONCAT(workers.first_name, " ", workers.last_name) as full_name, workers.worker_id, workers.profile_photo, workers.department, zones.zone_name, shifts.start_time as shift_start, shifts.end_time as shift_end')
            ->join('workers', 'workers.worker_id = attendance_records.worker_id')
            ->join('zones', 'zones.zone_id = attendance_records.zone_id', 'left')
            ->join('shifts', 'shifts.name = workers.shift', 'left')
            ->where('attendance_records.date', $today);
        
        if ($selectedZoneId) {
            $recentActivityQuery->where('attendance_records.zone_id', $selectedZoneId);
        }
        
        $recentActivity = $recentActivityQuery
            ->orderBy('COALESCE(attendance_records.check_out_time, attendance_records.check_in_time)', 'DESC', false)
            ->limit($perPage, $offset)
            ->findAll();
        
        // Format activity data
        $formattedActivity = [];
        $colors = ['blue', 'green', 'orange', 'pink', 'purple', 'yellow', 'cyan', 'red', 'indigo', 'teal'];
        $colorIndex = 0;
        
        foreach ($recentActivity as $record) {
            $initials = '';
            $nameParts = explode(' ', $record['full_name']);
            foreach ($nameParts as $part) {
                if (!empty($part)) {
                    $initials .= strtoupper($part[0]);
                }
            }
            
            // Determine check-in status (green if on time, red if late)
            $checkInStatus = 'green'; // default
            if (!empty($record['shift_start']) && !empty($record['check_in_time'])) {
                $shiftStart = strtotime($record['shift_start']);
                $checkIn = strtotime(date('H:i:s', strtotime($record['check_in_time'])));
                // Late if check-in is more than 5 minutes after shift start
                if ($checkIn > ($shiftStart + 300)) {
                    $checkInStatus = 'red';
                }
            }
            
            // Determine check-out status (green if after shift end, red if before)
            $checkOutStatus = 'gray'; // default when no shift data
            if (!empty($record['shift_start']) && !empty($record['shift_end']) && !empty($record['check_in_time']) && !empty($record['check_out_time'])) {
                $shiftStart = strtotime($record['shift_start']);
                $shiftEnd = strtotime($record['shift_end']);
                $checkInTime = strtotime($record['check_in_time']);
                $checkOutTime = strtotime($record['check_out_time']);
                
                // Extract just the time portion for comparison
                $checkInHMS = date('H:i:s', $checkInTime);
                $checkOutHMS = date('H:i:s', $checkOutTime);
                
                $checkInTimestamp = strtotime($checkInHMS);
                $checkOutTimestamp = strtotime($checkOutHMS);
                
                // Check if this is an overnight shift (end time < start time)
                $isOvernightShift = $shiftEnd < $shiftStart;
                
                if ($isOvernightShift) {
                    // For overnight shifts (e.g., 18:00-05:00):
                    // Worker MUST cross midnight to complete shift properly
                    // If checkout time < checkin time, they crossed midnight
                    $crossedMidnight = $checkOutTimestamp < $checkInTimestamp;
                    
                    if ($crossedMidnight) {
                        // They worked past midnight - always green for overnight workers
                        $checkOutStatus = 'green';
                    } else {
                        // Did NOT cross midnight - left same day as check-in = left early
                        $checkOutStatus = 'red';
                    }
                } else {
                    // Regular shift: check-out should be after shift end
                    if ($checkOutTimestamp >= $shiftEnd) {
                        $checkOutStatus = 'green'; // Left on time or late
                    } else {
                        $checkOutStatus = 'red'; // Left early
                    }
                }
            }
            
            $formattedActivity[] = [
                'id_number' => $record['worker_id'],
                'full_name' => $record['full_name'],
                'initials' => $initials,
                'color' => $colors[$colorIndex % count($colors)],
                'department' => $record['department'] ?? 'N/A',
                'zone' => $record['zone_name'] ?? 'N/A',
                'status' => empty($record['check_out_time']) ? 'IN' : 'OUT',
                'time_in' => date('H:i:s', strtotime($record['check_in_time'])),
                'time_out' => !empty($record['check_out_time']) ? date('H:i:s', strtotime($record['check_out_time'])) : '-',
                'is_latest' => $colorIndex === 0,
                'profile_photo' => $record['profile_photo'] ?? null,
                'check_in_status' => $checkInStatus,
                'check_out_status' => $checkOutStatus
            ];
            $colorIndex++;
        }
        
        // Get zone/reader stats
        $totalZones = $zoneModel->where('status', 'active')->countAllResults();
        $activeReaders = $totalZones; // Assuming all active zones have active readers
        
        // Calculate uptime (get earliest attendance record ever to show system operational time)
        $earliestRecord = (new \App\Models\AttendanceRecordModel())
            ->orderBy('check_in_time', 'ASC')
            ->first();
        
        $uptime = '0D 0H 0M';
        if ($earliestRecord) {
            $startTime = strtotime($earliestRecord['check_in_time']);
            $currentTime = time();
            $diff = $currentTime - $startTime;
            
            $days = floor($diff / 86400);
            $hours = floor(($diff % 86400) / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $uptime = $days . 'D ' . $hours . 'H ' . $minutes . 'M';
        }
        
        // Get all departments
        $departments = $departmentModel->findAll();
        
        // Calculate pagination
        $totalPages = ceil($totalRecords / $perPage);
        
        // Get assets tracked in the selected zone
        $assetModel = new \App\Models\AssetModel();
        $zoneAssets = [];
        if ($selectedZoneId) {
            $zoneAssets = $assetModel
                ->select('assets.*, workers.first_name, workers.last_name, workers.worker_id as w_id')
                ->join('workers', 'workers.worker_id = assets.assigned_worker_id', 'left')
                ->where('assets.last_seen_zone', $selectedZoneId)
                ->findAll();
        } else {
            $zoneAssets = $assetModel
                ->select('assets.*, workers.first_name, workers.last_name, workers.worker_id as w_id')
                ->join('workers', 'workers.worker_id = assets.assigned_worker_id', 'left')
                ->where('assets.status', 'assigned')
                ->where('assets.last_seen_zone IS NOT NULL')
                ->findAll();
        }
        
        $data = [
            'title' => 'RFID Worker Monitoring',
            'user' => $this->getLoggedInUser(),
            'checked_in' => $checkedInCount,
            'checked_out' => $checkedOutCount,
            'activity_logs' => $formattedActivity,
            'active_readers' => $activeReaders,
            'total_readers' => $totalZones,
            'last_updated' => date('H:i:s'),
            'uptime' => $uptime,
            'departments' => $departments,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'total_records' => $totalRecords,
            'selected_zone_id' => $selectedZoneId,
            'selected_zone_name' => $selectedZoneName,
            'zone_assets' => $zoneAssets
        ];
        
        return view('workers/monitoring', $data);
    }
    
    public function monitoringData()
    {
        $zoneModel = new \App\Models\ZoneModel();
        
        // Get selected zone from session (priority over request parameter, use zone_id varchar)
        $selectedZoneId = session()->get('selected_zone'); // This is like "Z-1001"
        
        // Get today's date
        $today = date('Y-m-d');
        
        // Get filters from request
        $zoneId = $this->request->getGet('zone_id') ?? $selectedZoneId;
        $department = $this->request->getGet('department');
        $status = $this->request->getGet('status');
        
        // Get check-in/out stats (use fresh model instances to avoid builder state issues)
        $checkedInQuery = (new \App\Models\AttendanceRecordModel())
            ->where('date', $today);
        
        if ($zoneId) {
            $checkedInQuery->where('zone_id', $zoneId);
        }
        
        if ($department) {
            $checkedInQuery->join('workers', 'workers.worker_id = attendance_records.worker_id');
            $checkedInQuery->where('workers.department', $department);
        }
        
        $checkedInCount = $checkedInQuery->countAllResults();
        
        $checkedOutQuery = (new \App\Models\AttendanceRecordModel())
            ->where('date', $today)
            ->where('check_out_time IS NOT NULL');
        
        if ($zoneId) {
            $checkedOutQuery->where('zone_id', $zoneId);
        }
        
        if ($department) {
            $checkedOutQuery->join('workers', 'workers.worker_id = attendance_records.worker_id');
            $checkedOutQuery->where('workers.department', $department);
        }
        
        $checkedOutCount = $checkedOutQuery->countAllResults();
        
        // Get total count for pagination (with filters applied)
        $totalCountQuery = (new \App\Models\AttendanceRecordModel())
            ->where('date', $today);
        
        if ($zoneId) {
            $totalCountQuery->where('zone_id', $zoneId);
        }
        
        if ($department) {
            $totalCountQuery->join('workers', 'workers.worker_id = attendance_records.worker_id', 'left');
            $totalCountQuery->where('workers.department', $department);
        }
        
        if ($status === 'IN') {
            $totalCountQuery->where('check_out_time IS NULL');
        } elseif ($status === 'OUT') {
            $totalCountQuery->where('check_out_time IS NOT NULL');
        }
        
        $totalRecords = $totalCountQuery->countAllResults();
        
        // Get recent attendance records (last 50 records today)
        $recentActivityQuery = (new \App\Models\AttendanceRecordModel())
            ->select('attendance_records.*, CONCAT(workers.first_name, " ", workers.last_name) as full_name, workers.worker_id, workers.profile_photo, workers.department, zones.zone_name, shifts.start_time as shift_start, shifts.end_time as shift_end')
            ->join('workers', 'workers.worker_id = attendance_records.worker_id')
            ->join('zones', 'zones.zone_id = attendance_records.zone_id', 'left')
            ->join('shifts', 'shifts.name = workers.shift', 'left')
            ->where('attendance_records.date', $today);
        
        if ($zoneId) {
            $recentActivityQuery->where('attendance_records.zone_id', $zoneId);
        }
        
        if ($department) {
            $recentActivityQuery->where('workers.department', $department);
        }
        
        if ($status === 'IN') {
            $recentActivityQuery->where('attendance_records.check_out_time IS NULL');
        } elseif ($status === 'OUT') {
            $recentActivityQuery->where('attendance_records.check_out_time IS NOT NULL');
        }
        
        $recentActivity = $recentActivityQuery
            ->orderBy('COALESCE(attendance_records.check_out_time, attendance_records.check_in_time)', 'DESC', false)
            ->limit(50)
            ->findAll();
        
        // Format activity data
        $formattedActivity = [];
        $colors = ['blue', 'green', 'orange', 'pink', 'purple', 'yellow', 'cyan', 'red', 'indigo', 'teal'];
        $colorIndex = 0;
        
        foreach ($recentActivity as $record) {
            $initials = '';
            $nameParts = explode(' ', $record['full_name']);
            foreach ($nameParts as $part) {
                if (!empty($part)) {
                    $initials .= strtoupper($part[0]);
                }
            }
            
            // Determine check-in status (green if on time, red if late)
            $checkInStatus = 'green'; // default
            if (!empty($record['shift_start']) && !empty($record['check_in_time'])) {
                $shiftStart = strtotime($record['shift_start']);
                $checkIn = strtotime(date('H:i:s', strtotime($record['check_in_time'])));
                // Late if check-in is more than 5 minutes after shift start
                if ($checkIn > ($shiftStart + 300)) {
                    $checkInStatus = 'red';
                }
            }
            
            // Determine check-out status (green if after shift end, red if before)
            $checkOutStatus = 'gray'; // default when no shift data
            if (!empty($record['shift_start']) && !empty($record['shift_end']) && !empty($record['check_in_time']) && !empty($record['check_out_time'])) {
                $shiftStart = strtotime($record['shift_start']);
                $shiftEnd = strtotime($record['shift_end']);
                $checkInTime = strtotime($record['check_in_time']);
                $checkOutTime = strtotime($record['check_out_time']);
                
                // Extract just the time portion for comparison
                $checkInHMS = date('H:i:s', $checkInTime);
                $checkOutHMS = date('H:i:s', $checkOutTime);
                
                $checkInTimestamp = strtotime($checkInHMS);
                $checkOutTimestamp = strtotime($checkOutHMS);
                
                // Check if this is an overnight shift (end time < start time)
                $isOvernightShift = $shiftEnd < $shiftStart;
                
                if ($isOvernightShift) {
                    // For overnight shifts (e.g., 18:00-05:00):
                    // Worker MUST cross midnight to complete shift properly
                    // If checkout time < checkin time, they crossed midnight
                    $crossedMidnight = $checkOutTimestamp < $checkInTimestamp;
                    
                    if ($crossedMidnight) {
                        // They worked past midnight - always green for overnight workers
                        $checkOutStatus = 'green';
                    } else {
                        // Did NOT cross midnight - left same day as check-in = left early
                        $checkOutStatus = 'red';
                    }
                } else {
                    // Regular shift: check-out should be after shift end
                    if ($checkOutTimestamp >= $shiftEnd) {
                        $checkOutStatus = 'green'; // Left on time or late
                    } else {
                        $checkOutStatus = 'red'; // Left early
                    }
                }
            }
            
            $formattedActivity[] = [
                'id_number' => $record['worker_id'],
                'full_name' => $record['full_name'],
                'initials' => $initials,
                'color' => $colors[$colorIndex % count($colors)],
                'department' => $record['department'] ?? 'N/A',
                'zone' => $record['zone_name'] ?? 'N/A',
                'status' => empty($record['check_out_time']) ? 'IN' : 'OUT',
                'time_in' => date('H:i:s', strtotime($record['check_in_time'])),
                'time_out' => !empty($record['check_out_time']) ? date('H:i:s', strtotime($record['check_out_time'])) : '-',
                'is_latest' => $colorIndex === 0,
                'profile_photo' => $record['profile_photo'] ?? null,
                'check_in_status' => $checkInStatus,
                'check_out_status' => $checkOutStatus
            ];
            $colorIndex++;
        }
        
        // Get zone/reader stats
        $totalZones = $zoneModel->where('status', 'active')->countAllResults();
        $activeReaders = $totalZones;
        
        // Calculate uptime (get earliest attendance record ever to show system operational time)
        $earliestRecord = (new \App\Models\AttendanceRecordModel())
            ->orderBy('check_in_time', 'ASC')
            ->first();
        
        $uptime = '0D 0H 0M';
        if ($earliestRecord) {
            $startTime = strtotime($earliestRecord['check_in_time']);
            $currentTime = time();
            $diff = $currentTime - $startTime;
            
            $days = floor($diff / 86400);
            $hours = floor(($diff % 86400) / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $uptime = $days . 'D ' . $hours . 'H ' . $minutes . 'M';
        }
        
        // Get assets in zone for live updates
        $assetModel = new \App\Models\AssetModel();
        $zoneAssets = [];
        if ($selectedZoneId) {
            $zoneAssets = $assetModel
                ->select('assets.*, workers.first_name, workers.last_name, workers.worker_id as w_id')
                ->join('workers', 'workers.worker_id = assets.assigned_worker_id', 'left')
                ->where('assets.last_seen_zone', $selectedZoneId)
                ->findAll();
        } else {
            $zoneAssets = $assetModel
                ->select('assets.*, workers.first_name, workers.last_name, workers.worker_id as w_id')
                ->join('workers', 'workers.worker_id = assets.assigned_worker_id', 'left')
                ->where('assets.status', 'assigned')
                ->where('assets.last_seen_zone IS NOT NULL')
                ->findAll();
        }
        
        return $this->response->setJSON([
            'success' => true,
            'checked_in' => $checkedInCount,
            'checked_out' => $checkedOutCount,
            'activity_logs' => $formattedActivity,
            'active_readers' => $activeReaders,
            'total_readers' => $totalZones,
            'last_updated' => date('H:i:s'),
            'uptime' => $uptime,
            'total_records' => $totalRecords,
            'zone_assets' => $zoneAssets
        ]);
    }

    /**
     * Store a new asset and optionally assign to a worker
     */
    public function storeAsset()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON(true);
        $assetModel = new \App\Models\AssetModel();

        $assetName = trim($data['asset_name'] ?? '');
        $epcNo = strtoupper(trim($data['epc_no'] ?? ''));
        $description = trim($data['description'] ?? '');
        $workerId = $data['worker_id'] ?? null;

        if (empty($assetName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Asset name is required']);
        }

        if (!empty($epcNo) && $assetModel->isEpcRegistered($epcNo)) {
            return $this->response->setJSON(['success' => false, 'message' => 'This EPC number is already registered to another asset']);
        }

        $insertData = [
            'asset_name'  => $assetName,
            'epc_no'      => $epcNo ?: null,
            'description' => $description ?: null,
            'status'      => 'available',
        ];

        if (!empty($workerId)) {
            $insertData['assigned_worker_id'] = $workerId;
            $insertData['assigned_at'] = date('Y-m-d H:i:s');
            $insertData['status'] = 'assigned';
        }

        $assetModel->insert($insertData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Asset created successfully' . (!empty($workerId) ? ' and assigned to worker' : ''),
            'asset_id' => $assetModel->getInsertID()
        ]);
    }

    /**
     * Assign an existing asset to a worker (set EPC no)
     */
    public function assignAsset()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON(true);
        $assetModel = new \App\Models\AssetModel();

        $workerId = $data['worker_id'] ?? null;
        $assetId = $data['asset_id'] ?? null;
        $assetName = trim($data['asset_name'] ?? '');
        $epcNo = strtoupper(trim($data['epc_no'] ?? ''));

        if (empty($workerId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Worker ID is required']);
        }

        if (empty($epcNo)) {
            return $this->response->setJSON(['success' => false, 'message' => 'EPC number is required']);
        }

        if (!empty($assetId)) {
            $asset = $assetModel->find($assetId);
            if (!$asset) {
                return $this->response->setJSON(['success' => false, 'message' => 'Asset not found']);
            }

            if (!empty($epcNo) && $assetModel->isEpcRegistered($epcNo, (int)$assetId)) {
                return $this->response->setJSON(['success' => false, 'message' => 'This EPC number is already registered to another asset']);
            }

            $assetModel->update($assetId, [
                'epc_no'             => $epcNo,
                'assigned_worker_id' => $workerId,
                'assigned_at'        => date('Y-m-d H:i:s'),
                'status'             => 'assigned',
            ]);
        } else {
            if (empty($assetName)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Asset name is required for new assets']);
            }

            if ($assetModel->isEpcRegistered($epcNo)) {
                return $this->response->setJSON(['success' => false, 'message' => 'This EPC number is already registered to another asset']);
            }

            $assetModel->insert([
                'asset_name'         => $assetName,
                'epc_no'             => $epcNo,
                'assigned_worker_id' => $workerId,
                'assigned_at'        => date('Y-m-d H:i:s'),
                'status'             => 'assigned',
            ]);
            $assetId = $assetModel->getInsertID();
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Asset assigned successfully with EPC: ' . $epcNo,
            'asset_id' => $assetId
        ]);
    }

    /**
     * Unassign asset from worker
     */
    public function unassignAsset()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON(true);
        $assetModel = new \App\Models\AssetModel();
        $assetId = $data['asset_id'] ?? null;

        if (empty($assetId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Asset ID is required']);
        }

        $assetModel->unassignFromWorker((int)$assetId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Asset unassigned successfully'
        ]);
    }

    /**
     * Update asset EPC number
     */
    public function updateAssetEpc()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON(true);
        $assetModel = new \App\Models\AssetModel();

        $assetId = $data['asset_id'] ?? null;
        $epcNo = strtoupper(trim($data['epc_no'] ?? ''));

        if (empty($assetId) || empty($epcNo)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Asset ID and EPC number are required']);
        }

        if ($assetModel->isEpcRegistered($epcNo, (int)$assetId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'This EPC number is already registered to another asset']);
        }

        $assetModel->update($assetId, ['epc_no' => $epcNo]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Asset EPC updated successfully'
        ]);
    }

    /**
     * Get all assets assigned to a specific worker
     */
    public function getWorkerAssets($workerId)
    {
        $assetModel = new \App\Models\AssetModel();
        $assets = $assetModel->getAssetsByWorker($workerId);

        return $this->response->setJSON([
            'success' => true,
            'assets'  => $assets
        ]);
    }

    /**
     * Get all assets detected in a specific zone
     */
    public function getAssetsInZone($zoneId)
    {
        $assetModel = new \App\Models\AssetModel();
        $assets = $assetModel->select('assets.*, workers.first_name, workers.last_name, workers.worker_id as w_id')
                             ->join('workers', 'workers.worker_id = assets.assigned_worker_id', 'left')
                             ->where('assets.last_seen_zone', $zoneId)
                             ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'assets'  => $assets
        ]);
    }

    /**
     * Asset list page (overview of all assets)
     */
    public function assetList()
    {
        $assetModel = new \App\Models\AssetModel();
        $assets = $assetModel->getAssetsWithWorkerInfo();

        $data = [
            'title'  => 'Asset Tracking',
            'assets' => $assets,
            'user'   => $this->getLoggedInUser(),
        ];

        return view('workers/assets', $data);
    }
}