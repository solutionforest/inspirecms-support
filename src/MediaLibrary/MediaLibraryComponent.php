<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

/**
 * @property Form $uploadFileForm
 * @property Form $filterForm
 */
class MediaLibraryComponent extends Component implements HasActions, HasForms
{
    use Concerns\HasFilters;
    use Concerns\HasSorts;
    use InteractsWithActions;
    use InteractsWithForms;

    #[Url(as: 'p')]
    public string | int | null $parentKey = null;

    public bool $isMultiple = false;

    public array | string | int | null $selectedMediaId = null;

    public null | Model | array $selectedMedia = null;

    public ?array $uploadFileData = [];

    public array $modelableConfig = [];

    public array $formConfig = [];

    /**
     * @var array<Action | ActionGroup>
     */
    protected array $cachedMediaLibraryActions = [];

    public function mount($parentKey = null)
    {
        if ($parentKey) {
            $this->parentKey = $parentKey;
        }

        $this->parentKey ??= static::getRootLevelParentId();

        if ($this->isMultiple()) {
            $this->selectedMediaId = [];
        }
        $this->fillForm();
    }

    public function booted()
    {
        $this->cacheMediaLibraryActions();
    }

    public static function canCreate(): bool
    {
        return Gate::check('create', [
            static::getMediaAssetModel(),
        ]);
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            //todo: add translations
            static::getRootLevelParentId() => 'Root',
        ];

        if ($this->parentKey == static::getRootLevelParentId()) {
            return $breadcrumbs;
        }

        /**
         * @var Model & MediaAsset $asset
         */
        $asset = $this->getEloquentQuery()->find($this->parentKey);
        $ancestorsAndSelf = $asset?->ancestorsAndSelf()->get()->reverse()->values() ?? collect();
        foreach ($ancestorsAndSelf as $item) {
            $breadcrumbs[$item->getKey()] = $item->title;
        }

