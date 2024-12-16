<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;

class CreateFolderAction extends BaseAction
{
    public static function getDefaultName(): ?string
    {
        return 'create-folder';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.actions.create_folder.label'));

        $this->modalHeading(__('inspirecms-support::media-library.actions.create_folder.modal.heading'));

        $this->successNotificationTitle(__('inspirecms-support::media-library.actions.create_folder.notifications.created.title'));

        $this->authorize('create');

        $this
            ->form([
                TextInput::make('title')
                    ->label(__('inspirecms-support::media-library.forms.title.label'))
                    ->required(),
            ])
            ->action(function (array $data) {
                if (empty($data['title'])) {
                    return;
                }
                $this->createMediaFolder($data['title']);
                $this->success();
            });
    }

    protected function createMediaFolder(string $title): Model
    {
        return $this->getModel()::create([
            'parent_id' => $this->getParentKey(),
            'title' => $title,
            'is_folder' => true,
        ]);
    }
}
