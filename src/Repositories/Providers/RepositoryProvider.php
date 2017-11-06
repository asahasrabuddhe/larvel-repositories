<?php

namespace Asahasrabuddhe\Repositories\Providers;

use Asahasrabuddhe\Repositories\Commands\Creators\RepositoryCreator;
use Asahasrabuddhe\Repositories\Commands\Creators\RuleCreator;
use Asahasrabuddhe\Repositories\Commands\MakeRepositoryCommand;
use Asahasrabuddhe\Repositories\Commands\MakeRuleCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryProvider.
 */
class RepositoryProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Config path.
        $config_path = __DIR__.'/../../../config/repositories.php';
        // Publish config.
        $this->publishes(
            [$config_path => config_path('repositories.php')],
            'repositories'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Register bindings.
        $this->registerBindings();
        // Register commands
        $this->commands([MakeRepositoryCommand::class, MakeRuleCommand::class]);
        // Config path.
        $config_path = dirname(dirname(dirname(__DIR__))).'/config/repositories.php';
        // Merge config.
        $this->mergeConfigFrom(
            $config_path,
            'repositories'
        );
    }

    /**
     * Register the bindings.
     */
    protected function registerBindings()
    {
        // FileSystem.
        $this->app->instance('FileSystem', new Filesystem());
        // Composer.
        $this->app->bind('Composer', function ($app) {
            return new Composer($this->app->make('FileSystem'));
        });
        // Repository creator.
        $this->app->singleton('RepositoryCreator', function ($app) {
            return new RepositoryCreator($this->app->make('FileSystem'));
        });
        // Rule creator.
        $this->app->singleton('RuleCreator', function ($app) {
            return new RuleCreator($this->app->make('FileSystem'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.repository.make',
            'command.rule.make',
        ];
    }
}
