<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $filterType = $this->request->getGet('filter') ?? 'today';
        $customDate = $this->request->getGet('date');
        $data = $this->buildDashboardData($filterType, $customDate);
        $data['title'] = 'Dashboard';
        $data['user']  = $this->getLoggedInUser();
        return view('dashboard/index', $data);
    }

    public function liveData()
    {
        $filterType = $this->request->getGet('filter') ?? 'today';
        $customDate = $this->request->getGet('date');
        return $this->response->setJSON($this->buildDashboardData($filterType, $customDate));
    }

    private function buildDashboardData(string $filterType, ?string $customDate): array
    {
        $workerModel      = new \App\Models\WorkerModel();
        $zoneModel        = new \App\Models\ZoneModel();
        $attendanceModel  = new \App\Models\AttendanceRecordModel();

        // Date filter
        $today       = date('Y-m-d');
        $startDate   = $today;
        $endDate     = $today;
        $filterLabel = 'Today';

        if ($customDate) {
            $startDate   = $endDate = $customDate;
            $filterLabel = date('M d, Y', strtotime($customDate));
            $filterType  = 'custom';
        } else {
            switch ($filterType) {
                case 'yesterday':
                    $startDate   = $endDate = date('Y-m-d', strtotime('-1 day'));
                    $filterLabel = 'Yesterday';
                    break;
                case 'week':
                    $startDate   = date('Y-m-d', strtotime('monday this week'));
                    $endDate     = date('Y-m-d', strtotime('sunday this week'));
                    $filterLabel = 'This Week';
                    break;
                case 'month':
                    $startDate   = date('Y-m-01');
                    $endDate     = date('Y-m-t');
                    $filterLabel = 'This Month';
                    break;
                default:
                    $startDate   = $endDate = $today;
                    $filterLabel = 'Today';
            }
        }

        $yesterday = date('Y-m-d', strtotime($startDate . ' -1 day'));

        // Zones
        $allZones   = $zoneModel->where('status', 'active')->findAll();
        $totalZones = count($allZones);

        // Attendance records
        if ($startDate === $endDate) {
            $todayRecords = $attendanceModel->getAttendanceByDate($startDate);
        } else {
            $todayRecords = $attendanceModel->getAttendanceByDateRange($startDate, $endDate);
        }
        $yesterdayRecords = $attendanceModel->getAttendanceByDate($yesterday);

        // Shift data
        $shiftModel    = new \App\Models\ShiftModel();
        $shifts        = $shiftModel->where('is_active', 1)->findAll();
        $shiftTimes    = [];
        $shiftEndTimes = [];
        foreach ($shifts as $shiftData) {
            $shiftTimes[$shiftData['name']]    = $shiftData['start_time'];
            $shiftEndTimes[$shiftData['name']] = $shiftData['end_time'] ?? '18:00:00';
        }

        // Total check-ins & change vs yesterday
        $totalCheckinsToday     = count($todayRecords);
        $totalCheckinsYesterday = count($yesterdayRecords);
        $checkinsChange         = '';
        $checkinsChangeColor    = 'gray';
        if ($totalCheckinsYesterday > 0) {
            $change = (($totalCheckinsToday - $totalCheckinsYesterday) / $totalCheckinsYesterday) * 100;
            if ($change != 0) {
                $checkinsChange      = ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
                $checkinsChangeColor = $change >= 0 ? 'green' : 'red';
            }
        }

        // Avg time in zone
        $totalMinutes    = 0;
        $completedVisits = 0;
        foreach ($todayRecords as $record) {
            if ($record['check_in_time'] && $record['check_out_time']) {
                $totalMinutes += (strtotime($record['check_out_time']) - strtotime($record['check_in_time'])) / 60;
                $completedVisits++;
            }
        }
        $avgTimeInZone    = $completedVisits > 0 ? round($totalMinutes / $completedVisits) : 0;
        $avgTimeFormatted = $this->formatMinutes($avgTimeInZone);

        $totalMinutesYesterday    = 0;
        $completedVisitsYesterday = 0;
        foreach ($yesterdayRecords as $record) {
            if ($record['check_in_time'] && $record['check_out_time']) {
                $totalMinutesYesterday += (strtotime($record['check_out_time']) - strtotime($record['check_in_time'])) / 60;
                $completedVisitsYesterday++;
            }
        }
        $avgTimeInZoneYesterday = $completedVisitsYesterday > 0 ? round($totalMinutesYesterday / $completedVisitsYesterday) : 0;
        $avgTimeChange          = '';
        $avgTimeChangeColor     = 'gray';
        if ($avgTimeInZoneYesterday > 0) {
            $change = (($avgTimeInZone - $avgTimeInZoneYesterday) / $avgTimeInZoneYesterday) * 100;
            if ($change != 0) {
                $avgTimeChange      = ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
                $avgTimeChangeColor = $change >= 0 ? 'green' : 'red';
            }
        }

        // Workers
        $allWorkers = $workerModel->getAllWorkersFormatted();
        $workers    = array_filter($allWorkers, fn($w) => $w['status'] === 'active');
        $workerMap  = array_column($allWorkers, null, 'worker_id');

        // Absent / late counts
        $absentCount   = 0;
        $lateCount     = 0;
        $absentWorkers = [];
        $lateWorkers   = [];
        $currentTime   = date('H:i:s');
        foreach ($workers as $worker) {
            $shift              = $worker['shift'] ?? 'morning';
            $shiftStartTime     = $this->resolveShiftTime($shiftTimes, $shift, '06:00:00');
            $workerTodayRecords = array_filter($todayRecords, fn($r) =>
                $r['worker_id'] == $worker['worker_id'] && $r['date'] == $startDate
            );
            if (empty($workerTodayRecords)) {
                if ($startDate < $today || ($startDate === $today && $currentTime > $shiftStartTime)) {
                    $absentCount++;
                    $absentWorkers[] = [
                        'worker_id' => $worker['worker_id'],
                        'name'      => $worker['full_name'],
                        'dept'      => ucfirst($worker['department'] ?? ''),
                        'shift'     => ucfirst($shift),
                    ];
                }
            } else {
                $checkInTimes = array_column($workerTodayRecords, 'check_in_time');
                if (!empty($checkInTimes)) {
                    $firstCheckIn = min($checkInTimes);
                    if (date('H:i:s', strtotime($firstCheckIn)) > $shiftStartTime) {
                        $lateCount++;
                        $lateWorkers[] = [
                            'worker_id'   => $worker['worker_id'],
                            'name'        => $worker['full_name'],
                            'dept'        => ucfirst($worker['department'] ?? ''),
                            'time_in'     => date('H:i', strtotime($firstCheckIn)),
                            'shift_start' => date('H:i', strtotime($shiftStartTime)),
                        ];
                    }
                }
            }
        }

        // Worker activity log — grouped by (worker, date) so each day is its own row-group
        $workerActivity = [];
        foreach ($workers as $worker) {
            $workerRecords = array_filter($todayRecords, fn($r) => $r['worker_id'] == $worker['worker_id']);
            if (empty($workerRecords)) continue;

            // Group records by date
            $recordsByDate = [];
            foreach ($workerRecords as $record) {
                $recordsByDate[$record['date']][] = $record;
            }
            ksort($recordsByDate);

            $shift          = $worker['shift'] ?? 'morning';
            $shiftStartTime = $this->resolveShiftTime($shiftTimes, $shift, '06:00:00');

            foreach ($recordsByDate as $recordDate => $dateRecords) {
                $checkInTimes  = array_column($dateRecords, 'check_in_time');
                $checkOutTimes = array_filter(array_column($dateRecords, 'check_out_time'));
                $firstCheckIn  = !empty($checkInTimes) ? min($checkInTimes) : null;
                $lastCheckOut  = !empty($checkOutTimes) ? max($checkOutTimes) : null;
                $isLate = $firstCheckIn && (date('H:i:s', strtotime($firstCheckIn)) > $shiftStartTime);

                $zones = [];
                $latestActivityTs = 0;
                foreach ($dateRecords as $record) {
                    if ($record['check_in_time']) {
                        $latestActivityTs = max($latestActivityTs, strtotime($record['check_in_time']));
                    }
                    if ($record['check_out_time']) {
                        $latestActivityTs = max($latestActivityTs, strtotime($record['check_out_time']));
                    }

                    $zone = $zoneModel->where('zone_id', $record['zone_id'])->where('status', 'active')->first();
                    if ($zone) {
                        $durationSec = ($record['check_in_time'] && $record['check_out_time'])
                            ? strtotime($record['check_out_time']) - strtotime($record['check_in_time'])
                            : null;
                        $zones[] = [
                            'name'     => $zone['zone_name'],
                            'entry'    => $record['check_in_time'] ? date('H:i', strtotime($record['check_in_time'])) : '-',
                            'exit'     => $record['check_out_time'] ? date('H:i', strtotime($record['check_out_time'])) : 'Active',
                            'duration' => $durationSec !== null ? $this->formatDuration($durationSec) : 'In Progress',
                            'entry_ts' => $record['check_in_time'] ? strtotime($record['check_in_time']) : null,
                        ];
                    }
                }

                if (!empty($zones)) {
                    usort($zones, static fn($a, $b) => ($b['entry_ts'] ?? 0) <=> ($a['entry_ts'] ?? 0));

                    $shiftEnd   = $this->resolveShiftTime($shiftEndTimes, $shift, '18:00:00');
                    $niz        = $this->calculateNotInZone($dateRecords, $recordDate, $shiftStartTime, $shiftEnd);

                    $workerActivity[] = [
                        'worker_id'          => $worker['worker_id'],
                        'name'               => $worker['full_name'],
                        'department'         => ucfirst($worker['department'] ?? ''),
                        'id_tag'             => $worker['worker_id'],
                        'date'               => $recordDate,
                        'date_label'         => date('M d', strtotime($recordDate)),
                        'time_in'            => $firstCheckIn ? date('H:i', strtotime($firstCheckIn)) : '-',
                        'time_out'           => $lastCheckOut ? date('H:i', strtotime($lastCheckOut)) : '-',
                        'is_late'            => $isLate,
                        'not_in_zone'        => $niz['formatted'],
                        'not_in_zone_sec'    => $niz['seconds'],
                        'not_in_zone_live'   => $niz['live'],
                        'not_in_zone_until'  => $niz['until'],
                        'server_time'        => $niz['server_time'],
                        'has_active_zone'    => $niz['has_active'],
                        'zones'              => $zones,
                        'latest_activity_ts' => $latestActivityTs,
                    ];
                }
            }
        }

        usort($workerActivity, static fn($a, $b) => ($b['latest_activity_ts'] ?? 0) <=> ($a['latest_activity_ts'] ?? 0));

        // Live zone occupancy
        $liveOccupancy  = [];
        $totalInAnyArea = 0;
        foreach ($allZones as $zone) {
            $activeInZone = array_filter($todayRecords, fn($r) =>
                $r['zone_id'] == $zone['zone_id'] && $r['check_in_time'] && !$r['check_out_time']
            );
            $count = count($activeInZone);
            $totalInAnyArea += $count;
            $occupancyWorkers = [];
            foreach ($activeInZone as $r) {
                $wData = $workerMap[$r['worker_id']] ?? null;
                $occupancyWorkers[] = [
                    'worker_id' => $r['worker_id'],
                    'name'      => $wData ? $wData['full_name'] : $r['worker_id'],
                    'entry'     => $r['check_in_time'] ? date('H:i', strtotime($r['check_in_time'])) : '-',
                    'entry_ts'  => $r['check_in_time'] ? strtotime($r['check_in_time']) : null,
                ];
            }
            $liveOccupancy[] = ['name' => $zone['zone_name'], 'count' => $count, 'workers' => $occupancyWorkers];
        }

        // Avg time per zone
        $avgZoneTime = [];
        foreach ($allZones as $zone) {
            $zoneRecords = array_filter($todayRecords, fn($r) =>
                $r['zone_id'] == $zone['zone_id'] && $r['check_in_time'] && $r['check_out_time']
            );
            $totalTime = 0;
            foreach ($zoneRecords as $record) {
                $totalTime += (strtotime($record['check_out_time']) - strtotime($record['check_in_time'])) / 60;
            }
            $avgTime = count($zoneRecords) > 0 ? round($totalTime / count($zoneRecords)) : 0;
            $workerVisits = [];
            foreach ($zoneRecords as $r) {
                $wId = $r['worker_id'];
                if (!isset($workerVisits[$wId])) {
                    $wData = $workerMap[$wId] ?? null;
                    $workerVisits[$wId] = ['name' => $wData ? $wData['full_name'] : $wId, 'visits' => 0, 'total_sec' => 0];
                }
                $workerVisits[$wId]['visits']++;
                $workerVisits[$wId]['total_sec'] += strtotime($r['check_out_time']) - strtotime($r['check_in_time']);
            }
            $zoneWorkers = [];
            foreach ($workerVisits as $wId => $wv) {
                $avgSec = $wv['visits'] > 0 ? round($wv['total_sec'] / $wv['visits']) : 0;
                $zoneWorkers[] = ['worker_id' => $wId, 'name' => $wv['name'], 'visits' => $wv['visits'], 'avg_time' => $this->formatDuration($avgSec)];
            }
            $avgZoneTime[] = ['name' => $zone['zone_name'], 'time' => $avgTime > 0 ? $this->formatMinutes($avgTime) : '-', 'workers' => $zoneWorkers];
        }

        // Live Summary Panel
        $workerIdsCameIn = array_unique(array_column($todayRecords, 'worker_id'));
        $totalCameIn     = count($workerIdsCameIn);

        $totalLeft = 0;
        foreach ($workerIdsCameIn as $wId) {
            $wRecords  = array_filter($todayRecords, fn($r) => $r['worker_id'] === $wId);
            $hasActive = array_filter($wRecords, fn($r) => !$r['check_out_time']);
            if (empty($hasActive)) $totalLeft++;
        }

        $personTotalMinutes = [];
        foreach ($workerIdsCameIn as $wId) {
            $wRecords = array_filter($todayRecords, fn($r) => $r['worker_id'] === $wId);
            $inTimes  = array_filter(array_column($wRecords, 'check_in_time'));
            $outTimes = array_filter(array_column($wRecords, 'check_out_time'));
            if (!empty($inTimes) && !empty($outTimes)) {
                $personTotalMinutes[] = (strtotime(max($outTimes)) - strtotime(min($inTimes))) / 60;
            }
        }
        $avgPersonTime = !empty($personTotalMinutes)
            ? $this->formatMinutes(round(array_sum($personTotalMinutes) / count($personTotalMinutes)))
            : '-';

        $notInZoneMinutes = [];
        foreach ($workerIdsCameIn as $wId) {
            $wRecords = array_filter($todayRecords, fn($r) => $r['worker_id'] === $wId);
            if (empty($wRecords)) continue;

            $wObj = null;
            foreach ($workers as $w) { if ($w['worker_id'] === $wId) { $wObj = $w; break; } }
            $wShift       = $wObj['shift'] ?? 'morning';
            $wShiftStart  = $this->resolveShiftTime($shiftTimes, $wShift, '06:00:00');
            $wShiftEnd    = $this->resolveShiftTime($shiftEndTimes, $wShift, '18:00:00');
            $niz          = $this->calculateNotInZone(array_values($wRecords), $endDate, $wShiftStart, $wShiftEnd);
            $notInZoneMinutes[] = $niz['seconds'] / 60;
        }
        $avgNotInZone = !empty($notInZoneMinutes)
            ? $this->formatMinutes(round(array_sum($notInZoneMinutes) / count($notInZoneMinutes)))
            : '-';

        $over8hWorkers = [];
        foreach ($workerIdsCameIn as $wId) {
            $wRecords = array_filter($todayRecords, fn($r) => $r['worker_id'] === $wId);
            $inTimes  = array_filter(array_column($wRecords, 'check_in_time'));
            $refOut   = array_filter(array_column($wRecords, 'check_out_time'));
            $firstIn  = !empty($inTimes) ? min($inTimes) : null;
            $lastOut  = !empty($refOut) ? max($refOut) : date('Y-m-d H:i:s');
            if ($firstIn) {
                $totalSec = strtotime($lastOut) - strtotime($firstIn);
                if ($totalSec >= 8 * 3600) {
                    $wData = null;
                    foreach ($allWorkers as $aw) {
                        if ($aw['worker_id'] === $wId) { $wData = $aw; break; }
                    }
                    $over8hWorkers[] = [
                        'id'       => $wId,
                        'name'     => $wData ? $wData['full_name'] : $wId,
                        'duration' => $this->formatDuration($totalSec),
                        'still_in' => empty($refOut) || count(array_filter($wRecords, fn($r) => !$r['check_out_time'])) > 0,
                    ];
                }
            }
        }

        // Came-in / left / in-area worker lists for modals
        $cameInWorkers = [];
        $leftWorkers   = [];
        $inAreaWorkers = [];
        foreach ($workerIdsCameIn as $wId) {
            $wData     = $workerMap[$wId] ?? null;
            $wRecords  = array_filter($todayRecords, fn($r) => $r['worker_id'] === $wId);
            $inTimes   = array_filter(array_column($wRecords, 'check_in_time'));
            $outTimes  = array_filter(array_column($wRecords, 'check_out_time'));
            $hasActive = !empty(array_filter($wRecords, fn($r) => !$r['check_out_time']));
            $entry     = !empty($inTimes)  ? date('H:i', strtotime(min($inTimes)))  : '-';
            $exit      = !empty($outTimes) ? date('H:i', strtotime(max($outTimes))) : '-';
            $cameInWorkers[] = [
                'worker_id' => $wId,
                'name'      => $wData ? $wData['full_name'] : $wId,
                'dept'      => $wData ? ucfirst($wData['department'] ?? '') : '',
                'time_in'   => $entry,
            ];
            if ($hasActive) {
                $inAreaWorkers[] = ['worker_id' => $wId, 'name' => $wData ? $wData['full_name'] : $wId, 'dept' => $wData ? ucfirst($wData['department'] ?? '') : '', 'time_in' => $entry];
            } else {
                $leftWorkers[] = ['worker_id' => $wId, 'name' => $wData ? $wData['full_name'] : $wId, 'dept' => $wData ? ucfirst($wData['department'] ?? '') : '', 'time_out' => $exit];
            }
        }

        return [
            'stats' => [
                'total_zones'           => $totalZones,
                'total_checkins'        => $totalCheckinsToday,
                'checkins_change'       => $checkinsChange,
                'checkins_change_color' => $checkinsChangeColor,
                'avg_time_in_zone'      => $avgTimeFormatted,
                'avg_time_change'       => $avgTimeChange,
                'avg_time_change_color' => $avgTimeChangeColor,
                'absent_count'          => $absentCount,
                'late_count'            => $lateCount,
                'absent_workers'        => $absentWorkers,
                'late_workers'          => $lateWorkers,
            ],
            'summary' => [
                'total_in_area'      => $totalInAnyArea,
                'total_came_in'      => $totalCameIn,
                'total_left'         => $totalLeft,
                'avg_person_time'    => $avgPersonTime,
                'avg_not_in_zone'    => $avgNotInZone,
                'total_transactions' => $totalCheckinsToday,
                'over_8h'            => $over8hWorkers,
                'in_area_workers'    => $inAreaWorkers,
                'came_in_workers'    => $cameInWorkers,
                'left_workers'       => $leftWorkers,
            ],
            'worker_activity' => $workerActivity,
            'live_occupancy'  => $liveOccupancy,
            'avg_zone_time'   => $avgZoneTime,
            'filter_label'    => $filterLabel,
            'filter_type'     => $filterType,
            'custom_date'     => $customDate,
            'selected_date'   => $startDate,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
        ];
    }

    private function resolveShiftTime(array $shiftTimes, string $shift, string $default): string
    {
        if (isset($shiftTimes[$shift])) {
            return $shiftTimes[$shift];
        }
        $lower = strtolower($shift);
        if (isset($shiftTimes[$lower])) {
            return $shiftTimes[$lower];
        }
        $capital = ucfirst($lower);
        if (isset($shiftTimes[$capital])) {
            return $shiftTimes[$capital];
        }
        return $default;
    }

    /**
     * Not-in-zone = shift start → shift end (capped at now), minus time inside any zone.
     * Counter pauses while the worker is inside a zone (based on latest RFID event).
     */
    private function calculateNotInZone(array $records, string $recordDate, string $shiftStart, string $shiftEnd): array
    {
        $shiftStartTs = strtotime($recordDate . ' ' . $shiftStart);
        $shiftEndTs   = strtotime($recordDate . ' ' . $shiftEnd);
        $now          = time();
        $isInZone     = $this->isWorkerInZone($records);

        if ($now < $shiftStartTs) {
            return [
                'seconds'    => 0,
                'formatted'  => '0m',
                'live'       => false,
                'has_active' => $isInZone,
                'until'      => $shiftEndTs,
                'server_time'=> $now,
            ];
        }

        $windowEnd    = min($shiftEndTs, $now);
        $observed     = max(0, $windowEnd - $shiftStartTs);
        $inZone       = $this->calculateInZoneSeconds($records, $shiftStartTs, $windowEnd, $isInZone);
        $notInZoneSec = max(0, $observed - $inZone);
        $live         = !$isInZone && $now < $shiftEndTs;

        return [
            'seconds'     => $notInZoneSec,
            'formatted'   => $notInZoneSec > 0 ? $this->formatDuration($notInZoneSec) : '0m',
            'live'        => $live,
            'has_active'  => $isInZone,
            'until'       => $shiftEndTs,
            'server_time' => $now,
        ];
    }

    /** Latest RFID event decides in/out — avoids stale open records freezing the counter. */
    private function isWorkerInZone(array $records): bool
    {
        $lastTs   = 0;
        $lastIsIn = false;
        foreach ($records as $r) {
            if (!empty($r['check_in_time'])) {
                $ts = strtotime($r['check_in_time']);
                if ($ts >= $lastTs) {
                    $lastTs   = $ts;
                    $lastIsIn = true;
                }
            }
            if (!empty($r['check_out_time'])) {
                $ts = strtotime($r['check_out_time']);
                if ($ts >= $lastTs) {
                    $lastTs   = $ts;
                    $lastIsIn = false;
                }
            }
        }
        return $lastIsIn;
    }

    private function calculateInZoneSeconds(array $records, int $shiftStartTs, int $windowEnd, bool $isInZone): int
    {
        $latestOpenInTs = 0;
        if ($isInZone) {
            foreach ($records as $r) {
                if (!empty($r['check_in_time']) && empty($r['check_out_time'])) {
                    $latestOpenInTs = max($latestOpenInTs, strtotime($r['check_in_time']));
                }
            }
        }

        $inZone = 0;
        foreach ($records as $r) {
            if (empty($r['check_in_time'])) {
                continue;
            }
            $zIn = strtotime($r['check_in_time']);
            if ($zIn >= $windowEnd) {
                continue;
            }

            if (!empty($r['check_out_time'])) {
                $zOut = strtotime($r['check_out_time']);
            } elseif ($isInZone && strtotime($r['check_in_time']) === $latestOpenInTs) {
                $zOut = $windowEnd;
            } else {
                // Stale open record — do not extend to now
                $zOut = $zIn;
            }

            $inZone += max(0, min($zOut, $windowEnd) - max($zIn, $shiftStartTs));
        }

        return $inZone;
    }

    private function formatMinutes($minutes)
    {
        if ($minutes < 60) return round($minutes) . ' mins';
        $hours = floor($minutes / 60);
        $mins  = round($minutes % 60);
        return $mins == 0 ? $hours . 'h' : $hours . 'h ' . $mins . 'm';
    }

    private function formatDuration($seconds)
    {
        $minutes = round($seconds / 60);
        if ($minutes < 60) return $minutes . 'm';
        $hours = floor($minutes / 60);
        $mins  = $minutes % 60;
        return $mins == 0 ? $hours . 'h' : $hours . 'h ' . $mins . 'm';
    }
}
