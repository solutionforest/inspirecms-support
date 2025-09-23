<?php

namespace SolutionForest\InspireCms\Support\TreeNode\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait CanCacheRecords
{
    protected array $nodeRecordsCache = []; // Cache model records by node ID

    protected static ?string $model = null;

    protected function cacheRecordAppend($record, $key = null)
    {
        $key = $key ?? $record->getKey();
        if (! isset($this->nodeRecordsCache[$key])) {
            $this->nodeRecordsCache[$key] = $record;
        }
    }

    protected function retrieveRecordById($recordKey)
    {
        if (isset($this->nodeRecordsCache[$recordKey])) {
            return $this->nodeRecordsCache[$recordKey];
        }

        $record = $this->getElquentQuery()->find($recordKey);

        $this->cacheRecordAppend($record);

        return $record;
    }

    /**
     * Clear all caches - useful when data changes
     */
    public function clearRecordCaches(): void
    {
        $this->nodeRecordsCache = [];
    }

    /**
     * Clear cache for specific nodes
     */
    public function clearNodeRecordCache(array $nodeIds): void
    {
        // Clear record cache for specific nodes
        foreach ($nodeIds as $nodeId) {
            unset($this->nodeRecordsCache[$nodeId]);
        }
    }

    public function refreshTree(): void
    {
        $this->clearRecordCaches(); // Clear record caches when refreshing
        parent::refreshTree();
    }

    /**
     * @return class-string<Model>
     */
    protected function getModel()
    {
        if (empty(static::$model)) {
            throw new \Exception('Model class not defined in TreeNode component.');
        }

        return static::$model;
    }

    protected function getElquentQuery(): Builder
    {
        return $this->getModel()::query();
    }
}
