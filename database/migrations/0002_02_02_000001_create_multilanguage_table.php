<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations
	 */
	public function up()
	{
		Schema::create('languages', function (Blueprint $table)
		{
			$table->id();
			$table->string('name')->unique()->index();
			$table->string('code', 5)->unique()->index();
			$table->string('flag')->nullable();
			$table->string('timezone')->nullable();
			$table->timestamps();
		});

		Schema::create('mldata', function (Blueprint $table)
		{
			$table->id();
			$table->foreignId('language_id')->constrained('languages');
			$table->string('key')->index();
			$table->longText('value');
			$table->timestamps();

			$table->unique(['language_id', 'key']);
		});
	}

	/**
	 * Reverse the migrations
	 */
	public function down(): void
	{
		Schema::dropIfExists('mldata');
		Schema::dropIfExists('languages');
	}
};
