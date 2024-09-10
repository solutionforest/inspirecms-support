<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\FileExplorer\Concerns;

use Filament\Notifications\Notification;

trait CanSelectFileItem
{
    protected ?string $selectedFilePath = null;

    public function selectedFilePath(string $path): static
    {
        $this->selectedFilePath = $path;

        return $this;
    }

    public function getSelectedFilePath(): ?string
    {
        return $this->selectedFilePath;
    }

    public function getSelectedFileContent()
    {
        $selectedFilePath = $this->getSelectedFilePath();

        if (empty($selectedFilePath)) {
            return null;
        }

        try {
            $disk = $this->getDisk();
            if (filled($disk)) {
                if ($disk->exists($selectedFilePath)) {
                    return $disk->get($selectedFilePath);
                } else {
                    throw new \Exception("Failed to read file: {$selectedFilePath}. File does not exist or is not accessible.");
                }
            }

            return file_get_contents($selectedFilePath);

        } catch (\Exception $e) {
            $this->getReadSelectedFileFailedNotification()->send();
        }
    }

    public function isSelectedItemDirectory(): bool
    {
        if ($this->selectedFilePath) {
            try {
                $fullPath = $this->getFullPath($this->selectedFilePath);

                return is_dir($fullPath);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        return false;
    }

    protected function getReadSelectedFileFailedNotification()
    {
        return Notification::make()
            ->title(__('inspirecms-support::notification.file_read_error.title'))
            ->body(__('inspirecms-support::notification.file_read_error.body'))
            ->danger();
    }
}
