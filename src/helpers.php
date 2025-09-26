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
