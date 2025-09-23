<?php

namespace Rdcstarr\Multilanguage;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Rdcstarr\Multilanguage\Models\LocaleData;
use Rdcstarr\Multilanguage\LocaleDataPlaceholders;
use Rdcstarr\Multilanguage\Models\Language;
use Throwable;

class LocaleDataManager
{
	/**
	 * Language code for locale data.
	 *
	 * @var ?string
	 */
	protected ?string $languageCode = null;

	/**
	 * Constructor - initialize with default language.
	 */
	public function __construct()
	{
		$this->languageCode = app()->getLocale() ?? 'en';
	}

	/**
	 * Retrieve all locale data from the cache or database.
	 *
	 * @return Collection A collection of all locale data as key-value pairs.
	 */
	public function all(): Collection
	{
		return Cache::tags(['localedata', "localedata.lang.{$this->languageCode}"])
			->rememberForever("data", fn() => LocaleData::byLanguageCode($this->languageCode)->pluck('value', 'key'));
	}

	/**
	 * Set the language for locale data retrieval.
	 *
	 * @param string|null $languageCode The language code to set (e.g., 'en', 'ro').
	 * @return $this A new instance of LocaleDataManager with the specified language.
	 */
	public function lang(?string $languageCode): self
	{
		$clone               = clone $this;
		$clone->languageCode = blank($languageCode) ? 'en' : trim($languageCode);

		return $clone;
	}

	/**
	 * Get the value of a specific locale data by its key.
	 *
	 * @param string $key The locale data key to retrieve.
	 * @param mixed $default The default value to return if the key doesn't exist.
	 * @return object The locale data value wrapped with placeholder methods.
	 */
	public function get(string $key, mixed $default = ''): mixed
	{
		if ($default === '' && !$this->has($key))
		{
			throw new InvalidArgumentException("Locale data key '{$key}' doesn't exist for language '{$this->languageCode}'.");
		}

		$value        = (string) $this->all()->get($key, $default);
		$placeholders = new LocaleDataPlaceholders();

		return new class ($value, $placeholders)
		{
			public function __construct(private string $value, private LocaleDataPlaceholders $placeholders)
			{
			}

			public function placeholders(array $replace = [], array $stringableHandlers = []): string
			{
				return $this->placeholders->placeholders($this->value, $replace, $stringableHandlers);
			}

			public function raw(): string
			{
				return $this->value;
			}

			public function __toString(): string
			{
				return $this->value;
			}
		};
	}

	/**
	 * Get multiple locale data values by their keys.
	 *
	 * @param array $keys An array of locale data keys to retrieve.
	 * @return array An associative array containing the requested key-value pairs.
	 */
	public function getMany(array $keys): array
	{
		$all = $this->all();

		$missingKeys = collect($keys)->reject(fn($key) => $all->has($key));

		$missingKeys->whenNotEmpty(function ($missing)
		{
			$firstMissing = $missing->first();
			throw new InvalidArgumentException("Locale data key '{$firstMissing}' doesn't exist for language '{$this->languageCode}'.");
		});

		return collect($keys)->mapWithKeys(fn($key) => [$key => $all->get($key)])->all();
	}

	/**
	 * Set a single locale data value or multiple at once.
	 *
	 * @param string|array $key The locale data key (string) or an array of key-value pairs.
	 * @param mixed $value The value to set (ignored when $key is an array).
	 * @return bool
	 */
	public function set(string|array $key, mixed $value = null): bool
	{
		if (is_array($key))
		{
			return $this->setMany($key);
		}

		try
		{
			$language = $this->getLanguage();

			LocaleData::updateOrCreate(
				['language_id' => $language->id, 'key' => $key],
				['value' => $value]
			);

			$this->flushCache();

			return true;

		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Set multiple locale data in a single batch operation.
	 *
	 * @param array $values An associative array of key-value pairs to store.
	 * @return bool
	 * @throws InvalidArgumentException If the values array is empty.
	 */
	public function setMany(array $values): bool
	{
		if (empty($values))
		{
			throw new InvalidArgumentException('Values array cannot be empty.');
		}

		try
		{
			$language = $this->getLanguage();

			$data = collect($values)->map(fn($value, $key) => [
				'language_id' => $language->id,
				'key'         => $key,
				'value'       => $value,
				'created_at'  => now(),
				'updated_at'  => now(),
			])->values()->toArray();

			LocaleData::upsert($data, ['language_id', 'key'], ['value', 'updated_at']);
			$this->flushCache();

			return true;

		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Check if a locale data key exists in the storage.
	 *
	 * @param string $key The locale data key to check for existence.
	 * @return bool True if the key exists, false otherwise.
	 */
	public function has(string $key): bool
	{
		return $this->all()->keys()->contains($key);
	}

	/**
	 * Remove a locale data by its key from the storage.
	 *
	 * @param string $key The locale data key to remove.
	 * @return bool
	 */
	public function forget(string $key): bool
	{
		try
		{
			$language = $this->getLanguage();

			$deleted = LocaleData::where([
				'language_id' => $language->id,
				'key'         => $key,
			])->delete();

			if ($deleted > 0)
			{
				$this->flushCache();
			}
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Clear all locale data cache for all languages.
	 *
	 * @return bool
	 */
	public function flushAllCache(): bool
	{
		try
		{
			Cache::tags(['localedata'])->flush();
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Clear the locale data cache to force fresh data retrieval.
	 *
	 * @return bool
	 */
	public function flushCache(): bool
	{
		try
		{
			Cache::tags(["localedata.lang.{$this->languageCode}"])->flush();
			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the Language model for the current language code.
	 *
	 * @return Language|null
	 * @throws InvalidArgumentException If the current language doesn't exist.
	 */
	protected function getLanguage(): ?Language
	{
		return Language::where('code', $this->languageCode)->first() ?? throw new InvalidArgumentException("Language code '{$this->languageCode}' doesn't exist.");
	}
}
