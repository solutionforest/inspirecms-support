<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Infolists;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset;

class ViewAction extends ItemAction
{
    public static function getDefaultName(): ?string
    {
        return 'view';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-support::media-library.buttons.view.label'));

        $this->modalHeading(fn () => __('inspirecms-support::media-library.buttons.view.heading', ['name' => $this->getModelLabel()]));

        $this->successNotificationTitle(__('inspirecms-support::media-library.buttons.edit.messages.success.title'));

        $this->authorize('view');

        $this->color('gray');

        $this->groupedIcon(FilamentIcon::resolve('inspirecms::view'));

        $this
            ->infolist([
                TextEntry::make('file')
                    ->label(__('inspirecms-support::media-library.forms.file.label'))
                    ->inlineLabel()
                    ->state(function (MediaAsset | Model $record) {

                        if ($record->isImage() || $record->isSvg()) {
                            $urlOrIcon = $record->getThumbnail();

                            return str("<img src=\"$urlOrIcon\" class=\"w-32 h-32 object-cover\">")->toHtmlString();
                        }

                        return 'View';
                    })
                    ->url(fn (MediaAsset | Model $record) => $record->getFirstMedia()?->getUrl(), true),
                TextEntry::make('id')->label(__('inspirecms-support::media-library.forms.id.label'))->copyable(),
                TextEntry::make('title')->label(__('inspirecms-support::media-library.forms.title.label')),
                Grid::make(3)
                    ->statePath('media')
                    ->schema([
                        TextEntry::make('file_name')
                            ->label(__('inspirecms-support::media-library.forms.file_name.label')),
                        TextEntry::make('mime_type')
                            ->label(__('inspirecms-support::media-library.forms.mime_type.label')),
                        TextEntry::make('size')
                            ->formatStateUsing(fn ($state) => $state ? MediaAssetHelper::getHumanFileSize($state) : null),
                    ]),
                TextEntry::make('caption')
                    ->label(__('inspirecms-support::media-library.forms.caption.label')),
                TextEntry::make('description')
                ->label(__('inspirecms-support::media-library.forms.description.label')),
            ])
            ->disabledForm()
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }
}
