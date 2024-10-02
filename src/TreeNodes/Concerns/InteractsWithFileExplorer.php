<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Notifications\Notification;
use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

trait InteractsWithFileExplorer
{
    use CanSelectFileItem;

    protected FileExplorer $fileExplorer;

    public function bootInteractsWithFileExplorer()
    {
        $this->fileExplorer = \Filament\Actions\Action::configureUsing(
            \Closure::fromCallable([$this, 'configureSelectedFileItemFormAction']),
            fn () => $this->fileExplorer($this->makeFileExplorer())
        );

        $this->cacheForm('selectedFileItemForm', $this->getSelectedFileItemForm());
    }

    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer
    {
        return $fileExplorer
            ->diskName($this->getDiskName())
            ->directory($this->getDirectory());
    }

    public function getFileExplorer(): FileExplorer
    {
        return $this->fileExplorer;
    }

    protected function makeFileExplorer(): FileExplorer
    {
        return FileExplorer::make($this);
    }

    public function getDiskName(): ?string
    {
        return null;
    }

    public function getDirectory(): ?string
    {
        return null;
    }

    public function getSelectedFileItemPath(): ?string
    {
        return $this->fileExplorerSelectedPath;
    }

    public function getPermissionDeniedNotification(): ?Notification
    {
        $title = $this->getPermissionDeniedNotificationTitle();

        if (! filled($title)) {
            return null;
        }

        return Notification::make()
            ->title($title)
            ->body($this->getPermissionDeniedNotificationBody())
            ->danger();
    }

    public function getPermissionDeniedNotificationTitle(): ?string
    {
        return __('inspirecms-support::notification.permission_denied.title');
    }

    public function getPermissionDeniedNotificationBody(): ?string
    {
        return __('inspirecms-support::notification.permission_denied.body');
    }
}
