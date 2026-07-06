<?php
/** @var string $qr_code */
/** @var string $itemLabel e.g. Product */
?>
<div class="bg-white dark:bg-background-dark rounded-xl border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-base text-indigo-500">qr_code_2</span>
        <?= esc($itemLabel) ?> QR Code
    </h2>
    <?php if (!empty($qr_code)): ?>
        <div class="flex flex-col items-center gap-4">
            <div id="qr-code-canvas" class="p-3 bg-white rounded-lg border border-gray-200"></div>
            <p class="text-xs font-mono text-gray-600 dark:text-gray-300 break-all text-center"><?= esc($qr_code) ?></p>
            <button type="button" onclick="downloadQrCode()" class="text-xs font-semibold text-primary hover:underline">Download QR image</button>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
        <script>
        (function() {
            const text = <?= json_encode($qr_code) ?>;
            const el = document.getElementById('qr-code-canvas');
            if (!el || typeof QRCode === 'undefined') return;
            el.innerHTML = '';
            const qr = new QRCode(el, { text, width: 160, height: 160, correctLevel: QRCode.CorrectLevel.M });
            window.downloadQrCode = function() {
                const img = el.querySelector('img');
                if (!img) return;
                const a = document.createElement('a');
                a.href = img.src;
                a.download = 'qr-<?= preg_replace('/[^a-zA-Z0-9_-]/', '_', $qr_code) ?>.png';
                a.click();
            };
        })();
        </script>
    <?php else: ?>
        <p class="text-sm text-gray-500 dark:text-gray-400">QR code will be generated when the item is saved.</p>
    <?php endif; ?>
</div>
