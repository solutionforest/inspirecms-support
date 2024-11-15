<?php

namespace SolutionForest\InspireCms\Support\Tests\Media;

use SolutionForest\InspireCms\Support\Tests\TestCase;
use SolutionForest\InspireCms\Support\Tests\TestModels\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use SolutionForest\InspireCms\Support\MediaLibrary\MediaLibraryComponent;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest;
use function Pest\Livewire\livewire;


class MediaAssetTest extends TestCase
{
    /** @test */
    public function it_can_convert_to_dto()
    {
        $mediaAsset = MediaAsset::factory()->isFolder()->create();

        $dto = $mediaAsset->toDto();

        $this->assertEquals($mediaAsset->id, $dto->uid);
        $this->assertEquals($mediaAsset->caption, $dto->caption);
        $this->assertEquals($mediaAsset->description, $dto->description);
        $this->assertEquals(array_keys($media->responsive_images ?? []), $dto->responsive);
    }

    /** @test */
    public function it_can_add_media()
    {
        $mediaAsset = MediaAsset::factory()->create();
        $testFileName = 'test-add-image.jpg';
        $file = UploadedFile::fake()->image($testFileName);

        $mediaAsset->addMedia($file)->toMediaCollection();

        $media = $mediaAsset->getFirstMedia();

        $this->assertNotNull($media);

        Storage::disk('public')->assertExists($media->id . '/' . $testFileName);
    }

    /** @test */
    public function it_can_add_media_with_mapped_properties()
    {
        $mediaAsset = MediaAsset::factory()->create();
        $testFileName = 'test-add-image.jpg';
        $file = UploadedFile::fake()->image($testFileName, 120, 130);

        $mediaAsset->addMediaWithMappedProperties($file);

        $media = $mediaAsset->getFirstMedia();

        $this->assertNotNull($media);

        $this->assertEquals($media->custom_properties['width'], 120);
        $this->assertEquals($media->custom_properties['height'], 130);
    }
    /** @test */
    public function it_can_get_url()
    {
        $mediaAsset = MediaAsset::factory()->create();
        $testFileName = 'test-add-image.jpg';
        $file = UploadedFile::fake()->image($testFileName);

        $mediaAsset->addMedia($file)->toMediaCollection();

        $media = $mediaAsset->getFirstMedia();

        $this->assertNotNull($media);

        $this->assertEquals($media->getUrl(), $mediaAsset->getUrl());
    }


    /** @test */
    public function it_can_get_thumbnail_url()
    {
        $mediaAsset = MediaAsset::factory()->create();
        $testFileName = 'test-add-image.jpg';
        $file = UploadedFile::fake()->image($testFileName);

        $mediaAsset->addMedia($file)->toMediaCollection();

        $media = $mediaAsset->getFirstMedia();

        $this->assertNotNull($media);

        $this->assertNotNull($mediaAsset->getThumbnailUrl());
    }


    /** @test */
    public function it_can_get_thumbnail()
    {
        $mediaAsset = MediaAsset::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 'test-content', 'application/pdf');

        $mediaAsset->addMedia($file)->toMediaCollection();

        $this->assertEquals('heroicon-o-document', $mediaAsset->getThumbnail());
    }


    /** @test */
    public function it_can_get_displayed_columns()
    {
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

        $this->assertEquals($imageColumns, $mediaAsset->getDisplayedColumns());

        $mediaAsset = MediaAsset::factory()->isFolder()->create();

        $folderColumns = [
            'title',
            'created_at',
            'updated_at',
            'created_by',
        ];

        $this->assertEquals($folderColumns, $mediaAsset->getDisplayedColumns());
    }

}
