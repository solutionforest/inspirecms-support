<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Infolists;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
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
            ->fillForm(function (?Model $record) {
                $data = $record?->attributesToArray();
                if ($record && $record instanceof MediaAsset) {
                    $media = $record->getFirstMedia();
                    if ($media) {
                        $data['media'] = $media->attributesToArray();
                        $data['file'] = $media->getPathRelativeToRoot();
                    }
                }

                return $data;
            })
            ->infolist([
                Infolists\Components\TextEntry::make('file')
                    ->label(__('inspirecms-support::media-library.forms.file.label'))
                    ->inlineLabel()
                    ->state(function (MediaAsset | Model $record) {

                        if ($record->isImage() || $record->isSvg()) {
                            $urlOrIcon = $record->getThumbnail();

                            return new HtmlString(<<<Html
                                <img src="$urlOrIcon" class="w-32 h-32 object-cover">
                                Html);
                        }

                        return 'View';
                    })
                    ->url(fn (MediaAsset | Model $record) => $record->getFirstMedia()?->getUrl(), true),
                Infolists\Components\TextEntry::make('id')->label(__('inspirecms-support::media-library.forms.id.label'))->copyable(),
                Infolists\Components\TextEntry::make('title')->label(__('inspirecms-support::media-library.forms.title.label')),
                Infolists\Components\Grid::make(2)->statePath('media')->schema([
                    Infolists\Components\TextEntry::make('file_name')->label(__('inspirecms-support::media-library.forms.file_name.label')),
                    Infolists\Components\TextEntry::make('mime_type')->label(__('inspirecms-support::media-library.forms.mime_type.label')),
                ]),
                Infolists\Components\TextEntry::make('caption')->label(__('inspirecms-support::media-library.forms.caption.label')),
                Infolists\Components\TextEntry::make('description')->label(__('inspirecms-support::media-library.forms.description.label')),
            ])
            ->disabledForm()
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }
}
