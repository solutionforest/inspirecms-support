<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary\Actions;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Section;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
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
            ->schema([
                Flex::make([])
                    ->columnSpanFull()
                    ->from('md')
                    ->schema([

                        TextEntry::make('file')
                            ->label(__('inspirecms-support::media-library.forms.file.label'))
                            ->aboveContent(fn (MediaAsset | Model $record) => ($record->isImage() || $record->isSvg())
                                ? Image::make($record->getThumbnailUrl(), $record->getKey())
                                    ->imageSize('14rem')
                                    ->url($record->getFirstMedia()->getUrl())
                                : null)
                            ->hintAction(
                                Action::make('open')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->url(fn (MediaAsset | Model $record) => $record->getFirstMedia()?->getUrl(), true)
                                    ->openUrlInNewTab()
                            )
                            ->grow(false),

                        Group::make([
                            TextEntry::make('id')
                                ->label(__('inspirecms-support::media-library.forms.id.label'))
                                ->copyable(),
                            TextEntry::make('title')
                                ->label(__('inspirecms-support::media-library.forms.title.label'))
                                ->copyable(),
                            TextEntry::make('caption')
                                ->label(__('inspirecms-support::media-library.forms.caption.label'))
                                ->copyable(),
                            TextEntry::make('description')
                                ->label(__('inspirecms-support::media-library.forms.description.label'))
                                ->copyable(),

                            Section::make()
                                ->statePath('details')
                                ->columns(2)
                                ->secondary()
                                ->compact()
                                ->schema(function (MediaAsset | Model $record) {
                                    $components = [];

                                    $media = $record?->getFirstMedia();
                                    $state = collect($record->getDisplayedColumns())
                                        ->mapWithKeys(function ($key) use ($media, $record) {
                                            $customPropertyKey = str_replace('custom-property.', '', $key);
                                            $value = match ($key) {
                                                'size' => (! $record->isFolder() ? $media?->human_readable_size : null),
                                                'uploaded_by', 'created_by' => $record->uploaded_by ?? null,
                                                // Default for not custom properties
                                                'created_at', 'updated_at', $customPropertyKey => ($record->isFolder()
                                                    ? $record?->{$key}
                                                    : $media?->{$key}) ?? null,
                                                // Default for custom properties
                                                default => $media->getCustomProperty($customPropertyKey) ?? null,
                                            };

                                            return [$key => $value];
                                        })
                                        ->all();
                                    foreach ($state as $key => $value) {

                                        if (in_array($key, ['model_id'])) {
                                            continue;
                                        }

                                        $entryLabel = trans("inspirecms-support::media-library.detail_info.{$key}.label");

                                        switch ($key) {
                                            case 'created_at':
                                            case 'updated_at':
                                                $components[] = TextEntry::make($key)
                                                    ->label($entryLabel)
                                                    ->state($value)
                                                    ->dateTime('Y-m-d H:i:s')
                                                    ->fontFamily('mono')
                                                    ->placeholder(__('inspirecms-support::media-library.detail_info.' . $key . '.empty'));

                                                break;
                                            default:
                                                $components[] = TextEntry::make($key)
                                                    ->label($entryLabel)
                                                    ->state($value)
                                                    ->fontFamily('mono')
                                                    ->copyable(match ($key) {
                                                        'model_id', 'size', 'uploaded_by', 'created_by' => true,
                                                        default => false,
                                                    })
                                                    ->placeholder(match ($key) {
                                                        'uploaded_by', 'created_by' => 'System',
                                                        default => null,
                                                    });

                                                break;
                                        }
                                    }

                                    return $components;
                                }),
                        ]),

                    ]),
            ])
            ->disabledForm()
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }
}
