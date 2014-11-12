<?php namespace Kmm\Pkg;

use Illuminate\Support\ServiceProvider;
use Kmm\Pkg\Commands\BundleModuleCommand;
use Kmm\Pkg\Commands\BundleTagCommand;
use Kmm\Pkg\Commands\BundleRefreshCommand;
use Kmm\Pkg\Commands\BundlePublishCommand;
use Kmm\Pkg\Commands\BundlePracticecommand;
use Kmm\Pkg\Commands\PracticePackagecommand;

class PkgServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
     * Booting
     */
    public function boot()
    {
        $this->package('kmm/pkg');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Bundle specific
		foreach(['Refresh', 'Practice', 'Module', 'Tag'] as $command)
        {
            $this->{"register$command"}();
        }

        // Practice specific
        foreach(['Package'] as $command)
        {
            $this->{"register$command"}();
        }
	}

    /**
     * Register the module
     */
    protected function registerModule()
    {
        // Add modules command
        $this->app['bundle.module'] = $this->app->share(function($app)
        {
             return new BundleModuleCommand($app);
        });

        $this->commands('bundle.module');
    }

    /**
     * Register the Tag
     */
    protected function registerTag()
    {
        // Add modules command
        $this->app['bundle.tag'] = $this->app->share(function($app)
        {
             return new BundleTagCommand($app);
        });

        $this->commands('bundle.tag');
    }

	/**
     * Register the refresh
     */
    protected function registerRefresh()
    {
        // Add modules command
		$this->app['bundle.package'] = $this->app->share(function($app)
		{
			 return new BundleRefreshCommand($app);
		});

        $this->commands('bundle.package');
    }

	/**
     * register command for practice
     */
    public function registerPractice()
    {
        $this->app['bundle.practice'] = $this->app->share(function($app)
        {
            return new BundlePracticecommand($app);
        });

        $this->commands('bundle.practice');
    }

    /**
     * register command for module
     */
    public function registerPackage()
    {
        $this->app['practice.package'] = $this->app->share(function($app)
        {
            return new PracticePackagecommand($app);
        });

        $this->commands('practice.package');
    }


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
