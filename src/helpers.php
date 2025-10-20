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

if (!function_exists('escape_placeholders'))
{
	/**
	 * Escape placeholder syntax in user-generated content
	 *
	 * @param string $value
	 * @return string
	 */
	function escape_placeholders(string $value): string
	{
		return \Rdcstarr\Multilanguage\LocaleDataPlaceholders::escape($value);
	}
}

if (!function_exists('unescape_placeholders'))
{
	/**
	 * Unescape previously escaped placeholder syntax
	 *
	 * @param string $value
	 * @return string
	 */
	function unescape_placeholders(string $value): string
	{
		return \Rdcstarr\Multilanguage\LocaleDataPlaceholders::unescape($value);
	}
}
