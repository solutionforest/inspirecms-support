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
            'danger' => Color::generateV3Palette('#f44336'),
            'gray' => Color::generateV3Palette('#5e5e5e'),
            'info' => Color::generateV3Palette('#88B0BA'),
            'primary' => Color::generateV3Palette('#B5834A'),
            'secondary' => Color::generateV3Palette('#bfa15a'),
            'success' => Color::generateV3Palette('#76ae51'),
            'warning' => Color::generateV3Palette('#f39e19'),
        ];
    }
}
