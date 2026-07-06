<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Floating widget settings (Find Stock, Analytics Assistant).
 */
class Widgets extends BaseConfig
{
    /** Allow dragging floating action buttons on screen */
    public bool $floatingButtonsMoveable = true;

    /** Allow dragging open panels by their header */
    public bool $panelsMoveable = true;

    /** Floating button size: sm | md | lg */
    public string $floatingButtonSize = 'md';

    /** Panel size preset: sm | md | lg */
    public string $panelSize = 'md';

    /**
     * @return array{btn: string, icon: string, text: string, dot: string, svg: string}
     */
    public function floatingButtonClasses(): array
    {
        return match ($this->floatingButtonSize) {
            'sm' => [
                'btn' => 'px-4 py-2.5 gap-2',
                'icon' => 'text-lg',
                'text' => 'text-xs',
                'dot' => 'h-2 w-2',
                'svg' => 'w-5 h-5',
            ],
            'lg' => [
                'btn' => 'px-7 py-4 gap-4',
                'icon' => 'text-2xl',
                'text' => 'text-base',
                'dot' => 'h-3.5 w-3.5',
                'svg' => 'w-7 h-7',
            ],
            default => [
                'btn' => 'px-5 py-3.5 gap-3',
                'icon' => 'text-xl',
                'text' => 'text-sm',
                'dot' => 'h-2.5 w-2.5',
                'svg' => 'w-6 h-6',
            ],
        };
    }

    /**
     * @return array{width: string, maxHeight: string, analyticsWidth: string, analyticsHeight: string}
     */
    public function panelDimensions(): array
    {
        return match ($this->panelSize) {
            'sm' => [
                'width' => '320px',
                'maxHeight' => '520px',
                'analyticsWidth' => '300px',
                'analyticsHeight' => '420px',
            ],
            'lg' => [
                'width' => '480px',
                'maxHeight' => '720px',
                'analyticsWidth' => '440px',
                'analyticsHeight' => '640px',
            ],
            default => [
                'width' => '420px',
                'maxHeight' => '640px',
                'analyticsWidth' => '400px',
                'analyticsHeight' => '600px',
            ],
        };
    }
}
