<?php

if (!function_exists('metadata'))
{
	/**
	 * Get app translation value or instance
	 *
	 * @param string|null $key
	 * @param string|null $default
	 * @return mixed
	 */
	function metadata(?string $key = null, ?string $default = null): mixed
	{
		$metadata = app('metadata');

		if ($key === null)
		{
			return $metadata;
		}

		return $metadata->get($key, $default);
	}
}
