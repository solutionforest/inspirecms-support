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
        $html = <<<Html
            <div x-data="diffChecker({ oldValue: '{$this->old}', newValue: '{$this->new}' })" class="diff-viewer">
                <div class="diff-line" x-html="getInlineDiff()"></div>
            </div>
        Html;

        return str($html)->toHtmlString();
    }
}
