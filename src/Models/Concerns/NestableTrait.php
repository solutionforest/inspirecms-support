<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
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
            if (blank($model->{$model->getNestableParentIdName()}) && ! is_null($model->getNestableRootValue())) {
                $model->{$model->getNestableParentIdName()} = $model->getNestableRootValue();
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
        return $this->belongsTo(static::class, $this->getNestableParentIdName());
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getNestableParentIdName());
    }

    //region Scopes
    public function scopeWhereIsRoot($query, $condition)
    {
        return $query->where($this->getQualifiedNestableParentIdColumn(), ($condition ? '=' : '!='), $this->getNestableRootValue());
    }

    public function scopeWhereIsLeaf($query)
    {
        return $query->whereDoesntHave('children');
    }

    public function scopeWhereParent($query, $parentId)
    {
        return $query->where($this->getQualifiedNestableParentIdColumn(), $parentId);
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
        return $this->{$this->getNestableParentIdName()} === $this->getNestableRootValue();
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    public function getNestableParentIdName(): string
    {
        return 'parent_id';
    }

    public function getQualifiedNestableParentIdColumn(): string
    {
        return $this->qualifyColumn($this->getNestableParentIdName());
    }

    public function getNestableRootValue(): int | string | null
    {
        return 0;
    }

    /**
     * Get the ID of the parent.
     *
     * @return int|string|null The ID of the parent, which can be an integer, a string, or null if there is no parent.
     */
    public function getParentId(): int | string | null
    {
        return $this->{$this->getNestableParentIdName()};
    }

    public function getFallbackParentId(): int | string | null
    {
        return $this->getNestableRootValue();
    }

    /**
     * Set the current instance as the root node.
     *
     * @param bool $save Indicates whether to save the instance after setting it as root. Default is true.
     */
    public function asRoot($save = true)
    {
        return $this->setParentNode($this->getNestableRootValue(), $save);
    }

    /**
     * Sets the parent node for the current node.
     *
     * @param Model|string|int|null $parent The parent node to set.
     * @param bool $save Whether to save the changes immediately. Default is true.
     */
    public function setParentNode($parent, $save = true)
    {
        $parentKey = $parent instanceof Model ? $parent?->getKey() : $parent;
        $this->{$this->getNestableParentIdName()} = $parentKey ?? $this->getFallbackParentId();

        if ($save) {
            $this->save();
        }

        return $this;
    }
}
