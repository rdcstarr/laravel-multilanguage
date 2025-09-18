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

		Artisan::call('db:seed', [
			'--class' => LanguagesSeeder::class,
		]);
	}
}
