<?php

namespace SolutionForest\InspireCms\Support\PathGenerators;

use SolutionForest\InspireCms\Support\Base\PathGenerators\BaseContentPathGenerator;

class ContentPathGenerator extends BaseContentPathGenerator implements ContentPathGeneratorInterface
{
    public function getPath(): string
    {
        return $this->getFullPath();
    }
}
