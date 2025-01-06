<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;

class ItemBulkAction extends Action
{
    protected EloquentCollection | Collection | array | Closure | null $records = null;

    public function getLivewireClickHandler(): ?string
    {
        if (! $this->isLivewireClickHandlerEnabled()) {
            return null;
        }

        if (is_string($this->action)) {
            return $this->action;
        }

        if ($event = $this->getLivewireEventClickHandler()) {
            return $event;
        }

        return $this->generateJavaScriptClickHandler('mountMediaLibraryItemAction') ?? parent::getLivewireClickHandler();
    }

    protected function generateJavaScriptClickHandler(string $method): ?string
    {
        if ($selectedRecords = $this->getRecords()) {
            $recordKeys = Js::from(collect($selectedRecords)->map(fn ($record) => $record instanceof Model ? $record->getKey() : $record)->all());
            return "{$method}('{$this->getName()}', {$recordKeys})";
        }

        return null;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'records' => [$this->getRecords()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        return match ($parameterType) {
            EloquentCollection::class, Collection::class => [$this->getRecords()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }

    public function records(EloquentCollection | Collection | array | Closure | null $records): static
    {
        $this->records = $records;

        return $this;
    }

    public function getRecords(): EloquentCollection | Collection | array | null
    {
        return $this->records = $this->evaluate($this->records);
    }
}
