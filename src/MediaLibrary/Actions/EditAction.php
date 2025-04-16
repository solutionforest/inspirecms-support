<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class EditAction extends ItemAction
{
    public static function getDefaultName(): ?string
    {
        return 'edit';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.buttons.edit.label'));

        $this->modalHeading(fn () => __('inspirecms-support::media-library.buttons.edit.heading', ['name' => $this->getModelLabel()]));

        $this->successNotificationTitle(__('inspirecms-support::media-library.buttons.edit.messages.success.title'));

        $this->authorize('update');

        $this->groupedIcon(FilamentIcon::resolve('inspirecms::edit'));

        $this
            ->fillForm(function (?Model $record) {
                $data = $record?->attributesToArray();
                if ($record && $record instanceof MediaAsset) {
                    $media = $record->getFirstMedia();
                    if ($media) {
                        $data['file'] = $media->getPathRelativeToRoot();
                    }
                }

                return $data;
            })
            ->form(function () {

                $file = FileUpload::make('file')
                    ->label(__('inspirecms-support::media-library.forms.file.label'))
                    ->validationAttribute(__('inspirecms-support::media-library.forms.file.validation_attribute'))
                    ->disk(MediaLibraryRegistry::getDisk())
                    ->directory(MediaLibraryRegistry::getDirectory())
                    ->deletable(false)
                    ->openable()
                    ->downloadable()
                    ->imageEditor()
                    ->saveUploadedFileUsing(function ($component, TemporaryUploadedFile $file, Model $record): ?string {
                        try {
                            if (! $file->exists()) {
                                return null;
                            }
                        } catch (UnableToCheckFileExistence $exception) {
                            return null;
                        }

                        if (! $record instanceof MediaAsset) {
                            return null;
                        }

                        $record->media()->delete();
                        $record->addMedia($file)->toMediaCollection();

                        return $record->getFirstMedia()->getPathRelativeToRoot();
                    });

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
                    TextInput::make('title')
                        ->label(__('inspirecms-support::media-library.forms.title.label'))
                        ->validationAttribute(__('inspirecms-support::media-library.forms.title.validation_attribute'))
                        ->required(),
                    TextInput::make('caption')
                        ->label(__('inspirecms-support::media-library.forms.caption.label'))
                        ->validationAttribute(__('inspirecms-support::media-library.forms.title.caption')),
                    Textarea::make('description')
                        ->label(__('inspirecms-support::media-library.forms.description.label'))
                        ->validationAttribute(__('inspirecms-support::media-library.forms.description.caption')),
                ];
            })
            ->action(function (array $data, ?Model $record, Action $action) {
                if (empty($data) || ! $record) {
                    return;
                }
                $record->update($data);
                $action->success();
            });
    }
}
