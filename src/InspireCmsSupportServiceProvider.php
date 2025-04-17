<?php

namespace SolutionForest\InspireCms\Support;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Schema\Blueprint;
use Livewire\Livewire;
use SolutionForest\InspireCms\Support\Base\Manifests;
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

    public function registeringPackage()
    {
        //
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Manifests\ModelRegistryInterface::class, fn () => $this->app->make(Manifests\ModelRegistry::class));
        $this->app->singleton(Manifests\MediaLibraryRegistryInterface::class, fn () => $this->app->make(Manifests\MediaLibraryRegistry::class));
        $this->app->singleton(Manifests\ResolverRegistryInterface::class, fn () => $this->app->make(Manifests\ResolverRegistry::class));
        $this->app->singleton(Manifests\AuthManagerInterface::class, fn () => $this->app->make(Manifests\AuthManager::class));

        Blueprint::mixin(new \SolutionForest\InspireCms\Support\Macros\BlueprintMarcos);

        \SolutionForest\InspireCms\Support\Facades\ResolverRegistry::register($this->app);
    }

    public function packageBooted(): void
    {
        Livewire::component('inspirecms-support::file-explorer', TreeNodes\FileExplorerComponent::class);
        Livewire::component('inspirecms-support::model-explorer', TreeNodes\ModelExplorerComponent::class);

        Livewire::component('inspirecms-support::media-library', MediaLibrary\MediaLibraryComponent::class);
        Livewire::component('inspirecms-support::media-library.folders', MediaLibrary\FolderBrowserComponent::class);
        Livewire::component('inspirecms-support::media-library.detail-info', MediaLibrary\MediaDetailComponent::class);

        // Asset Registration
        FilamentAsset::register([
            Css::make('tree-node', __DIR__ . '/../resources/dist/components/tree-node.css'),
            Css::make('media-library', __DIR__ . '/../resources/dist/components/media-library.css'),
            AlpineComponent::make('media-draggable-item-component', __DIR__ . '/../resources/dist/components/media-draggable-item.js')->loadedOnRequest(),
            AlpineComponent::make('tree-node-component', __DIR__ . '/../resources/dist/components/tree-node.js')->loadedOnRequest(),
        ], 'solution-forest/inspirecms-support');

        FilamentIcon::register($this->getIcons());

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn () => view('inspirecms-support::forms.components.media-picker.modal')
        );
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_nestable-trees_table',
            'create_media-assets_table',
            'create_custom_spatie_media_table',
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        $iconPrefix = 'inspirecms::';

        return collect([

            'info' => 'heroicon-o-information-circle',
            'warn' => 'heroicon-o-exclamation-triangle',
            'error' => 'heroicon-o-exclamation-circle',
            'success' => 'heroicon-o-check-circle',

            'add' => 'heroicon-o-plus-small',
            'edit' => 'heroicon-m-pencil-square',
            'view' => 'heroicon-o-eye',
            'delete' => 'heroicon-m-trash',

            'reset' => 'heroicon-o-arrow-path',
            'clone' => 'heroicon-o-document-duplicate',
            'attach' => 'heroicon-o-link',
            'detach' => 'heroicon-m-x-mark',
            'restore' => 'heroicon-m-arrow-uturn-left',

            'edit.simple' => 'heroicon-o-pencil',

            'upload' => 'heroicon-m-arrow-up-tray',
            'download' => 'heroicon-m-arrow-down-tray',

            'create_folder' => 'heroicon-o-folder-plus',
            'open_folder' => 'heroicon-o-folder-open',

            'pdf' => view('inspirecms-support::icons.pdf'),
            'excel' => view('inspirecms-support::icons.excel'),
            'svg' => view('inspirecms-support::icons.svg'),

        ])->mapWithKeys(fn ($icon, $key) => ["{$iconPrefix}{$key}" => $icon])->all();
    }
}
