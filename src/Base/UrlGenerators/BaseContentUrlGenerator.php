<?php

namespace SolutionForest\InspireCms\Support\Base\UrlGenerators;

use SolutionForest\InspireCms\Factories\ContentPathGeneratorFactory;
use SolutionForest\InspireCms\Models\Contracts\Content;

abstract class BaseContentUrlGenerator
{
    protected Content $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function getUrl(): string
    {
        $path = ContentPathGeneratorFactory::createFor($this->content)->getPath();

        return url($path);
    }
}
