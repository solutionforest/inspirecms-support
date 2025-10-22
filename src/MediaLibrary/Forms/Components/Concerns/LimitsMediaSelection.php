<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\Concerns;

use Closure;

trait LimitsMediaSelection
{
    protected int | Closure | null $max = null;

    protected int | Closure | null $min = null;

    public function max(int | Closure | null $max): static
    {
        $this->max = $max;

        $this->rule('array');
        $this->rule(static function (self $component): string {
            $max = $component->getMax();

            return "max:{$max}";
        });

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->evaluate($this->max);
    }

    public function min(int | Closure | null $min): static
    {
        $this->min = $min;
        $this->rule('array');
        $this->rule(static function (self $component): string {
            $min = $component->getMin();

            return "min:{$min}";
        });

        return $this;
    }

    public function getMin(): ?int
    {
        return $this->evaluate($this->min);
    }
}
