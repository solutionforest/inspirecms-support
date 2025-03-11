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
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use SolutionForest\InspireCms\Support\Facades\MediaLibraryRegistry;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;
use SolutionForest\InspireCms\Support\Facades\ResolverRegistry;
use SolutionForest\InspireCms\Support\InspireCmsSupportServiceProvider;
use SolutionForest\InspireCms\Support\Resolvers\UserResolver;

class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => '\\SolutionForest\\InspireCms\\Support\\Tests\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
        Factory::guessModelNamesUsing(
            fn ($factory) => 'SolutionForest\\InspireCms\\Support\\Tests\\Models\\' . str_replace('Factory', '', class_basename($factory))
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

            \Kalnoy\Nestedset\NestedSetServiceProvider::class,

            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,

            InspireCmsSupportServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Avoid migration for 'cache', 'sessions', to avoid migration error
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        
        // region inspirecms support
        MediaLibraryRegistry::setDisk('public');
        MediaLibraryRegistry::setDirectory('');
        MediaLibraryRegistry::setThumbnailCrop(300, 300);

        ModelRegistry::setTablePrefix('cms_');

        ResolverRegistry::set('user', UserResolver::class);
        // endregion inspirecms support
    }

    protected function loadMigrationsFrom($paths): void
    {
        // Stub files
        if (is_array($paths)) {

            foreach ($paths as $folder) {

                $migrationPath = realpath($folder);

                if ($migrationPath == false) {
                    continue;
                }
    
                // Load .stub files
                foreach (glob("{$migrationPath}/*.php.stub") as $path) {
                    $migration = include $path;
                    $migration->up();
                }
            }

        }
        // End with '/../database/migrations'
        elseif (is_string($paths) && str($paths)->endsWith('/../database/migrations')) {
            $migrationPath = realpath(__DIR__ . '/../database/migrations');

            foreach (glob("{$migrationPath}/*.php") as $path) {
                $migration = include $path;
                $migration->up();
            }
        }
        else {
            parent::loadMigrationsFrom($paths);
        }
    }

    protected function defineDatabaseMigrations()
    {
        // $this->loadLaravelMigrations();

        $this->loadMigrationsFrom([
            // __DIR__ . '/../../vendor/spatie/laravel-medialibrary/database/migrations',

            __DIR__ . '/../../database/migrations',
        ]);

        // test migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}
