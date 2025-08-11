<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;
use Illuminate\Support\LazyCollection;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasItemBulkActions;

class ItemBulkAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->bulk();
        $this->accessSelectedRecords();
    }

    public function getLivewireClickHandler(): ?string
    {
        if (! $this->isLivewireClickHandlerEnabled()) {
            return null;
        }

        if (is_string($this->action)) {
            return $this->action;
        }

        if ($event = $this->getLivewireEventClickHandler()) {
            return $event;
        }

        return $this->generateJavaScriptClickHandler('mountMediaLibraryItemAction') ?? parent::getLivewireClickHandler();
    }

    protected function generateJavaScriptClickHandler(string $method): ?string
    {
        if ($this->canAccessSelectedRecords() && ($livewire = $this->getLivewire()) && $livewire instanceof HasItemBulkActions) {
            $recordKeys = Js::from(collect($livewire->getSelectedMediaAssetIds()));

            return "{$method}('{$this->getName()}', {$recordKeys})";
        }

        return null;
    }

    public function getSelectedRecords(): EloquentCollection | Collection | LazyCollection
    {
        $livewire = $this->getLivewire();
        if ($this->canAccessSelectedRecords() && $livewire instanceof HasItemBulkActions) {
            return $livewire->getSelectedMediaAssets();
        }

        return parent::getSelectedRecords();
    }
}