        return $breadcrumbs;
    }

    #[On('updatedSelectedMediaId')]
    public function updatedSelectedMediaId($value)
    {
        if ($value && ! is_array($value) && ! $this->isMultiple()) {
            $this->selectedMedia = $this->getEloquentQuery()->find($value);
        } else {
            $this->selectedMedia = null;
        }
    }

    public function openFolder($mediaId = null)
    {
        $mediaId ??= $this->selectedMediaId;
        if (! $this->isMultiple()) {
            $this->resetSelectedMedia();
        }
        $this->changeParent($mediaId);
    }

    public function changeParent($key)
    {
        if (blank($key) || $key == $this->parentKey) {
            return;
        }
        if ($key == static::getRootLevelParentId()) {
            $this->parentKey = $key;

            return;
        }
        $media = $this->getEloquentQuery()->find($key);
        if ($media && $media->isFolder()) {
            $this->parentKey = $key;

            return;
        }
    }

    //region Actions
    protected function cacheMediaLibraryActions(): void
    {
        /** @var array<string, Action | ActionGroup> */
        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureAction']),
            fn (): array => [
                $this->createFolderAction(),
                $this->editMediaAction(),
                $this->viewMediaAction(),
                $this->openFolderAction(),
                $this->deleteMediaAction(),
            ],
        );

        foreach ($actions as $action) {
            if ($action instanceof ActionGroup) {
                $action->livewire($this);

                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                $this->mergeCachedActions($flatActions);
                $this->cachedMediaLibraryActions[] = $action;

                continue;
            }

            if (! $action instanceof Action) {
                throw new InvalidArgumentException('The actions must be an instance of ' . Action::class . ', or ' . ActionGroup::class . '.');
            }

            $this->cacheAction($action);
            $this->cachedMediaLibraryActions[] = $action;
        }
    }

    protected function configureAction(Action $action): void
    {
        if ($action instanceof \SolutionForest\InspireCms\Support\MediaLibrary\Actions\BaseAction) {
            $action->parentKey(fn () => $this->parentKey);
        }

        switch (true) {
            case $action instanceof \SolutionForest\InspireCms\Support\MediaLibrary\Actions\EditAction:
            case $action instanceof \SolutionForest\InspireCms\Support\MediaLibrary\Actions\ViewAction:
            case $action instanceof \SolutionForest\InspireCms\Support\MediaLibrary\Actions\DeleteAction:
            case $action instanceof \SolutionForest\InspireCms\Support\MediaLibrary\Actions\OpenFolderAction:
                $action->record(fn () => $this->selectedMedia);

                break;
        }
    }

    protected function getCachedMediaLibraryAction(string $name): ?Action
    {
        $actions = $this->cachedMediaLibraryActions;

        return collect($actions)->first(fn (Action | ActionGroup $action) => $action->getName() === $name);
    }

    /**
     * @param  Model & MediaAsset  $asset
     * @return array
     */
    public function getActionsForAsset($asset)
    {
        $actions = [];

        if ($asset->isFolder()) {
            $actions[] = $this->getCachedMediaLibraryAction('open-folder');
        } else {
            $actions[] = $this->getCachedMediaLibraryAction('edit');
            $actions[] = $this->getCachedMediaLibraryAction('view');
        }

        $actions[] = $this->getCachedMediaLibraryAction('delete');

        return collect($actions)
            ->filter(fn (Action $action) => $action->isVisible())
            ->all();
    }

    public function createFolderAction(): Action
    {
        return \SolutionForest\InspireCms\Support\MediaLibrary\Actions\CreateFolderAction::make();
    }

    public function editMediaAction(): Action
    {
        return \SolutionForest\InspireCms\Support\MediaLibrary\Actions\EditAction::make();
    }

    public function viewMediaAction(): Action
    {
        return \SolutionForest\InspireCms\Support\MediaLibrary\Actions\ViewAction::make();
    }

    public function openFolderAction(): Action
    {
        return \SolutionForest\InspireCms\Support\MediaLibrary\Actions\OpenFolderAction::make()
            ->action(fn (?Model $record) => $this->openFolder($record?->getKey()));
    }

    public function deleteMediaAction(): Action
    {
        return \SolutionForest\InspireCms\Support\MediaLibrary\Actions\DeleteAction::make()
            ->after(fn () => $this->resetSelectedMedia());
    }
    //endregion Actions

    //region Form
    public function uploadFileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('files')
                    ->label(__('inspirecms-support::media-library.forms.files.label'))
                    ->disk(MediaLibraryRegistry::getDisk())
                    ->directory(MediaLibraryRegistry::getDirectory())
                    ->imageEditor()
                    ->multiple(),
            ])
            ->statePath($this->getFormStatePathFor('uploadFileForm'));
    }

    public function saveUploadFile()
    {
        $files = $this->uploadFileData['files'] ?? [];
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $this->createMediaFromUploadedFile($file);
        }

        $this->dispatch('form-processing-finished');

        $this->fillForm();
    }

    public function getFormStatePathFor(string $formName): ?string
    {
        return match ($formName) {
            'uploadFileForm' => 'uploadFileData',
            'filterForm' => $this->getFilterFormStatePath(),
            'sortForm' => $this->getSortFormStatePath(),
            default => $this->getFormStatePath(),
        };
    }

    protected function getForms(): array
    {
        return [
            'uploadFileForm',
            'filterForm',
            'sortForm',
        ];
    }

    protected function fillForm(): void
    {
        $this->uploadFileForm->fill();
        $this->fillFilterForm();
        $this->fillSortForm();
    }
    //endregion Form

    public function isFormCollapsed(string $name): bool
    {
        switch ($name) {
            case 'sortForm':
                return
                    collect($this->ensureSort())
                        ->where(fn ($v, $k) => ! $this->isSortColumnInvisible($k))
                        ->count() <= 0 &&
                    data_get($this->formConfig, 'sort.collap_open', false) == false;
            case 'filterForm':
                return
                    collect($this->ensureFilter())
                        ->where(fn ($v, $k) => ! $this->isFilterColumnInvisible($k))
                        ->count() <= 0 &&
                    data_get($this->formConfig, 'filter.collap_open', false) == false;
            case 'uploadFileForm':
                return data_get($this->formConfig, 'upload.collap_open', false) == false;
            default:
                return false;
        }
    }

    public function getMediaFromParent()
    {
        $query = $this->getEloquentQuery()
            ->with('media')
            ->whereParent($this->parentKey);

        $filter = $this->ensureFilter();
        $sort = $this->ensureSort();

        switch ($sort['type'] ?? null) {
            case 'name':
                $query = $query->withAggregate('media', 'name')->orderBy('media_name', $sort['direction'] ?? 'asc');

                break;
            case 'created_at':
                $query = $query->withAggregate('media', 'created_at')->orderBy('media_created_at', $sort['direction'] ?? 'asc');

                break;
            case 'updated_at':
                $query = $query->withAggregate('media', 'updated_at')->orderBy('media_updated_at', $sort['direction'] ?? 'asc');

                break;
            case 'size':
                $query = $query->withSum('media', 'size')->orderBy('media_sum_size', $sort['direction'] ?? 'asc');
            default:
                $query = $query->orderBy('id', $sort['direction'] ?? 'asc');

                break;
        }

        if (isset($filter['title'])) {
            $query = $query->where('title', 'like', "%{$filter['title']}%");
            unset($filter['title']);
        }

        if (count($filter) > 0) {
            $query = $query
                ->whereHas('media', function ($query) use ($filter) {

                    foreach ($filter as $key => $value) {
                        switch ($key) {
                            case 'type':
                                if (is_array($value)) {
                                    $query = $query->where(function ($q) use ($value) {
                                        foreach ($value as $mediaType) {

                                            $mimeType = match ($mediaType) {
                                                'image' => 'image/%',
                                                'video' => 'video/%',
                                                'audio' => 'audio/%',
                                                'document' => 'application/%',
                                                'archive' => 'application/zip',
                                                default => null,
                                            };
                                            if ($mimeType == '%' || is_null($mimeType)) {
                                                continue;
                                            }
                                            if (str_contains($mimeType, '%')) {
                                                $q->orWhere('mime_type', 'like', $mimeType);
                                            } else {
                                                $q->orWhere('mime_type', $mimeType);
                                            }
                                        }
                                    });
                                }

                                break;
                            default:
                                if (! is_null($value)) {
                                    $query->where($key, $value);
                                }

                                break;
                        }
                    }
                });
        }

        return $query->get();
    }

    public function render()
    {
        return view('inspirecms-support::livewire.components.media-library', [
            'mediaItems' => $this->getMediaFromParent(),
        ]);
    }

    //region Helpers
    protected function resetSelectedMedia(): void
    {
        $this->selectedMediaId = [];
        $this->selectedMedia = null;
    }

    protected function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    protected function createMediaFromUploadedFile(TemporaryUploadedFile $file): Model
    {
        $media = $this->getEloquentQuery()->create([
            'parent_id' => $this->parentKey,
            'title' => $file->getClientOriginalName(),
        ]);

        $media->addMediaWithMappedProperties($file);

        return $media;
    }

    protected function isFilterColumnInvisible(string $column): bool
    {
        return in_array($column, $this->formConfig['filter']['invisible_columns'] ?? []);
    }

    protected function isSortColumnInvisible(string $column): bool
    {
        return in_array($column, $this->formConfig['sort']['invisible_columns'] ?? []);
    }

    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::with(['media']);
    }

    protected static function getMediaAssetModel(): string
    {
        return ModelRegistry::get(\SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset::class);
    }

    protected static function getRootLevelParentId(): string | int
    {
        return app(static::getMediaAssetModel())->getRootLevelParentId();
    }
    //endregion Helpers
}
