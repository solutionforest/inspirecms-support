<?php

namespace SolutionForest\InspireCms\Support\Models\Polymorphic;

use Kalnoy\Nestedset\NodeTrait;
use SolutionForest\InspireCms\Support\Base\Models\BaseModel;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree as NestableTreeContract;

class NestableTree extends BaseModel implements NestableTreeContract
{
    use NodeTrait;

    protected $guarded = ['id'];

    public function nestable()
    {
        return $this->morphTo();
    }

    // region Node

    public function determineOrderColumnName(): string
    {
        return $this->getLftName();
    }
    protected function getScopeAttributes()
    {
        return [
            'nestable_type',
        ];
    }

    public function getRootLevelParentId()
    {
        return null;
    }

    public function isRoot()
    {
        return $this->getParentId() === $this->getRootLevelParentId();
    }
    // endregion Node

    /** {@inheritDoc} */
    public static function setNewOrderForNestable($parentId, array $morphableIds, string $morphableType): void
    {
        // Get morph type from the model class string
        if (class_exists($morphableType)) {
            $morphableType = app($morphableType)->getMorphClass();
        }

        if ($parentId === app(static::class)->getRootLevelParentId()) {

            static::rebuildTreeForNestable($morphableType, $morphableIds);


        } else if (($parent = static::find($parentId)) && $parent != null) {

            $records = static::scopedForNestableType($morphableType)
                ->where(app(static::class)->getParentIdName(), $parentId)
                ->get();

            $sortedRecords = $records->sortBy(fn ($item) => array_search($item->nestable_id, $morphableIds))
                ->values()
                ->toArray();

            static::scopedForNestableType($morphableType)
                ->rebuildSubtree($parent, $sortedRecords);
        }

    }

    public static function rebuildTreeForNestable($morphableType, $morphableIds = [])
    {
        $records = static::scopedForNestableType($morphableType)
            ->withDepth()
            ->get();

        if (! empty($morphableIds)) {
            
            $sortedRecords = $records->toTree()
                ->sortBy(fn ($item) => array_search($item->nestable_id, $morphableIds))
                ->values()
                ->toArray();
        } else {
            $sortedRecords = $records->toTree()->toArray();
        }

        static::scopedForNestableType($morphableType)
            ->rebuildTree($sortedRecords);
    }

    protected static function scopedForNestableType($morphableType)
    {
        return static::scoped([
            'nestable_type' => $morphableType,
        ]);
    }
}
