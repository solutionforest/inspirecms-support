<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\FileExplorer\Concerns;

use Filament\Notifications\Notification;

trait CanSelectFileItem
{
    public function getFileContent(string $path)
    {
        if (empty($path)) {
            return null;
        }

        try {
            $disk = $this->getDisk();
            if (filled($disk)) {
                if ($disk->exists($path)) {
                    return $disk->get($path);
                } else {
                    throw new \Exception("Failed to read file: {$path}. File does not exist or is not accessible.");
                }
            }

            return file_get_contents($path);

        } catch (\Exception $e) {
            $this->getReadSelectedFileFailedNotification()->send();
        }

        return null;
    }

    public function isSelectedItemDirectory(?string $path): bool
    {
        if ($path) {
            try {
                $fullPath = $this->getFullPath($path);

                return is_dir($fullPath);
            } catch (\Throwable $th) {
                // throw $th;
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

    public function attachItemsToNodes(string $parentKey, array $items, array &$nodes)
    {
        foreach ($nodes as &$node) {
            if ($node['path'] === $parentKey) {
                $node['children'] = array_merge($node['children'] ?? [], $items);

                return;
            }
        }

        // search deeper
        foreach ($nodes as &$node) {
            if (empty($node['children'])) {
                continue;
            }

            $this->attachItemsToNodes($parentKey, $items, $node['children']);
        }
    }
}
