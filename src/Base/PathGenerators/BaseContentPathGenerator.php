<?php

namespace SolutionForest\InspireCms\Support\Base\PathGenerators;

use SolutionForest\InspireCms\Models\Contracts\Content;

abstract class BaseContentPathGenerator
{
    protected Content $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    protected function getFullPath(): string
    {
        $ancestors = $this->content->ancestors();
        $slugs = [];
        foreach ($ancestors as $ancestor) {
            $slugs[] = $ancestor->slug;
        }
        $slugs[] = $this->content->slug;

        return implode('/', $slugs);
    }
}
