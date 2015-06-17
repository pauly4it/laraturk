<?php namespace Pauly4it\LaraTurk;

use Illuminate\Support\ServiceProvider;

class LaraTurkServiceProvider extends ServiceProvider {

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
		$configPath = __DIR__ . '/../config/laraturk.php';

		$config = [ $configPath => config_path('laraturk.php') ];

		$this->publishes( $config );
	}
	
	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->singleton('laraturk', function ($app) {
            return new MechanicalTurk;
        });
	}

	/**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('laraturk');
    }

}