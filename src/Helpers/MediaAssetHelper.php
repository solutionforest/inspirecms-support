<?php

namespace SolutionForest\InspireCms\Support\Helpers;

use Exception;
use Filament\Forms\Components\Actions\Action as FormComponentAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components\AutoFileUpload;
use SolutionForest\InspireCms\Support\MediaLibrary\MediaLibraryComponent;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;
use SolutionForest\InspireCms\Support\Services\MediaAssetService;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\Support\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaAssetHelper
{
    private const DISPLAYED_COLUMNS_DEFAULT = [
        'created_at',
        'updated_at',
        'uploaded_by',
    ];

    private const DISPLAYED_COLUMNS_NON_FOLDER = [
        'model_id',
        'file_name',
        'mime_type',
        'size',
        ...self::DISPLAYED_COLUMNS_DEFAULT,
    ];

    private const DISPLAYED_COLUMNS_FOLDER = [
        'title',
        ...self::DISPLAYED_COLUMNS_DEFAULT,
    ];

    /**
     * @throws \Throwable
     */
    public static function validateMediaBeforeAddFromUrl(FileAdder $fileAdder)
    {
        // Validate size
        if (($maxSize = MediaLibraryRegistry::getMaxSize()) || ($minSize = MediaLibraryRegistry::getMinSize())) {

            // Get size from temporary file
            /**
             * @var string
             */
            $tempFilePath = $fileAdder->getFile();
            $tempFileSize = filesize($tempFilePath);

            if (isset($maxSize) && $maxSize != null && $maxSize > -1 && $tempFileSize > $maxSize) {
                $message = "File size of {$tempFileSize} bytes exceeds the maximum allowed size of {$maxSize} bytes.";

                throw new FileIsTooBig($message);
            }

            if (isset($minSize) && $minSize != null && $minSize > -1 && $tempFileSize < $minSize) {
                throw new Exception("The file size is less than the minimum allowed size of {$minSize} bytes.");
            }
        }
    }

    /**
     * @return class-string<Model | MediaAsset>
     */
    public static function getMediaAssetModel()
    {
        return ModelRegistry::get(MediaAsset::class);
    }

    public static function getDefaultCollectionName(): string
    {
        return 'default';
    }

    public static function getDisk(): string
    {
        return MediaLibraryRegistry::getDisk();
    }

    public static function getFileAutoUploadField($parentKey, $name = 'files'): FileUpload
    {
        $handleFileUploaded = function (\Livewire\Component $livewire) {
            if ($livewire instanceof MediaLibraryComponent) {
                // Refresh the asset on media library
                $livewire->clearCache();
            }
        };
        $field = AutoFileUpload::make($name)
            ->label(__('inspirecms-support::media-library.forms.files.label'))
            ->validationAttribute(__('inspirecms-support::media-library.forms.files.validation_attribute'))
            ->multiple()
            ->hintActions([
                FormComponentAction::make('uploadByType')
                    ->button()->outlined()
                    ->label(__('inspirecms-support::media-library.buttons.upload_by_type.label'))
                    ->modalHeading(__('inspirecms-support::media-library.buttons.upload_by_type.heading'))
                    ->successNotificationTitle(__('inspirecms-support::media-library.buttons.upload_by_type.messages.success.title'))
                    ->failureNotificationTitle(__('inspirecms-support::media-library.buttons.upload_by_type.messages.error.title'))
                    ->icon(FilamentIcon::resolve('inspirecms::upload'))
                    ->modalWidth('screen-xl')
                    ->stickyModalHeader()
                    ->stickyModalFooter()
                    ->modalSubmitActionLabel(__('inspirecms-support::media-library.buttons.upload.label'))
                    ->after(fn (\Livewire\Component $livewire) => $handleFileUploaded($livewire))
                    ->fillForm([
                        'upload_from' => 'url',
                        'files' => [],
                        'url' => '',
                    ])
                    ->form(function () {

                        $selectField = Select::make('upload_from')
                            ->label(__('inspirecms-support::media-library.forms.upload_from.label'))
                            ->validationAttribute(__('inspirecms-support::media-library.forms.upload_from.validation_attribute'))
                            ->options([
                                'file' => __('inspirecms-support::media-library.forms.upload_from.options.file'),
                                'url' => __('inspirecms-support::media-library.forms.upload_from.options.url'),
                            ])
                            ->live()
                            ->required();

                        $fromUrlField = TextInput::make('url')
                            ->label(__('inspirecms-support::media-library.forms.url.label'))
                            ->validationAttribute(__('inspirecms-support::media-library.forms.url.validation_attribute'))
                            ->url()
                            ->required()
                            ->placeholder('https://example.com/image.jpg')
                            ->visible(function ($get) {
                                return $get('upload_from') === 'url';
                            });

                        $fromFileField = FileUpload::make('files')
                            ->label(__('inspirecms-support::media-library.forms.files.label'))
                            ->validationAttribute(__('inspirecms-support::media-library.forms.files.validation_attribute'))
                            ->imageEditor()
                            ->multiple()
                            ->storeFiles(false)
                            ->visible(function ($get) {
                                return $get('upload_from') === 'file';
                            });

                        return [
                            $selectField,
                            MediaAssetHelper::configureFileUploadField($fromFileField),
                            $fromUrlField,
                        ];
                    })
                    ->action(function (array $data, FormComponentAction $action) use ($parentKey) {
                        try {

                            $target = $data['upload_from'] ?? 'file';

                            switch ($target) {
                                case 'file':
                                    if (empty($data['files']) || ! is_array($data['files'])) {
                                        return;
                                    }
                                    $results = MediaAssetService::createMediaAssetFromFiles(
                                        files: $data['files'],
                                        parentKey: $parentKey,
                                    );
                                    // $successCount = data_get($results, 'success', 0);
                                    // $totalCount = count($data['files']);
                                    $failMessages = collect($results['fails'] ?? [])
                                        ->map(function ($array) {
                                            $file = $array['file'] ?? 'Unknown file';
                                            if ($file instanceof UploadedFile) {
                                                $file = $file->getClientOriginalName();
                                            }
                                            $reason = $array['error'] ?? 'Unknown error';

                                            return "<ul><li>File: {$file}</li><li>Error: {$reason}</li></ul>";
                                        })
                                        ->map(fn ($message) => "<li>{$message}</li>");

                                    if ($failMessages->isNotEmpty()) {
                                        $action
                                            ->failureNotification(
                                                fn (Notification $notification) => $notification
                                                    ->title('Some files failed to upload')
                                                    ->body(str($failMessages->implode(''))->wrap('<ul>', '</ul>'))
                                                    ->warning()
                                            )
                                            ->failure();

                                        return;
                                    }

                                    break;

                                case 'url':

                                    if (empty($data['url'])) {
                                        throw new \InvalidArgumentException('URL cannot be empty.');
                                    }

                                    MediaAssetService::createMediaAssetFromUrl(
                                        url: $data['url'] ?? '',
                                        parentKey: $parentKey,
                                    );

                                    break;

                                default:
                                    throw new \InvalidArgumentException(
                                        'Invalid upload target specified: ' . $target
                                    );
                            }

                            $action->success();

                        } catch (\Throwable $th) {
                            logger()->error('Failed to upload file by type: ' . $th->getMessage(), [
                                'data' => $data,
                                'exception' => $th,
                            ]);

                            $action
                                ->failureNotification(
                                    fn (Notification $notification) => $notification
                                        ->body($th->getMessage())
                                )
                                ->failure();
                        }
                    }),
            ])
            ->storeFiles(false)
            ->saveAutoUploadFileUsing(function (TemporaryUploadedFile $file, \Livewire\Component $livewire) use ($parentKey, $handleFileUploaded) {
                $error = null;
                $isSuccess = false;

                try {
                    $mediaAsset = MediaAssetService::createMediaAssetFromFile(
                        file: $file,
                        parentKey: $parentKey,
                    );

                    if (! $mediaAsset) {
                        $error = 'Failed to create media asset from file.';
                    } else {
                        $isSuccess = true;
                        $handleFileUploaded($livewire);
                    }

                } catch (Exception $e) {
                    logger()->error('Failed to save auto upload file: ' . $e->getMessage(), [
                        'file' => $file,
                        'exception' => $e,
                    ]);
                    $error = $e->getMessage();
                    $isSuccess = false;
                }

                return [
                    'file' => $file,
                    'success' => $isSuccess,
                    'errorMessage' => filled($error) ? str('File **:filename** failed to upload (Detail: :errorMessage)')
                        ->replace(':filename', $file->getClientOriginalName())
                        ->replace(':errorMessage', $error)
                        ->toString() : null,
                ];
            });

        return self::configureFileUploadField($field);
    }

    public static function configureFileUploadField(FileUpload $field)
    {
        if (MediaLibraryRegistry::hasLimitedMimeTypes()) {
            $field->acceptedFileTypes(MediaLibraryRegistry::getLimitedMimeTypes());
        }

        if (($maxSize = MediaLibraryRegistry::getMaxSize()) !== null) {
            $field->maxSize($maxSize);
        }
        if (($minSize = MediaLibraryRegistry::getMinSize()) !== null) {
            $field->minSize($minSize);
        }

        return $field;
    }

    public static function getMediaAssetDisplayedColumnsForFolder(): array
    {
        return array_unique(self::DISPLAYED_COLUMNS_FOLDER);
    }

    public static function getMediaAssetDisplayedColumnsForNonFolder(): array
    {
        return array_unique(self::DISPLAYED_COLUMNS_NON_FOLDER);
    }

    public static function getMediaAssetDisplayedColumnsForImage(): array
    {
        return array_unique(array_merge(
            static::getMediaAssetDisplayedColumnsForNonFolder(),
            [
                'custom-property.dimensions',
            ]
        ));
    }

    public static function getMediaAssetDisplayedColumnsForVideo(): array
    {
        return array_unique(array_merge(
            static::getMediaAssetDisplayedColumnsForNonFolder(),
            [
                'custom-property.duration',
                'custom-property.resolution',
                'custom-property.channels',
                'custom-property.bit_rate',
                'custom-property.frame_rate',
            ]
        ));
    }

    public static function getMediaAssetDisplayedColumnsForAudio(): array
    {
        return array_unique(array_merge(
            static::getMediaAssetDisplayedColumnsForNonFolder(),
            [
                'custom-property.duration',
                'custom-property.channels',
                'custom-property.bit_rate',
            ]
        ));
    }

    public static function getHumanFileSize(int | float $size): string
    {
        return File::getHumanReadableSize($size);
    }
}
