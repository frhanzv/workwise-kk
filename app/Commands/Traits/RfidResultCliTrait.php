<?php

namespace App\Commands\Traits;

use CodeIgniter\CLI\CLI;

trait RfidResultCliTrait
{
    protected function resolveRfidEntity(array $result): array
    {
        if (isset($result['product'])) {
            $name = $result['product']['name'] ?? 'Unknown Product';
            $id   = $result['product']['code'] ?? (string) ($result['product']['id'] ?? '');

            return [
                'label'  => 'PRODUCT',
                'name'   => $name,
                'id'     => $id,
                'suffix' => ' (' . $name . ')',
            ];
        }

        if (isset($result['raw_material'])) {
            $name = $result['raw_material']['name'] ?? 'Unknown Material';
            $id   = $result['raw_material']['code'] ?? (string) ($result['raw_material']['id'] ?? '');

            return [
                'label'  => 'RAW MATERIAL',
                'name'   => $name,
                'id'     => $id,
                'suffix' => ' (' . $name . ')',
            ];
        }

        if (isset($result['worker'])) {
            $name = $result['worker']['name'] ?? 'Unknown Worker';

            return [
                'label'  => 'WORKER',
                'name'   => $name,
                'id'     => (string) ($result['worker']['id'] ?? ''),
                'suffix' => ' (' . $name . ')',
            ];
        }

        return [
            'label'  => 'TAG',
            'name'   => '',
            'id'     => '',
            'suffix' => '',
        ];
    }

    protected function formatRfidAction(string $action): string
    {
        return match ($action) {
            'checkin'       => 'CHECK IN',
            'checkout'      => 'CHECK OUT',
            'zone_transfer' => 'ZONE TRANSFER',
            'present'       => 'STILL IN ZONE',
            'duplicate'         => 'DUPLICATE',
            'denied'            => 'DENIED',
            'location_mismatch' => 'LOCATION MISMATCH',
            default             => strtoupper(str_replace('_', ' ', $action)),
        };
    }

    protected function writeRfidResultDetails(array $result, array $entity): void
    {
        if (!empty($result['success'])) {
            $action = $this->formatRfidAction($result['action'] ?? 'unknown');
            $color  = match ($result['action'] ?? '') {
                'present'           => 'yellow',
                'location_mismatch' => 'light_red',
                default             => 'light_green',
            };

            CLI::write("  ✓ {$action}: [{$entity['label']}] {$entity['name']} ({$entity['id']})", $color);

            if (!empty($result['previous_zone']['name'])) {
                CLI::write('  Left: ' . $result['previous_zone']['name'], 'cyan');
            }
            if (!empty($result['zone']['name'])) {
                CLI::write('  Zone: ' . $result['zone']['name'], 'cyan');
            }
            if (!empty($result['time'])) {
                CLI::write('  Time: ' . $result['time'], 'cyan');
            }
            if (!empty($result['duration'])) {
                CLI::write('  Duration: ' . $result['duration'], 'cyan');
            }

            return;
        }

        $prefix = $entity['name'] !== ''
            ? "[{$entity['label']}] {$entity['name']}: "
            : '';

        CLI::error('  ✗ ' . $prefix . ($result['message'] ?? 'Unknown error'));
    }
}
