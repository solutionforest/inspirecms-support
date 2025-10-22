<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Livewire;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\DeleteAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\EditAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\OpenFolderAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\RenameAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\ViewAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasFilters;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasItemActions as HasItemActionsTrait;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasItemBulkActions;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\HasSorts;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\InteractsWithHeaderActions;
use SolutionForest\InspireCms\Support\MediaLibrary\Concerns\WithMediaAssets;
use SolutionForest\InspireCms\Support\MediaLibrary\Contracts\HasItemActions;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Throwable;

use function Filament\authorize;

/**
 * @property \Filament\Schemas\Schema $uploadForm
 */
class MediaLibraryComponent extends Component implements HasItemActions, HasItemBulkActions
{
    use HasFilters;
    use HasItemActionsTrait;
    use HasSorts;
    use InteractsWithHeaderActions;
    use WithMediaAssets;
    use WithPagination;

    #[Modelable]
    public array $selectedMediaId = [];

    public ?string $toggleMediaId = null;

    public ?string $parentKey = null;

    /**
     * @var MediaAsset | Model | null
     */
    public ?Model $parentRecord = null;

    public null | int | string $page = null;

    public null | int | string $perPage = 15;

    #[Locked]
    public bool $isModalPicker = false;

    public array $formConfig = [];

    public array $uploadData = [];

    /**
     * @var Collection
     */
    protected $cachedSelectedMedia = [];

    public ?int $maxSelections = null; // Maximum number of selections allowed (null = unlimited)

    protected $listeners = [
        'openFolder',
        'deleteFolder',
        'deleteMedia',
        'moveMediaItem',
        'resetMediaLibrary' => 'resetAll',
        'clearMediaLibraryCache' => 'clearCache',
        'autoupload-file--upload-success' => 'notifyAutoUploadSuccess',
    ];

    protected function queryString()
    {
        if ($this->isMediaPickerModal()) {
            return [];
        }

        return [
            'parentKey' => ['as' => 'mp'],
            'page' => ['as' => static::getPageName()],
            'perPage' => ['as' => 'mperPage'],
        ];
    }

    public function mount()
    {
        if (is_null($this->parentKey) || blank($this->parentKey)) {
            $this->parentKey = static::getRootLevelParentId();
        }
        if ($this->parentKey != null) {
            $this->parentRecord = $this->resolveAssetRecord($this->parentKey);
        }
        $this->resetUploadForm();
    }

    public function updatedPaginators($page, $pageName)
    {
        if ($pageName == static::getPageName()) {
            $this->page = $page;
            $this->clearCache();
        }
    }

    public function updating($key, $value)
    {
        $checkKey = Str::before($key, '.');
        if (in_array($checkKey, ['filter', 'sort'])) {
            $this->clearCache();
            if (! $this->isMediaPickerModal()) {
                $this->resetSelectedMedia();
            }
        }
    }

    public function updated($key, $value)
    {
        $checkKey = Str::before($key, '.');
        if ($checkKey == 'selectedMediaId') {
            // Remove media
            if (count($this->selectedMediaId) <= 0) {
                $this->resetToggleMediaId();
            }
            if (! $this->isMultipleSelection() && count($this->selectedMediaId) > 1) {
                $this->selectedMediaId = collect([])->wrap($value)->take(1)->all();
            }
        }
    }

    public function openFolder($mediaId = null)
    {
        $this->clearCache();

        $this->resetUploadForm();
        // Tell FilePond on the frontend to reset the file input
        $this->dispatch('autoupload-file--filepond-reset');

        $mediaId ??= $this->selectedMediaId;
        $this->changeParent($mediaId);
    }

    public function deleteFolder($mediaId)
    {
        $this->dispatch('openFolder', static::getRootLevelParentId())->self();
        $this->dispatch('deleteMedia', $mediaId)->self();
    }

    public function deleteMedia($mediaId)
    {
        $this->handleMediaItemDelete($mediaId);
    }

    public function toggleMedia($mediaId = null, $isFolder = true)
    {
        $this->toggleMediaId = $mediaId;
        if ($this->isMediaPickerModal() && $isFolder == true) {
            //
        } else {
            $this->resetSelectedMedia();
            if ($mediaId != null) {
                $this->selectedMediaId = [$mediaId];
            }
        }
    }

    public function isUnderRoot(): bool
    {
        return $this->isUnderFolder(static::getRootLevelParentId());
    }

    public function isUnderFolder($folderId): bool
    {
        return $this->parentKey == $folderId;
    }

    public function getSelectedMediaAssets(): Collection
    {
        return $this->resolveAssetRecords($this->getSelectedMediaAssetIds());
    }

    public function getSelectedMediaAssetIds(): array
    {
        return $this->selectedMediaId;
    }

    public function hasAnyMediaSelected(): bool
    {
        return count($this->getSelectedMediaAssetIds()) > 0 || $this->toggleMediaId != null;
    }

