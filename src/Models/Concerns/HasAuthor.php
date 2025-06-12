<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use SolutionForest\InspireCms\Support\Facades\ResolverRegistry;
use SolutionForest\InspireCms\Support\Resolvers\UserResolverInterface;

trait HasAuthor
{
    public static function bootHasAuthor()
    {
        static::creating(function ($model) {
            if (empty($model->author_id) && empty($model->author_type)) {
                $author = $model->resolveAuthor();

                $model->author_id = $author?->getKey();
                $model->author_type = $author?->getMorphClass();

            }
        });
    }

    public function author()
    {
        return $this->morphTo();
    }

    protected function resolveAuthor()
    {
        $resolver = ResolverRegistry::get(UserResolverInterface::class);

        if (! $resolver instanceof UserResolverInterface) {
            throw new \Exception('User resolver must implement ' . UserResolverInterface::class);
        }

        return $resolver->resolve($this);
    }
}
