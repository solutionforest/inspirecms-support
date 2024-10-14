<?php

namespace SolutionForest\InspireCms\Support\Models\Concerns;

use SolutionForest\InspireCms\Support\Facades\ResolverManifest;

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
        $resolver = ResolverManifest::get('user');

        if (! in_array(\SolutionForest\InspireCms\Support\Resolver\UserResolverInterface::class, class_implements($resolver))) {
            throw new \Exception('User resolver must implement UserResolverInterface');
        }

        return $resolver::resolve();
    }
}
