<?php

namespace SolutionForest\InspireCms\Support;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use SolutionForest\InspireCms\Support\Base\Manifests\MediaLibraryManifest;
use SolutionForest\InspireCms\Support\Base\Manifests\MediaLibraryManifestInterface;
use SolutionForest\InspireCms\Support\Testing\TestsInspireCmsSupport;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InspireCmsSupportServiceProvider extends PackageServiceProvider
{
    public static string $name = 'inspirecms-support';

    public static string $viewNamespace = 'inspirecms-support';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('solutionforest/inspirecms-support');
            });

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(MediaLibraryManifestInterface::class, fn () => $this->app->make(MediaLibraryManifest::class));
    }

    public function packageBooted(): void
    {
        Livewire::component('inspirecms-support::file-explorer', TreeNodes\FileExplorerComponent::class);
        Livewire::component('inspirecms-support::model-explorer', TreeNodes\ModelExplorerComponent::class);

        Livewire::component('inspirecms-support::media-library', MediaLibrary\MediaLibraryComponent::class);

        // Asset Registration
        FilamentAsset::register([
            Css::make('tree-node', __DIR__ . '/../resources/dist/tree-node.css'),
            Css::make('media-library', __DIR__ . '/../resources/dist/media-library.css'),
        ], 'solution-forest/inspirecms-support');

        // Testing
        Testable::mixin(new TestsInspireCmsSupport);
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_media-assets_table',
        ];
    }
}
