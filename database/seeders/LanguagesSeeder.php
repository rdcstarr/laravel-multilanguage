<?php

namespace Rdcstarr\Multilanguage\Database\Seeders;

use Rdcstarr\Multilanguage\Models\Language;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguagesSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		try
		{
			$languages = File::json(__DIR__ . '/languages.json');

			collect($languages)->each(function ($language)
			{
				Language::updateOrCreate(
					['code' => $language['code']],
					[
						'name' => $language['name'],
						'flag' => $language['flag'] ?? null,
					]
				);
			});
		}
		catch (Exception $e)
		{
			$this->command->error('Seeding failed: ' . $e->getMessage());
			return;
		}

	}
}
