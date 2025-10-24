<?php

namespace SolutionForest\InspireCms\Support\Diff;

class Diff
{
    public $old;

    public $new;

    public function __construct($old, $new)
    {
        $this->old = $old;
        $this->new = $new;
    }

    public function __toString()
    {
        // Properly escape the values for JavaScript to prevent syntax errors
        $oldValue = json_encode($this->old ?? '');
        $newValue = json_encode($this->new ?? '');

        // Escape for HTML attribute - use htmlspecialchars with ENT_QUOTES
        $oldValueEscaped = htmlspecialchars($oldValue, ENT_QUOTES, 'UTF-8');
        $newValueEscaped = htmlspecialchars($newValue, ENT_QUOTES, 'UTF-8');

        $html = <<<HTML
            <div x-data="diffChecker({ oldValue: {$oldValueEscaped}, newValue: {$newValueEscaped} })" class="diff-viewer">
                <div class="diff-line" x-html="getInlineDiff()"></div>
            </div>
        HTML;

        return $html;
    }
}
