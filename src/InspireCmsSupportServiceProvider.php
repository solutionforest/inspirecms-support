<?php

namespace SolutionForest\InspireCms\Support;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use SolutionForest\InspireCms\Support\Base\Manifests;
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
        $this->app->singleton(Manifests\MediaLibraryManifestInterface::class, fn () => $this->app->make(Manifests\MediaLibraryManifest::class));
        $this->app->singleton(Manifests\ResolverManifestInterface::class, fn () => $this->app->make(Manifests\ResolverManifest::class));
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

        FilamentIcon::register([
            'inspirecms-support::pdf' => view('inspirecms-support::icons.pdf'),
            'inspirecms-support::excel' => view('inspirecms-support::icons.excel'),
        ]);
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_nestable-trees_table',
            'create_media-assets_table',
        ];
    }
}
