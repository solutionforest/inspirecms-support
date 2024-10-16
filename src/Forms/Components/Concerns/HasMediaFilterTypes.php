<?php

namespace SolutionForest\InspireCms\Support\Forms\Components\Concerns;

trait HasMediaFilterTypes
{
    protected array $filterTypes = [];

    public function image(): static
    {
        return $this->filterTypes(['image']);
    }

    public function video(): static
    {
        return $this->filterTypes(['video']);
    }

    public function audio(): static
    {
        return $this->filterTypes(['audio']);
    }

    public function document(): static
    {
        return $this->filterTypes(['document']);
    }

    public function archive(): static
    {
        return $this->filterTypes(['archive']);
    }

    public function filterTypes(array $types, bool $merge = false): static
    {
        if ($merge) {
            $this->filterTypes = array_merge($this->filterTypes, $types);
        } else {
            $this->filterTypes = $types;
        }

        return $this;
    }

    public function getFilterTypes(): array
    {
        return $this->filterTypes;
    }
}
