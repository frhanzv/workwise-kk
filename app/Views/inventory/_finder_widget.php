<!-- Global Stock Finder (floating — available on every page) -->
<div id="stock-finder-btn" class="fixed bottom-28 right-6 z-50">
    <button
        type="button"
        onclick="openStockFinder()"
        class="flex items-center gap-3 bg-gradient-to-r from-primary to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-5 py-3.5 rounded-full shadow-2xl hover:shadow-primary/40 transition-all duration-300 transform hover:scale-105">
        <span class="material-symbols-outlined text-xl">inventory_2</span>
        <span class="font-semibold hidden sm:inline text-sm">Find Stock</span>
        <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
        </span>
    </button>
</div>

<div id="stock-finder-panel" class="hidden fixed z-[60] w-[min(100vw-2rem,420px)] bg-white dark:bg-background-dark shadow-2xl rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col"
     style="bottom: 24px; right: 24px; max-height: min(85vh, 640px);">

    <div class="bg-gradient-to-r from-primary to-blue-600 px-4 py-3 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-white text-xl">inventory_2</span>
            <div>
                <h3 class="text-white font-bold text-sm">Find Stock</h3>
                <p class="text-blue-100 text-[10px]">Tag · Batch · Item name</p>
            </div>
        </div>
        <button type="button" onclick="closeStockFinder()" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors" title="Close">
            <span class="material-symbols-outlined text-white text-lg">close</span>
        </button>
    </div>

    <div class="p-4 space-y-3 overflow-y-auto flex-1">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg pointer-events-none">search</span>
            <input
                type="text"
                id="stock-finder-input"
                class="block w-full pl-10 pr-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-primary focus:ring-primary text-sm"
                placeholder="UHF tag, batch code, or item name…"
                autocomplete="off"/>
        </div>
        <p id="stock-finder-hint" class="text-xs text-gray-500 dark:text-gray-400">Type or scan — shows where the item is right now.</p>
        <div id="stock-finder-results" class="space-y-2"></div>
    </div>

    <div class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 shrink-0">
        <a href="<?= base_url('inventory/search-stock') ?>" class="text-xs text-primary hover:underline">Open full Search Stock page →</a>
    </div>
</div>

