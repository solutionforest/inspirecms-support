<?php

namespace SolutionForest\InspireCms\Support;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use SolutionForest\InspireCms\Support\Testing\TestsFilamentTreeNode;
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

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        Livewire::component('inspirecms-support::file-explorer', TreeNodes\FileExplorerComponent::class);
        Livewire::component('inspirecms-support::model-explorer', TreeNodes\ModelExplorerComponent::class);

        // Asset Registration
        FilamentAsset::register([
            Js::make('tree-node', __DIR__ . '/../resources/dist/tree-node.js'),
            Css::make('tree-node', __DIR__ . '/../resources/dist/tree-node.css')->loadedOnRequest(),
        ], 'solution-forest/inspirecms-support');

        // Testing
        Testable::mixin(new TestsFilamentTreeNode);
    }
}
