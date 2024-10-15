<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
    use InteractsWithActions;
    use InteractsWithForms;

    #[Url(as: 'p')]
    public string | int | null $parentKey = null;

    public bool $isMultiple = false;

    public array $filter = [];

    public array | string | int | null $selectedMediaId = null;

    public null | Model | array $selectedMedia = null;

    public ?array $uploadFileData = [];

    public ?array $filterData = [];

    public array $modelableConfig = [];

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

    //region Form
    public function uploadFileForm(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('files')
                    ->disk(MediaLibraryManifest::getDisk())
                    ->directory(MediaLibraryManifest::getDirectory())
                    ->multiple(),
            ])
            ->statePath($this->getFormStatePathFor('uploadFileForm'));
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->placeholder('Search by title')
                    ->live(true),
                TagsInput::make('mime_type')
                    ->label('Mime Type')
                    ->placeholder('Search by mime type')
                    ->live(true),
            ])
            ->statePath($this->getFormStatePathFor('filterForm'));
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

    public function createFolderAction(): Action
    {
        return Action::make('createFolder')
            ->form([
                TextInput::make('title')->required(),
            ])
            ->successNotificationTitle('Folder created')
            ->action(function (array $data, Action $action) {
                if (empty($data['title'])) {
                    return;
                }
                $this->createMediaFolder($data['title']);
                $action->success();
            });
    }

    public function getFormStatePathFor(string $formName): ?string
    {
        return match ($formName) {
            'uploadFileForm' => 'uploadFileData',
            'filterForm' => 'filter',
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
        $this->filterForm->fill($this->filter ?? []);
    }
    //endregion Form

    public function getMediaFromParent()
    {
        $query = $this->getEloquentQuery()
            ->with('media')
            ->parent($this->parentKey);

        if (! empty($this->filter)) {
            $filter = $this->filter;
            $query = $query->where(
                fn ($q) => $q
                    ->orWhere('is_folder', true)
                    ->orWhereHas('media', function ($query) use ($filter) {

                        foreach ($filter as $key => $value) {
                            switch ($key) {
                                case 'mime_type':
                                    if (is_array($value)) {
                                        $query = $query->where(function ($q) use ($value) {
                                            foreach ($value as $mimeType) {

                                                $mimeType = str_replace(['*'], ['%'], $mimeType);
                                                if ($mimeType == '%') {
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
                                case 'title':
                                    if (! is_null($value)) {
                                        $query->where('title', 'like', "%$value%");
                                    }
                                    break;
                                default:
                                    if (!is_null($value)) {
                                        $query->where($key, $value);
                                    }
                                    break;
                            }
                        }
                    })
            );
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

    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::query();
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
