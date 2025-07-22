<?php

namespace SolutionForest\InspireCms\Support\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
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
use SolutionForest\InspireCms\Support\Tests\Concerns\WithMediaAsset;
use SolutionForest\InspireCms\Support\Tests\Models\User;

class TestCase extends Orchestra
{
    use WithWorkbench;
    use WithMediaAsset;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => '\\SolutionForest\\InspireCms\\Support\\Tests\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
        Factory::guessModelNamesUsing(
            fn ($factory) => 'SolutionForest\\InspireCms\\Support\\Tests\\Models\\' . str_replace('Factory', '', class_basename($factory))
        );

        $this->actingAs(
            User::factory()->create()
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,

            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,

            \Kalnoy\Nestedset\NestedSetServiceProvider::class,

            \Spatie\MediaLibrary\MediaLibraryServiceProvider::class,

            InspireCmsSupportServiceProvider::class,

            AdminPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $app['config']->set('auth.providers.users.model', User::class);

        // Avoid migration for 'cache', 'sessions', to avoid migration error
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        // region inspirecms support
        MediaLibraryRegistry::setDisk('public');
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
        } else {
            parent::loadMigrationsFrom($paths);
        }
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom([
            // __DIR__ . '/../../vendor/spatie/laravel-medialibrary/database/migrations',

            __DIR__ . '/../../database/migrations',
        ]);

        // test migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}
