<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Support\Facades\FilamentIcon;

class OpenFolderAction extends ItemAction
{
    public static function getDefaultName(): ?string
    {
        return 'openFolder';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.buttons.open_folder.label'));

        $this->authorize('view');

        $this->color('gray');

        $this->groupedIcon(FilamentIcon::resolve('inspirecms::open_folder'));
    }
}
