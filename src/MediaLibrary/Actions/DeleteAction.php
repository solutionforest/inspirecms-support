<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;

class DeleteAction extends ItemAction
{
    use CanCustomizeProcess;

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

        $this->failureNotificationTitle(__('inspirecms-support::media-library.buttons.delete.messages.error.title'));

        $this->authorize('delete');

        $this->color('danger');

        $this->icon(FilamentIcon::resolve('inspirecms::delete'));

        $this->modalIcon(FilamentIcon::resolve('inspirecms::delete'));

        $this->action(function (): void {
            $result = $this->process(static fn (Model $record) => $record->delete());

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
        });
    }
}
