<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class UploadAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'upload';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.buttons.upload.label'));

        $this->modalHeading(__('inspirecms-support::media-library.buttons.upload.heading'));

        $this->successNotificationTitle(__('inspirecms-support::media-library.buttons.upload.messages.success.title'));

        $this->authorize('create');

        $this->icon(FilamentIcon::resolve('inspirecms::upload'));

        $this->modalWidth('screen-xl');

        $this->stickyModalHeader();

        $this->stickyModalFooter();

        $this->modalSubmitActionLabel(__('inspirecms-support::media-library.buttons.upload.label'));

        $this->form(function () {

            $file = \Filament\Forms\Components\FileUpload::make('files')
                ->label(__('inspirecms-support::media-library.forms.files.label'))
                ->validationAttribute(__('inspirecms-support::media-library.forms.files.validation_attribute'))
                ->disk(MediaLibraryRegistry::getDisk())
                ->directory(MediaLibraryRegistry::getDirectory())
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

            return [$file];

        })->action(function (array $data) {
            if (empty($data['files'])) {
                return;
            }

            $this->uploadMedia($data['files']);
            $this->success();
        });
    }

    protected function uploadMedia(array $files)
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
        $media = $this->getModel()::create([
            'parent_id' => $this->getParentKey(),
            'title' => $file->getClientOriginalName(),
        ]);

        $media->addMediaWithMappedProperties($file);

        return $media;
    }
}
