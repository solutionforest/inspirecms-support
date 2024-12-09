<?php

namespace SolutionForest\InspireCms\Support;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Schema\Blueprint;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use SolutionForest\InspireCms\Support\Base\Manifests;
use SolutionForest\InspireCms\Support\Testing\TestsInspireCmsSupport;
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
        $package->name(static::$name);

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
        $this->app->singleton(Manifests\ModelRegistryInterface::class, fn () => $this->app->make(Manifests\ModelRegistry::class));
        $this->app->singleton(Manifests\MediaLibraryRegistryInterface::class, fn () => $this->app->make(Manifests\MediaLibraryRegistry::class));
        $this->app->singleton(Manifests\ResolverRegistryInterface::class, fn () => $this->app->make(Manifests\ResolverRegistry::class));

        Blueprint::mixin(new \SolutionForest\InspireCms\Support\Macros\BlueprintMarcos);
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
            AlpineComponent::make('media-library-component', __DIR__ . '/../resources/dist/components/media-library.js')->loadedOnRequest(),
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
