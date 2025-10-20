<?php

namespace Rdcstarr\Multilanguage;

use Closure;
use Illuminate\Support\Str;

class LocaleDataPlaceholders
{
	/**
	 * Custom placeholders set by the application.
	 *
	 * @var array
	 */
	protected static array $customPlaceholders = [];


	/**
	 * Replace placeholders in a line with corresponding values.
	 *
	 * Placeholders are in the format :key, :Key, :KEY and can also be wrapped in XML-like tags for Closure replacements.
	 *
	 * @param string $line The line containing placeholders to replace.
	 * @param array $replace An associative array of replacements where keys are placeholder names and values are the replacements or Closures.
	 * @param array $stringableHandlers An associative array mapping class names to callables for converting objects to strings.
	 * @return string The line with all placeholders replaced.
	 */
	public function placeholders(string $line, array $replace = [], array $stringableHandlers = []): string
	{
		// Merge placeholders in order of priority:
		// 1. Default placeholders (lowest priority)
		// 2. Config placeholders
		// 3. Custom static placeholders
		// 4. Method parameter placeholders (highest priority)
		$replace = array_merge(
			$this->getDefaultPlaceholders(),
			$this->getConfigPlaceholders(),
			static::$customPlaceholders,
			$replace
		);

		if (empty($replace))
		{
			return $line;
		}

		$shouldReplace = [];

		foreach ($replace as $key => $value)
		{
			if ($value instanceof Closure)
			{
				$pattern = '/<' . preg_quote((string) $key, '/') . '>(.*?)<\/' . preg_quote((string) $key, '/') . '>/s';

				$line = preg_replace_callback(
					$pattern,
					fn($args) => $value($args[1]),
					$line
				);

				continue;
			}

			if (is_object($value))
			{
				$class = get_class($value);

				if (isset($stringableHandlers[$class]) && is_callable($stringableHandlers[$class]))
				{
					$value = call_user_func($stringableHandlers[$class], $value);
				}
			}

			$keyStr = (string) $key;
			$valStr = (string) ($value ?? '');

			// Basic transformations
			$shouldReplace[":$keyStr"] = $valStr; // :key => exact

			// Case transformations
			$shouldReplace[':' . Str::ucfirst($keyStr)] = Str::ucfirst($valStr); // :Key => ucfirst
			$shouldReplace[':' . Str::upper($keyStr)]   = Str::upper($valStr); // :KEY => upper
			$shouldReplace[":{$keyStr}_lower"]          = Str::lower($valStr); // :key_lower => lower
			$shouldReplace[":{$keyStr}_title"]          = Str::title($valStr); // :key_title => Title Case

			// Laravel-specific transformations
			$shouldReplace[":{$keyStr}_camel"]  = Str::camel($valStr); // :key_camel => camelCase
			$shouldReplace[":{$keyStr}_studly"] = Str::studly($valStr); // :key_studly => StudlyCase
			$shouldReplace[":{$keyStr}_snake"]  = Str::snake($valStr); // :key_snake => snake_case
			$shouldReplace[":{$keyStr}_kebab"]  = Str::kebab($valStr); // :key_kebab => kebab-case
			$shouldReplace[":{$keyStr}_slug"]   = Str::slug($valStr); // :key_slug => url-safe-slug

			// Pluralization
			$shouldReplace[":{$keyStr}_plural"]   = Str::plural($valStr); // :key_plural => pluralized
			$shouldReplace[":{$keyStr}_singular"] = Str::singular($valStr); // :key_singular => singularized

			// String manipulation
			$shouldReplace[":{$keyStr}_limit"]  = Str::limit($valStr, 50); // :key_limit => limited to 50 chars
			$shouldReplace[":{$keyStr}_words"]  = Str::words($valStr, 10); // :key_words => limited to 10 words
			$shouldReplace[":{$keyStr}_start"]  = Str::start($valStr, '/'); // :key_start => /prefixed
			$shouldReplace[":{$keyStr}_finish"] = Str::finish($valStr, '/'); // :key_finish => suffixed/

			// URL and path helpers
			$shouldReplace[":{$keyStr}_basename"] = basename($valStr); // :key_basename => file basename
			$shouldReplace[":{$keyStr}_dirname"]  = dirname($valStr); // :key_dirname => directory name

			// Encoding
			$shouldReplace[":{$keyStr}_base64"]       = base64_encode($valStr); // :key_base64 => base64 encoded
			$shouldReplace[":{$keyStr}_urlencode"]    = urlencode($valStr); // :key_urlencode => URL encoded
			$shouldReplace[":{$keyStr}_htmlentities"] = htmlentities($valStr, ENT_QUOTES, 'UTF-8'); // :key_htmlentities => HTML entities

			// Special Laravel helpers
			$shouldReplace[":{$keyStr}_class"]  = class_basename($valStr); // :key_class => class name without namespace
			$shouldReplace[":{$keyStr}_method"] = Str::camel("get_$valStr"); // :key_method => getValueMethod
		}

		return strtr($line, $shouldReplace);
	}

