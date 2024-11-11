<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Support\Observers\NestableModelObserver;

trait NestableTrait
{
    public static function bootNestableTrait()
    {
        static::observe(new NestableModelObserver);
    }

    public function parent(): BelongsTo
    {
        $relationship = $this->belongsTo(static::class, $this->getNestableParentIdName());

        if (in_array(BelongsToNestableTree::class, class_uses(static::class))) {
            return $relationship->withoutGlobalScope('nestableTreeDetail');
        }

        return $relationship;
    }

    public function children(): HasMany
    {
        $relationship = $this->hasMany(static::class, $this->getNestableParentIdName());

        if (in_array(BelongsToNestableTree::class, class_uses(static::class))) {
            return $relationship->withoutGlobalScope('nestableTreeDetail');
        }

        return $relationship;
    }

    //region Scopes
    public function scopeWhereIsRoot($query, bool $condition = true)
    {
        return $query->where($this->getQualifiedNestableParentIdColumn(), ($condition ? '=' : '!='), $this->getNestableRootValue());
    }

    public function scopeWhereIsLeaf($query)
    {
        return $query->whereDoesntHave('children');
    }

    public function scopeWhereParent($query, $parentId)
    {
        if (is_null($parentId)) {
            return $query->whereNull($this->getNestableParentIdName());
        }

        return $query->where($this->getQualifiedNestableParentIdColumn(), $parentId);
    }
    //endregion Scopes

    public function descendants(): Collection
    {
        $this->loadMissing('children');

        return $this->children->flatMap(function ($child) {
            return $child->descendants();
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
     * @param  bool  $save  Indicates whether to save the instance after setting it as root. Default is true.
     */
    public function asRoot($save = true)
    {
        return $this->setParentNode($this->getNestableRootValue(), $save);
    }

    /**
     * Sets the parent node for the current node.
     *
     * @param  Model|string|int|null  $parent  The parent node to set.
     * @param  bool  $save  Whether to save the changes immediately. Default is true.
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
