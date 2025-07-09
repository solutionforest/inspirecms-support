<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

/**
 * @property string $id
 * @property string $title
 * @property string|int $nestable_id
 * @property string $parent_id
 * @property bool $is_folder
 * @property ?string $caption
 * @property ?string $description
 * @property ?string $author_type
 * @property ?string $author_id
 * @property ?\Carbon\CarbonInterface $created_at
 * @property ?\Carbon\CarbonInterface $updated_at
 *
 * @template TMedia of \Spatie\MediaLibrary\MediaCollections\Models\Media = \Spatie\MediaLibrary\MediaCollections\Models\Media
 */
interface MediaAsset extends BelongsToNestableTree, HasAuthor, HasDtoModel, HasMedia, HasRecursiveRelationshipsInterface
{
    /**
     * @return TMedia|null
     */
    public function getFirstMedia();

    /**
     * Get the URL for the media asset.
     *
     * @param  string  $conversionName  The name of the conversion (optional).
     * @param  bool  $isAbsolute  Whether to return an absolute URL.
     * @return ?string The URL of the media asset.
     */
    public function getUrl(string $conversionName = '', bool $isAbsolute = true);

    /**
     * Get the URL of the thumbnail for the media asset.
     *
     * @param  bool  $isAbsolute  Whether to return an absolute URL.
     * @return ?string The URL of the thumbnail.
     */
    public function getThumbnailUrl(bool $isAbsolute = true);

    /**
     * Get the thumbnail URL or path for the media asset.
     *
     * @return ?string The thumbnail URL or icon.
     */
    public function getThumbnail();

    /**
     * Determine if the media asset is a svg.
     *
     * @return bool True if the media asset is s svg, false otherwise.
     */
    public function isSvg();

    /**
     * Determine if the media asset is an image.
     *
     * @return bool True if the media asset is an image, false otherwise.
     */
    public function isImage();

    /**
     * Determine if the media asset is a video.
     *
     * @return bool True if the media asset is a video, false otherwise.
     */
    public function isVideo();

    /**
     * Determine if the media asset is an audio file.
     *
     * @return bool True if the media asset is an audio file, false otherwise.
     */
    public function isAudio();

    /**
     * Determine if the media asset is a PDF.
     *
     * @return bool True if the media asset is a PDF, false otherwise.
     */
    public function isPdf();

    /**
     * Determine if the media asset is a folder.
     *
     * @return bool True if the media asset is a folder, false otherwise.
     */
    public function isFolder();

    /**
     * @param  string | UploadedFile | TemporaryUploadedFile  $file
     * @return FileAdder
     */
    public function addMediaWithMappedProperties($file);

    /**
     * Sync the media properties with the model.
     *
     * This method is used to adjust the properties of the media asset
     * based on the model's attributes and custom properties.
     *
     * @param  \Spatie\MediaLibrary\MediaCollections\Models\Media  $media
     * @return void
     */
    public function syncMediaProperties($media);

    /**
     * Get the columns to be displayed.
     *
     * @return string[] The array of displayed columns.
     */
    public function getDisplayedColumns();

    /**
     * Scope a query to only include folders.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFolders($query, bool $condition = true);
}
