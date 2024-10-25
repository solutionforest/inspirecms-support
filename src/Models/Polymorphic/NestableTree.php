<?php

namespace SolutionForest\InspireCms\Support\Models\Polymorphic;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\NestableTrait;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree as NestableTreeContract;
use SolutionForest\InspireCms\Support\Models\Scopes\SortedScope;
use Spatie\EloquentSortable\SortableTrait;

class NestableTree extends BaseModel implements NestableTreeContract
{
    use NestableTrait;
    use SortableTrait;

    protected $guarded = ['id'];

    public function nestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function buildSortQuery(): Builder
    {
        $query = method_exists(parent::class, 'buildSortQuery') ? parent::buildSortQuery() : static::query();

        return $query->where($this->getNestableParentIdColumn(), $this->parent_id);
    }

    public function scopeParent($query, $parentId)
    {
        $query->where($this->getNestableParentIdColumn(), $parentId);
    }

    public function scopeRoot($query)
    {
        $query->where($this->getNestableParentIdColumn(), $this->getNestableRootValue());
    }

    public function determineOrderColumnName(): string
    {
        return 'order';
    }

    public function shouldSortWhenCreating(): bool
    {
        return true;
    }

    public static function setNewOrderForNestable(
        $ids,
        string $morphableType,
        int $startOrder = 1,
    ): void {
        $modifyQuery = function ($query) use ($morphableType) {
            $query
                ->where('nestable_type', $morphableType);
        };
        static::setNewOrder($ids, $startOrder, 'nestable_id', $modifyQuery);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SortedScope);
    }
}
