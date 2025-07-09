<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UploadAction extends Action
{
    protected bool $uploadFromFiles = true;

    public static function getDefaultName(): ?string
    {
        return 'upload';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(function () {
            if ($this->isUploadFromFiles()) {
                return __('inspirecms-support::media-library.buttons.upload.label');
            }
            return __('inspirecms-support::media-library.buttons.upload_from_url.label');
        });

        $this->modalHeading(function () {
            if ($this->isUploadFromFiles()) {
                return __('inspirecms-support::media-library.buttons.upload.heading');
            }
            return __('inspirecms-support::media-library.buttons.upload_from_url.heading');
        });

        $this->successNotificationTitle(function () {
            return __('inspirecms-support::media-library.buttons.upload.messages.success.title');
        });

        $this->authorize('create');

        $this->icon(FilamentIcon::resolve('inspirecms::upload'));

        $this->modalWidth('screen-xl');

        $this->stickyModalHeader();

        $this->stickyModalFooter();

        $this->modalSubmitActionLabel(__('inspirecms-support::media-library.buttons.upload.label'));

        $this->form(function () {

            if (! $this->isUploadFromFiles()) {
                return [
                    TextInput::make('url')
                        ->label(__('inspirecms-support::media-library.forms.url.label'))
                        ->validationAttribute(__('inspirecms-support::media-library.forms.url.validation_attribute'))
                        ->url()
                        ->required()
                        ->placeholder('https://example.com/image.jpg'),
                ];
            }

            $file = FileUpload::make('files')
                ->label(__('inspirecms-support::media-library.forms.files.label'))
                ->validationAttribute(__('inspirecms-support::media-library.forms.files.validation_attribute'))
                ->imageEditor()
                ->multiple()
                ->storeFiles(false);

            if (MediaLibraryRegistry::hasLimitedMimeTypes()) {
                $file->acceptedFileTypes(MediaLibraryRegistry::getLimitedMimeTypes());
            }

            if (($maxSize = MediaLibraryRegistry::getMaxSize()) !== null) {
                $file->maxSize($maxSize);
            }
            if (($minSize = MediaLibraryRegistry::getMinSize()) !== null) {
                $file->minSize($minSize);
            }

            return [
                $file,
            ];

        })->action(function (array $data, self $action) {
            try {

                DB::beginTransaction();

                if ($this->isUploadFromFiles()) {
                    
                    if (empty($data['files'])) {
                        return;
                    }

                    $this->handleMediaUploadFromFiles($data['files']);

                } else {

                    if (empty($data['url'])) {
                        return;
                    }
                    
                    $this->handleMediaUploadFromUrl($data['url']);

                }

                DB::commit();

                $this->success();
            } catch (\Throwable $th) {

                DB::rollBack();

                $detailErrorMessage = $th->getMessage();
                if ($th instanceof FileIsTooBig) {
                    $detailErrorMessage = __('inspirecms-support::media-library.buttons.upload.messages.error.file_too_big', [
                        'max_size' => MediaLibraryRegistry::getMaxSize(),
                    ]);
                }
                
                Notification::make()
                    ->title(__('inspirecms-support::media-library.buttons.upload.messages.error.title'))
                    ->body($detailErrorMessage)
                    ->danger()
                    ->send();

                $this->failure();
            }
        });
    }

    public function uploadFromFiles(bool $condition = true): static
    {
        $this->uploadFromFiles = $condition;

        return $this;
    }

    public function uploadFromUrl(bool $condition = true): static
    {
        return $this->uploadFromFiles(! $condition);
    }

    public function isUploadFromFiles(): bool
    {
        return $this->uploadFromFiles;
    }

    /**
     * @return Model & MediaAsset
     * @throws \Exception
     */
    protected function handleMediaUploadFromUrl(string $url)
    {
        $title = str(basename($url))->before('?')->toString();
        
        $asset = $this->getModel()::create([
            'parent_id' => $this->getParentKey(),
            'title' => $title,
        ]);

        $asset->addMediaFromUrlWithMappedProperties($url);

        return $asset;
    }

    protected function handleMediaUploadFromFiles(array $files)
    {
        foreach ($files as $file) {
            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $this->createMediaFromUploadedFile($file);
        }
    }

    /**
     * @return Model & MediaAsset
     */
    protected function createMediaFromUploadedFile(TemporaryUploadedFile $file)
    {
        $asset = $this->getModel()::create([
            'parent_id' => $this->getParentKey(),
            'title' => $file->getClientOriginalName(),
        ]);

        $asset->addMediaWithMappedProperties($file);

        return $asset;
    }
}
