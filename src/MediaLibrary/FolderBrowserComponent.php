<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;

#[Lazy]
class FolderBrowserComponent extends Component implements Contracts\HasItemActions
{
    use Concerns\HasItemActions;
    use Concerns\WithMediaAssets;

    public $folders;

    public $parentKey;

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
            Actions\RenameAction::make(),
            Actions\DeleteAction::make()
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
