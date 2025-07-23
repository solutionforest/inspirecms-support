<?php

namespace SolutionForest\InspireCms\Support\Tests\Concerns;

use Closure;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use SolutionForest\InspireCms\Support\Services\MediaAssetService;

trait WithMediaAsset
{
    public function createMediaAssetWithMediaFromFile(string $filename, ?string $parentKey = null, ?Closure $mediaProcessingCallback = null)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'pdf':
                $content = str_repeat('A', 1000);
                if ($mediaProcessingCallback) {
                    $tmpResult = $mediaProcessingCallback($content);
                    $content = $tmpResult['content'] ?? $content;
                }
                $file = UploadedFile::fake()->createWithContent($filename, $content);

                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'webp':
            case 'gif':
                [$width, $height] = [600, 400];
                if ($mediaProcessingCallback) {
                    $dimensions = $mediaProcessingCallback($width, $height);
                    $width = $dimensions['width'] ?? $width;
                    $height = $dimensions['height'] ?? $height;
                }
                $file = UploadedFile::fake()->image($filename, $width, $height);

                break;
            default:
                $mimeType = match ($extension) {
                    'mp4' => 'video/mp4',
                    'mp3' => 'audio/mpeg',
                    default => null,
                };
                $kb = 1000; // Default size in kilobytes
                if ($mediaProcessingCallback) {
                    $tmpResult = $mediaProcessingCallback($kb);
                    $kb = $tmpResult['size'] ?? $kb;
                }
                $file = UploadedFile::fake()->create($filename, $kb, $mimeType);
        }

        return MediaAssetService::createMediaAssetFromFile(
            file: $file,
            parentKey: $parentKey,
        );
    }

    public function createMediaAssetWithMediaViaUrlByExtension(string $extension, ?string $parentKey = null)
    {
        return MediaAssetService::createMediaAssetFromUrl(
            url: $this->getDummyMediaUrlByExtension($extension),
            parentKey: $parentKey,
        );
    }

    public function getDummyMediaUrlByExtension($extension)
    {
        return match ($extension) {
            // Images
            'png' => 'https://placehold.co/600x400.png',
            'jpg', 'webp' => "https://picsum.photos/200/300.{$extension}",
            'gif' => 'https://www.sample-videos.com/gif/3.gif',
            'bmp' => 'https://www.filesampleshub.com/download/image/bmp/sample1.bmp',
            'svg' => 'https://upload.wikimedia.org/wikipedia/commons/0/02/SVG_logo.svg',
            'tiff' => 'https://examplefiles.org/files/images/tiff-example-file-download-500x500.tiff',
            // Non-images
            'pdf' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'mp4' => 'https://www.w3schools.com/html/mov_bbb.mp4',
            'mp3' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
            default => throw new InvalidArgumentException("Unsupported media extension: {$extension}"),
        };
    }
}
