<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

/**
 * @property Form $uploadForm
 */
class MediaLibraryComponent extends Component implements Contracts\HasItemActions
{
    use Concerns\HasFilters;
    use Concerns\HasItemActions;
    use Concerns\HasSorts;
    use Concerns\InteractsWithHeaderActions;
    use Concerns\WithMediaAssets;
    use WithPagination;

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

    public bool $mountedMediaPickerModal = false;

    public array $formConfig = [];

    public array $uploadData = [];

    /**
     * @var Collection
     */
    protected $cachedSelectedMedia = [];

    protected $listeners = [
        'openFolder',
        'deleteFolder',
        'deleteMedia',
        'moveMediaItem',
        'resetMediaLibrary' => 'resetAll',
        'clearMediaLibraryCache' => 'clearCache',
        'media-picker-modal:init' => 'initializeMediaLibraryPickerModal',
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
        if ($checkKey == 'selectedMediaId' && count($this->selectedMediaId) <= 0) { // Remove media
            $this->resetToggleMediaId();
        }
    }

    public function initializeMediaLibraryPickerModal(array $config = [])
    {
        try {

            if (isset($config['page']) && is_numeric($config['page'])) {
                $this->page = intval($config['page']);
            }
            if (isset($config['forms']['filter']['disabledColumns']) && is_array($config['forms']['filter']['disabledColumns'])) {
                $this->formConfig['filter']['disabled_columns'] = $config['forms']['filter']['disabledColumns'];
            }
            if (isset($config['forms']['sort']['disabledColumns']) && is_array($config['forms']['sort']['disabledColumns'])) {
                $this->formConfig['sort']['disabled_columns'] = $config['forms']['sort']['disabledColumns'];
            }

            if (isset($config['forms']['filter']['d']) && is_array($config['forms']['filter']['d'])) {
                foreach ($config['forms']['filter']['d'] as $key => $value) {
                    $this->filter[$key] = $value;
                }
            }
            if (isset($config['forms']['sort']['d']) && is_array($config['forms']['sort']['d'])) {
                foreach ($config['forms']['sort']['d'] as $key => $value) {
                    $this->sort[$key] = $value;
                }
            }

        } finally {

            $this->mountedMediaPickerModal = true;
            $this->clearCache();

            // Finish the setup, and hide the loading spinner
            $this->dispatch('media-picker-modal-setup-complete');
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

    public function hasAnyMediaSelected(): bool
    {
        return count($this->selectedMediaId) > 0 || $this->toggleMediaId != null;
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
            return \Filament\authorize('create', $this->getMediaAssetModel())->allowed();
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

        } catch (\Throwable $th) {
            // Skip
        }
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
                ->color(\Filament\Support\Colors\Color::Neutral)
                ->outlined()
                ->form([
                    Forms\Components\TextInput::make('title')
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
        return collect($this->getCachedHeaderActions())
            ->filter(fn (Action | ActionGroup $action) => $action->isVisible())
            ->all();
    }

    protected function getMediaItemActions(): array
    {
        return [
            Actions\OpenFolderAction::make()
                ->dispatch('openFolder', fn (?Model $record) => ['mediaId' => $record?->getKey()]),

            Actions\EditAction::make(),
            Actions\ViewAction::make(),

            Actions\RenameAction::make(),
            Actions\DeleteAction::make(),
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
            case $action instanceof Actions\OpenFolderAction:
                $action
                    ->visible(fn (?Model $record): bool => $record !== null && $record instanceof MediaAsset && $record->isFolder());

                break;
            case $action instanceof Actions\RenameAction:
            case $action instanceof Actions\DeleteAction:
                $action->after(fn () => $this->clearCache());

                break;
            case $action instanceof Actions\EditAction:
            case $action instanceof Actions\ViewAction:
                $action
                    ->visible(function (?Model $record): bool {
                        return $record !== null && $record instanceof MediaAsset && ! $record->isFolder();
                    });

                break;
        }
    }
    // endregion Actions

    // region Form

    public function uploadForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->statePath('uploadData')
            ->schema([
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
            default => $this->getFormStatePath(),
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
        if ($this->isMediaPickerModal() && ! $this->mountedMediaPickerModal) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                items: collect(),
                total: 0,
                perPage: $this->perPage,
            );
        }

        /**
         * @var \Illuminate\Database\Eloquent\Builder $query
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
            ->get()
            ->collect();
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
