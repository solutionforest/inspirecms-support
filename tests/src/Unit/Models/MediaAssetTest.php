<?php

namespace SolutionForest\InspireCms\Support\Tests\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Support\Tests\Models\MediaAsset;
use SolutionForest\InspireCms\Support\Tests\TestCase;

uses(TestCase::class);

describe('media unit', function () {

    it('can convert to dto', function () {
        $mediaAsset = MediaAsset::factory()->isFolder()->create();

        $dto = $mediaAsset->toDto();

        expect($dto->uid)->toBe($mediaAsset->id);
        expect($dto->caption)->toBe($mediaAsset->caption);
        expect($dto->description)->toBe($mediaAsset->description);
        expect(array_keys($media->responsive_images ?? []))->toBe($dto->responsive);
    });

    it('can add media', function (array $data) {

        $mediaAsset = MediaAsset::factory()->create();
        $testFileName = 'test-add-image.jpg';

        if (isset($data['width']) && isset($data['height'])) {
            $file = UploadedFile::fake()->image($testFileName, $data['width'], $data['height']);
            $mediaAsset->addMediaWithMappedProperties($file);
        } else {
            $file = UploadedFile::fake()->image($testFileName);
            $mediaAsset->addMedia($file)->toMediaCollection();
        }

        $media = $mediaAsset->getFirstMedia();

        expect($media)->not->toBeNull();
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());

        if (isset($data['width'])) {
            expect($media->custom_properties['width'])->toBe($data['width']);
        }
        if (isset($data['height'])) {
            expect($media->custom_properties['height'])->toBe($data['height']);
        }

    })->with([
        'normal' => fn () => [
            'width' => null,
            'height' => null,
        ],
        'with mapped properties' => fn () => [
            'width' => 120,
            'height' => 130,
        ],
    ]);

    it('can get url', function ($type) {

        $mediaAsset = MediaAsset::factory()->create();
        $testFileName = 'test-add-image.jpg';
        $file = UploadedFile::fake()->image($testFileName);

        $mediaAsset->addMedia($file)->toMediaCollection();

        $media = $mediaAsset->getFirstMedia();

        expect($media)->not->toBeNull();

        if ($type === 'thumbnail') {
            expect($mediaAsset->getThumbnailUrl())->not->toBeNull();
        } else {
            expect($media->getUrl())->toBe($mediaAsset->getUrl());
        }

    })
        ->with([
            'base',
            'thumbnail',
        ]);

    it('can get displayed columns', function () {
        $mediaAsset = MediaAsset::factory()->create();
        $testFileName = 'test-add-image.jpg';
        $file = UploadedFile::fake()->image($testFileName);
        $mediaAsset->addMediaWithMappedProperties($file);
        $imageColumns = [
            'file_name',
            'mime_type',
            'size',
            'custom-property.dimensions',
            'created_at',
            'updated_at',
            'uploaded_by',
        ];

        expect($mediaAsset->getDisplayedColumns())->toBe($imageColumns);

        $mediaAsset = MediaAsset::factory()->isFolder()->create();

        $folderColumns = [
            'title',
            'created_at',
            'updated_at',
            'created_by',
        ];

        expect($mediaAsset->getDisplayedColumns())->toBe($folderColumns);
    });

})->group('media', 'unit');
