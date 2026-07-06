/**
 * Draggable floating widgets — positions persist in localStorage.
 */
(function () {
    const cfg = window.WW_WIDGET_CONFIG || {};
    const STORAGE_KEY = 'ww-widget-positions';
    const DRAG_THRESHOLD = 8;

    function getPositions() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function savePosition(id, left, top) {
        const positions = getPositions();
        positions[id] = { left, top };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(positions));
    }

    function clientPoint(e) {
        if (e.touches && e.touches.length) {
            return { x: e.touches[0].clientX, y: e.touches[0].clientY };
        }
        return { x: e.clientX, y: e.clientY };
    }

    function constrain(left, top, width, height) {
        const maxLeft = Math.max(0, window.innerWidth - width);
        const maxTop = Math.max(0, window.innerHeight - height);
        return {
            left: Math.max(0, Math.min(left, maxLeft)),
            top: Math.max(0, Math.min(top, maxTop)),
        };
    }

    function applyPosition(el, left, top) {
        el.style.bottom = 'auto';
        el.style.right = 'auto';
        el.style.left = left + 'px';
        el.style.top = top + 'px';
    }

    function applySavedPosition(el) {
        const saved = getPositions()[el.id];
        if (!saved) {
            return;
        }
        applyPosition(el, saved.left, saved.top);
    }

    function makeDraggable(el, enabled, options) {
        options = options || {};
        if (!el || !enabled) {
            return;
        }

        el.classList.add('ww-floating-moveable');

        let dragging = false;
        let moved = false;
        let offsetX = 0;
        let offsetY = 0;
        let startX = 0;
        let startY = 0;

        function onStart(e) {
            if (e.type === 'mousedown' && e.button !== 0) {
                return;
            }
            if (e.target.closest('input, textarea, select, iframe')) {
                return;
            }
            if (!options.allowButtonDrag && e.target.closest('button, a')) {
                return;
            }

            const rect = el.getBoundingClientRect();
            const point = clientPoint(e);
            dragging = true;
            moved = false;
            startX = point.x;
            startY = point.y;
            offsetX = point.x - rect.left;
            offsetY = point.y - rect.top;
            applyPosition(el, rect.left, rect.top);
            el.style.transition = 'none';
        }

        function onMove(e) {
            if (!dragging) {
                return;
            }

            const point = clientPoint(e);
            const deltaX = Math.abs(point.x - startX);
            const deltaY = Math.abs(point.y - startY);

            if (!moved && deltaX < DRAG_THRESHOLD && deltaY < DRAG_THRESHOLD) {
                return;
            }

            moved = true;
            el.dataset.wasDragged = '1';
            e.preventDefault();

            const rect = el.getBoundingClientRect();
            const next = constrain(point.x - offsetX, point.y - offsetY, rect.width, rect.height);
            applyPosition(el, next.left, next.top);
        }

        function onEnd() {
            if (!dragging) {
                return;
            }

            dragging = false;
            el.style.transition = '';

            if (moved) {
                const rect = el.getBoundingClientRect();
                savePosition(el.id, rect.left, rect.top);
            }
        }

        el.addEventListener('mousedown', onStart);
        el.addEventListener('touchstart', onStart, { passive: false });
        document.addEventListener('mousemove', onMove);
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('mouseup', onEnd);
        document.addEventListener('touchend', onEnd);

        el.addEventListener('click', function (e) {
            if (el.dataset.wasDragged === '1') {
                e.preventDefault();
                e.stopImmediatePropagation();
                el.dataset.wasDragged = '0';
            }
        }, true);
    }

    function initFloatingButtons() {
        const enabled = cfg.floatingButtonsMoveable !== false;
        ['stock-finder-btn', 'analytics-floating-btn'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) {
                return;
            }
            applySavedPosition(el);
            makeDraggable(el, enabled, { allowButtonDrag: true });
        });
    }

    function initPanelDrag(panelId, handleSelector, options) {
        const enabled = cfg.panelsMoveable !== false;
        const panel = document.getElementById(panelId);
        if (!panel || !enabled) {
            return;
        }

        const handle = panel.querySelector(handleSelector);
        if (!handle) {
            return;
        }

        handle.classList.add('cursor-move');

        let dragging = false;
        let offsetX = 0;
        let offsetY = 0;

        function onStart(e) {
            if (options && options.isBlocked && options.isBlocked()) {
                return;
            }
            if (e.type === 'mousedown' && e.button !== 0) {
                return;
            }
            if (!e.target.closest(handleSelector)) {
                return;
            }
            if (e.target.closest('button, a, input')) {
                return;
            }

            const rect = panel.getBoundingClientRect();
            const point = clientPoint(e);
            dragging = true;
            offsetX = point.x - rect.left;
            offsetY = point.y - rect.top;
            applyPosition(panel, rect.left, rect.top);
            panel.style.transition = 'none';
            e.preventDefault();
        }

        function onMove(e) {
            if (!dragging) {
                return;
            }
            e.preventDefault();
            const point = clientPoint(e);
            const rect = panel.getBoundingClientRect();
            const next = constrain(point.x - offsetX, point.y - offsetY, rect.width, rect.height);
            applyPosition(panel, next.left, next.top);
        }

        function onEnd() {
            if (!dragging) {
                return;
            }
            dragging = false;
            panel.style.transition = '';
            const rect = panel.getBoundingClientRect();
            savePosition(panelId, rect.left, rect.top);
        }

        panel.addEventListener('mousedown', onStart);
        panel.addEventListener('touchstart', onStart, { passive: false });
        document.addEventListener('mousemove', onMove);
        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('mouseup', onEnd);
        document.addEventListener('touchend', onEnd);

        applySavedPosition(panel);
    }

    window.WWFloatingWidgets = {
        init: initFloatingButtons,
        initPanelDrag: initPanelDrag,
        applySavedPosition: applySavedPosition,
        resetPositions: function () {
            localStorage.removeItem(STORAGE_KEY);
        },
    };

    document.addEventListener('DOMContentLoaded', function () {
        initFloatingButtons();
    });

    const style = document.createElement('style');
    style.textContent = '.ww-floating-moveable { cursor: grab; } .ww-floating-moveable:active { cursor: grabbing; }';
    document.head.appendChild(style);
})();
