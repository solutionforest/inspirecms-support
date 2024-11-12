<?php

namespace SolutionForest\InspireCms\Support\Tests\Media;

use SolutionForest\InspireCms\Support\Tests\TestCase;
use SolutionForest\InspireCms\Support\Tests\TestModels\MediaAsset;

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
}
