<?php

namespace SolutionForest\InspireCms\Support\Tests\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Services\MediaAssetService;
use SolutionForest\InspireCms\Support\Tests\Models\MediaAsset;
use SolutionForest\InspireCms\Support\Tests\TestCase;

uses(TestCase::class);
pest()->group('media', 'unit');

dataset('image_extensions', [
    'jpg' => ['jpg'],
    'png' => ['png'],
    'webp' => ['webp'],
    'gif' => ['gif'],
]);

dataset('media_conversions', [
    'base' => '',
    'thumbnail' => 'preview',
]);

it('can convert to dto', function () {
    $mediaAsset = MediaAsset::factory()->isFolder()->create();

    $dto = $mediaAsset->toDto();

    expect($dto->uid)->toBe($mediaAsset->id);
    expect($dto->caption)->toBe($mediaAsset->caption);
    expect($dto->description)->toBe($mediaAsset->description);
    expect(array_keys($media->responsive_images ?? []))->toBe($dto->responsive);
});

it('can add image', function ($extension = 'jpg', $width = null, $height = null) {

    $testFileName = "test-add-image.{$extension}";

    if (isset($width) || isset($height)) {
        $file = UploadedFile::fake()->image($testFileName, $width ?? 10, $height ?? 10);
    } else {
        $file = UploadedFile::fake()->image($testFileName);
    }

    $mediaAsset = MediaAssetService::createMediaAssetFromFile(
        file: $file,
    );

    $mediaAsset->refresh();

    $media = $mediaAsset->getFirstMedia();

    expect($media)->not->toBeNull();

    $diskName = $media->disk;
    Storage::disk($diskName)->assertExists($media->getPathRelativeToRoot());

    if (isset($width)) {
        expect($media->custom_properties['width'])->toBe($width);
    }
    if (isset($height)) {
        expect($media->custom_properties['height'])->toBe($height);
    }
})
->with('image_extensions')
->with([
    'normal' => [],
    'with mapped properties' => [120, 130],
]);

it('can get url', function ($conversionName) {

    $mediaAsset = MediaAsset::factory()->create();
    $testFileName = 'test-add-image.jpg';
    $file = UploadedFile::fake()->image($testFileName);

    $mediaAsset->addMedia($file)->toMediaCollection();

    $media = $mediaAsset->getFirstMedia();

    expect($media)->not->toBeNull();

    // Check if the URL is correct
    $url = $mediaAsset->getUrl($conversionName);
    expect($url)->not->toBeNull();

    // Check file existence on disk
    $diskName = $media->disk;
    Storage::disk($diskName)->assertExists($media->getPathRelativeToRoot($conversionName));

})->with('media_conversions');

it('can get displayed columns', function ($extension = null) {

    $isFolder = is_null($extension);
    if ($isFolder) {
        $mediaAsset = MediaAsset::factory()->isFolder()->create();
        $targetColumns = MediaAssetHelper::getMediaAssetDisplayedColumnsForFolder();
    } else {
        $testFileName = "test-media.{$extension}";
        if (in_array($extension, ['jpg', 'png', 'webp', 'gif'])) {
            $file = UploadedFile::fake()->image($testFileName);
        } else {
            $file = match ($extension) {
                'mp4' => UploadedFile::fake()->create($testFileName, 1000, 'video/mp4'),
                'mp3' => UploadedFile::fake()->create($testFileName, 1000, 'audio/mpeg'),
                'pdf' => UploadedFile::fake()->createWithContent($testFileName, str_repeat('A', 1000)),
                default => UploadedFile::fake()->create($testFileName, 1000),
            };
        }
        $mediaAsset = MediaAssetService::createMediaAssetFromFile(
            file: $file,
        );
        $targetColumns = match ($extension) {
            'jpg', 'png', 'webp', 'gif' => MediaAssetHelper::getMediaAssetDisplayedColumnsForImage(),
            'mp4' => MediaAssetHelper::getMediaAssetDisplayedColumnsForVideo(),
            'mp3' => MediaAssetHelper::getMediaAssetDisplayedColumnsForAudio(),
            default => MediaAssetHelper::getMediaAssetDisplayedColumnsForNonFolder(),
        };
    }
    $mediaAsset->refresh();

    if ($isFolder) {
        expect($mediaAsset->isFolder())->toBeTrue();
    } else {
        expect($mediaAsset->isFolder())->toBeFalse();
        expect($mediaAsset->getFirstMedia())->not->toBeNull();
    }

    expect($mediaAsset->getDisplayedColumns())->toBe($targetColumns);

})->with([
    'image' => ['jpg'],
    // 'video' => ['mp4'],
    // 'audio' => ['mp3'],
    'file' => ['txt'],
    'folder' => [],
]);
