<?php

namespace Rdcstarr\Multilanguage;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Support\Facades\Blade;
use Rdcstarr\Multilanguage\Commands\InstallCommand;

class MultilanguageServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		/*
		 * This class is a Package Service Provider
		 *
		 * More info: https://github.com/spatie/laravel-package-tools
		 */
		$package->name('multilanguage')
			->hasCommand(InstallCommand::class);
	}

	public function register(): void
	{
		parent::register();

		// Register the MldataManager singleton
		$this->app->singleton('mldata', MldataManager::class);
	}

	public function boot(): void
	{
		parent::boot();

		// Load migrations
		if (app()->runningInConsole())
		{
			$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

			// Publish migrations
			$this->publishes([
				__DIR__ . '/../database/migrations' => database_path('migrations'),
			], 'migrations');

			// Publish seeder stub with correct namespace
			$this->publishes([
				__DIR__ . '/../stubs/LanguagesSeeder.php.stub' => database_path('seeders/LanguagesSeeder.php'),
			], 'seeders');
		}

		// @mldata('key', 'default')
		Blade::directive('mldata', fn($expression) => "<?php echo e(mldata()->get($expression)); ?>");

		// @mldataForLang('lang', 'key', 'default')
		Blade::directive('mldataForLang', function ($expression)
		{
			[$lang, $key, $default] = array_pad(explode(',', $expression, 3), 3, null);
			$lang                   = trim($lang);
			$key                    = $key ? trim($key) : "''";
			$default                = $default ? trim($default) : 'null';

			return "<?php echo e(mldata()->lang({$lang})->get({$key}, {$default})); ?>";
		});

		// @hasMldata('key')
		Blade::if('hasMldata', fn($key) => mldata()->has($key));

		// @hasMldataForLang('lang', 'key')
		Blade::if('hasMldataForLang', fn($lang, $key) => mldata()->lang($lang)->has($key));
	}
}
