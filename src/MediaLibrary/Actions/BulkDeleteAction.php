<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Throwable;

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

        $this->failureNotificationTitle(function (int $successCount, int $totalCount) {
            if ($successCount) {
                return trans_choice('inspirecms-support::media-library.buttons.delete.messages.deleted_partial.title', $successCount, [
                    'count' => Number::format($successCount),
                    'total' => Number::format($totalCount),
                ]);
            }

            return trans_choice('inspirecms-support::media-library.buttons.delete.messages.deleted_none.title', $totalCount, [
                'count' => Number::format($totalCount),
                'total' => Number::format($totalCount),
            ]);
        });

        $this->authorize('deleteAny');

        $this->color('danger');

        $this->icon(FilamentIcon::resolve('inspirecms::delete'));

        $this->modalIcon(FilamentIcon::resolve('inspirecms::delete'));

        $this->action(function (?Collection $records, self $action) {
            if ($records != null) {

                $records->each(static function (Model $record) use ($action, &$isFirstException): void {
                    try {
                        $record->delete() || $action->reportBulkProcessingFailure();
                    } catch (Throwable $exception) {
                        $action->reportBulkProcessingFailure();

                        if ($isFirstException) {
                            // Only report the first exception to not flood error logs. Even if Filament
                            // did not catch exceptions like this, only the first would be reported
                            // as the rest of the process would be halted.
                            report($exception);

                            $isFirstException = false;
                        }
                    }
                });
            }
        });
    }
}
