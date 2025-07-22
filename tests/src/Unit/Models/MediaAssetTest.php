<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SolutionForest\InspireCms\Support\Helpers\MediaAssetHelper;
use SolutionForest\InspireCms\Support\Services\MediaAssetService;
use SolutionForest\InspireCms\Support\Tests\Models\MediaAsset;
use SolutionForest\InspireCms\Support\Tests\TestCase;

uses(TestCase::class);
pest()->group('unit', 'model', 'media_asset');

beforeEach(function () {
    $this->dummyRandImageUrl = 'https://picsum.photos/200/300';
});

dataset('image_extensions', [
    'jpg' => ['jpg'],
    'png' => ['png'],
    'webp' => ['webp'],
    'gif' => ['gif'],
]);
dataset('upload_via', [
    'via_upload_file' => ['via_upload_file'],
    'via_url' => ['via_url'],
]);

function validateConvertions($mediaAsset)
{
    $media = $mediaAsset->getFirstMedia();
    $extension = $media->extension;
    $hasConversion = in_array($extension, ['jpg', 'png', 'webp', 'gif']);
    $defaultConvertionNames = [
        '', // Default conversion
        'preview', // Preview conversion
    ];

    foreach ($defaultConvertionNames as $conversionName) {
        if (! $hasConversion) {
            continue;
        }
        if (filled($conversionName)) {
            expect($media->hasGeneratedConversion($conversionName))->toBeTrue();
        }
        validateMediaUrl($mediaAsset, $conversionName);
    }
}

function validateMediaUrl($mediaAsset, $conversionName = '')
{

    $media = $mediaAsset->getFirstMedia();

    expect($media)->not->toBeNull();

    // Check if the URL is correct
    $url = $mediaAsset->getUrl($conversionName);
    expect($url)->not->toBeNull();

    // Check file existence on disk
    $diskName = $media->disk;
    Storage::disk($diskName)->assertExists($media->getPathRelativeToRoot($conversionName));
}

function getExpectedDisplayedColumnsByExtension($extension)
{
    return match ($extension) {
        'jpg', 'png', 'webp', 'gif' => MediaAssetHelper::getMediaAssetDisplayedColumnsForImage(),
        'mp4' => MediaAssetHelper::getMediaAssetDisplayedColumnsForVideo(),
        'mp3' => MediaAssetHelper::getMediaAssetDisplayedColumnsForAudio(),
        default => MediaAssetHelper::getMediaAssetDisplayedColumnsForNonFolder(),
    };
}

function validateDisplayedColumns($mediaAsset, $expectedColumns)
{
    $displayedColumns = $mediaAsset->getDisplayedColumns();
    // Compare displayed columns with expected columns
    foreach ($expectedColumns as $column) {
        expect($displayedColumns)->toContain($column);
    }
}

it('can convert to dto', function () {
    $mediaAsset = MediaAsset::factory()->isFolder()->create();

    $dto = $mediaAsset->toDto();

    expect($dto->uid)->toBe($mediaAsset->id);
    expect($dto->caption)->toBe($mediaAsset->caption);
    expect($dto->description)->toBe($mediaAsset->description);
    expect(array_keys($media->responsive_images ?? []))->toBe($dto->responsive);
});

test('can create media asset (image)', function ($extension, array $properties = []) {

    $width = $properties['width'] ?? 10;
    $height = $properties['height'] ?? 10;
    
    $mediaAsset = $this->createMediaAssetWithMediaFromFile("test-add-image.{$extension}", null, fn () => [
        'width' => $width,
        'height' => $height,
    ]);

    $mediaAsset->refresh();

    $media = $mediaAsset->getFirstMedia();
    expect($media)->not->toBeNull();

    validateConvertions($mediaAsset);
    validateDisplayedColumns($mediaAsset, getExpectedDisplayedColumnsByExtension($extension));

    expect($media->custom_properties['width'])->toBe($width);
    expect($media->custom_properties['height'])->toBe($height);
})
    ->with('image_extensions')
    ->with([
        'with mapped properties' => fn () => [
            'width' => 120,
            'height' => 130,
        ],
    ]);

