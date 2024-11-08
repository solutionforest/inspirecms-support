<?php

namespace SolutionForest\InspireCms\Support\Media;

use SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest;
use SolutionForest\InspireCms\Support\Tests\TestCase;

class MediaAssetTest extends TestCase
{
    /** @test */
    public function it_can_convert_to_dto()
    {
        $mediaAsset = MediaLibraryManifest::getModel()::factory()->isFolder()->create();

        $dto = $mediaAsset->toDto();

        $this->assertEquals($mediaAsset->id, $dto->uid);
        $this->assertEquals($mediaAsset->caption, $dto->caption);
        $this->assertEquals($mediaAsset->description, $dto->description);
        $this->assertEquals(array_keys($media->responsive_images ?? []), $dto->responsive);
    }
}
