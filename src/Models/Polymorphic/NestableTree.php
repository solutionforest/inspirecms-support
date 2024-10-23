<?php

namespace SolutionForest\InspireCms\Support\Models\Polymorphic;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\NestableTrait;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree as NestableTreeContract;
use Spatie\EloquentSortable\SortableTrait;

class NestableTree extends BaseModel implements NestableTreeContract
{
    use NestableTrait;
    use SortableTrait;

    protected $guarded = ['id'];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

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
        return $query->where($this->getNestableParentIdColumn(), $parentId);
    }
}
