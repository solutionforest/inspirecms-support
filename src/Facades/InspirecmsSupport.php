<?php

namespace Solutionforest\Inspirecms\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Solutionforest\Inspirecms\Support\InspirecmsSupport
 */
class InspirecmsSupport extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Solutionforest\Inspirecms\Support\InspirecmsSupport::class;
    }
}
