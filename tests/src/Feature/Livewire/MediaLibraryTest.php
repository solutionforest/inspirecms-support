<?php

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\MediaLibrary\FilterType;
use SolutionForest\InspireCms\Support\Tests\Models\MediaAsset;
use SolutionForest\InspireCms\Support\Tests\TestCase;

uses(TestCase::class);

pest()->group('feature', 'livewire', 'media-library');

const FORM_NAMES_UPLOAD = 'uploadForm';
const FORM_NAMES_FILTER = 'filterForm';

const LIVEWIRE_MEDIA_LIBRARY = 'inspirecms-support::media-library';

test('renders media library component', function () {
    Livewire::test(LIVEWIRE_MEDIA_LIBRARY)
        ->assertSee(__('inspirecms-support::media-library.folder.plural'))
        ->assertSee(__('inspirecms-support::media-library.media.plural'));
});

test('can upload media', function () {
    $uuid = '123e4567-e89b-12d3-a456-426614174000';
    Livewire::test(LIVEWIRE_MEDIA_LIBRARY)
        ->assertFormFieldExists('files', FORM_NAMES_UPLOAD)
        ->fillForm([
            'files' => [
                $uuid => UploadedFile::fake()
                    ->image('test-image.jpg')
                    ->size(500), // Size in kilobytes
            ],
        ], FORM_NAMES_UPLOAD)
        ->dispatchFormFieldEvent(
            'autoupload-file--start-upload',
            fn ($form, $state) => [
                collect($form->getComponents())->firstWhere(fn ($c) => $c->getName() == 'files')?->getStatePath(),
                $uuid,
            ],
            FORM_NAMES_UPLOAD
        )
        ->assertHasNoErrors();

    // Assert that the media was uploaded successfully
    $this->assertDatabaseHas(app(MediaAsset::class)->getTable(), [
        'title' => 'test-image',
        'is_folder' => 0, // false
    ]);
    $this->assertDatabaseHas('media', [
        'name' => 'test-image',
        'file_name' => 'test-image.jpg',
        'collection_name' => 'default',
    ]);
});

test('can filtering media library', function () {
    // Ready data
    $folders = MediaAsset::factory(2)
        ->sequence(fn (Sequence $sequence) => [
            'title' => "test-folder-{$sequence->index}",
            'parent_id' => KeyHelper::generateMinUuid(),
        ])
        ->isFolder()
        ->create();
    $mediaAssets = collect([
        'test-image-1.jpg',
        'dump-image-2.jpg',
        'test-audio.mp3',
    ])->map(function ($title) {
        $record = $this->createMediaAssetWithMediaFromFile($title);
        $record->update([
            'title' => $title,
        ]);
        $record->refresh();

        return $record;
    });

    $this->assertDatabaseCount(app(MediaAsset::class)->getTable(), count($folders) + count($mediaAssets));

    $livewire = Livewire::test(LIVEWIRE_MEDIA_LIBRARY)
        ->assertFormFieldExists('title', FORM_NAMES_FILTER)
        ->assertFormFieldExists('type', FORM_NAMES_FILTER);

    // Test livewire with idle state
    $livewire
        ->assertCount('assets', count($folders) + count($mediaAssets));

    // // Test filtering by title
    $livewire
        ->fillForm([
            'title' => 'test',
            'type' => [],
        ], FORM_NAMES_FILTER)
        ->call('clearCache')
        ->assertCount('assets', 4);

    // Test filtering by type
    $livewire
        ->fillForm([
            'title' => '',
            'type' => [FilterType::Image->value],
        ], FORM_NAMES_FILTER)
        ->call('clearCache')
         // 2 images
        ->assertCount('assets', 2);

    // Reset filter to idle state
    $livewire
        ->fillForm([
            'title' => '',
            'type' => [],
        ], FORM_NAMES_FILTER)
        ->call('clearCache')
        ->assertCount('assets', count($folders) + count($mediaAssets));
});

test('can create a media with folder', function () {

    // Preset to allow the action
    Gate::before(fn () => true);

    $livewire = Livewire::test(LIVEWIRE_MEDIA_LIBRARY);

    // Action: createFolder
    $livewire
        ->assertActionExists('createFolder')
        ->callAction('createFolder', [
            'title' => 'test-folder-1',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified(__('inspirecms-support::media-library.buttons.create_folder.messages.success.title'));

    // Validate: after folder creation
    $this->assertDatabaseCount(app(MediaAsset::class)->getTable(), 1);
    $this->assertDatabaseHas(app(MediaAsset::class)->getTable(), [
        'title' => 'test-folder-1',
        'is_folder' => 1, // true
    ]);

    $targetFolder = MediaAsset::folders()->first();
    expect($targetFolder)->not->toBeNull();

    // Action: change folder (parent)
    $livewire
        ->dispatch('openFolder', mediaId: $targetFolder->getKey())
        ->assertSet('parentKey', $targetFolder->getKey());

    // Action: create media in folder
    $uuid = '123e4567-e89b-12d3-a456-426614174000';
    $livewire
        ->assertFormFieldExists('files', FORM_NAMES_UPLOAD)
        ->fillForm([
            'files' => [
                $uuid => UploadedFile::fake()
                    ->image('test-image.jpg')
                    ->size(500), // Size in kilobytes
            ],
        ], FORM_NAMES_UPLOAD)
        ->dispatchFormFieldEvent(
            'autoupload-file--start-upload',
            fn ($form, $state) => [
                collect($form->getComponents())->firstWhere(fn ($c) => $c->getName() == 'files')?->getStatePath(),
                $uuid,
            ],
            FORM_NAMES_UPLOAD
        )
        ->assertHasNoErrors();

    // Validate: after media asset creation
    $this->assertDatabaseCount(app(MediaAsset::class)->getTable(), 2);
    $this->assertDatabaseHas(app(MediaAsset::class)->getTable(), [
        'title' => 'test-image',
        'is_folder' => 0,
        'parent_id' => $targetFolder->getKey(),
    ]);
    $this->assertDatabaseHas('media', [
        'name' => 'test-image',
        'file_name' => 'test-image.jpg',
        'collection_name' => 'default',
    ]);

    // Validate: delete folder (folder browser)
    $livewire
        ->call('deleteFolder', mediaId: $targetFolder->getKey())
        ->assertDispatched('openFolder')
        ->assertDispatched('deleteMedia');
    $livewire
        ->dispatch('openFolder', KeyHelper::generateMinUuid())
        ->assertSet('parentKey', KeyHelper::generateMinUuid());
    $livewire->dispatch('deleteMedia', mediaId: $targetFolder->getKey())
        ->assertNotified(__('inspirecms-support::media-library.messages.item_deleted'));

    $this->assertDatabaseCount(app(MediaAsset::class)->getTable(), 0);
});
