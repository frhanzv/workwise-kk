/**
 * Shared helpers for clickable column sorting (asc / desc toggle).
 */
function compareSortValues(a, b, dir) {
    const empty = (v) => v === null || v === undefined || v === '' || v === '-' || v === '—';
    if (empty(a) && empty(b)) return 0;
    if (empty(a)) return 1;
    if (empty(b)) return -1;

    let result;
    if (typeof a === 'number' && typeof b === 'number') {
        result = a - b;
    } else {
        result = String(a).localeCompare(String(b), undefined, { numeric: true, sensitivity: 'base' });
    }
    return dir === 'asc' ? result : -result;
}

function sortBy(items, getValue, dir) {
    return [...items].sort((a, b) => compareSortValues(getValue(a), getValue(b), dir));
}

function parseClockMinutes(value) {
    if (!value || value === '-' || value === '—') return -1;
    const match = String(value).match(/^(\d{1,2}):(\d{2})/);
    if (!match) return 0;
    return parseInt(match[1], 10) * 60 + parseInt(match[2], 10);
}

function parseDurationSeconds(value) {
    if (!value || value === '-' || value === '—' || value === 'In Progress') return -1;
    let seconds = 0;
    const hours = String(value).match(/(\d+)h/);
    const minutes = String(value).match(/(\d+)m/);
    const secs = String(value).match(/(\d+)s/);
    if (hours) seconds += parseInt(hours[1], 10) * 3600;
    if (minutes) seconds += parseInt(minutes[1], 10) * 60;
    if (secs) seconds += parseInt(secs[1], 10);
    return seconds;
}

function updateSortableHeaders(tableEl, column, dir) {
    if (!tableEl) return;
    tableEl.querySelectorAll('th[data-sort]').forEach((th) => {
        const isActive = th.dataset.sort === column;
        th.classList.toggle('text-primary', isActive);
        th.classList.toggle('dark:text-white', isActive);
        const icon = th.querySelector('.sort-icon');
        if (icon) {
            icon.textContent = isActive ? (dir === 'asc' ? 'arrow_upward' : 'arrow_downward') : 'unfold_more';
            icon.classList.toggle('text-primary', isActive);
        }
    });
}

function bindSortableHeaders(tableEl, onSort) {
    if (!tableEl) return;
    tableEl.querySelectorAll('th[data-sort]').forEach((th) => {
        th.classList.add('cursor-pointer', 'select-none', 'hover:text-primary', 'transition-colors');
        th.addEventListener('click', (e) => {
            e.stopPropagation();
            onSort(th.dataset.sort);
        });
    });
}

function toggleSortState(state, column, defaultDir) {
    if (state.column === column) {
        state.dir = state.dir === 'asc' ? 'desc' : 'asc';
    } else {
        state.column = column;
        state.dir = defaultDir || 'asc';
    }
    return state;
}

function sortableHeader(label, column, extraClass) {
    return `<th data-sort="${column}" class="${extraClass || ''}">
        <span class="inline-flex items-center gap-0.5">${label}
            <span class="material-symbols-outlined sort-icon text-sm opacity-70">unfold_more</span>
        </span>
    </th>`;
}
