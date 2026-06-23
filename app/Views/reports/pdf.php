<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>WorkWise Report - <?= esc($filter_label) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>"></style>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
            padding: 20px;
        }
        
        .header {
            background: #667eea;
            color: white;
            padding: 25px 20px;
            margin-bottom: 25px;
            text-align: center;
            border-radius: 5px;
        }
        
        .header h1 {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 11pt;
            margin-top: 5px;
        }
        
        .meta-info {
            text-align: center;
            margin-bottom: 25px;
            padding: 12px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .meta-info .period {
            font-size: 13pt;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .meta-info .generated {
            font-size: 9pt;
            color: #666;
        }
        
        .summary-row {
            width: 100%;
            margin-bottom: 25px;
        }
        
        .summary-card {
            width: 23%;
            display: inline-block;
            padding: 12px 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
            margin-right: 2%;
            vertical-align: top;
        }
        
        .summary-card:last-child {
            margin-right: 0;
        }
        
        .summary-card .label {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 6px;
            font-weight: bold;
        }
        
        .summary-card .value {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
            margin: 5px 0;
        }
        
        .summary-card .subtext {
            font-size: 8pt;
            color: #999;
            margin-top: 3px;
        }
        
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            color: #333;
            margin: 25px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 3px solid #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table thead {
            background: #667eea;
            color: white;
        }
        
        table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        table td {
            padding: 8px 6px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }
        
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: bold;
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
            font-size: 9pt;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        strong {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>WorkWise Analytics Report</h1>
        <div class="subtitle">Worker Activity & Zone Performance Analysis</div>
    </div>
    
    <!-- Meta Information -->
    <div class="meta-info">
        <div class="period"><?= esc($filter_label) ?></div>
        <div class="generated">Generated on <?= esc($generated_date) ?></div>
    </div>
    
    <!-- Summary Cards -->
    <div class="summary-row">
        <div class="summary-card">
            <div class="label">Active Workers</div>
            <div class="value"><?= esc($summary['active_workers']) ?></div>
            <div class="subtext">of <?= esc($summary['total_workers']) ?> total</div>
        </div><!--
        --><div class="summary-card">
            <div class="label">Total Check-Ins</div>
            <div class="value"><?= esc($summary['total_check_ins']) ?></div>
            <div class="subtext"><?= esc($summary['completed_visits']) ?> completed</div>
        </div><!--
        --><div class="summary-card">
            <div class="label">Avg. Visit Duration</div>
            <div class="value"><?= esc($summary['avg_duration']) ?></div>
            <div class="subtext">per zone visit</div>
        </div><!--
        --><div class="summary-card">
            <div class="label">Active Zones</div>
            <div class="value"><?= esc($summary['total_zones']) ?></div>
            <div class="subtext">monitored areas</div>
        </div>
    </div>
    
    <!-- Zone Analytics Section -->
    <h2 class="section-title">Zone Analytics</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Zone Name</th>
                <th style="width: 25%;">Location</th>
                <th style="width: 15%;">Total Visits</th>
                <th style="width: 15%;">Completed</th>
                <th style="width: 15%;">Avg. Duration</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($zone_stats)): ?>
                <tr>
                    <td colspan="5" class="empty-state">No zone activity recorded for this period</td>
                </tr>
            <?php else: ?>
                <?php foreach ($zone_stats as $zone): ?>
                    <tr>
                        <td><strong><?= esc($zone['zone_name']) ?></strong></td>
                        <td><?= esc($zone['location']) ?></td>
                        <td><span class="badge"><?= esc($zone['total_visits']) ?> visits</span></td>
                        <td><?= esc($zone['completed_visits']) ?></td>
                        <td><strong><?= esc($zone['avg_duration']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if (count($worker_stats) > 10): ?>
        <div class="page-break"></div>
        
        <!-- Repeat header on new page -->
        <div class="header">
            <h1>WorkWise Analytics Report</h1>
            <div class="subtitle">Worker Activity & Zone Performance Analysis</div>
        </div>
        
        <div class="meta-info">
            <div class="period"><?= esc($filter_label) ?></div>
            <div class="generated">Page 2 - Worker Productivity</div>
        </div>
    <?php endif; ?>
    
    <!-- Worker Productivity Section -->
    <h2 class="section-title">Worker Productivity Report</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 18%;">Worker Name</th>
                <th style="width: 13%;">Department</th>
                <th style="width: 15%;">Position</th>
                <th style="width: 13%;">Total Visits</th>
                <th style="width: 12%;">Zones Visited</th>
                <th style="width: 14%;">Total Time</th>
                <th style="width: 15%;">Avg. Duration</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($worker_stats)): ?>
                <tr>
                    <td colspan="7" class="empty-state">No worker activity recorded for this period</td>
                </tr>
            <?php else: ?>
                <?php foreach ($worker_stats as $worker): ?>
                    <tr>
                        <td><strong><?= esc($worker['worker_name']) ?></strong></td>
                        <td><?= esc($worker['department']) ?></td>
                        <td style="font-size: 8pt;"><?= esc($worker['position']) ?></td>
                        <td><span class="badge"><?= esc($worker['total_visits']) ?> visits</span></td>
                        <td><?= esc($worker['zones_visited']) ?> zones</td>
                        <td><strong><?= esc($worker['total_duration']) ?></strong></td>
                        <td><strong><?= esc($worker['avg_duration']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>WorkWise</strong> - Worker Activity Monitoring System</p>
        <p>This report is confidential and intended for internal use only.</p>
    </div>
</body>
</html>
