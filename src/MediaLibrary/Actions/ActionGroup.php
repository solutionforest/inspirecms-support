<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Actions\ActionGroup as BaseActionGroup;

class ActionGroup extends BaseActionGroup
{
    public function record($record): static
    {
        foreach ($this->actions as &$action) {
            $action->record($record);
        }

        return $this;
    }

    public function records($records): static
    {
        foreach ($this->actions as &$action) {
            if ($action instanceof ItemBulkAction || $action instanceof ActionGroup) {
                $action->records($records);
            }
        }

        return $this;
    }
}
