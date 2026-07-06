<?= $this->include('templates/header') ?>

<?php
$inputClass = 'block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-primary focus:ring-primary sm:text-sm p-2.5';
$labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1';
?>

<div class="flex flex-col gap-6 px-2 sm:px-4 py-2 sm:py-4 w-full">
    <div class="flex flex-wrap items-center justify-between gap-4 mt-4 md:mt-2">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tag + Stock In</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Step-by-step: select item → tag with qty → scan to stock in</p>
        </div>
        <a href="<?= base_url('products/list') ?>" class="text-sm text-primary hover:underline">← Master Lists</a>
    </div>

    <!-- Progress -->
    <div class="flex items-center gap-2">
        <div class="flex-1 flex items-center gap-2">
            <div id="bar-1" class="h-1.5 flex-1 rounded-full bg-primary"></div>
            <div id="bar-2" class="h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
            <div id="bar-3" class="h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        </div>
    </div>
    <div class="flex justify-between text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 -mt-3">
        <span id="label-1" class="text-primary">1. Select</span>
        <span id="label-2">2. Tag + Qty</span>
        <span id="label-3">3. Stock In</span>
    </div>

    <div id="alert" class="hidden p-4 rounded-lg text-sm border"></div>

    <div class="p-4 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 text-sm text-blue-900 dark:text-blue-100 space-y-2">
        <p><strong>Lookup desk scanning (products &amp; raw materials only):</strong></p>
        <ul class="list-disc pl-5 space-y-1 text-blue-800 dark:text-blue-200">
            <li>Set the desk antenna to <strong>LOOKUP</strong> in Zones — never IN/OUT here.</li>
            <li><strong>Step 2</strong> — scan fills the EPC only (no stock in/out).</li>
            <li><strong>Step 3</strong> — scan the same tag again to confirm stock in. Location comes from the reader zone automatically.</li>
            <li>Warehouse IN/OUT gates are unchanged. Staff/worker tags are not affected by this page.</li>
        </ul>
    </div>

    <div id="listen-banner" class="hidden flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
            <div>
                <p class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Listening for lookup-desk RFID scans</p>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Scans fill EPC automatically — no stock change until step 3 confirm</p>
            </div>
        </div>
        <span id="listen-time" class="text-xs text-indigo-600 dark:text-indigo-400 tabular-nums"></span>
    </div>

    <!-- STEP 1: Select product / raw material -->
    <div id="step-1" class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Select Product or Raw Material</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose what you are tagging and stocking in.</p>
        </div>

        <div class="flex gap-2">
            <button type="button" id="tab-all" onclick="setItemFilter('all')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-primary text-white">All</button>
            <button type="button" id="tab-product" onclick="setItemFilter('product')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">Products</button>
            <button type="button" id="tab-raw_material" onclick="setItemFilter('raw_material')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">Raw Materials</button>
        </div>

        <div class="space-y-1.5">
            <label class="<?= $labelClass ?>" for="item_search">Search</label>
            <input type="text" id="item_search" class="<?= $inputClass ?>" placeholder="Search by name or code…" autocomplete="off"/>
        </div>

        <div id="item-list" class="max-h-[28rem] overflow-y-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"></div>

        <div class="flex justify-end pt-2">
            <button type="button" id="btn-next-1" onclick="goStep2()" disabled
                    class="px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                Next: Tag with Qty →
            </button>
        </div>
    </div>

    <!-- STEP 2: Tagging with qty -->
    <div id="step-2" class="hidden bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Tagging with Qty</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scan at the <strong>lookup desk</strong> to capture the UHF tag, then set registered qty (no stock in yet).</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div id="selected-item-card" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm space-y-1"></div>

                <div class="space-y-1.5">
                    <label class="<?= $labelClass ?>" for="batch_code">Batch code <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" id="batch_code" class="<?= $inputClass ?> font-mono" placeholder="Product / material code or lot"/>
                </div>

                <div class="space-y-1.5">
                    <label class="<?= $labelClass ?>" for="epc_scan">Scan UHF EPC Tag</label>
                    <input type="text" id="epc_scan" class="<?= $inputClass ?> font-mono uppercase text-lg tracking-wide" placeholder="Scan tag…" autocomplete="off"/>
                </div>

                <div id="qty-preview" class="hidden p-4 rounded-xl border-2 border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">Tag scanned</p>
                    <p id="qty-preview-epc" class="text-xs font-mono text-purple-600 dark:text-purple-400 break-all"></p>
                    <p id="qty-preview-note" class="text-xs text-gray-500 dark:text-gray-400"></p>
                    <div class="space-y-1.5">
                        <label class="<?= $labelClass ?>" for="registered_qty">Registered qty for this tag</label>
                        <input type="number" id="registered_qty" step="0.001" min="0.001" class="<?= $inputClass ?> text-lg font-bold tabular-nums"/>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Max capacity on this tag. You choose how much to stock in on the next step.</p>
                    </div>
                </div>
            </div>

            <div id="existing-tags" class="hidden space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Existing tags on this item</p>
                <ul id="existing-tags-list" class="space-y-1 text-xs"></ul>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="goStep1()" class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                ← Back
            </button>
            <button type="button" id="btn-next-2" onclick="goStep3()" disabled
                    class="px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                Next: Scan Stock In →
            </button>
        </div>
    </div>

    <!-- STEP 3: Scan to confirm stock in -->
    <div id="step-3" class="hidden bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Stock In</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Set stock in qty, then scan the <strong>same tag</strong> at the lookup desk to confirm. Storage zone is taken from the reader.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="p-4 rounded-xl border-2 border-amber-400 dark:border-amber-500 bg-amber-50 dark:bg-amber-900/20 space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending stock in</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Item</p>
                        <p id="confirm-item" class="font-semibold text-gray-900 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Registered on tag</p>
                        <p>
                            <strong id="confirm-registered" class="text-xl font-bold text-indigo-600 dark:text-indigo-400 tabular-nums"></strong>
                            <span id="confirm-unit-reg" class="text-gray-500 text-sm ml-1"></span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Current on tag</p>
                        <p>
                            <strong id="confirm-current" class="text-lg font-semibold text-gray-700 dark:text-gray-300 tabular-nums"></strong>
                            <span id="confirm-unit-cur" class="text-gray-500 text-sm ml-1"></span>
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400">EPC to confirm</p>
                        <p id="confirm-epc" class="font-mono font-bold text-purple-700 dark:text-purple-300 break-all"></p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Storage location (from RFID scan)</p>
                    <p id="confirm-zone" class="text-sm font-bold text-amber-700 dark:text-amber-300">— scan at lookup desk —</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Zone is set when you scan on step 2 or 3. No manual selection needed.</p>
                </div>
                <div class="space-y-1.5">
                    <label class="<?= $labelClass ?>" for="stock_in_qty">Stock in quantity</label>
                    <input type="number" id="stock_in_qty" step="0.001" min="0.001" class="<?= $inputClass ?> text-lg font-bold tabular-nums"/>
                    <p id="stock-in-hint" class="text-[11px] text-gray-500 dark:text-gray-400"></p>
                </div>
                <div class="space-y-1.5">
                    <label class="<?= $labelClass ?>" for="epc_confirm">Scan same UHF EPC to confirm</label>
                    <input type="text" id="epc_confirm" class="<?= $inputClass ?> font-mono uppercase text-lg tracking-wide" placeholder="Scan same tag at lookup desk…" autocomplete="off"/>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="goStep2FromConfirm()" class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                ← Back
            </button>
            <button type="button" id="btn-confirm" onclick="confirmStockIn()"
                    class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                Confirm Stock In
            </button>
        </div>
    </div>

    <!-- Success -->
    <div id="step-done" class="hidden bg-white dark:bg-background-dark rounded-xl border border-green-300 dark:border-green-700 p-6 space-y-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-3xl">check_circle</span>
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Stock In Complete</h2>
                <p id="done-message" class="text-sm text-gray-500 dark:text-gray-400"></p>
            </div>
        </div>
        <div id="done-body" class="text-sm text-gray-700 dark:text-gray-200 space-y-1 p-4 rounded-lg bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800"></div>
        <button type="button" onclick="startOver()" class="px-5 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90">
            Start another
        </button>
    </div>
