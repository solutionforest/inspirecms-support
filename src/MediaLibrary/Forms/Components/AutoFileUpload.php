<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Forms\Components;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AutoFileUpload extends FileUpload
{
    public ?Closure $saveAutoUploadFileUsing = null;

    protected string $view = 'inspirecms-support::forms.components.auto-file-upload';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->storeFiles(false)
            ->extraAlpineAttributes(function (AutoFileUpload $component): array {
                return [
                    'x-on:autoupload-file--upload-error.window' => <<<'HTML'
                        const serverReturnError = $event.detail.error ?? null;
                        // If is array, it means multiple files upload error
                        if (Array.isArray(serverReturnError)) {
                            for (const error of serverReturnError) {
                                console.error('FilePond auto upload error:', error);
                            }
                            error = serverReturnError.join('<br>');
                        } else {
                            console.error('FilePond auto upload error:', serverReturnError ?? 'Unknown error');
                            error = 'Some files failed to upload';
                        }
                    HTML,
                    'x-on:autoupload-file--upload-success.window' => <<<'HTML'
                        const serverId = $event.detail.serverId ?? null;
                        const fileId = pond?.getFiles().find(file => file.serverId === serverId)?.id ?? null;
                        //console.log('FilePond auto upload success fileId:', fileId);
                        if (fileId != null) {
                            pond?.removeFile(fileId);
                        }
                    HTML,
                ];
            })
            ->registerListeners([
                'autoupload-file--start-multiple-upload' => [
                    function (AutoFileUpload $component, $statePath) {
                        if ($statePath !== $component->getStatePath()) {
                            return;
                        }
                        $component->handleAutoFilesUpload();
                    },
                ],
                'autoupload-file--start-upload' => [
                    function (AutoFileUpload $component, $statePath, $serverId = null) {
                        if ($statePath !== $component->getStatePath()) {
                            return;
                        }
                        if ($serverId) {
                            $serverId = array_map('trim', explode(',', $serverId));
                        }
                        $component->handleAutoFilesUpload($serverId);
                    },
                ],
            ]);
    }

    public function saveAutoUploadFileUsing(Closure $callback): static
    {
        $this->saveAutoUploadFileUsing = $callback;

        return $this;
    }

    public function saveAutoUploadFile(TemporaryUploadedFile $file): array
    {
        return (array) $this->evaluate($this->saveAutoUploadFileUsing, [
            'file' => $file,
        ]);
    }

    public function dispatchUploadFailedEvent($serverId, $errorMessage)
    {
        // Tell to frontend that the upload failed
        $this->getLivewire()->dispatch(
            'autoupload-file--upload-error',
            serverId: $serverId,
            error: is_array($errorMessage) ? array_values($errorMessage) : $errorMessage,
        );

        $errorNotification = Notification::make()
            ->danger()
            ->seconds(30);

        if (is_array($errorMessage)) {
            $errorNotification = $errorNotification
                ->title(str_replace(':count', count($errorMessage), 'Total :count files failed to upload'))
                ->body(
                    str(
                        collect($errorMessage)->values()->map(function ($error, $index) {
                            return str($error)->prepend($index + 1 . '. ')->markdown();
                        })->implode('')
                    )->toHtmlString()
                );
        } else {
            $errorNotification = $errorNotification
                ->title('File Upload Error')
                ->body(str($errorMessage)->markdown()->toHtmlString());
        }

        $errorNotification->send();
    }

    public function dispatchUploadSuccessEvent($serverId)
    {
        $this->getLivewire()->dispatch(
            'autoupload-file--upload-success',
            serverId: $serverId,
        );
    }

    public function handleAutoFilesUpload($serverId = null)
    {
        $state = $this->getState();

        $errors = [];

        foreach ($state as $key => $file) {

            if (is_array($serverId)) {
                // If serverId is an array, we check if the current key is in the serverId array.
                if (! in_array($key, $serverId)) {
                    continue;
                }
            } else {
                // If no serverId is provided, handle the upload for all files.
                if ($serverId && $key !== $serverId) {
                    continue;
                }
            }

            if (! $file instanceof TemporaryUploadedFile) {
                // If the file is not a TemporaryUploadedFile, we cannot proceed.
                $this->dispatchUploadFailedEvent($key, 'Invalid file type or file not found.');

                continue;
            }

            $result = $this->saveAutoUploadFile($file);

            if (data_get($result, 'success', false) === false) {

                $errorMessage = data_get($result, 'errorMessage', 'File upload failed.');
                // Multiply file upload error handling
                if (! $serverId || is_array($serverId)) {
                    // If no serverId is provided, we assume this is a multiple file upload.
                    // Combine all errors into a single message for multiple uploads
                    $errors[$key] = $errorMessage;
                } else {
                    $this->dispatchUploadFailedEvent($key, $errorMessage);
                }
            } else {
                // If the save operation was successful, dispatch a success event.
                $this->dispatchUploadSuccessEvent($key);
            }
        }

        if (! empty($errors)) {
            if (! $serverId) {
                $this->dispatchUploadFailedEvent(null, $errors);
            } else {
                collect($errors)->each(
                    fn ($error, $key) => $this->dispatchUploadFailedEvent($key, $error)
                );
            }
        }

        // Dispatch finished event
        if (! $serverId) {
            $this->getLivewire()->dispatch('autoupload-file--processfiles');
        } else {
            $this->getLivewire()->dispatch('autoupload-file--processfile', [
                'serverId' => $serverId,
            ]);
        }
    }
}
