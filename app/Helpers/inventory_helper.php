<?php

if (! function_exists('normalize_inventory_qty')) {
    /**
     * Round to 3 decimals and snap floating-point dust to zero.
     */
    function normalize_inventory_qty(float $qty): float
    {
        $qty = round($qty, 3);

        if (abs($qty) < 0.0005) {
            return 0.0;
        }

        return $qty;
    }
}

if (! function_exists('format_inventory_qty')) {
    /**
     * Format stock quantity: whole numbers without decimals (0, 5, 10),
     * fractional values up to 3 decimal places (5.5, 10.25).
     */
    function format_inventory_qty($qty): string
    {
        $qty = normalize_inventory_qty((float) $qty);

        if (abs($qty - round($qty)) < 0.0000001) {
            return (string) (int) round($qty);
        }

        return rtrim(rtrim(sprintf('%.3f', $qty), '0'), '.');
    }
}