</div>

<script>
const items = <?= json_encode($items ?? [], JSON_UNESCAPED_UNICODE) ?>;
const csrfName = <?= json_encode(csrf_token()) ?>;
const csrfHash = <?= json_encode(csrf_hash()) ?>;
const itemUrl = <?= json_encode(base_url('inventory/tag-stock-in/item')) ?>;
const previewUrl = <?= json_encode(base_url('inventory/tag-stock-in/preview')) ?>;
const submitUrl = <?= json_encode(base_url('inventory/tag-stock-in/submit')) ?>;
const scansUrl = <?= json_encode(base_url('inventory/search-stock/scans')) ?>;

let currentStep = 1;
let itemFilter = 'all';
let selectedKey = null;
let selectedItem = null;
let pending = null;
let busy = false;
let lastScanZone = null;
let lastPollTs = Date.now() / 1000;
let pollTimer = null;
const seenScanIds = new Set();
const itemMap = Object.fromEntries(items.map(i => [i.type + ':' + i.id, i]));

function formatQty(n) {
    const v = Number(n) || 0;
    if (Math.abs(v - Math.round(v)) < 1e-9) return String(Math.round(v));
    return v.toFixed(3).replace(/\.?0+$/, '');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function showAlert(message, type) {
    const el = document.getElementById('alert');
    el.classList.remove('hidden', 'bg-green-100', 'dark:bg-green-900/20', 'border-green-500', 'text-green-700', 'dark:text-green-400', 'bg-red-100', 'dark:bg-red-900/20', 'border-red-500', 'text-red-700', 'dark:text-red-400');
    if (type === 'success') {
        el.classList.add('bg-green-100', 'dark:bg-green-900/20', 'border-green-500', 'text-green-700', 'dark:text-green-400');
    } else {
        el.classList.add('bg-red-100', 'dark:bg-red-900/20', 'border-red-500', 'text-red-700', 'dark:text-red-400');
    }
    el.textContent = message;
}

function hideAlert() {
    document.getElementById('alert').classList.add('hidden');
}

function setProgress(step) {
    currentStep = step;
    for (let i = 1; i <= 3; i++) {
        const bar = document.getElementById('bar-' + i);
        const label = document.getElementById('label-' + i);
        if (i < step) {
            bar.className = 'h-1.5 flex-1 rounded-full bg-green-500';
            label.className = 'text-green-600 dark:text-green-400';
        } else if (i === step) {
            bar.className = 'h-1.5 flex-1 rounded-full bg-primary';
            label.className = 'text-primary';
        } else {
            bar.className = 'h-1.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700';
            label.className = 'text-gray-500 dark:text-gray-400';
        }
    }
}

function showStep(step) {
    hideAlert();
    ['step-1', 'step-2', 'step-3', 'step-done'].forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    const listenBanner = document.getElementById('listen-banner');
    if (step === 2 || step === 3) {
        listenBanner.classList.remove('hidden');
        startListenPoll();
    } else {
        listenBanner.classList.add('hidden');
        stopListenPoll();
    }
    if (step === 'done') {
        document.getElementById('step-done').classList.remove('hidden');
        setProgress(3);
        document.getElementById('bar-3').className = 'h-1.5 flex-1 rounded-full bg-green-500';
        document.getElementById('label-3').className = 'text-green-600 dark:text-green-400';
        return;
    }
    document.getElementById('step-' + step).classList.remove('hidden');
    setProgress(step);
}

function applyScanZone(scan) {
    if (!scan?.zone_id) return;
    lastScanZone = {
        zone_id: scan.zone_id,
        zone_name: scan.zone_name || scan.zone_id,
    };
    if (pending) {
        pending.scan_zone_id = lastScanZone.zone_id;
        pending.scan_zone_name = lastScanZone.zone_name;
    }
    paintScanZone();
}

function paintScanZone() {
    const el = document.getElementById('confirm-zone');
    if (!el) return;
    const zone = pending?.scan_zone_name || lastScanZone?.zone_name;
    el.textContent = zone || '— scan at lookup desk —';
}

function stopListenPoll() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

function startListenPoll() {
    stopListenPoll();
    const tick = () => {
        document.getElementById('listen-time').textContent = 'Updated ' + new Date().toLocaleTimeString();
        pollLookupScans();
    };
    pollTimer = setInterval(tick, 1200);
    tick();
}

async function pollLookupScans() {
    if (currentStep !== 2 && currentStep !== 3) return;
    try {
        const res = await fetch(scansUrl + '?since=' + encodeURIComponent(lastPollTs), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store',
        });
        const data = await res.json();
        if (!data.success || !data.scans?.length) return;

        for (const scan of data.scans) {
            if (seenScanIds.has(scan.id)) continue;
            seenScanIds.add(scan.id);
            lastPollTs = Math.max(lastPollTs, scan.ts || lastPollTs);
            applyScanZone(scan);

            if (currentStep === 2) {
                document.getElementById('epc_scan').value = scan.epc || '';
                await stageTag();
                continue;
            }

            if (currentStep === 3 && pending) {
                document.getElementById('epc_confirm').value = scan.epc || '';
                if ((scan.epc || '').toUpperCase() === pending.epc_no) {
                    await confirmStockIn();
                } else {
                    showAlert('Tag does not match. Scan ' + pending.epc_no + ' to confirm.', 'error');
                }
            }
        }
    } catch (e) {
        // retry on next poll
    }
}

