<?php

namespace SolutionForest\InspireCms\Support\MediaLibrary;

use Filament\Support\Contracts\HasLabel;

enum FilterType: string implements HasLabel
{
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case Document = 'document';
    case Archive = 'archive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Image => __('inspirecms-support::media-library.filter.type.options.image'),
            self::Video => __('inspirecms-support::media-library.filter.type.options.video'),
            self::Audio => __('inspirecms-support::media-library.filter.type.options.audio'),
            self::Document => __('inspirecms-support::media-library.filter.type.options.document'),
            self::Archive => __('inspirecms-support::media-library.filter.type.options.archive'),
            default => $this->name,
        };
    }

    public function toMimeType(): null | string | array
    {
        return match ($this) {
            self::Image => 'image/*',
            self::Video => 'video/*',
            self::Audio => 'audio/*',
            self::Document => 'application/*',
            self::Archive => 'application/zip',
            default => null,
        };
    }

    public static function toMimeTypes(array $values): array
    {
        return collect($values)
            ->flatten()
            ->map(fn ($value) => self::tryFrom($value))->filter()
            ->map(fn (FilterType $value) => $value->toMimeType())->filter()
            ->flatten()
            ->toArray();
    }
}
