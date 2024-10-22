<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait NestableTrait
{
    public static function bootNestableTrait()
    {
        // static::addGlobalScope('appendLevel', function ($builder) {
        //     $builder->addSelect(['level' => static::selectRaw('COUNT(*) - 1')
        //         ->from('model_nestables as ancestors')
        //         ->whereRaw('ancestors.id = model_nestables.id OR ancestors.id = model_nestables.parent_id')
        //         ->whereRaw('ancestors.id = model_nestables.id OR EXISTS (
        //             SELECT 1 FROM model_nestables as descendants
        //             WHERE descendants.id = model_nestables.id
        //             AND ancestors.id = descendants.parent_id
        //         )')
        //         ->groupBy('ancestors.id')
        //     ]);
        // });

        static::creating(function (self $model) {
            //region Set the parent ID to the fallback parent ID if it is blank
            if (blank($model->{$model->getNestableParentIdColumn()}) && ! is_null($model->getNestableRootValue())) {
                $model->{$model->getNestableParentIdColumn()} = $model->getNestableRootValue();
            }
            //endregion
        });
        static::deleting(function (self $model) {
            $model->children()->delete();
        });
        if (method_exists(static::class, 'forceDeleting')) {
            static::forceDeleting(function (self $model) {
                $model->children()->forceDelete();
            });
        }
        if (method_exists(static::class, 'restoring')) {
            static::restoring(function (self $model) {
                $model->parent()->restore();
            });
        }
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getNestableParentIdColumn());
    }

    public function withTrashedParent(): BelongsTo
    {
        return $this->parent()->withTrashed();
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getNestableParentIdColumn());
    }

    //region Scopes
    public function scopeRoot($query)
    {
        return $query->where($this->getNestableParentIdColumn(), $this->getNestableRootValue());
    }

    public function scopeLeaf($query)
    {
        return $query->whereDoesntHave('children');
    }

    public function scopeParent($query, $parentId)
    {
        return $query->where($this->getNestableParentIdColumn(), $parentId);
    }
    //endregion Scopes

    public function descendants(): Collection
    {
        return $this->children->flatMap(function ($child) {
            return $child->descendants()->prepend($child);
        })->prepend($this);
    }

    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current !== null) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function ancestorsAndSelf(): Collection
    {
        return $this->ancestors()->prepend($this);
    }

    public function getLevelAttribute(): int
    {
        return $this->getLevel();
    }

    public function getLevel(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    public function isRoot(): bool
    {
        return $this->{$this->getNestableParentIdColumn()} === $this->getNestableRootValue();
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Get the name of 'parent id' column.
     *
     * @return ?string
     */
    protected function getNestableParentIdColumn()
    {
        return 'parent_id';
    }

    public function getNestableRootValue(): int | string
    {
        return 0;
    }
}
