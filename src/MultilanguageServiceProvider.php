<?php

namespace Rdcstarr\Multilanguage;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Support\Facades\Blade;
use Rdcstarr\Multilanguage\Commands\InstallCommand;

class MultilanguageServiceProvider extends PackageServiceProvider
{
	/*
	 * This class is a Package Service Provider
	 *
	 * More info: https://github.com/spatie/laravel-package-tools
	 */
	public function configurePackage(Package $package): void
	{
		$package->name('multilanguage')
			->hasConfigFile()
			->hasCommand(InstallCommand::class);
	}

	public function register(): void
	{
		parent::register();

		$this->app->singleton('localedata', LocaleDataManager::class);
		$this->app->singleton('localedata.placeholders', LocaleDataPlaceholders::class);
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

		// @localedata('key', 'default')
		Blade::directive('localedata', fn($expression) => "<?php echo e(localedata()->get($expression)); ?>");

		// @localedataForLang('lang', 'key', 'default')
		Blade::directive('localedataForLang', function ($expression)
		{
			[$lang, $key, $default] = array_pad(explode(',', $expression, 3), 3, null);
			$lang                   = trim($lang);
			$key                    = $key ? trim($key) : "''";
			$default                = $default ? trim($default) : 'null';

			return "<?php echo e(localedata()->lang({$lang})->get({$key}, {$default})); ?>";
		});

		// @hasLocaledata('key')
		Blade::if('hasLocaledata', fn($key) => localedata()->has($key));

		// @hasLocaledataForLang('lang', 'key')
		Blade::if('hasLocaledataForLang', fn($lang, $key) => localedata()->lang($lang)->has($key));
	}
}
