<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Livewire;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\DeleteAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\RenameAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasItemActions as HasItemActionsTrait;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\WithMediaAssets;
use SolutionForest\InspireCms\Support\MediaLibrary\Contracts\HasItemActions;

#[Lazy]
class FolderBrowserComponent extends Component implements HasItemActions
{
    use HasItemActionsTrait;
    use WithMediaAssets;

    public $folders;

    #[Reactive]
    public $parentKey;

    public function boot()
    {
        if ($this->folders && $this->folders instanceof \Illuminate\Database\Eloquent\Collection) {
            $this->folders->loadCount('children');
        }
    }

    public function placeholder()
    {
        return view('inspirecms-support::components.media-library.loading-section', [
            'count' => 5,
            'height' => '4rem',
        ]);
    }

    public function render()
    {
        return view('inspirecms-support::livewire.components.media-library.folder-browser');
    }

    // region Actions
    protected function getMediaItemActions(): array
    {
        return [
            RenameAction::make(),
            DeleteAction::make()
                ->action(function (Model $record) {
                    $this->dispatch('deleteFolder', $record->getKey());
                }),
        ];
    }
    // endregion Actions

    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::with([]);
    }
}
