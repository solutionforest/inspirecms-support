<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use Spatie\MediaLibrary\Conversions\FileManipulator;

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

        $this->successNotification(fn (Notification $notification) => $notification
            ->title(__('inspirecms-support::media-library.buttons.edit.messages.success.title'))
            ->body('If you re-upload the file, please refresh the page to see the changes, e.g. thumbanil of media.')
        );

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
                    // ->deletable(false) // Allow re-upload without changing the file name, path, and id
                    ->helperText('You can re-upload the file without changing the file name, path, and id.')
                    ->openable()
                    ->downloadable()
                    ->imageEditor()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Model $record): ?string {
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

                        // Avoid delete 'media' model, just delete the media file and replace it with the new file
                        // - Keep:
                        //      -  file_path
                        //      -  file_name
                        //      -  mime_type
                        try {

                            DB::beginTransaction();

                            if (($media = $record->getFirstMedia())) {

                                $disk = $media->disk;
                                $path = $media->getPathRelativeToRoot();

                                // Replace the existing media file with the new file
                                Storage::disk($disk)->delete($path);
                                Storage::disk($disk)->putFileAs(dirname($path), $file, $media->file_name);

                                // Mark the media as conversion not generated, so it will be regenerated
                                $media->markAsConversionNotGenerated($media->collection_name);
                                $fileManipulator = app(FileManipulator::class);
                                $fileManipulator->createDerivedFiles($media);

                            } else {
                                // If no existing media, just add the new file
                                $record->addMediaWithMappedProperties($file);
                            }

                            $record->syncMediaProperties($record->getFirstMedia());

                            DB::commit();

                        } catch (\Throwable $th) {
                            Notification::make()
                                ->title('An error occurred while saving the media file.')
                                ->body($th->getMessage())
                                ->danger()
                                ->send();
                            DB::rollBack();
                            throw new Halt;
                        }

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
                    TextInput::make('id')
                        ->label(__('inspirecms-support::media-library.forms.id.label'))
                        ->validationAttribute(__('inspirecms-support::media-library.forms.id.validation_attribute'))
                        ->readOnly(),
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
            ->action(function (array $data, ?Model $record, Action $action, \Livewire\Livewire|\Livewire\Component $livewire) {
                if (empty($data) || ! $record) {
                    return;
                }
                $record->update($data);
                $action->success();

                // Ensure the media is updated
                $livewire->dispatch('media-thumb-updated', [
                    'id' => $record->getKey() ?? null,
                ]);
            });
    }
}