<script>
(function () {
    const finderUrl = <?= json_encode(base_url('inventory/finder')) ?>;
    const scansUrl  = <?= json_encode(base_url('inventory/search-stock/scans')) ?>;
    let finderTimer = null;
    let lastFinderQ = '';
    let pollTs = Date.now() / 1000;
    let pollId = null;
    const seenIds = new Set();

    window.openStockFinder = function () {
        document.getElementById('stock-finder-btn').classList.add('hidden');
        document.getElementById('stock-finder-panel').classList.remove('hidden');
        const input = document.getElementById('stock-finder-input');
        input.focus();
        startFinderPoll();
    };

    window.closeStockFinder = function () {
        document.getElementById('stock-finder-panel').classList.add('hidden');
        document.getElementById('stock-finder-btn').classList.remove('hidden');
        stopFinderPoll();
    };

    function stopFinderPoll() {
        if (pollId) {
            clearInterval(pollId);
            pollId = null;
        }
    }

    function startFinderPoll() {
        stopFinderPoll();
        pollId = setInterval(pollLookupScans, 1200);
        pollLookupScans();
    }

    async function pollLookupScans() {
        if (document.getElementById('stock-finder-panel').classList.contains('hidden')) return;
        try {
            const res = await fetch(scansUrl + '?since=' + encodeURIComponent(pollTs), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success || !data.scans?.length) return;

            for (const scan of data.scans) {
                if (seenIds.has(scan.id)) continue;
                seenIds.add(scan.id);
                pollTs = Math.max(pollTs, scan.ts || pollTs);
                if (scan.epc) {
                    document.getElementById('stock-finder-input').value = scan.epc;
                    lastFinderQ = '';
                    runFinderSearch();
                }
            }
        } catch (e) { /* retry next poll */ }
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function fmtQty(n) {
        const v = Number(n) || 0;
        if (Math.abs(v - Math.round(v)) < 1e-9) return String(Math.round(v));
        return v.toFixed(3).replace(/\.?0+$/, '');
    }

    function locationBadge(loc) {
        if (!loc) return '<span class="text-gray-500">—</span>';
        const tone = loc.status === 'in_zone'
            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
            : (loc.status === 'last_seen'
                ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400');
        const since = loc.since ? '<p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">' + esc(loc.since) + '</p>' : '';
        return '<div class="mt-2 p-2.5 rounded-lg ' + tone + '">' +
            '<p class="text-[10px] font-bold uppercase tracking-wider opacity-80">' + esc(loc.label) + '</p>' +
            '<p class="text-sm font-bold">' + esc(loc.zone_name) + '</p>' + since + '</div>';
    }

    function renderResults(results, query) {
        const el = document.getElementById('stock-finder-results');
        const hint = document.getElementById('stock-finder-hint');

        if (!results.length) {
            hint.textContent = 'No match for “' + query + '”. Try tag, batch code, or name.';
            el.innerHTML = '';
            return;
        }

        hint.textContent = results.length + ' result' + (results.length === 1 ? '' : 's') + ' for “' + query + '”';
        el.innerHTML = results.map(r => {
            const epc = r.epc_no ? '<p class="text-[10px] font-mono text-purple-600 dark:text-purple-400 mt-1 break-all">' + esc(r.epc_no) + '</p>' : '';
            return '<div class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40">' +
                '<div class="flex items-start justify-between gap-2">' +
                '<div class="min-w-0">' +
                '<span class="text-[10px] font-bold uppercase tracking-wider text-primary">' + esc(r.type_label) + '</span>' +
                '<p class="font-semibold text-sm text-gray-900 dark:text-white truncate">' + esc(r.name) + '</p>' +
                '<p class="text-xs font-mono text-gray-500 dark:text-gray-400">' + esc(r.code) + '</p>' +
                epc +
                '</div>' +
                '<div class="text-right shrink-0">' +
                '<p class="text-lg font-bold text-primary tabular-nums">' + fmtQty(r.balance) + '</p>' +
                '<p class="text-[10px] text-gray-500">' + esc(r.unit || 'qty') + '</p>' +
                '</div></div>' +
                locationBadge(r.location) +
                '<p class="text-[10px] text-gray-500 dark:text-gray-400 mt-2">Allowed: ' + esc(r.allowed_zones) + '</p>' +
                '</div>';
        }).join('');
    }

    async function runFinderSearch() {
        const q = document.getElementById('stock-finder-input').value.trim();
        if (!q) {
            document.getElementById('stock-finder-results').innerHTML = '';
            document.getElementById('stock-finder-hint').textContent = 'Type or scan — shows where the item is right now.';
            lastFinderQ = '';
            return;
        }
        if (q === lastFinderQ) return;
        lastFinderQ = q;

        document.getElementById('stock-finder-hint').textContent = 'Searching…';

        try {
            const res = await fetch(finderUrl + '?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) {
                document.getElementById('stock-finder-hint').textContent = data.message || 'Search failed.';
                return;
            }
            renderResults(data.results || [], data.query || q);
        } catch (e) {
            document.getElementById('stock-finder-hint').textContent = 'Search failed. Try again.';
        }
    }

    window.runFinderSearch = runFinderSearch;

    document.getElementById('stock-finder-input')?.addEventListener('input', function () {
        clearTimeout(finderTimer);
        finderTimer = setTimeout(runFinderSearch, 350);
    });
    document.getElementById('stock-finder-input')?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(finderTimer);
            runFinderSearch();
        }
        if (e.key === 'Escape') closeStockFinder();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !document.getElementById('stock-finder-panel').classList.contains('hidden')) {
            closeStockFinder();
        }
    });
})();
</script>