	/**
	 * Get default placeholders for dates, times, and other common values.
	 *
	 * @return array
	 */
	protected function getDefaultPlaceholders(): array
	{
		$now = now();

		return [
			// Date components
			'year'           => $now->year, // 2025
			'month'          => $now->month, // 10
			'month_name'     => $now->monthName, // October
			'month_short'    => $now->shortMonthName, // Oct
			'day'            => $now->day, // 20
			'day_name'       => $now->dayName, // Monday
			'day_short'      => $now->shortDayName, // Mon
			'quarter'        => $now->quarter, // 4

			// Time components
			'hour'           => $now->hour, // 14 (24-hour format)
			'hour_12'        => $now->format('g'), // 2 (12-hour format)
			'minute'         => $now->minute, // 30
			'second'         => $now->second, // 45
			'am_pm'          => $now->format('A'), // AM/PM
			'am_pm_lower'    => $now->format('a'), // am/pm

			// Common date formats
			'date'           => $now->toDateString(), // 2025-10-20
			'date_formatted' => $now->format('d/m/Y'), // 20/10/2025
			'date_us'        => $now->format('m/d/Y'), // 10/20/2025
			'time'           => $now->toTimeString(), // 14:30:45
			'time_short'     => $now->format('H:i'), // 14:30
			'datetime'       => $now->toDateTimeString(), // 2025-10-20 14:30:45
			'timestamp'      => $now->timestamp, // Unix timestamp

			// ISO formats
			'iso'            => $now->toIso8601String(), // 2025-10-20T14:30:45+00:00
			'iso_date'       => $now->toIso8601ZuluString(), // 2025-10-20T14:30:45Z

			// Human-readable formats
			'ago'            => $now->diffForHumans(), // 1 second ago
			'timezone'       => $now->timezoneName, // UTC

			// Week and year information
			'week'           => $now->week, // Week number
			'week_year'      => $now->weekYear, // ISO week year
			'day_of_year'    => $now->dayOfYear, // Day number in year (1-365/366)
			'days_in_month'  => $now->daysInMonth, // 31

			// Application info (if available)
			'app_name'       => config('app.name', 'Laravel'),
			'app_env'        => config('app.env', 'production'),
			'app_url'        => config('app.url', ''),
		];
	}

	/**
	 * Get placeholders from the configuration file.
	 *
	 * @return array
	 */
	protected function getConfigPlaceholders(): array
	{
		return config('multilanguage.placeholders', []);
	}

	/**
	 * Set custom placeholders that will be available globally.
	 * These will override default and config placeholders but can be overridden by runtime placeholders.
	 *
	 * @param array $placeholders
	 * @return void
	 */
	public static function setCustomPlaceholders(array $placeholders): void
	{
		static::$customPlaceholders = array_merge(static::$customPlaceholders, $placeholders);
	}

	/**
	 * Add a single custom placeholder.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function addCustomPlaceholder(string $key, mixed $value): void
	{
		static::$customPlaceholders[$key] = $value;
	}

	/**
	 * Clear all custom placeholders.
	 *
	 * @return void
	 */
	public static function clearCustomPlaceholders(): void
	{
		static::$customPlaceholders = [];
	}

	/**
	 * Get all custom placeholders.
	 *
	 * @return array
	 */
	public static function getCustomPlaceholders(): array
	{
		return static::$customPlaceholders;
	}
}
