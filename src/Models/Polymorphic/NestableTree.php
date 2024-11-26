<?php

namespace SolutionForest\InspireCms\Support\Models\Polymorphic;

use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Concerns\HasRecursiveRelationships;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree as NestableTreeContract;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property string|int $id
 * @property string $nestable_type
 * @property string|int $nestable_id
 * @property int $order
 * @property string|int $parent_id
 * @property-read ?\Illuminate\Support\Carbon $created_at
 * @property-read ?\Illuminate\Support\Carbon $updated_at
 *
 * @implements NestableTreeContract<NestableTree>
 */
class NestableTree extends BaseModel implements NestableTreeContract
{
    use HasRecursiveRelationships;
    use SortableTrait;

    protected $guarded = ['id'];

    public function nestable()
    {
        return $this->morphTo();
    }

    //region Sortable
    public function buildSortQuery()
    {
        return static::query()
            ->where($this->getParentKeyName(), $this->getParentId())
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
