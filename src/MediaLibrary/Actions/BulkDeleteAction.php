<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Illuminate\Support\Collection;

class BulkDeleteAction extends ItemBulkAction
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

        $this->authorize('deleteAny');

        $this->color('danger');

        $this->icon('heroicon-o-trash');

        $this->modalIcon('heroicon-o-trash');

        $this->action(function (?Collection $records) {
            if ($records != null) {
                $result = $records->each->delete();
                $this->success();
            }
        });
    }
}
