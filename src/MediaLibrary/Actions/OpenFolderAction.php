<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

class OpenFolderAction extends BaseAction
{
    public static function getDefaultName(): ?string
    {
        return 'open-folder';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.actions.open_folder.label'));

        $this->authorize('view');

        $this->color('gray');

        $this->icon('heroicon-o-folder');
    }
}
