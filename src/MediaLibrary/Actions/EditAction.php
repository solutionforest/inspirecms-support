<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use SolutionForest\InspireCms\Support\Services\MediaAssetService;

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

        $this->successNotification(
            fn (Notification $notification) => $notification
                ->title(__('inspirecms-support::media-library.buttons.edit.messages.success.title'))
                ->body(__('inspirecms-support::media-library.buttons.edit.messages.success.body'))
        );

        $this->failureNotificationTitle(__('inspirecms-support::media-library.buttons.edit.messages.error.title'));

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

                $data['upload_from'] = 'file'; // Default to file upload

                return $data;
            })
            ->form(function () {

                $selectField = Select::make('upload_from')
                    ->label(__('inspirecms-support::media-library.forms.upload_from.label'))
                    ->validationAttribute(__('inspirecms-support::media-library.forms.upload_from.validation_attribute'))
                    ->options([
                        'file' => __('inspirecms-support::media-library.forms.upload_from.options.file'),
                        'url' => __('inspirecms-support::media-library.forms.upload_from.options.url'),
                    ])
                    ->live()
                    ->required()
                    ->default('file');

                $fromUrlField = TextInput::make('url')
                    ->label(__('inspirecms-support::media-library.forms.url.label'))
                    ->validationAttribute(__('inspirecms-support::media-library.forms.url.validation_attribute'))
                    ->url()
                    ->required()
                    ->placeholder('https://example.com/image.jpg')
                    ->visible(function ($get) {
                        return $get('upload_from') === 'url';
                    });

                $fromFileField = FileUpload::make('file')
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

                        $this->handleMediaFileUpdateFromFile($record, $file);

                        return $record->getFirstMedia()->getPathRelativeToRoot();
                    })
                    ->visible(function ($get) {
                        return $get('upload_from') === 'file';
                    });

                return [
                    $selectField,
                    MediaAssetHelper::configureFileUploadField($fromFileField),
                    $fromUrlField,
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
            ->action(function (array $data, ?Model $record, \Livewire\Livewire | \Livewire\Component $livewire) {
                if (empty($data) || ! $record) {
                    return;
                }

                $target = $data['upload_from'] ?? 'file';
                $fromUrl = $data['url'] ?? '';
                unset($data['upload_from'], $data['url']);

                if ($target === 'url') {

                    // already wrap in db-transaction
                    try {

                        $mediaAsset = MediaAssetService::uploadMediaFromUrlWithoutDelete(
                            $record,
                            $fromUrl
                        );

                        $mediaAsset->update($data);

                    } catch (\Throwable $th) {
                        $this
                            ->failureNotification(
                                fn (Notification $notification) => $notification
                                    ->body($th->getMessage())
                            )
                            ->failure();

                        return;
                    }
                } else {

                    $record->update($data);
                }

                $this->success();

                // Ensure the media is updated
                $livewire->dispatch('media-thumb-updated', [
                    'id' => $record->getKey() ?? null,
                ]);
            });
    }

    protected function handleMediaFileUpdateFromFile(Model | MediaAsset $record, TemporaryUploadedFile $file)
    {
        try {

            $mediaAsset = MediaAssetService::updateMediaFromFileWithoutDelete($record, $file);

        } catch (\Throwable $th) {
            Notification::make()
                ->title($this->getFailureNotificationTitle() ?? __('inspirecms-support::media-library.buttons.edit.messages.error.title'))
                ->body($th->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }
}
