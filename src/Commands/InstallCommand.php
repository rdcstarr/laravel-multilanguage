<?php

namespace Rdcstarr\Multilanguage\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Rdcstarr\Multilanguage\Database\Seeders\LanguagesSeeder;

use function Laravel\Prompts\confirm;

class InstallCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'multilanguage:install {--force : Run commands without confirmation}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install the multilingual package';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if (!$this->option('force'))
		{
			if (!confirm("This will publish migrations, run migrations, and seed the languages table. Do you want to continue?"))
			{
				$this->warn('🚫 Multilanguage package installation was canceled.');
				return self::SUCCESS;
			}
		}

		$this->components->info('Starting Multilanguage Package Installation...');

		$steps = [
			'📄 Publishing seeders' => 'publishSeeders',
			'🏁 Running migrations' => 'runMigrations',
			'🌱 Seeding languages'  => 'runSeeder',
		];

		collect($steps)->each(function ($method, $name)
		{
			try
			{
				$this->components->task($name, fn() => $this->{$method}());
			}
			catch (Exception $e)
			{
				$this->components->error($name . ' failed: ' . $e->getMessage());
				exit;
			}
		});

		$this->components->success('Multilanguage Package Installation Completed Successfully!');
	}

	/**
	 * Publish the seeders.
	 *
	 * @return void
	 */
	protected function publishSeeders()
	{
		Artisan::call('vendor:publish', [
			'--provider' => 'Rdcstarr\Multilanguage\MultilanguageServiceProvider',
			'--tag'      => 'seeders',
			'--force'    => true,
		]);
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	protected function runMigrations()
	{
		Artisan::call('migrate');
	}

	/**
	 * Seed the languages table.
	 *
	 * @return void
	 */
	protected function runSeeder()
	{
		// Try to use the published seeder first, then fall back to package seeder
		$publishedSeederClass = 'Database\\Seeders\\LanguagesSeeder';
		$packageSeederClass   = LanguagesSeeder::class;

		// Check if published seeder exists in the app
		$publishedSeederPath = database_path('seeders/LanguagesSeeder.php');

		if (file_exists($publishedSeederPath))
		{
			$seederClass = $publishedSeederClass;
		}
		else
		{
			$seederClass = $packageSeederClass;
		}

		Artisan::call('db:seed', [
			'--class' => $seederClass,
		]);
	}
}
