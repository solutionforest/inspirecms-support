<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\BaseAction;

class DeleteAction extends BaseAction
{
    public static function getDefaultName(): ?string
    {
        return 'delete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.actions.delete.label'));

        $this->modalHeading(fn () => __('inspirecms-support::media-library.actions.edit.modal.heading', ['name' => $this->getModelLabel()]));

        $this->successNotificationTitle(__('inspirecms-support::media-library.actions.delete.notifications.deleted.title'));

        $this->authorize('delete');

        $this->color('danger');

        $this->icon('heroicon-o-trash');

        $this->action(function (?Model $record) {
            if (! $record) {
                return;
            }

            $record->delete();
            $this->success();
        });
    }
}
