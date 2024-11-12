<?php

namespace SolutionForest\InspireCms\Support\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use SolutionForest\InspireCms\Support\InspireCmsSupportServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'SolutionForest\\InspireCms\\Support\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
        Factory::guessModelNamesUsing(
            fn ($factory) => 'SolutionForest\\InspireCms\\Support\\Tests\\TestModels\\' . str_replace('Factory', '', class_basename($factory))
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,

            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,

            InspireCmsSupportServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        //region inspirecms support
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest::setDisk('public');
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest::setDirectory('');
        \SolutionForest\InspireCms\Support\Facades\MediaLibraryManifest::setThumbnailCrop(300, 300);

        \SolutionForest\InspireCms\Support\Facades\InspireCmsSupport::setTablePrefix('cms_');

        \SolutionForest\InspireCms\Support\Facades\ResolverManifest::set('user', \SolutionForest\InspireCms\Support\Resolver\UserResolver::class);
        //endregion inspirecms support

        $migrations = [
            __DIR__ . '/../database/migrations/create_nestable-trees_table.php.stub',
            __DIR__ . '/../database/migrations/create_media-assets_table.php.stub',
            __DIR__ . '/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub',
        ];

        foreach ($migrations as $migrationPath) {
            $migration = include $migrationPath;
            $migration->up();
        }
    }
}