function setItemFilter(filter) {
    itemFilter = filter;
    ['all', 'product', 'raw_material'].forEach(f => {
        const btn = document.getElementById('tab-' + f);
        if (!btn) return;
        const active = f === filter;
        btn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold ' + (
            active ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300'
        );
    });
    renderItemList();
}

function renderItemList() {
    const q = (document.getElementById('item_search').value || '').toLowerCase().trim();
    const list = document.getElementById('item-list');
    const filtered = items.filter(item => {
        if (itemFilter !== 'all' && item.type !== itemFilter) return false;
        if (!q) return true;
        return (item.name + ' ' + item.code + ' ' + (item.sap_code || '')).toLowerCase().includes(q);
    });

    if (!filtered.length) {
        list.className = 'max-h-[28rem] overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg';
        list.innerHTML = '<p class="p-6 text-center text-sm text-gray-400">No items found.</p>';
        return;
    }

    list.className = 'max-h-[28rem] overflow-y-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden';
    list.innerHTML = filtered.map(item => {
        const key = item.type + ':' + item.id;
        const active = key === selectedKey;
        const typeLabel = item.type === 'product' ? 'Product' : 'Raw Material';
        const typeCls = item.type === 'product'
            ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
            : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300';
        return `
            <button type="button" data-key="${escapeHtml(key)}"
                class="w-full text-left px-4 py-3 bg-white dark:bg-background-dark hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors ${active ? 'bg-primary/10 dark:bg-primary/20 ring-inset ring-2 ring-primary' : ''}"
                onclick="selectItem('${escapeHtml(key)}')">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">${escapeHtml(item.name)}</p>
                        <p class="text-xs font-mono text-gray-500 dark:text-gray-400">${escapeHtml(item.code)}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold ${typeCls}">${typeLabel}</span>
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 tabular-nums mt-1">Bal ${formatQty(item.quantity_on_hand)} · ${formatQty(item.qty_per_tag)}/tag</p>
                    </div>
                </div>
            </button>
        `;
    }).join('');
}

