<?php

namespace SolutionForest\InspireCms\Support\Dtos;

use SolutionForest\InspireCms\Support\Base\Dtos\BaseDto;

class MediaAssetDto extends BaseDto
{
    /**
     * @var string
     */
    public $uid;

    /**
     * @var ?string
     */
    public $caption;

    /**
     * @var ?string
     */
    public $description;

    /**
     * @var array
     */
    public $meta;

    /**
     * @var array
     */
    public $responsive;

    /**
     * @var string
     */
    public $disk;
}
