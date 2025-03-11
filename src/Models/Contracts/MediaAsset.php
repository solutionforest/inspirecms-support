<?php

namespace SolutionForest\InspireCms\Support\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\BelongsToNestableTree;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasRecursiveRelationshipsInterface;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
 */
interface MediaAsset extends BelongsToNestableTree, HasAuthor, HasDtoModel, HasMedia, HasRecursiveRelationshipsInterface
{
    /**
     * @return null | Model&Media
     */
    public function getFirstMedia();

    /**
     * Get the URL for the media asset.
     *
     * @param  string  $conversionName  The name of the conversion (optional).
     * @return ?string The URL of the media asset.
     */
    public function getUrl(string $conversionName = '');

    /**
     * Get the URL of the thumbnail for the media asset.
     *
     * @return ?string The URL of the thumbnail.
     */
    public function getThumbnailUrl();

    /**
     * Get the thumbnail URL or path for the media asset.
     *
     * @return ?string The thumbnail URL or icon.
     */
    public function getThumbnail();

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
     * Determine if the media asset is a folder.
     *
     * @return bool True if the media asset is a folder, false otherwise.
     */
    public function isFolder();

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
