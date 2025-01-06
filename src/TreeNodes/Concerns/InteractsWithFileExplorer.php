<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Forms;
use Filament\Notifications\Notification;
use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

/**
 * @property Forms\Form $mountedTreeNodeItemActionForm
 */
trait InteractsWithFileExplorer
{
    use CanSelectFileItem;
    use HasTreeNodeItemActions;

    protected FileExplorer $fileExplorer;

    public function bootInteractsWithFileExplorer()
    {
        $this->fileExplorer = $this->fileExplorer($this->makeFileExplorer());
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

    public function getTreeNode()
    {
        return $this->getFileExplorer();
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

    // region Forms
    /**
     * @return array<string, Forms\Form>
     */
    protected function getInteractsWithFileExplorerForms(): array
    {
        return [
            'mountedTreeNodeItemActionForm' => $this->getMountedTreeNodeItemActionForm(),
        ];
    }
    // endregion Forms
}
