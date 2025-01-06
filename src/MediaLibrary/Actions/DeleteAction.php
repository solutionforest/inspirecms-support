<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Illuminate\Database\Eloquent\Model;

class DeleteAction extends ItemAction
{
    public static function getDefaultName(): ?string
    {
        return 'delete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.actions.delete.label'));

        $this->requiresConfirmation();

        $this->modalHeading(fn () => __('inspirecms-support::media-library.actions.delete.modal.heading', ['name' => $this->getModelLabel()]));

        $this->successNotificationTitle(__('inspirecms-support::media-library.actions.delete.notification.deleted.title'));

        $this->authorize('delete');

        $this->color('danger');

        $this->icon('heroicon-o-trash');

        $this->modalIcon('heroicon-o-trash');

        $this->action(function (?Model $record) {
            if ($record) {
                $record->delete();
                $this->success();
            }
        });
    }
}
