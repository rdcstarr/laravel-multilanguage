<?php

if (!function_exists('mldata'))
{
	/**
	 * Get app mldata value or instance
	 *
	 * @param string|null $key
	 * @param string|null $default
	 * @return mixed
	 */
	function mldata(?string $key = null, ?string $default = null): mixed
	{
		$mldata = app('mldata');

		if ($key === null)
		{
			return $mldata;
		}

		return $mldata->get($key, $default);
	}
}
