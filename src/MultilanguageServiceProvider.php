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

		// Register the MetadataManager singleton
		$this->app->singleton('metadata', MetadataManager::class);
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

		// @metadata('key', 'default')
		Blade::directive('metadata', fn($expression) => "<?php echo e(metadata()->get($expression)); ?>");

		// @metadataForLang('lang', 'key', 'default')
		Blade::directive('metadataForLang', function ($expression)
		{
			[$lang, $key, $default] = array_pad(explode(',', $expression, 3), 3, null);
			$lang                   = trim($lang);
			$key                    = $key ? trim($key) : "''";
			$default                = $default ? trim($default) : 'null';

			return "<?php echo e(metadata()->lang({$lang})->get({$key}, {$default})); ?>";
		});

		// @hasMetadata('key')
		Blade::if('hasMetadata', fn($key) => metadata()->has($key));

		// @hasMetadataForLang('lang', 'key')
		Blade::if('hasMetadataForLang', fn($lang, $key) => metadata()->lang($lang)->has($key));
	}
}
