<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Reactive;
use Livewire\Component;

#[Lazy]
class FolderBrowserComponent extends Component implements Contracts\HasItemActions
{
    use Concerns\HasItemActions;
    use Concerns\WithMediaAssets;

    #[Reactive]
    public ?Model $parentRecord = null;

    public string $title = 'Folders';

    public function placeholder()
    {
        return view('inspirecms-support::components.media-library.loading-section', [
            'count' => 5,
            'height' => '4rem',
        ]);
    }

    public function render()
    {
        return view('inspirecms-support::livewire.components.media-library.folder-browser', [
            'folders' => is_null($this->parentRecord) ? [] : $this->getFoldersFromUpperLevel(),
        ]);
    }

    protected function getFoldersFromUpperLevel()
    {
        if (is_null($this->parentRecord)) {
            return collect();
        }

        return $this->getEloquentQuery()
            ->withCount('children')
            ->whereParent($this->parentRecord->getParentId())
            ->folders()
            ->get();
    }

    // region Actions
    protected function getMediaItemActions(): array
    {
        return [
            Actions\RenameAction::make(),
            Actions\ActionGroup::make([
                Actions\DeleteAction::make()
                    // Back to root level after delete
                    ->after(fn () => $this->dispatch('openFolder', $this->getRootLevelParentId())),
            ])->dropdown(false),
        ];
    }
    // endregion Actions

    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::with([]);
    }
}
