<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\InspireCms\Support\Facades\InspireCmsSupport;

/**
 * This trait provides functionality for models to belong to a NestableTree,
 * allowing them to be organized in a hierarchical structure.
 *
 * It manages the relationship with the NestableTree model and provides methods
 * for creating, updating, and managing the hierarchical structure.
 */
trait BelongToNestableTree
{
    protected bool $updateTreeOrder = false;

    public static function bootBelongToNestableTree()
    {
        static::created(function ($model) {
            $model->createOrUpdateNode();
        });

        static::saved(function ($model) {
            $model->createOrUpdateNode();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::forceDeleting(function ($model) {
                $model->nestableTree()?->delete();
            });
        } else {
            static::deleting(function ($model) {
                $model->nestableTree()?->delete();
            });
        }
    }

    public function nestableTree(): MorphOne
    {
        return $this->morphOne(InspireCmsSupport::getNestableTreeModel(), 'nestable');
    }

    protected function createOrUpdateNode()
    {
        $nodeData = $this->getTreeData();

        if ($this->nestableTree) {
            $this->nestableTree->update($nodeData);
        } else {
            $this->nestableTree()->create($nodeData);
        }

        $this->load('nestableTree');

        $this->updateSiblingsSort();
    }

    public function getTreeData(): array
    {
        $column = method_exists($this, 'getNestableParentIdColumn') ? $this->getNestableParentIdColumn() : 'parent_id';

        $data = [
            $column => $this->getParentId() ?? $this->fallbackParentId(),
            // Add any other fields that should be stored in the Node model
        ];

        if ($this->updateTreeOrder) {
            $data['order'] = $this->calculateOrder();
        }

        return $data;
    }

    public function getParentId(): string | int | null
    {
        $column = method_exists($this, 'getNestableParentIdColumn') ? $this->getNestableParentIdColumn() : 'parent_id';

        return $this->{$column} ?? $this->fallbackParentId();
    }

    protected function calculateOrder(): int
    {
        try {

            $nestableTreeClass = InspireCmsSupport::getNestableTreeModel();

            $parentId = $this->getParentId() ?? $this->fallbackParentId();

            $maxOrder = $nestableTreeClass::query()
                ->parent($parentId)
                ->when($this->nestableTree, fn ($q) => $q->where('id', '!=', $this->nestableTree->id))
                ->max('order');

            return $maxOrder !== null ? $maxOrder + 1 : $this->fallbackSort();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function updateSiblingsSort()
    {
        // Check `nestableTree` relationship is loaded
        if (! $this->relationLoaded('nestableTree')) {
            $this->load('nestableTree');
        }

        if (is_null($this->nestableTree)) {
            return;
        }

        try {

            $nestableTreeClass = InspireCmsSupport::getNestableTreeModel();

            $parentId = $this->getParentId() ?? $this->fallbackParentId();

            $siblings = $nestableTreeClass::query()
                ->parent($parentId)
                ->when($this->nestableTree, fn ($q) => $q->where('id', '!=', $this->nestableTree->id ?? null))
                ->orderBy('order')
                ->get();

        } catch (\Exception $e) {

            // Throw exception that have problem in getSortQuery
            throw new \Exception('Have error on \'' . __METHOD__ . '\'. Please check you table columns or the method \'getParentId\'.', $e->getCode(), $e);
        }

        foreach ($siblings as $index => $sibling) {
            if ($sibling->order >= $this->nestableTree->order && $sibling->id != $this->nestableTree->id) {
                $sibling->update(['order' => $index + $this->nestableTree->order + 1]);
            } else {
                $sibling->update(['order' => $index]);
            }
        }
    }

    protected function fallbackParentId()
    {
        return 0;
    }

    protected function fallbackSort()
    {
        return 1;
    }

    public function scopeSorted($query, string $direction = 'asc')
    {
        $query
            ->joinRelationship('nestableTree')
            ->orderByPowerJoins('nestableTree.order', $direction);
    }
}
