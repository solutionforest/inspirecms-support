<?php

namespace SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

trait HasSelectedItemForm
{
    protected array $selectedModelItemFormActions = [];

    protected ?array $selectedModelItemFormSchema = null;

    public function selectedModelItemFormSchema(array $schema): static
    {
        $this->selectedModelItemFormSchema = $schema;

        return $this;
    }

    public function selectedModelItemFormActions(array $ations): static
    {
        $this->selectedModelItemFormActions = $ations;

        return $this;
    }

    public function getSelectedModelItemFormSchema(): ?array
    {
        return $this->selectedModelItemFormSchema;
    }

    public function getSelectedModelItemFormActions(): array
    {
        return $this->selectedModelItemFormActions;
    }
}