function selectItem(key) {
    selectedKey = key;
    selectedItem = itemMap[key] || null;
    document.getElementById('btn-next-1').disabled = !selectedItem;
    renderItemList();
}

async function goStep2() {
    if (!selectedItem) return;
    hideAlert();
    busy = true;
    try {
        const url = itemUrl + '?type=' + encodeURIComponent(selectedItem.type) + '&id=' + encodeURIComponent(selectedItem.id);
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success) {
            showAlert(data.message || 'Could not load item.', 'error');
            return;
        }
        selectedItem = data.item;
        itemMap[selectedItem.type + ':' + selectedItem.id] = selectedItem;
        pending = null;
        paintStep2();
        showStep(2);
        document.getElementById('epc_scan').value = '';
        document.getElementById('epc_scan').focus();
    } catch (e) {
        showAlert('Network error while loading item.', 'error');
    } finally {
        busy = false;
    }
}

function paintStep2() {
    const item = selectedItem;
    const typeLabel = item.type === 'product' ? 'Product' : 'Raw Material';
    document.getElementById('selected-item-card').innerHTML = `
        <p class="text-gray-500 dark:text-gray-400">${typeLabel}: <strong class="text-gray-900 dark:text-white">${escapeHtml(item.name)}</strong></p>
        <p class="text-gray-500 dark:text-gray-400">Code: <strong class="font-mono text-gray-900 dark:text-white">${escapeHtml(item.code)}</strong></p>
        <p class="text-gray-500 dark:text-gray-400">Balance: <strong class="text-indigo-600 dark:text-indigo-400 tabular-nums">${formatQty(item.quantity_on_hand)}</strong> ${escapeHtml(item.unit || '')}</p>
        <p class="text-gray-500 dark:text-gray-400">Qty per tag: <strong class="text-gray-900 dark:text-white tabular-nums">${formatQty(item.qty_per_tag)}</strong> · Mode: <strong>${item.tag_mode === 'multi' ? 'Multi' : 'Single'}</strong></p>
    `;
    document.getElementById('batch_code').value = item.code || '';
    document.getElementById('qty-preview').classList.add('hidden');
    document.getElementById('btn-next-2').disabled = true;
    renderTags(item.tags || []);
}

