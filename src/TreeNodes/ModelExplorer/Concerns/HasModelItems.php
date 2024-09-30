<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasModelItems
{
    protected ?Closure $modifyQueryUsing = null;

    protected ?Closure $determineRecordLabelUsing = null;

    protected ?Closure $determineRecordHasChildrenUsing = null;

    public function getRootItems()
    {
        $query = $this->getModelExplorerQuery();

        $rootKey = $this->getRootLevelKey();
        $parentColumnName = $this->getParentColumnName();

        if ($rootKey) {
            $query->where($parentColumnName, $rootKey);
        } else {
            $query->whereNull($parentColumnName);
        }

        return $query->get();
    }

    public function getChildren(string | int $parentKey)
    {
        $query = $this->getModelExplorerQuery();

        $parentColumnName = $this->getParentColumnName();

        $query->where($parentColumnName, $parentKey);

        return $query->get();
    }

    /**
     * @return Builder
     *
     * @throws \Exception
     */
    protected function getModelExplorerQuery()
    {
        $model = $this->getModel();

        if (empty($model)) {
            throw new \Exception('Model not configured: Please set up the model for the ModelExplorer.');
        }

        if (is_null($this->determineRecordLabelUsing)) {
            throw new \Exception('Record label not configured: Please set up the record label for the ModelExplorer.');
        }

        if (is_null($this->determineRecordHasChildrenUsing)) {
            throw new \Exception('Record has children not configured: Please set up the record has children for the ModelExplorer.');
        }

        $query = $model::query();

        if ($this->modifyQueryUsing) {
            $query = $this->evaluate($this->modifyQueryUsing, [
                'query' => $query,
            ]);
        }

        return $query;
    }

    public function modifyQueryUsing(Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function determineRecordLabelUsing(Closure $callback): static
    {
        $this->determineRecordLabelUsing = $callback;

        return $this;
    }

    public function determineRecordHasChildrenUsing(Closure $callback): static
    {
        $this->determineRecordHasChildrenUsing = $callback;

        return $this;
    }

    public function getRecordsFrom(string | int | null $parentKey): Collection
    {
        return $parentKey === null ? $this->getRootItems() : $this->getChildren($parentKey);
    }

    public function findRecord(string | int $key): ?Model
    {
        return $this->getModelExplorerQuery()->find($key);
    }

    /**
     * @param  Collection<Model>  $records
     */
    public function parseAsItems($records, int $depth = 0): Collection
    {
        return collect($records)->map(function ($record) use ($depth): array {
            return [
                'key' => $record->getKey(),
                'parentKey' => $record->{$this->getParentColumnName()},
                'label' => $this->evaluate($this->determineRecordLabelUsing, [
                    'record' => $record,
                ]),
                'hasChildren' => $this->evaluate($this->determineRecordHasChildrenUsing, [
                    'record' => $record,
                ]),
                'depth' => $depth,
            ];
        });
    }
}
