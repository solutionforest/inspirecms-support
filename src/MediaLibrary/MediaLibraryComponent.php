<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest;

/**
 * @property Form $uploadFileForm
 * @property Form $filterForm
 */
class MediaLibraryComponent extends Component implements HasActions, HasForms
{
    use Concerns\HasFilters;
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

    public function mount($parentKey = null)
    {
        $this->parentKey = $parentKey ?? static::getRootLevelParentId();
        if ($this->isMultiple()) {
            $this->selectedMediaId = [];
        }
        $this->fillForm();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            static::getRootLevelParentId() => 'Root',
        ];
        if ($this->parentKey == static::getRootLevelParentId()) {
            return $breadcrumbs;
        }

        $media = $this->getEloquentQuery()->find($this->parentKey);
        if ($media) {
            $breadcrumbs = array_merge($breadcrumbs, $media->ancestorsAndSelf()->mapWithKeys(fn ($item) => [
                $item->getKey() => $item->title,
            ])->all());

            return $breadcrumbs;
        }

        return [];
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

    public function deleteMedia()
    {
        if ($this->selectedMediaId) {
            $media = $this->getEloquentQuery()->find($this->selectedMediaId);
            if ($media) {
                $media->delete();
            }
        }

        Notification::make()
            ->title(__('inspirecms-support::media-library.actions.delete.notifications.deleted.title'))
            ->success()
            ->send();

        $this->selectedMediaId = [];
        $this->selectedMedia = null;
    }

    public function openFolder($mediaId = null)
    {
        $mediaId ??= $this->selectedMediaId;
        if (! $this->isMultiple()) {
            $this->selectedMediaId = [];
            $this->selectedMedia = null;
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
    public function createFolderAction(): Action
    {
        return Action::make('createFolder')
            ->modalHeading(__('inspirecms-support::media-library.actions.create_folder.modal.heading'))
            ->form([
                Forms\Components\TextInput::make('title')
                    ->label(__('inspirecms-support::media-library.forms.title.label'))
                    ->required(),
            ])
            ->successNotificationTitle(__('inspirecms-support::media-library.actions.create_folder.notifications.created.title'))
            ->action(function (array $data, Action $action) {
                if (empty($data['title'])) {
                    return;
                }
                $this->createMediaFolder($data['title']);
                $action->success();
            });
    }

    public function editMediaAction(): Action
    {
        return Action::make('editMedia')
            ->modalHeading(fn (Action $action) => __('inspirecms-support::media-library.actions.edit.modal.heading', ['name' => $action->getModelLabel()]))
            ->modelLabel(__('inspirecms-support::media-library.media'))
            ->record(fn () => $this->selectedMedia)
            ->fillForm(function (?Model $record) {
                $data = $record?->attributesToArray();

                return $data;
            })
            ->form(
                fn (Form $form) => $form
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('inspirecms-support::media-library.forms.title.label'))
                            ->required(),
                        Forms\Components\TextInput::make('caption')
                            ->label(__('inspirecms-support::media-library.forms.caption.label')),
                        Forms\Components\Textarea::make('description')
                            ->label(__('inspirecms-support::media-library.forms.description.label')),
                    ])
            )
            ->successNotificationTitle(__('inspirecms-support::media-library.actions.edit.notifications.saved.title'))
            ->action(function (array $data, ?Model $record, Action $action) {
                if (empty($data) || ! $record) {
                    return;
                }
                $record->update($data);
                $action->success();
            });
    }

    public function viewMediaAction(): Action
    {
        return Action::make('viewMedia')
            ->modalHeading(fn (Action $action) => __('inspirecms-support::media-library.actions.view.modal.heading', ['name' => $action->getModelLabel()]))
            ->modelLabel(__('inspirecms-support::media-library.media'))
            ->record(fn () => $this->selectedMedia)
            ->fillForm(function (?Model $record) {
                $data = $record?->attributesToArray();
                if ($record && $record instanceof \SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset) {
                    $media = $record->getFirstMedia();
                    if ($media) {
                        $data['media'] = $media->attributesToArray();
                    }
                }

                return $data;
            })
            ->form(
                fn (Form $form) => $form
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('inspirecms-support::media-library.forms.title.label'))
                            ->required(),
                        Forms\Components\Grid::make(2)
                            ->statePath('media')
                            ->schema([
                                Forms\Components\TextInput::make('file_name')
                                    ->label(__('inspirecms-support::media-library.forms.file_name.label')),
                                Forms\Components\TextInput::make('mime_type')
                                    ->label(__('inspirecms-support::media-library.forms.mime_type.label')),
                            ]),
                        Forms\Components\TextInput::make('caption')
                            ->label(__('inspirecms-support::media-library.forms.caption.label')),
                        Forms\Components\Textarea::make('description')
                            ->label(__('inspirecms-support::media-library.forms.description.label')),
                    ])
            )
            ->disabledForm()
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }
    //endregion Actions

    //region Form
    public function uploadFileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('files')
                    ->label(__('inspirecms-support::media-library.forms.files.label'))
                    ->disk(MediaLibraryManifest::getDisk())
                    ->directory(MediaLibraryManifest::getDirectory())
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
            default => $this->getFormStatePath(),
        };
    }

    protected function getForms(): array
    {
        return [
            'uploadFileForm',
            'filterForm',
        ];
    }

    protected function fillForm(): void
    {
        $this->uploadFileForm->fill();
        $this->fillFilterForm();
    }
    //endregion Form

    public function isFormCollapsed(string $name): bool
    {
        switch ($name) {
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
            ->parent($this->parentKey);

        $filter = $this->ensureFilter();

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

        $media->addMedia($file)->toMediaCollection();

        return $media;
    }

    protected function createMediaFolder(string $title): Model
    {
        return $this->getEloquentQuery()->create([
            'parent_id' => $this->parentKey,
            'title' => $title,
            'is_folder' => true,
        ]);
    }

    protected function isFilterColumnInvisible(string $column): bool
    {
        return in_array($column, $this->formConfig['filter']['invisible_columns'] ?? []);
    }

    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::with(['media']);
    }

    protected static function getMediaAssetModel(): string
    {
        return MediaLibraryManifest::getModel();
    }

    protected static function getRootLevelParentId(): string | int
    {
        return (new (static::getMediaAssetModel()))->getNestableRootValue();
    }
    //endregion Helpers
}