function renderTags(tags) {
    const wrap = document.getElementById('existing-tags');
    const list = document.getElementById('existing-tags-list');
    if (!tags || !tags.length) {
        wrap.classList.add('hidden');
        list.innerHTML = '';
        return;
    }
    wrap.classList.remove('hidden');
    list.innerHTML = tags.map(t => `
        <li class="p-2 rounded-lg bg-purple-50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-900/30">
            <span class="font-mono text-purple-700 dark:text-purple-300 break-all">${escapeHtml(t.epc_no)}</span>
            <span class="block text-gray-500 mt-0.5">Registered: ${formatQty(t.tag_registered_quantity ?? t.tag_quantity)} · Current: ${formatQty(t.tag_quantity)}</span>
        </li>
    `).join('');
}

async function stageTag() {
    if (busy || !selectedItem) return;
    hideAlert();

    const epcNo = document.getElementById('epc_scan').value.trim().toUpperCase();
    if (!epcNo || epcNo.length < 4) {
        showAlert('Scan a valid UHF EPC tag.', 'error');
        return;
    }

    busy = true;
    try {
        const url = previewUrl
            + '?type=' + encodeURIComponent(selectedItem.type)
            + '&id=' + encodeURIComponent(selectedItem.id)
            + '&epc_no=' + encodeURIComponent(epcNo);
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success) {
            showAlert(data.message || 'Tag not accepted.', 'error');
            pending = null;
            document.getElementById('qty-preview').classList.add('hidden');
            document.getElementById('btn-next-2').disabled = true;
            document.getElementById('epc_scan').select();
            return;
        }

        pending = {
            epc_no: data.epc_no,
            mode: data.mode,
            registered_qty: data.registered_qty,
            current_qty: data.current_qty,
            max_stock_in: data.max_stock_in,
            scan_zone_id: lastScanZone?.zone_id || null,
            scan_zone_name: lastScanZone?.zone_name || null,
        };

        document.getElementById('qty-preview').classList.remove('hidden');
        document.getElementById('qty-preview-epc').textContent = pending.epc_no;
        document.getElementById('qty-preview-note').textContent = pending.mode === 'existing'
            ? 'Existing tag — current ' + formatQty(pending.current_qty) + ' on hand'
            : 'New tag — set registered qty for this tag';
        document.getElementById('registered_qty').value = formatQty(pending.registered_qty);
        document.getElementById('btn-next-2').disabled = false;
    } catch (e) {
        showAlert('Network error while reading tag.', 'error');
    } finally {
        busy = false;
    }
}

