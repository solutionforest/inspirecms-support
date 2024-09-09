<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\Contracts;

use Filament\Forms\Form;
use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer;

interface HasFileExplorer
{
    public function fileExplorer(FileExplorer $fileExplorer): FileExplorer;

    public function selectedFileItemForm(Form $form): Form;

    public function getSelectedFileItemFormActions(): array;

    public function saveSelectedItem();
}
