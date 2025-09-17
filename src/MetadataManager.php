<?php

namespace Rdcstarr\Multilanguage;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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
	 * @throws \InvalidArgumentException If the current language is not allowed.
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
	 * @throws \InvalidArgumentException If the language is not allowed.
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
	 * Validate if the current language is allowed.
	 *
	 * @throws \InvalidArgumentException If the current language is not allowed.
	 */
	protected function validateCurrentLanguage(): void
	{
		if ($this->allowedLanguages !== null && !in_array($this->languageCode, $this->allowedLanguages, true))
		{
			throw new \InvalidArgumentException("Language code '{$this->languageCode}' is not allowed. Allowed languages: " . implode(', ', $this->allowedLanguages));
		}
	}
}