    public function resetSelectedMedia(): void
    {
        $this->selectedMediaId = [];
        $this->cachedSelectedMedia = null;
    }

    public function resetToggleMediaId(): void
    {
        $this->toggleMediaId = null;
    }

    public function deselectAllMedia(): void
    {
        $this->resetSelectedMedia();
        $this->resetToggleMediaId();
    }

    public function clearCache()
    {
        unset(
            $this->assets,
            $this->folders,
        );
    }

    public function resetAll()
    {
        $this->resetSelectedMedia();
        $this->resetToggleMediaId();
        $this->resetUploadForm();
        $this->clearCache();
    }

    public function notifyAutoUploadSuccess()
    {
        Notification::make()
            ->title(__('inspirecms-support::media-library.messages.uploaded'))
            ->success()
            ->send();

        $this->clearCache();
        $this->dispatch('$refresh');
    }

    public function isMediaPickerModal(): bool
    {
        return $this->isModalPicker;
    }

    public function canDragAndDrop(): bool
    {
        return ! $this->isMediaPickerModal();
    }

    public function canUpload(): bool
    {
        try {
            return authorize('create', $this->getMediaAssetModel())->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }

    /**
     * Move a media item from one location to another.
     *
     * @param  string  $livewireKey
     * @param  string  $targetId  The ID of the media item to be moved.
     * @param  string  $toId  The ID of the target location where the media item will be moved to.
     * @return void
     */
    public function moveMediaItem($livewireKey, $targetId, $toId)
    {
        if ($livewireKey != $this->getId()) {
            return;
        }

        try {
            $toAsset = $this->resolveAssetRecord($toId);
            if (is_null($toAsset) || ! $toAsset->isFolder()) {
                return;
            }
            $targetAsset = $this->resolveAssetRecord($targetId);
            if (is_null($targetAsset)) {
                return;
            }

            $success = $targetAsset->setParentNode($toAsset);

            if ($success == true) {
                Notification::make()
                    ->title(__('inspirecms-support::media-library.messages.item_moved'))
                    ->success()
                    ->send();
                $this->resetAll();
                $this->dispatch('$refresh');
            }

        } catch (Throwable $th) {
            // Skip
        }
    }

    protected function isMultipleSelection(): bool
    {
        if (is_null($this->maxSelections)) {
            return true; // Unlimited selections allowed
        }

        return $this->maxSelections > 1;
    }

    // region Actions

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createFolder')
                ->label(__('inspirecms-support::media-library.buttons.create_folder.label'))
                ->successNotificationTitle(__('inspirecms-support::media-library.buttons.create_folder.messages.success.title'))
                ->authorize('create')
                ->icon(FilamentIcon::resolve('inspirecms::create_folder'))
                ->modalIcon(FilamentIcon::resolve('inspirecms::create_folder'))
                ->modalWidth('sm')
                ->color(Color::Neutral)
                ->outlined()
                ->schema([
                    TextInput::make('title')
                        ->label(__('inspirecms-support::media-library.forms.title.label'))
                        ->validationAttribute(__('inspirecms-support::media-library.forms.title.validation_attribute'))
                        ->required()
                        ->autofocus(),
                ])
                ->action(function (array $data, Actions\Action $action) {
                    if (empty($data['title'])) {
                        return;
                    }
                    $record = $action->getModel()::create([
                        'parent_id' => $this->parentKey,
                        'title' => $data['title'],
                        'is_folder' => true,
                    ]);
                    $action->success();
                }),
            Actions\Action::make('upload')
                ->label(__('inspirecms-support::media-library.buttons.upload.label'))
                ->alpineClickHandler('() => showUploadForm = ! showUploadForm'),
        ];
    }

    public function getVisibleHeaderActions(): array
    {
        return collect($this->getHeaderActions())
            ->filter(fn (Action | ActionGroup $action) => $action->isVisible())
            ->all();
    }

    protected function getMediaItemActions(): array
    {
        return [
            OpenFolderAction::make()
                ->dispatch('openFolder', fn (?Model $record) => ['mediaId' => $record?->getKey()]),

            EditAction::make(),
            ViewAction::make(),

            RenameAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function configureAction(Action $action): void
    {
        if ($action instanceof Actions\Action) {
            $action
                ->parentKey(fn () => $this->parentKey)
                ->after(fn () => $this->clearCache());
        }
    }

    protected function configureMediaItemAction($action): void
    {
        $action->parentKey(fn () => $this->parentKey);

        switch (true) {
            case $action instanceof OpenFolderAction:
                $action
                    ->visible(fn (?Model $record): bool => $record !== null && $record instanceof MediaAsset && $record->isFolder());

                break;
            case $action instanceof RenameAction:
            case $action instanceof DeleteAction:
                $action->after(fn () => $this->clearCache());

                break;
            case $action instanceof EditAction:
            case $action instanceof ViewAction:
                $action
                    ->visible(function (?Model $record): bool {
                        return $record !== null && $record instanceof MediaAsset && ! $record->isFolder();
                    });

                break;
        }
    }
    // endregion Actions

    // region Form

    public function uploadForm(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->statePath('uploadData')
            ->components([
                MediaAssetHelper::getFileAutoUploadField($this->getParentRecord()?->getKey() ?? $this->getRootLevelParentId()),
            ]);
    }

    protected function resetUploadForm(): void
    {
        $this->uploadForm->fill([
            'files' => [],
        ]);
    }

    public function getFormStatePathFor(string $formName): ?string
    {
        return match ($formName) {
            'uploadForm' => 'uploadData',
            'filterForm' => $this->getFilterFormStatePath(),
            'sortForm' => $this->getSortFormStatePath(),
            default => 'form',
        };
    }

    protected function getForms(): array
    {
        return [
            'uploadForm',
            'filterForm',
            'sortForm',
        ];
    }

    protected function mutateSortData(array $data): array
    {
        if (! isset($data['type'])) {
            $data['type'] = 'default';
        }
        if (! isset($data['direction'])) {
            $data['direction'] = 'desc';
        }

        return $data;
    }
    // endregion Form

    // region Computed

    /**
     * Get the media assets from the parent.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Model&MediaAsset>
     */
    #[Computed(persist: true, seconds: 120)]
    public function assets()
    {
        /**
         * @var Builder $query
         */
        $query = $this->getEloquentQuery()
            ->whereParent($this->parentKey)
            ->withCount('children');

        $query = $this->applySortCriteria($query);
        $query = $this->applyFilterCriteria($query);

        return $query->paginate(
            perPage: $this->perPage,
            pageName: static::getPageName(),
            page: $this->page,
        );
    }

    /**
     * Get the folders from the parent.
     *
     * @return Collection<Model&MediaAsset>
     */
    #[Computed]
    public function folders()
    {
        // From upper level
        if (is_null($this->parentRecord) || ! $this->parentRecord->exists) {
            return collect();
        }

        return $this->getEloquentQuery()
            ->with([])
            ->withCount('children')
            ->whereParent($this->parentRecord->getParentId())
            ->folders()
            ->get();
    }
    // endregion Computed

    public function render()
    {
        return view('inspirecms-support::livewire.components.media-library.index', [
            'pageOptions' => static::getPageOptions(),
            'breadcrumbs' => $this->getBreadcrumbs(),
        ]);
    }

    // region Helpers

    protected function isFilterColumnDisabled(string $column): bool
    {
        return in_array($column, $this->formConfig['filter']['disabled_columns'] ?? []);
    }

    protected function isSortColumnDisabled(string $column): bool
    {
        return in_array($column, $this->formConfig['sort']['disabled_columns'] ?? []);
    }

    protected static function getPageName(): string
    {
        return 'mpage';
    }

    protected static function getPageOptions(): array
    {
        return [5, 10, 15, 20, 50, 100, 'all'];
    }

    protected function changeParent($key)
    {
        $this->clearCache();
        if (! $this->isMediaPickerModal()) {
            $this->resetSelectedMedia();
        }
        $this->resetToggleMediaId();
        $this->resetPage(static::getPageName());

        if (blank($key) || $key == $this->parentKey) {
            return;
        }

        if ($key == static::getRootLevelParentId()) {
            $this->parentKey = $key;
            // Reset parent record
            $this->parentRecord = null;

            return;
        }

        if ($this->getParentRecord()?->getKey() != $key) {
            $this->parentRecord = $this->getEloquentQuery()->find($key);
        }
        // Check if the key is a folder
        if ($this->getParentRecord()?->isFolder() ?? false) {
            $this->parentKey = $key;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model & MediaAsset |null
     */
    protected function getParentRecord()
    {
        return $this->parentRecord;
    }

    protected function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            static::getRootLevelParentId() => __('inspirecms-support::tree-node.root'),
        ];

        if ($this->isUnderRoot()) {
            return $breadcrumbs;
        }

        $asset = $this->getParentRecord();
        $ancestorsAndSelf = $asset?->ancestorsAndSelf->reverse()->values() ?? collect();
        foreach ($ancestorsAndSelf as $item) {
            $breadcrumbs[$item->getKey()] = $item->title;
        }

        return $breadcrumbs;
    }

    protected function handleMediaItemDelete($mediaId)
    {
        $record = $this->resolveAssetRecord($mediaId);
        if (is_null($record)) {
            return false;
        }

        $isSuccess = $record->delete();
        if ($isSuccess) {

            Notification::make()
                ->title(__('inspirecms-support::media-library.messages.item_deleted'))
                ->success()
                ->send();

            // Reset the upload form and clear cache
            // (Avoid using 'resetAll' here to avoid resetting the toggle/select media)
            $this->resetUploadForm();
            $this->clearCache();
            $this->dispatch('$refresh');
        } else {
            Notification::make()
                ->title(__('inspirecms-support::media-library.messages.item_deletion_failed'))
                ->danger()
                ->send();
        }
    }
    // endregion Helpers
}
