<?php

namespace SolutionForest\InspireCms\Support\TreeNode\ModelExplorer\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasModelItems
{
    protected ?Closure $modifyQueryUsing = null;

    protected ?Closure $determineItemTitleUsing = null;

    protected ?Closure $determineItemDescriptionUsing = null;

    protected ?Closure $determineItemIconUsing = null;

    protected ?Closure $determineItemUrlUsing = null;

    protected ?Closure $determineItemDepthUsing = null;

    protected ?Closure $determineItemHasChildrenUsing = null;

    protected ?Closure $determineItemIsDisabledUsing = null;

    protected ?Closure $resolveRecordUsing = null;

    protected ?Closure $resolveItemKeyUsing = null;

    protected ?Closure $mutuateRootNodeItemsUsing = null;

    protected ?Closure $mutuateNodeItemsUsing = null;

    /**
     * @return Collection<int, Model>
     */
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

    /**
     * @param string|int $parentKey
     * @return Collection<int, Model>
     */
    public function getChildren(string | int $parentKey)
    {
        $query = $this->getModelExplorerQuery();

        $parentColumnName = $this->getParentColumnName();

        $query->where($parentColumnName, $parentKey);

        return $query->get();
    }

    /**
     * @phpstan-ignore-next-line missingType.generic
     * @return Builder
     *
     * @throws \Exception
     */
    public function getModelExplorerQuery()
    {
        $model = $this->getModel();

        if (empty($model)) {
            throw new \Exception('Model not configured: Please set up the model for the ModelExplorer.');
        }

        if (is_null($this->determineItemTitleUsing)) {
            throw new \Exception('Record label not configured: Please set up the record label for the ModelExplorer.');
        }

        if (is_null($this->determineItemHasChildrenUsing)) {
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

    public function determineItemTitleUsing(Closure $callback): static
    {
        $this->determineItemTitleUsing = $callback;

        return $this;
    }

    public function determineItemDescriptionUsing(Closure $callback): static
    {
        $this->determineItemDescriptionUsing = $callback;

        return $this;
    }

    public function determineItemHasChildrenUsing(Closure $callback): static
    {
        $this->determineItemHasChildrenUsing = $callback;

        return $this;
    }

    public function determineItemIconUsing(Closure $callback): static
    {
        $this->determineItemIconUsing = $callback;

        return $this;
    }

    public function determineItemUrlUsing(Closure $callback): static
    {
        $this->determineItemUrlUsing = $callback;

        return $this;
    }

    public function determineItemDepthUsing(Closure $callback): static
    {
        $this->determineItemDepthUsing = $callback;

        return $this;
    }

    public function determineItemIsDisabledUsing(Closure $callback): static
    {
        $this->determineItemIsDisabledUsing = $callback;

        return $this;
    }

    public function resolveRecordUsing(Closure $callback): static
    {
        $this->resolveRecordUsing = $callback;

        return $this;
    }

    public function resolveItemKeyUsing(Closure $callback): static
    {
        $this->resolveItemKeyUsing = $callback;

        return $this;
    }

    public function mutuateRootNodeItemsUsing(Closure $callback): static
    {
        $this->mutuateRootNodeItemsUsing = $callback;

        return $this;
    }

    public function mutuateNodeItemsUsing(Closure $callback): static
    {
        $this->mutuateNodeItemsUsing = $callback;

        return $this;
    }

    /**
     * @return Collection<int, Model>
     */
    public function getRecordsFrom(string | int | null $parentKey)
    {
        return $parentKey === null ? $this->getRootItems() : $this->getChildren($parentKey);
    }

    /**
     * @param array<string, mixed> $items
     * @return array<string, mixed>
     */
    public function mutuateRootNodeItems(array $items): array
    {
        if ($this->mutuateRootNodeItemsUsing) {
            return $this->evaluate($this->mutuateRootNodeItemsUsing, [
                'items' => $items,
            ]);
        }

        return $items;
    }

    /**
     * @param  string|int|array  $key
     * @return null|Model|Collection<int, Model>
     */
    public function findRecord($key)
    {
        $query = $this->getModelExplorerQuery();

        if ($this->resolveRecordUsing) {
            return $this->evaluate($this->resolveRecordUsing, [
                'key' => $key,
                'query' => $query,
            ]);
        }

        return $query->find($key);
    }

    /**
     * @param  Collection<int, Model>  $records
     * @return Collection<int, array>
     */
    public function parseAsItems($records, string | int $parentKey): Collection
    {
        return collect($records)->map(function ($record) use ($parentKey): array {

            $itemParentKey = $this->evaluate($this->determineRecordParentIdUsing, [
                'record' => $record,
            ]) ?? $record->{$this->getParentColumnName()} ?? $parentKey;

            $item = [
                'key' => $record->getKey(),

                'parentKey' => $itemParentKey,

                'title' => $this->evaluate($this->determineItemTitleUsing, [
                    'record' => $record,
                ]),

                'description' => $this->evaluate($this->determineItemDescriptionUsing, [
                    'record' => $record,
                ]),

                'hasChildren' => $this->evaluate($this->determineItemHasChildrenUsing, [
                    'record' => $record,
                ]),

                'depth' => $this->evaluate($this->determineItemDepthUsing, [
                    'record' => $record,
                    'parentKey' => $itemParentKey,
                ]) ?? 0,

                'icon' => $this->evaluate($this->determineItemIconUsing, [
                    'record' => $record,
                ]),

                'link' => $this->evaluate($this->determineItemUrlUsing, [
                    'record' => $record,
                ]),

                'isDisabled' => $this->evaluate($this->determineItemIsDisabledUsing, [
                    'record' => $record,
                ]) ?? false,
            ];

            if ($this->mutuateNodeItemsUsing) {
                $item = $this->evaluate($this->mutuateNodeItemsUsing, [
                    'item' => $item,
                    'record' => $record,
                ]);
            }

            return $item;
        });
    }

    /**
     * @param array<string,mixed> $item
     * @return mixed
     */
    public function getNodeItemKey(array $item): mixed
    {
        $result = data_get($item, 'key');

        if ($this->resolveItemKeyUsing) {
            $result = $this->evaluate($this->resolveItemKeyUsing, [
                'item' => $item,
                'key' => $result,
            ]);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function getNodeItemArguments(array $item): array
    {
        return Arr::only($item, ['key', 'parentKey', 'hasChildren', 'depth']);
    }

    /**
     * @param array<string,mixed> $item
     * @param ?string $locale
     * @return ?string
     */
    public function getTitleForItem(array $item, ?string $locale = null): ?string
    {
        $title = $item['title'] ?? null;

        if (filled($locale) && is_array($title)) {
            $title = $title[$locale] ?? $item['fallbackTitle'] ?? null;
        }

        if (is_array($title)) {
            $title = Arr::first($title);
        }

        return $title;
    }
}
