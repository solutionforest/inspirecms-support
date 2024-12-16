<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\BaseAction;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class EditAction extends BaseAction
{
    public static function getDefaultName(): ?string
    {
        return 'edit';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.actions.edit.label'));

        $this->modalHeading(fn () => __('inspirecms-support::media-library.actions.edit.modal.heading', ['name' => $this->getModelLabel()]));
        
        $this->successNotificationTitle(__('inspirecms-support::media-library.actions.edit.notifications.saved.title'));

        $this->authorize('update');

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
            ->form([
                FileUpload::make('file')
                    ->label(__('inspirecms-support::media-library.forms.file.label'))
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
                    }),
                TextInput::make('title')
                    ->label(__('inspirecms-support::media-library.forms.title.label'))
                    ->required(),
                TextInput::make('caption')
                    ->label(__('inspirecms-support::media-library.forms.caption.label')),
                Textarea::make('description')
                    ->label(__('inspirecms-support::media-library.forms.description.label')),
            ])
            ->action(function (array $data, ?Model $record, Action $action) {
                if (empty($data) || ! $record) {
                    return;
                }
                $record->update($data);
                $action->success();
            });
    }
}
