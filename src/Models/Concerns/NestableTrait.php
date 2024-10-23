<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

trait NestableTrait
{
    public static function bootNestableTrait()
    {
        static::creating(function (self $model) {
            //region Set the parent ID to the fallback parent ID if it is blank
            if (blank($model->{$model->getNestableParentIdColumn()}) && ! is_null($model->getNestableRootValue())) {
                $model->{$model->getNestableParentIdColumn()} = $model->getNestableRootValue();
            }
            //endregion
        });
        static::deleting(function (self $model) {
            $model->children()->each(function ($child) {
                $child->delete();
            });
        });
        
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::forceDeleting(function (self $model) {
                $model->children()->withTrashed()->each(function ($child) {
                    $child->forceDelete();
                });
            });
            static::restoring(function (self $model) {
                $model->parent()->restore();
            });
        }
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getNestableParentIdColumn());
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

    public function getNestableParentIdColumn(): string
    {
        return 'parent_id';
    }

    public function getNestableRootValue(): int | string
    {
        return 0;
    }
}
