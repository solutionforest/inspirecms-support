<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Forms\Components\TextInput;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class RenameAction extends ItemAction
{
    public static function getDefaultName(): ?string
    {
        return 'rename';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.buttons.rename.label'));

        $this->modalHeading(fn (null | Model | MediaAsset $record) => __('inspirecms-support::media-library.buttons.rename.heading', ['name' => $record?->title]));

        $this->successNotificationTitle(__('inspirecms-support::media-library.buttons.rename.messages.success.title'));

        $this->authorize('update');

        $this->groupedIcon(FilamentIcon::resolve('inspirecms::edit.simple'));

        $this->color('gray');

        $this
            ->visible(fn (?Model $record) => $record != null)
            ->fillForm(function (null | Model | MediaAsset $record) {
                return [
                    'title' => $record?->title,
                ];
            })
            ->form([
                TextInput::make('title')
                    ->label(__('inspirecms-support::media-library.forms.title.label'))
                    ->validationAttribute(__('inspirecms-support::media-library.forms.title.validation_attribute'))
                    ->required(),
            ])
            ->action(function (?Model $record, array $data) {
                if (empty($data['title']) || ! $record) {
                    return;
                }
                $record->update([
                    'title' => $data['title'],
                ]);
                $this->success();
            });
    }
}
