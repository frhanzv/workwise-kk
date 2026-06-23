<?= $this->include('templates/header') ?>

<div class="flex flex-col gap-6">

    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4 mt-6 md:mt-4">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('dashboard') ?>" class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    <?= esc($worker['first_name'] . ' ' . $worker['last_name']) ?>
                </h1>
                <div class="flex items-center gap-3 mt-0.5">
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400"><?= esc($worker['worker_id']) ?></span>
                    <?php if (!empty($worker['department'])): ?>
                        <span class="text-xs text-gray-400">·</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400"><?= esc(ucfirst($worker['department'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($worker['position'])): ?>
                        <span class="text-xs text-gray-400">·</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400"><?= esc($worker['position']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Date Picker -->
        <div class="flex items-center gap-3">
            <form method="get" class="flex items-center gap-2">
                <label class="text-xs text-gray-500 dark:text-gray-400 font-medium">Date</label>
                <input type="date" name="date" value="<?= esc($selected_date) ?>" max="<?= esc($today) ?>"
                       onchange="this.form.submit()"
                       class="h-9 px-3 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-primary focus:border-primary"/>
            </form>
            <a href="<?= base_url('workers/view/' . esc($worker['worker_id'])) ?>" class="flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                <span class="material-symbols-outlined text-base">person</span>
                Worker Profile
            </a>
        </div>
    </div>

    <!-- ── Summary Cards ──────────────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">First Entry</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">
                <?= $first_in ? date('H:i', strtotime($first_in)) : '—' ?>
            </p>
            <?php if ($first_in): ?>
                <p class="text-xs text-gray-400 mt-0.5"><?= date('d M Y', strtotime($first_in)) ?></p>
            <?php endif; ?>
        </div>
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Last Exit</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">
                <?= $last_out ? date('H:i', strtotime($last_out)) : ($first_in ? '<span class="text-green-500 text-sm">Still Inside</span>' : '—') ?>
            </p>
        </div>
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Total In Zones</p>
            <p class="text-xl font-bold text-primary"><?= $total_in_zone_sec > 0 ? esc($total_in_zone) : '—' ?></p>
        </div>
        <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Time Not in Any Zone</p>
            <p class="text-xl font-bold text-gray-600 dark:text-gray-300"><?= $total_not_in_zone_sec > 0 ? esc($total_not_in_zone) : '—' ?></p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- ── Gate-by-Gate Activity Table ─────────────────────────────── -->
        <div class="flex-1 min-w-0 bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">route</span>
                    Gate-by-Gate Movement
                </h2>
                <span class="text-xs text-gray-500 dark:text-gray-400"><?= count($zone_visits) ?> record<?= count($zone_visits) !== 1 ? 's' : '' ?></span>
            </div>

            <?php if (empty($zone_visits)): ?>
                <div class="px-6 py-12 text-center">
                    <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600 block mb-2">location_off</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No activity recorded for <?= date('d M Y', strtotime($selected_date)) ?>.</p>
                </div>
            <?php else: ?>
                <!-- Visual timeline -->
                <div class="px-6 py-4 space-y-0">
                    <?php foreach ($zone_visits as $i => $visit): ?>
                        <div class="flex gap-4 <?= $i < count($zone_visits) - 1 ? 'pb-0' : '' ?>">
                            <!-- Timeline spine -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 <?= $visit['is_active'] ? 'bg-green-100 dark:bg-green-900/30 border-2 border-green-400' : 'bg-primary/10 dark:bg-primary/20 border-2 border-primary' ?>">
                                    <span class="material-symbols-outlined text-sm <?= $visit['is_active'] ? 'text-green-500' : 'text-primary' ?>">
                                        <?= $visit['is_active'] ? 'person_pin' : 'location_on' ?>
                                    </span>
                                </div>
                                <?php if ($i < count($zone_visits) - 1): ?>
                                    <div class="w-0.5 bg-gray-200 dark:bg-gray-700 flex-1 my-1" style="min-height: 24px;"></div>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 pb-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white"><?= esc($visit['zone_name']) ?></p>
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs text-green-500">login</span>
                                                <?= $visit['entry_time'] ? date('H:i', strtotime($visit['entry_time'])) : '—' ?>
                                            </span>
                                            <span>→</span>
                                            <span class="flex items-center gap-1 <?= $visit['is_active'] ? 'text-green-500 font-semibold' : '' ?>">
                                                <span class="material-symbols-outlined text-xs <?= $visit['is_active'] ? 'text-green-500' : 'text-red-400' ?>">logout</span>
                                                <?= $visit['is_active'] ? 'Active now' : ($visit['exit_time'] ? date('H:i', strtotime($visit['exit_time'])) : '—') ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <?php if ($visit['duration_fmt']): ?>
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 dark:bg-primary/20 text-primary">
                                                <?= esc($visit['duration_fmt']) ?>
                                            </span>
                                        <?php elseif ($visit['is_active']): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                                                <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span></span>
                                                In Progress
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Gap to next zone (time not in any area between zones) -->
                                <?php if ($i < count($zone_visits) - 1 && $visit['exit_time'] && $zone_visits[$i + 1]['entry_time']): ?>
                                    <?php
                                        $gapSec = strtotime($zone_visits[$i + 1]['entry_time']) - strtotime($visit['exit_time']);
                                        $gapMin = round($gapSec / 60);
                                    ?>
                                    <?php if ($gapMin > 0): ?>
                                        <div class="ml-2 mt-1 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                                            <span class="material-symbols-outlined text-xs">more_time</span>
                                            <?= $gapMin ?>m gap before next zone
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Flat table view (printable) -->
                <div class="border-t border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <table class="w-full text-xs" id="trackingTable">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Zone / Gate</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entry Time</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Exit Time</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                                <th class="px-4 py-2.5 text-left font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($zone_visits as $i => $visit): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-2.5 text-gray-400"><?= $i + 1 ?></td>
                                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white"><?= esc($visit['zone_name']) ?></td>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                        <?= $visit['entry_time'] ? date('H:i:s', strtotime($visit['entry_time'])) : '—' ?>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                        <?php if ($visit['is_active']): ?>
                                            <span class="text-green-500 font-semibold">Active</span>
                                        <?php else: ?>
                                            <?= $visit['exit_time'] ? date('H:i:s', strtotime($visit['exit_time'])) : '—' ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <?= $visit['duration_fmt'] ? esc($visit['duration_fmt']) : ($visit['is_active'] ? '<span class="text-green-500">In Progress</span>' : '—') ?>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <?php if ($visit['is_active']): ?>
                                            <span class="px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium">Active</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Exited</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Right Column: Zone Summary + Time Breakdown ──────────────── -->
        <div class="w-full lg:w-72 flex-shrink-0 flex flex-col gap-4">

            <!-- Time per Zone Summary -->
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">bar_chart</span>
                    Total Time per Zone
                </h2>
                <?php if (empty($zone_summary)): ?>
                    <p class="text-xs text-gray-400 dark:text-gray-500">No data for this date.</p>
                <?php else: ?>
                    <div class="flex flex-col gap-3">
                        <?php foreach ($zone_summary as $zs): ?>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate pr-2"><?= esc($zs['name']) ?></span>
                                    <span class="text-xs font-bold text-primary whitespace-nowrap"><?= esc($zs['fmt']) ?></span>
                                </div>
                                <?php
                                    $pct = $total_observed_sec > 0 ? min(100, round(($zs['seconds'] / $total_observed_sec) * 100)) : 0;
                                ?>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-primary h-1.5 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5"><?= $zs['visits'] ?> visit<?= $zs['visits'] !== 1 ? 's' : '' ?> · <?= $pct ?>% of observed time</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Time Breakdown Summary -->
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">pie_chart</span>
                    Time Breakdown
                </h2>
                <div class="flex flex-col gap-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary flex-shrink-0"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Total in zones</span>
                        </div>
                        <span class="text-sm font-bold text-primary"><?= $total_in_zone_sec > 0 ? esc($total_in_zone) : '—' ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-gray-300 dark:bg-gray-500 flex-shrink-0"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Not in any zone</span>
                        </div>
                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300"><?= $total_not_in_zone_sec > 0 ? esc($total_not_in_zone) : '—' ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-300">Total observed</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white"><?= esc($total_observed) ?></span>
                    </div>
                </div>

                <!-- Donut-style visual bar -->
                <?php if ($total_observed_sec > 0): ?>
                    <?php
                        $inPct  = round(($total_in_zone_sec / $total_observed_sec) * 100);
                        $outPct = 100 - $inPct;
                    ?>
                    <div class="mt-4">
                        <div class="flex h-3 rounded-full overflow-hidden">
                            <div class="bg-primary transition-all" style="width: <?= $inPct ?>%"></div>
                            <div class="bg-gray-200 dark:bg-gray-600 flex-1"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-xs text-gray-400"><?= $inPct ?>% in zone</span>
                            <span class="text-xs text-gray-400"><?= $outPct ?>% between zones</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Worker Info Card -->
            <div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">badge</span>
                    Worker Info
                </h2>
                <dl class="flex flex-col gap-2">
                    <div class="flex justify-between">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="text-xs font-medium text-gray-900 dark:text-white"><?= esc($worker['first_name'] . ' ' . $worker['last_name']) ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Worker ID</dt>
                        <dd class="text-xs font-mono text-primary"><?= esc($worker['worker_id']) ?></dd>
                    </div>
                    <?php if (!empty($worker['rfid_tag_id'])): ?>
                    <div class="flex justify-between">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">RFID Tag</dt>
                        <dd class="text-xs font-mono text-gray-700 dark:text-gray-300"><?= esc($worker['rfid_tag_id']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Status</dt>
                        <dd>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $worker['status'] === 'active' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' ?>">
                                <?= ucfirst(esc($worker['status'])) ?>
                            </span>
                        </dd>
                    </div>
                    <?php if (!empty($worker['shift'])): ?>
                    <div class="flex justify-between">
                        <dt class="text-xs text-gray-500 dark:text-gray-400">Shift</dt>
                        <dd class="text-xs text-gray-700 dark:text-gray-300"><?= esc(ucfirst($worker['shift'])) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
                <a href="<?= base_url('workers/view/' . esc($worker['worker_id'])) ?>" class="mt-3 flex items-center justify-center gap-1.5 h-8 w-full rounded-lg border border-gray-300 dark:border-gray-600 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <span class="material-symbols-outlined text-sm">person</span>
                    View Full Profile
                </a>
            </div>

            <!-- Export -->
            <button onclick="exportTracking()" class="flex items-center justify-center gap-2 h-10 w-full rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-base">download</span>
                Export This Report
            </button>
        </div>
    </div>
</div>

<script>
function exportTracking() {
    const workerName = '<?= esc($worker['first_name'] . '_' . $worker['last_name']) ?>';
    const date       = '<?= esc($selected_date) ?>';
    const table      = document.getElementById('trackingTable');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    const csv  = [];

    // Header info
    csv.push('"Worker Tracking Report"');
    csv.push('"Worker","<?= esc($worker['first_name'] . ' ' . $worker['last_name']) ?>"');
    csv.push('"ID","<?= esc($worker['worker_id']) ?>"');
    csv.push('"Date","<?= date('d M Y', strtotime($selected_date)) ?>"');
    csv.push('"First Entry","<?= $first_in ? date('H:i:s', strtotime($first_in)) : '—' ?>"');
    csv.push('"Last Exit","<?= $last_out ? date('H:i:s', strtotime($last_out)) : '—' ?>"');
    csv.push('"Total In Zones","<?= esc($total_in_zone) ?>"');
    csv.push('"Total Not In Zone","<?= esc($total_not_in_zone) ?>"');
    csv.push('"Total Observed","<?= esc($total_observed) ?>"');
    csv.push('');

    // Table headers
    const headers = [];
    rows[0].querySelectorAll('th').forEach(th => headers.push('"' + th.innerText.trim() + '"'));
    csv.push(headers.join(','));

    // Table rows
    for (let i = 1; i < rows.length; i++) {
        const row = [];
        rows[i].querySelectorAll('td').forEach(td => {
            let text = (td.innerText || td.textContent || '').replace(/\n/g,' ').replace(/\s+/g,' ').trim().replace(/"/g,'""');
            row.push('"' + text + '"');
        });
        csv.push(row.join(','));
    }

    const blob = new Blob(['﻿' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a    = document.createElement('a');
    a.href     = window.URL.createObjectURL(blob);
    a.download = 'tracking_' + workerName + '_' + date + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

<?= $this->include('templates/footer') ?>
