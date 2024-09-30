<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Concerns;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Nette\NotImplementedException;

/**
 * @property Form $selectedFileItemForm
 */
trait CanSelectFileItem
{
    public array $fileExplorerSelectedItemData = [];

    #[On('getChildren')]
    public function getChildren(string $path, int $level)
    {
        if (! $this->getFileExplorer()->checkPermission($path)) {
            $this->getPermissionDeniedNotification()->send();

            return [];
        }

        $children = $this->getFileExplorer()->getFileDataCollection($path, $level);
        $this->dispatch('childrenLoaded', children: $children->toArray(), path: $path);
    }

    #[On('selectFile')]
    public function selectFile($path)
    {
        $fileExplorer = $this->getFileExplorer();

        $fileExplorerSelectedPath = $fileExplorer->getSelectedFilePath();

        ray($fileExplorerSelectedPath === $path, [$fileExplorerSelectedPath, $path])->blue()->label('a');
        // Add a guard clause to prevent re-selection of the same file
        if ($fileExplorerSelectedPath && $fileExplorerSelectedPath === $path) {
            return;
        }

        ray([empty($path), $fileExplorer])->green()->label('b');
        if (empty($path)) {
            $this->selectedFileItemForm->fill([]);

            return;
        }

        $fileExplorer->selectedFilePath($path);
        ray([empty($path), $fileExplorer, $fileExplorer->getSelectedFileContent()])->red()->label('c');

        $this->selectedFileItemForm->fill([
            'path' => $path,
            'full_path' => $fileExplorer->getFullPath($path),
        ]);

    }

    public function getSelectedFileItemForm(): Form
    {
        if ((! $this->isCachingForms) && $this->hasCachedForm('selectedFileItemForm')) {
            return $this->getForm('selectedFileItemForm');
        }

        return $this->selectedFileItemForm(
            $this
                ->makeForm()
                ->schema($this->getSelectedFileItemFormSchema())
        )->statePath('fileExplorerSelectedItemData');
    }

    protected function getSelectedFileItemFormSchema(): array
    {
        if ($schema = $this->getFileExplorer()->getSelectedFileItemFormSchema()) {
            return $schema;
        }

        return [
            Forms\Components\TextInput::make('path')->readOnly(),
            Forms\Components\TextInput::make('full_path')->readOnly(),
            Forms\Components\Textarea::make('content')
                ->readOnly()
                ->autosize(),
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
