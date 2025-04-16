<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Closure;
use Filament\Actions\Action as BaseAction;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class Action extends BaseAction
{
    protected Closure | string | int | null $parentKey = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model(ModelRegistry::get(MediaAsset::class));

        $this->modelLabel(fn ($record) => $record ? $this->getRecordTitle($record) : __('inspirecms-support::media-library.media.singular'));

        $this->recordTitleAttribute('title');
    }

    public function isVisible(): bool
    {
        if (! $this->isAuthorized()) {
            return false;
        }

        return parent::isVisible();
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
