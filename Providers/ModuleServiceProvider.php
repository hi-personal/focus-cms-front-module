<?php

namespace Modules\FocusCmsFrontModule\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'FocusCmsFrontModule';

    protected string $moduleNameLower = 'focuscmsfrontmodule';


    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    |
    | Ez a fázis a container build során fut.
    | Ide kerül:
    | - config merge
    | - helper functions
    |
    */

    public function register(): void
    {
        $this->registerConfig();
    }


    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    |
    | Ez a fázis a container fully booted állapotában fut.
    | Ide kerül:
    | - routes
    | - views
    | - translations
    | - migrations
    | - commands
    | - blade components
    |
    */

    public function boot(): void
    {
        $this->registerModuleHelpers();

        $this->registerViews();

        $this->registerTranslations();

        $this->registerMigrations();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        $this->registerBladeComponents();
    }


    /*
    |--------------------------------------------------------------------------
    | MODULE HELPERS
    |--------------------------------------------------------------------------
    */

    protected function registerModuleHelpers(): void
    {
        $path = base_path(
            "Modules/{$this->moduleName}/Helpers"
        );

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . '/*.php') as $file) {

            require_once $file;

        }
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    protected function registerConfig(): void
    {
        $path = base_path(
            "Modules/{$this->moduleName}/config"
        );

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . '/*.php') as $file) {

            $key = pathinfo($file, PATHINFO_FILENAME);

            $this->mergeConfigFrom(
                $file,
                "module.{$this->moduleNameLower}.{$key}"
            );

        }
    }


    /*
    |--------------------------------------------------------------------------
    | VIEWS
    |--------------------------------------------------------------------------
    */

    protected function registerViews(): void
    {
        $path = base_path(
            "Modules/{$this->moduleName}/resources/views"
        );

        if (!is_dir($path)) {
            return;
        }

        $this->loadViewsFrom(
            $path,
            $this->moduleNameLower
        );

        View::addNamespace(
            $this->moduleNameLower,
            $path
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSLATIONS
    |--------------------------------------------------------------------------
    */

    protected function registerTranslations(): void
    {
        $path = base_path(
            "Modules/{$this->moduleName}/resources/lang"
        );

        if (!is_dir($path)) {
            return;
        }

        $this->loadTranslationsFrom(
            $path,
            $this->moduleNameLower
        );

        Lang::addNamespace(
            $this->moduleNameLower,
            $path
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MIGRATIONS
    |--------------------------------------------------------------------------
    */

    protected function registerMigrations(): void
    {
        $path = base_path(
            "Modules/{$this->moduleName}/database/migrations"
        );

        if (is_dir($path)) {

            $this->loadMigrationsFrom($path);

        }
    }


    /*
    |--------------------------------------------------------------------------
    | COMMANDS
    |--------------------------------------------------------------------------
    */

    protected function registerCommands(): void
    {
        $path = base_path(
            "Modules/{$this->moduleName}/Console/Commands"
        );

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . '/*.php') as $file) {

            $class =
                "Modules\\{$this->moduleName}\\Console\\Commands\\"
                . basename($file, '.php');

            if (class_exists($class)) {

                $this->commands($class);

            }

        }
    }


    /*
    |--------------------------------------------------------------------------
    | BLADE COMPONENTS
    |--------------------------------------------------------------------------
    */

    protected function registerBladeComponents(): void
    {
        $path = base_path(
            "Modules/{$this->moduleName}/Classes/Components"
        );

        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . '/*.php') as $file) {

            $class =
                "Modules\\{$this->moduleName}\\Classes\\Components\\"
                . basename($file, '.php');

            if (class_exists($class)) {

                $tag = strtolower(
                    preg_replace(
                        '/(?<!^)[A-Z]/',
                        '-$0',
                        basename($file, '.php')
                    )
                );

                Blade::component(
                    $class,
                    "{$this->moduleNameLower}-{$tag}"
                );

            }

        }
    }
}