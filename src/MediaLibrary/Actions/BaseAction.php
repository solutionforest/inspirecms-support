<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Closure;
use Filament\Actions\Action;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

abstract class BaseAction extends Action
{
    protected Closure | string | int | null $parentKey = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model(ModelRegistry::get(MediaAsset::class));

        $this->modelLabel(__('inspirecms-support::media-library.media'));

        $this->visible(fn () => $this->isAuthorized());
    }

    public function parentKey(Closure | string | int | null $parentKey): static
    {
        $this->parentKey = $parentKey;

        return $this;
    }

    public function getParentKey(): string | int | null
    {
        return $this->evaluate($this->parentKey) ?? app($this->getModel())->getRootLevelParentId();
    }
}
