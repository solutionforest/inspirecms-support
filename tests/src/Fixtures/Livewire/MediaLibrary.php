<?php

namespace SolutionForest\InspireCms\Support\Tests\Fixtures\Livewire;

use Illuminate\Support\MessageBag;
use SolutionForest\InspireCms\Support\MediaLibrary\MediaLibraryComponent;

class MediaLibrary extends MediaLibraryComponent
{
    public function mount()
    {
        parent::mount();

        $this->setErrorBag(new MessageBag);
    }

    public function getErrorBag()
    {
        $bag = parent::getErrorBag();

        if (is_null($bag)) {
            $bag = new MessageBag;
            $this->setErrorBag($bag);
        }

        return $bag;
    }
}
