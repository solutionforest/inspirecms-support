<?php

namespace SolutionForest\InspireCms\Support\Models\Polymorphic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function nestable(): MorphTo
    {
        return $this->morphTo();
    }

    //region Sortable
    public function buildSortQuery()
    {
        return static::query()
            ->where($this->getNestableParentIdName(), $this->getParentId())
            ->where('nestable_type', $this->nestable_type);
    }

    public function shouldSortWhenCreating(): bool
    {
        return true;
    }

    public function determineOrderColumnName(): string
    {
        return 'order';
    }
    //endregion Sortable

    /** {@inheritDoc} */
    public static function setNewOrderForNestable($parentId, array $morphableIds, string $morphableType): void
    {
        // Get morph type from the model class string
        if (class_exists($morphableType)) {
            $morphableType = app($morphableType)->getMorphClass();
        }

        static::setNewOrder(
            $morphableIds,
            1,
            'nestable_id',
            fn ($q) => $q
                ->where('nestable_type', $morphableType)
                ->whereParent($parentId)
        );

    }
}
