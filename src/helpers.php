<?php

if (!function_exists('localedata'))
{
	/**
	 * Get app localedata value or instance
	 *
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	function localedata(?string $key = null, mixed $default = ''): mixed
	{
		$localedata = app('localedata');

		if ($key === null)
		{
			return $localedata;
		}

		return $localedata->get($key, $default);
	}
}

if (!function_exists('_ld'))
{
	/**
	 * Get app localedata value or instance
	 *
	 * @param string|null $key
	 * @param mixed $default
	 * @return mixed
	 */
	function _ld(?string $key = null, mixed $default = ''): mixed
	{
		$localedata = app('localedata');

		if ($key === null)
		{
			return $localedata;
		}

		return $localedata->get($key, $default);
	}
}

if (!function_exists('set_placeholder'))
{
	/**
	 * Add a custom placeholder
	 *
	 * @param string|array $key
	 * @param mixed $value
	 * @return void
	 */
	function set_placeholder(string|array $key, mixed $value = ''): void
	{
		if (is_array($key))
		{
			\Rdcstarr\Multilanguage\LocaleDataPlaceholders::setCustomPlaceholders($key);
		}
		else
		{
			\Rdcstarr\Multilanguage\LocaleDataPlaceholders::addCustomPlaceholder($key, $value);
		}
	}
}
