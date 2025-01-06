<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Js;

class ItemAction extends Action
{
    public function getLivewireClickHandler(): ?string
    {
        if (! $this->isLivewireClickHandlerEnabled()) {
            return null;
        }

        if (is_string($this->action)) {
            return $this->action;
        }

        if ($event = $this->getLivewireEventClickHandler()) {
            return $event;
        }

        return $this->generateJavaScriptClickHandler('mountMediaLibraryItemAction') ?? parent::getLivewireClickHandler();
    }

    protected function generateJavaScriptClickHandler(string $method): ?string
    {
        if ($record = $this->getRecord()) {
            return "{$method}('{$this->getName()}', '{$record->getKey()}')";
        }

        if ($selectedRecords = $this->getRecords()) {
            $recordKeys = Js::from($selectedRecords->map(fn ($record) => $record instanceof Model ? $record->getKey() : $record)->all());

            return "{$method}('{$this->getName()}', {$recordKeys})";
        }

        return null;
    }
}
