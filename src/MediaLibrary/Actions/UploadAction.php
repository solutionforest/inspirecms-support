<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Services\MediaAssetService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        $this->failureNotificationTitle(__('inspirecms-support::media-library.buttons.upload.messages.error.title'));

        $this->authorize('create');

        $this->icon(FilamentIcon::resolve('inspirecms::upload'));

        $this->modalWidth('screen-xl');

        $this->stickyModalHeader();

        $this->stickyModalFooter();

        $this->modalSubmitActionLabel(__('inspirecms-support::media-library.buttons.upload.label'));

        $this->form(function () {

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

        })->action(function (array $data, self $action) {
            try {

                $target = $data['upload_from'] ?? 'file';

                switch ($target) {
                    case 'file':
                        if (empty($data['files']) || ! is_array($data['files'])) {
                            return;
                        }
                        $results = MediaAssetService::createMediaAssetFromFiles(
                            files: $data['files'],
                            parentKey: $this->getParentKey()
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
                            $this
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
                            return;
                        }
                        MediaAssetService::createMediaAssetFromUrl(
                            url: $data['url'],
                            parentKey: $this->getParentKey()
                        );

                        break;

                    default:
                        throw new \InvalidArgumentException(
                            'Invalid upload target specified: ' . $target
                        );
                }

                $this->success();

            } catch (\Throwable $th) {

                $this
                    ->failureNotification(
                        fn (Notification $notification) => $notification
                            ->body($th->getMessage())
                    )
                    ->failure();
            }
        });
    }
}
