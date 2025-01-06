<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Infolists;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
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

        $this->label(__('inspirecms-support::media-library.actions.view.label'));

        $this->modalHeading(fn () => __('inspirecms-support::media-library.actions.view.modal.heading', ['name' => $this->getModelLabel()]));

        $this->successNotificationTitle(__('inspirecms-support::media-library.actions.edit.notification.saved.title'));

        $this->authorize('view');

        $this->color('gray');

        $this->groupedIcon('heroicon-o-eye');

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
                    ->validationAttribute(__('inspirecms-support::media-library.forms.file.validation_attribute'))
                    ->inlineLabel()
                    ->state(function (MediaAsset | Model $record) {
                        $urlOrIcon = $record->getThumbnail();

                        if ($record->isImage()) {
                            return new HtmlString(<<<Html
                                <img src="$urlOrIcon" class="w-32 h-32 object-cover">
                                Html);
                        } else {
                            return new HtmlString(
                                Blade::render(<<<'blade'
                                <x-filament::icon 
                                    icon="{{ $icon }}" 
                                    class="h-6 w-6"
                                >
                                </x-filament::icon>
                                blade, [
                                    'icon' => $urlOrIcon,
                                ])
                            );
                        }
                    })
                    ->url(fn (MediaAsset | Model $record) => $record->getFirstMedia()?->getUrl(), true),
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
