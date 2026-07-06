<?php

namespace App\Libraries;

/**
 * Short-lived queue of scans from LOOKUP antennas (Search Stock desk).
 * Warehouse IN/OUT readers never write here.
 */
class RfidLookupQueue
{
    private const CACHE_KEY = 'rfid_lookup_scans';
    private const MAX_ITEMS = 100;
    private const TTL       = 600;

    public static function push(array $scan): array
    {
        $entry = array_merge($scan, [
            'id' => uniqid('lookup_', true),
            'ts' => microtime(true),
        ]);

        $cache = cache();
        $items = $cache->get(self::CACHE_KEY);
        if (!is_array($items)) {
            $items = [];
        }

        array_unshift($items, $entry);
        $items = array_slice($items, 0, self::MAX_ITEMS);
        $cache->save(self::CACHE_KEY, $items, self::TTL);

        return $entry;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function since(float $sinceTs): array
    {
        $items = cache()->get(self::CACHE_KEY);
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(
            $items,
            static fn ($row) => (float) ($row['ts'] ?? 0) > $sinceTs
        ));
    }
}
