<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use SolutionForest\InspireCms\Support\Exceptions\NotImplementedException;

/**
 * @property Form $selectedFileItemForm
 */
trait CanSelectFileItem
{
    public array $fileExplorerSelectedItemData = [];

    public ?string $fileExplorerSelectedPath = null;

    public array $cachedFileExplorerItems = [];

    #[On('getFilesFrom')]
    public function getFilesFromDirectory(string $path, int $level)
    {
        if (! $this->getFileExplorer()->checkPermission($path)) {
            $this->getPermissionDeniedNotification()->send();
            return;
        }

        if (isset($this->cachedFileExplorerItems[$path])) {
            return;
        }

        $children = $this->getFileExplorer()->getFileDataCollection($path, $level)->toArray();

        $this->cachedFileExplorerItems[$path] = $children;
    }

    #[On('selectFile')]
    public function selectFile($path)
    {
        // Add a guard clause to prevent re-selection of the same file
        if ($this->fileExplorerSelectedPath && $this->fileExplorerSelectedPath === $path) {
            return;
        }

        // A guard for avoiding selecting a directory
        if ($this->getFileExplorer()->isSelectedItemDirectory($path)) {
            return;
        }

        if (empty($path)) {
            $this->selectedFileItemForm->fill([]);
            return;
        }

        $this->fileExplorerSelectedPath = $path;

        $this->dispatch('fileExplorerSelectedItemChanged', $path);
    }

    #[On('fileExplorerSelectedItemChanged')]
    public function fileExplorerSelectedItemChanged(?string $path)
    {
        $data = [
            'path' => $path,
            'full_path' => $this->getFileExplorer()->getFullPath($path),
            'content' => $this->getFileExplorer()->getFileContent($path),
        ];

        $data = $this->mutateFileExplorerSelectedItemDataToFill($data);

        $this->selectedFileItemForm->fill($data);

    }

    protected function mutateFileExplorerSelectedItemDataToFill(array $data)
    {
        return $data;
    }

    public function getGroupedNodeItems()
    {
        $fileExplorer = $this->getFileExplorer();

        if (empty($this->cachedFileExplorerItems)) {
            $rootPath = $fileExplorer->getRootPath();
            $items = $this->getFileExplorer()->getFileDataCollection($rootPath, 0)->toArray();
            $this->cachedFileExplorerItems[$rootPath] = $items;
        }
        
        // Convert the items array as node tree items array
        $nodes = [];
        $groupByDepth = collect($this->cachedFileExplorerItems)->flatten(1)->groupBy('level');
        foreach ($groupByDepth as $depth => $flattenItems) {
            if ($depth === 0) {
                $nodes = collect($flattenItems)->map(fn ($item) => array_merge($item, ['children' => []]))->toArray();
                continue;
            }

            $groupByParentKey = collect($flattenItems)
                ->groupBy(fn ($item) => str($item['path'])->beforeLast('/')->toString())
                ->toArray();
            foreach ($groupByParentKey as $parentKey => $items) {
                $fileExplorer->attachItemsToNodes($parentKey, $items, $nodes);
            }

        }

        return $nodes;
    }

    public function getSelectedFileItemForm(): Form
    {
        if ((! $this->isCachingForms) && $this->hasCachedForm('selectedFileItemForm')) {
            return $this->getForm('selectedFileItemForm');
        }

        return $this
            ->selectedFileItemForm(
                $this
                    ->makeForm()
                    ->schema($this->getSelectedFileItemFormSchema())
                    ->statePath('fileExplorerSelectedItemData')
            );
    }

    public function getSelectedFileItemFormSchema(): array
    {
        if ($schema = $this->getFileExplorer()->getSelectedFileItemFormSchema()) {
            return $schema;
        }

        return [
            Forms\Components\TextInput::make('path')->readOnly(),
            Forms\Components\TextInput::make('full_path')->readOnly(),
            Forms\Components\Textarea::make('content')->readOnly(),
        ];
    }

    public function selectedFileItemForm(Form $form): Form
    {
        return $form;
    }

    public function getSelectedFileItemFormActions(): array
    {
        return $this->getFileExplorer()->getSelectedItemFormActions();
    }

    protected function configureSelectedFileItemFormAction(Action $action): void
    {
        //
    }

    public function saveSelectedItem()
    {
        throw new NotImplementedException('Please implement your ' . __FUNCTION__ . ' function.');
    }
}