test('can create media asset (non-image)', function ($extension) {

    $mediaAsset = $this->createMediaAssetWithMediaFromFile("test-add-file.{$extension}");

    $mediaAsset->refresh();

    $media = $mediaAsset->getFirstMedia();
    expect($media)->not->toBeNull();

    validateConvertions($mediaAsset);
    validateDisplayedColumns($mediaAsset, getExpectedDisplayedColumnsByExtension($extension));

})->with([
    // tbc
    // 'mp4' => ['mp4'],
    // 'mp3' => ['mp3'],
    'pdf' => ['pdf'],
]);

test('create media asset (folder)', function () {

    $mediaAsset = MediaAsset::factory()->isFolder()->create();

    $mediaAsset->refresh();

    expect($mediaAsset)->not->toBeNull();
    expect($mediaAsset->isFolder())->toBeTrue();
    expect($mediaAsset->getFirstMedia())->toBeNull();

    validateDisplayedColumns($mediaAsset, MediaAssetHelper::getMediaAssetDisplayedColumnsForFolder());
});

test('can create media asset via', function ($via) {

    $mediaAsset = match ($via) {
        'via_upload_file' => $this->createMediaAssetWithMediaFromFile('test-image.jpg'),
        'via_url' => $this->createMediaAssetWithMediaViaUrlByExtension('jpg'),
    };

    $mediaAsset->refresh();

    expect($mediaAsset)->not->toBeNull();

    $media = $mediaAsset->getFirstMedia();
    expect($media)->not->toBeNull();

    $extension = $media->extension;

    validateConvertions(mediaAsset: $mediaAsset);
    validateDisplayedColumns($mediaAsset, getExpectedDisplayedColumnsByExtension($extension));

})->with('upload_via');

test('can re-upload media without deleting old media', function ($via, $fromExtension, $toExtension) {

    $mediaAsset = match ($via) {
        'via_upload_file' => $this->createMediaAssetWithMediaFromFile("test-media.{$fromExtension}"),
        'via_url' => $this->createMediaAssetWithMediaViaUrlByExtension($fromExtension),
    };

    $mediaAsset->refresh();
    $oldMedia = $mediaAsset->getFirstMedia();
    expect($oldMedia)->not->toBeNull();

    $mediaAsset = match ($via) {
        'via_upload_file' => MediaAssetService::updateMediaFromFileWithoutDelete(
            mediaAsset: $mediaAsset,
            file: UploadedFile::fake()->image("new-media.{$toExtension}"),
        ),
        'via_url' => MediaAssetService::uploadMediaFromUrlWithoutDelete(
            mediaAsset: $mediaAsset,
            url: $this->getDummyMediaUrlByExtension($toExtension),
        ),
    };

    $mediaAsset->refresh();

    expect($mediaAsset->getFirstMedia())->not->toBeNull();
    expect($mediaAsset->getFirstMedia()->getKey())->toBe($oldMedia->getKey());
    // File path should not change
    expect($mediaAsset->getFirstMedia()->getPathRelativeToRoot())->toBe($oldMedia->getPathRelativeToRoot());

})
    ->with('upload_via')
    ->with([
        'same_extension' => ['jpg', 'jpg'],
        'different_extension' => ['jpg', 'png'],
    ]);

test('throws exception when re-uploading media with different extension to non-image', function ($via) {
    $fromExtension = 'jpg';
    $toExtension = 'pdf';

    $mediaAsset = match ($via) {
        'via_upload_file' => $this->createMediaAssetWithMediaFromFile("test-media.{$fromExtension}"),
        'via_url' => $this->createMediaAssetWithMediaViaUrlByExtension($fromExtension),
    };

    $mediaAsset->refresh();
    $oldMedia = $mediaAsset->getFirstMedia();
    expect($oldMedia)->not->toBeNull();

    $mediaAsset = match ($via) {
        'via_upload_file' => MediaAssetService::updateMediaFromFileWithoutDelete(
            mediaAsset: $mediaAsset,
            file: UploadedFile::fake()->create("new-media.{$toExtension}"),
        ),
        'via_url' => MediaAssetService::uploadMediaFromUrlWithoutDelete(
            mediaAsset: $mediaAsset,
            url: $this->getDummyMediaUrlByExtension($toExtension),
        ),
    };

})->with('upload_via')->throws(\Exception::class);

