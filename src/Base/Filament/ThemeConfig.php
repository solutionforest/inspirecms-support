<?php

namespace SolutionForest\InspireCms\Support\Base\Filament;

use Filament\Support\Colors\Color;

class ThemeConfig
{
    public static function fontFamily()
    {
        return 'DM Sans';
    }

    public static function colors()
    {
        return [
            'danger' => Color::hex('#f44336'),
            'gray' => Color::hex('#5e5e5e'),
            'info' => Color::hex('#88B0BA'),
            'primary' => Color::hex('#B5834A'),
            'secondary' => Color::hex('#bfa15a'),
            'success' => Color::hex('#76ae51'),
            'warning' => Color::hex('#f39e19'),
        ];
    }
}