function goStep3() {
    if (!pending || !selectedItem) return;
    hideAlert();

    const registered = parseFloat(document.getElementById('registered_qty').value);
    if (!registered || registered <= 0) {
        showAlert('Enter a registered qty greater than zero.', 'error');
        return;
    }

    pending.registered_qty = registered;
    const unit = selectedItem.unit || 'pcs';
    const current = pending.current_qty || 0;
    const maxIn = Math.max(0, registered - current);

    document.getElementById('confirm-item').textContent = selectedItem.name + ' (' + selectedItem.code + ')';
    document.getElementById('confirm-registered').textContent = formatQty(registered);
    document.getElementById('confirm-unit-reg').textContent = unit;
    document.getElementById('confirm-current').textContent = formatQty(current);
    document.getElementById('confirm-unit-cur').textContent = unit;
    document.getElementById('confirm-epc').textContent = pending.epc_no;

    const stockInput = document.getElementById('stock_in_qty');
    stockInput.max = maxIn > 0 ? maxIn : registered;
    stockInput.value = maxIn > 0 ? formatQty(maxIn) : formatQty(registered);
    document.getElementById('stock-in-hint').textContent = maxIn > 0
        ? 'Max ' + formatQty(maxIn) + ' ' + unit + ' (registered ' + formatQty(registered) + ' − current ' + formatQty(current) + ')'
        : 'Tag already at registered qty — increase registered on previous step if needed.';

    document.getElementById('epc_confirm').value = '';
    paintScanZone();
    showStep(3);
    document.getElementById('epc_confirm').focus();
}

function goStep1() {
    pending = null;
    lastScanZone = null;
    showStep(1);
}

function goStep2FromConfirm() {
    showStep(2);
    document.getElementById('epc_scan').focus();
}

