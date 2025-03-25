<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Support\Facades\FilamentIcon;
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

        $this->label(__('inspirecms-support::media-library.buttons.delete.label'));

        $this->requiresConfirmation();

        $this->modalHeading(fn () => __('inspirecms-support::media-library.buttons.delete.heading', ['name' => $this->getModelLabel()]));

        $this->successNotificationTitle(__('inspirecms-support::media-library.buttons.delete.messages.success.title'));

        $this->authorize('deleteAny');

        $this->color('danger');

        $this->icon(FilamentIcon::resolve('inspirecms::delete'));

        $this->modalIcon(FilamentIcon::resolve('inspirecms::delete'));

        $this->action(function (?Collection $records) {
            if ($records != null) {
                $result = $records->each->delete();
                $this->success();
            }
        });
    }
}
