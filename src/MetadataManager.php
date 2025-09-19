<?php

namespace Rdcstarr\Multilanguage;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Rdcstarr\Multilanguage\Models\Language;
use Rdcstarr\Multilanguage\Models\Metadata;
use Throwable;

class MetadataManager
{
	/**
	 * The cache key used for storing settings.
	 *
	 * @var string
	 */
	protected string $cacheKey = 'app_metadata';

	/**
	 * Language code for settings.
	 *
	 * @var ?string
	 */
	protected ?string $languageCode = null;

	/**
	 * Allowed language codes for the application.
	 *
	 * @var array|null
	 */
	protected ?array $allowedLanguages = null;

	/**
	 * Constructor - initialize with default language.
	 */
	public function __construct()
	{
		$this->languageCode = app()->getLocale() ?? 'en';
	}

	/**
	 * Retrieve all settings from the cache or database.
	 *
	 * @return Collection A collection of all settings as key-value pairs.
	 * @throws InvalidArgumentException If the current language is not allowed.
	 */
	public function all(): Collection
	{
		$this->validateCurrentLanguage();

		return Cache::rememberForever($this->cacheKey(), fn() => Metadata::byLanguageCode($this->languageCode)->pluck('value', 'key'));
	}

	/**
	 * Set the language for metadata retrieval.
	 *
	 * @param string|null $languageCode The language code to set (e.g., 'en', 'ro').
	 * @return $this A new instance of MetadataManager with the specified language.
	 * @throws InvalidArgumentException If the language is not allowed.
	 */
	public function lang(?string $languageCode): self
	{
		$clone               = clone $this;
		$clone->languageCode = blank($languageCode) ? 'en' : trim($languageCode);

		return $clone;
	}

	/**
	 * Get the value of a specific setting by its key.
	 *
	 * @param string $key The setting key to retrieve.
	 * @param mixed $default The default value to return if the key doesn't exist.
	 * @return mixed The setting value or the default value if not found.
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->all()->get($key, $default);
	}

	/**
	 * Get multiple setting values by their keys.
	 *
	 * @param array $keys An array of setting keys to retrieve.
	 * @return array An associative array containing the requested key-value pairs.
	 */
	public function getMany(array $keys): array
	{
		$all = $this->all();

		return collect($keys)->mapWithKeys(fn($key) => [$key => $all->get($key)])->all();
	}

	/**
	 * Set a single setting value or multiple settings at once.
	 *
	 * @param string|array $key The setting key (string) or an array of key-value pairs.
	 * @param mixed $value The value to set (ignored when $key is an array).
	 * @return bool True if the operation was successful, false otherwise.
	 */
	public function set(string|array $key, mixed $value = null): bool
	{
		try
		{
			if (is_array($key))
			{
				return $this->setMany($key);
			}

			$language = $this->getLanguage();
			if (!$language)
			{
				return false;
			}

			Metadata::updateOrCreate(
				['language_id' => $language->id, 'key' => $key],
				['value' => $value]
			);

			$this->flushCache();

			return true;
		}
		catch (Throwable $e)
		{
			report($e);
			return false;
		}
	}

	/**
	 * Set multiple settings in a single batch operation.
	 *
	 * @param array $settings An associative array of key-value pairs to store.
	 * @return bool True if the operation was successful, false otherwise.
	 */
	public function setMany(array $settings): bool
	{
		if (empty($settings))
		{
			return true;
		}

		try
		{
			$language = $this->getLanguage();
			if (!$language)
			{
				return false;
			}

			$data = collect($settings)->map(fn($value, $key) => [
				'language_id' => $language->id,
				'key'         => $key,
				'value'       => $value,
				'created_at'  => now(),
				'updated_at'  => now(),
			])->values()->toArray();

			Metadata::upsert($data, ['language_id', 'key'], ['value', 'updated_at']);
			$this->flushCache();

			return true;
		}
		catch (Throwable $e)
		{
			report($e);
			return false;
		}
	}

	/**
	 * Check if a setting key exists in the storage.
	 *
	 * @param string $key The setting key to check for existence.
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has(string $key): bool
	{
		return $this->all()->has($key);
	}

	/**
	 * Remove a setting by its key from the storage.
	 *
	 * @param string $key The setting key to remove.
	 * @return bool True if the key was successfully deleted, false if not found or on error.
	 */
	public function forget(string $key): bool
	{
		try
		{
			$language = $this->getLanguage();
			if (!$language)
			{
				return false;
			}

			$deleted = Metadata::where([
				'language_id' => $language->id,
				'key'         => $key,
			])->delete();

			if ($deleted > 0)
			{
				$this->flushCache();
				return true;
			}

			return false;
		}
		catch (Throwable $e)
		{
			report($e);
			return false;
		}
	}

	/**
	 * Clear all settings cache for all languages.
	 *
	 * @return bool True if all caches were successfully cleared, false otherwise.
	 */
	public function flushAllCache(): bool
	{
		try
		{
			Language::all()->each(function ($language)
			{
				Cache::forget("{$this->cacheKey}:{$language->code}");
			});

			return true;
		}
		catch (Throwable $e)
		{
			return false;
		}
	}

	/**
	 * Clear the settings cache to force fresh data retrieval.
	 *
	 * @return bool True if the cache was successfully cleared, false otherwise.
	 */
	public function flushCache(): bool
	{
		try
		{
			Cache::forget($this->cacheKey());
			return true;
		}
		catch (Throwable $e)
		{
			return false;
		}
	}