async function confirmStockIn() {
    if (busy || !pending || !selectedItem) return;
    hideAlert();

    const confirmEpc = document.getElementById('epc_confirm').value.trim().toUpperCase();
    if (!confirmEpc || confirmEpc.length < 4) {
        showAlert('Scan the same tag to confirm.', 'error');
        document.getElementById('epc_confirm').focus();
        return;
    }
    if (confirmEpc !== pending.epc_no) {
        showAlert('Tag does not match. Scan ' + pending.epc_no + ' to confirm.', 'error');
        document.getElementById('epc_confirm').value = '';
        document.getElementById('epc_confirm').focus();
        return;
    }

    const stockInQty = parseFloat(document.getElementById('stock_in_qty').value);
    if (!stockInQty || stockInQty <= 0) {
        showAlert('Enter stock in quantity greater than zero.', 'error');
        document.getElementById('stock_in_qty').focus();
        return;
    }

    const registered = pending.registered_qty;
    const maxIn = Math.max(0, registered - (pending.current_qty || 0));
    if (stockInQty > maxIn + 0.0001 && pending.mode === 'existing') {
        showAlert('Stock in cannot exceed ' + formatQty(maxIn) + ' for this tag.', 'error');
        return;
    }
    if (stockInQty > registered + 0.0001) {
        showAlert('Stock in cannot exceed registered qty (' + formatQty(registered) + ').', 'error');
        return;
    }

    const storageZoneId = (pending.scan_zone_id || lastScanZone?.zone_id || '').trim();
    if (!storageZoneId) {
        showAlert('Scan at the lookup desk (LOOKUP antenna) to set storage location.', 'error');
        return;
    }

    busy = true;
    const btn = document.getElementById('btn-confirm');
    btn.disabled = true;

    const body = new URLSearchParams();
    body.set(csrfName, csrfHash);
    body.set('type', selectedItem.type);
    body.set('id', String(selectedItem.id));
    body.set('batch_code', document.getElementById('batch_code').value.trim());
    body.set('epc_no', pending.epc_no);
    body.set('registered_quantity', String(registered));
    body.set('stock_in_quantity', String(stockInQty));
    body.set('storage_zone_id', storageZoneId);

    try {
        const res = await fetch(submitUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body.toString(),
        });
        const data = await res.json();
        if (!data.success) {
            showAlert(data.message || 'Confirm failed.', 'error');
            return;
        }

        selectedItem = data.item;
        itemMap[selectedItem.type + ':' + selectedItem.id] = selectedItem;

        document.getElementById('done-message').textContent = data.message || 'Success.';
        document.getElementById('done-body').innerHTML = `
            <p><span class="text-gray-500">Item:</span> <strong>${escapeHtml(data.item.name)}</strong> (${escapeHtml(data.item.code)})</p>
            <p><span class="text-gray-500">EPC:</span> <strong class="font-mono text-purple-600 dark:text-purple-400">${escapeHtml(data.tag.epc_no)}</strong></p>
            <p><span class="text-gray-500">Stocked in:</span> <strong class="text-green-600 dark:text-green-400">${formatQty(data.quantity)}</strong></p>
            <p><span class="text-gray-500">Stored at:</span> <strong class="text-amber-600 dark:text-amber-400">${escapeHtml(data.storage_zone_name || '—')}</strong></p>
            <p><span class="text-gray-500">New balance:</span> <strong class="text-indigo-600 dark:text-indigo-400">${formatQty(data.balance_after)}</strong></p>
        `;
        pending = null;
        showStep('done');
    } catch (e) {
        showAlert('Network error while confirming.', 'error');
    } finally {
        busy = false;
        btn.disabled = false;
    }
}

function startOver() {
    selectedKey = null;
    selectedItem = null;
    pending = null;
    lastScanZone = null;
    document.getElementById('btn-next-1').disabled = true;
    document.getElementById('item_search').value = '';
    document.getElementById('epc_scan').value = '';
    document.getElementById('epc_confirm').value = '';
    setItemFilter('all');
    showStep(1);
}

function bindScanInput(id, onEnter) {
    const el = document.getElementById(id);
    el.addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/\s+/g, '');
    });
    el.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            onEnter();
        }
    });
}

document.getElementById('item_search').addEventListener('input', renderItemList);
document.getElementById('registered_qty')?.addEventListener('input', function () {
    if (pending) document.getElementById('btn-next-2').disabled = !(parseFloat(this.value) > 0);
});

bindScanInput('epc_scan', stageTag);
bindScanInput('epc_confirm', confirmStockIn);

// After first scan stages qty, Enter on scan field already handled; also allow auto-advance feel
document.getElementById('epc_scan').addEventListener('change', stageTag);

renderItemList();
showStep(1);

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopListenPoll();
    } else if (currentStep === 2 || currentStep === 3) {
        startListenPoll();
    }
});
</script>

<?= $this->include('templates/footer') ?>
