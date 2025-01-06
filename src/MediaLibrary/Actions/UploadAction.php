<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

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

        $this->label(__('inspirecms-support::media-library.actions.upload.label'));

        $this->modalHeading(__('inspirecms-support::media-library.actions.upload.modal.heading'));

        $this->successNotificationTitle(__('inspirecms-support::media-library.actions.upload.notification.uploaded.title'));

        $this->authorize('create');

        $this->icon('heroicon-o-arrow-up-tray');

        $this->modalWidth('screen-xl');

        $this->stickyModalHeader();

        $this->stickyModalFooter();

        $this->modalSubmitActionLabel(__('inspirecms-support::media-library.actions.upload.modal.submit.label'));

        $this->form([

            \Filament\Forms\Components\FileUpload::make('files')
                ->label(__('inspirecms-support::media-library.forms.files.label'))
                ->disk(MediaLibraryRegistry::getDisk())
                ->directory(MediaLibraryRegistry::getDirectory())
                ->imageEditor()
                ->multiple()
                ->storeFiles(false),

        ])->action(function (array $data) {
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
