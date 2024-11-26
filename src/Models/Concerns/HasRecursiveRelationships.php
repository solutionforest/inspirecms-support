<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use SolutionForest\InspireCms\Support\Observers\HasRecursiveRelationshipsObserver;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships as BaseHasRecursiveRelationships;

trait HasRecursiveRelationships
{
    use BaseHasRecursiveRelationships {
        scopeIsRoot as private traitScopeIsRoot;
    }

    public static function bootHasRecursiveRelationships()
    {
        static::observe(new HasRecursiveRelationshipsObserver);
    }

    /**
     * @return int|string|null
     */
    public function getParentId()
    {
        return $this->{$this->getParentKeyName()};
    }

    /**
     * @return int|string|null
     */
    public function getRootLevelParentId()
    {
        return 0;
    }

    /**
     * @return bool
     */
    public function isRootLevel()
    {
        return $this->getParentId() === $this->getRootLevelParentId();
    }

    /**
     * @param  bool  $save  Indicates whether to save the instance after setting it as root. Default is true.
     */
    public function asRoot($save = true)
    {
        return $this->setParentNode($this->getRootLevelParentId(), $save);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model|string|int|null  $parent  The parent node to set.
     * @param  bool  $save  Whether to save the changes immediately. Default is true.
     */
    public function setParentNode($parent, $save = true)
    {
        $parentKey = $parent instanceof \Illuminate\Database\Eloquent\Model ? $parent?->getKey() : $parent;
        $this->{$this->getParentKeyName()} = $parentKey ?? $this->getRootLevelParentId();

        if ($save) {
            $this->save();
        }

        return $this;
    }

    //region Scopes
    /**
     * Limit the query to root models.
     *
     * @param  \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder<static>  $query
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder<static>
     */
    public function scopeIsRoot(Builder $query)
    {
        $rootValue = $this->getRootLevelParentId();

        if ($rootValue === null) {
            return $this->traitScopeIsRoot($query);
        }

        return $query->where($this->qualifyColumn($this->getParentKeyName()), $rootValue);
    }

    public function scopeWhereIsRoot($query, bool $condition = true)
    {
        if (! $condition) {
            return $query->whereNot(fn ($q) => $this->scopeIsRoot($q));
        }

        return $this->scopeIsRoot($query);
    }

    public function scopeWhereParent($query, $parentId)
    {
        if (is_null($parentId)) {
            return $query->whereNull($this->getParentKeyName());
        }

        return $query->where($this->qualifyColumn($this->getParentKeyName()), $parentId);
    }

    public function scopeWhereIsLeaf($query)
    {
        return $query->whereDoesntHave('children');
    }
    //endregion Scopes
}
