<?php

namespace SolutionForest\InspireCms\Support\Base\Dtos;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModelDto
 *
 * @template TModle of Model
 * @template TDto of BaseModelDto
 *
 * @extends BaseDto<TDto>
 */
abstract class BaseModelDto extends BaseDto
{
    /**
     * @var ?TModle
     */
    protected $model;

    /**
     * @param  TModle  $model
     * @return TDto
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return TModle|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param  TModle  $model
     * @return TDto
     */
    public static function fromModel($model)
    {
        return static::fromArray($model->attributesToArray())->setModel($model);
    }
}
