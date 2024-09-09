<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\FileExplorer\Concerns;

trait HasSelectedItemForm
{
    protected array $selectedItemFormActions = [];

    protected ?array $selectedFileItemFormSchema = null;

    public function selectedFileItemFormSchema(array $schema): static
    {
        $this->selectedFileItemFormSchema = $schema;

        return $this;
    }

    public function selectedItemFormActions(array $actions): static
    {
        $this->selectedItemFormActions = $actions;

        return $this;
    }

    public function getSelectedFileItemFormSchema(): ?array
    {
        return $this->selectedFileItemFormSchema;
    }

    public function getSelectedItemFormActions(): array
    {
        return $this->selectedItemFormActions;
    }
}