	/**
	 * Get the Language model for the current language code.
	 *
	 * @return Language|null
	 */
	protected function getLanguage(): ?Language
	{
		return Language::where('code', $this->languageCode)->first();
	}

	/**
	 * Generate the cache key for the current language.
	 *
	 * @return string
	 */
	protected function cacheKey(): string
	{
		return "{$this->cacheKey}:{$this->languageCode}";
	}

	/**
	 * Set or get the allowed languages for the application.
	 *
	 * @param array|null $languages Array of language codes to allow (e.g., ['ro', 'en', 'it']). If null, returns current allowed languages.
	 * @return array|self Returns array of allowed languages if $languages is null, otherwise returns $this for chaining.
	 */
	public function allowedLanguages(?array $languages = null): array|self
	{
		if ($languages === null)
		{
			return $this->allowedLanguages ?? [];
		}

		$this->allowedLanguages = array_filter(array_map('trim', $languages));

		return $this;
	}

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
			$shouldReplace[':' . Str::lower($keyStr)]   = Str::lower($valStr); // :key_lower => lower
			$shouldReplace[':' . Str::title($keyStr)]   = Str::title($valStr); // :Key_title => Title Case

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
	 * Advanced placeholder replacement with custom transformations and Laravel helpers.
	 *
	 * @param string $line The line containing placeholders to replace.
	 * @param array $replace An associative array of replacements.
	 * @param array $customTransformations Custom transformation functions.
	 * @return string The line with all placeholders replaced.
	 */
	public function advancedPlaceholders(string $line, array $replace = [], array $customTransformations = []): string
	{
		if (empty($replace))
		{
			return $line;
		}

		// Add default Laravel transformations
		$defaultTransformations = [
			'route'        => fn($value) => route($value),
			'url'          => fn($value) => url($value),
			'asset'        => fn($value) => asset($value),
			'config'       => fn($value) => config($value),
			'trans'        => fn($value) => trans($value),
			'trans_choice' => fn($value, $count = 1) => trans_choice($value, $count),
			'old'          => fn($value) => old($value),
			'request'      => fn($value) => request($value),
			'session'      => fn($value) => session($value),
			'auth_user'    => fn($attribute = 'id') => auth()->user()?->{$attribute},
			'carbon'       => fn($value) => \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s'),
			'carbon_human' => fn($value) => \Carbon\Carbon::parse($value)->diffForHumans(),
			'money'        => fn($value) => number_format((float) $value, 2, '.', ','),
			'percentage'   => fn($value) => number_format((float) $value * 100, 2) . '%',
		];

		$transformations = array_merge($defaultTransformations, $customTransformations);

		// Handle advanced transformations with pipe syntax: :key|transformation
		$pattern = '/:([\w]+)(?:\|([\w_]+)(?:\((.*?)\))?)?/';

		$line = preg_replace_callback($pattern, function ($matches) use ($replace, $transformations)
		{
			$key            = $matches[1];
			$transformation = $matches[2] ?? null;
			$params = isset($matches[3]) ? explode(',', $matches[3]) : [];

			if (!isset($replace[$key]))
			{
				return $matches[0]; // Return original if key not found
			}

			$value = $replace[$key];

			if ($transformation && isset($transformations[$transformation]))
			{
				$transformer = $transformations[$transformation];
				$value = $transformer($value, ...$params);
			}

			return (string) $value;
		}, $line);

		// Fallback to basic placeholder replacement
		return $this->placeholders($line, $replace);
	}

	/**
	 * Create Laravel-specific placeholders for common use cases.
	 *
	 * @param array $data Base data array.
	 * @return array Enhanced data with Laravel-specific placeholders.
	 */
	public function laravelPlaceholders(array $data = []): array
	{
		$enhanced = $data;

		// Add common Laravel context
		$enhanced['app_name']  = config('app.name', 'Laravel');
		$enhanced['app_env']   = config('app.env', 'production');
		$enhanced['app_url']   = config('app.url', 'http://localhost');
		$enhanced['app_debug'] = config('app.debug') ? 'true' : 'false';

		// User context (if authenticated)
		if (auth()->check())
		{
			$user                   = auth()->user();
			$enhanced['user_id']    = $user->id;
			$enhanced['user_name']  = $user->name ?? '';
			$enhanced['user_email'] = $user->email ?? '';
		}

		// Request context
		if (app()->bound('request'))
		{
			$request                    = request();
			$enhanced['request_path']   = $request->path();
			$enhanced['request_url']    = $request->url();
			$enhanced['request_method'] = $request->method();
			$enhanced['request_ip']     = $request->ip();
		}

		// Date/time context
		$enhanced['now']       = now()->toDateTimeString();
		$enhanced['today']     = today()->toDateString();
		$enhanced['timestamp'] = time();

		return $enhanced;
	}

	/**
	 * Validate if the current language is allowed.
	 *
	 * @throws InvalidArgumentException If the current language is not allowed.
	 */
	protected function validateCurrentLanguage(): void
	{
		if ($this->allowedLanguages !== null && !in_array($this->languageCode, $this->allowedLanguages, true))
		{
			throw new InvalidArgumentException("Language code '{$this->languageCode}' is not allowed. Allowed languages: " . implode(', ', $this->allowedLanguages));
		}
	}
}
