<?php

namespace SolutionForest\InspireCms\Support\Tests\Fixtures\Livewire;

use SolutionForest\InspireCms\Support\MediaLibrary\MediaLibraryComponent;

class MediaLibrary extends MediaLibraryComponent
{
    public function mount()
    {
        parent::mount();

        $this->setErrorBag(new \Illuminate\Support\MessageBag());
    }

    public function getErrorBag()
    {
        $bag = parent::getErrorBag();

        if (is_null($bag)) {
            $bag = new \Illuminate\Support\MessageBag();
            $this->setErrorBag($bag);
        }

        return $bag;
    }
}
