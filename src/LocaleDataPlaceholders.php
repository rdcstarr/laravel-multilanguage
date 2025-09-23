<?php

namespace Rdcstarr\Multilanguage;

use Closure;
use Illuminate\Support\Str;

class LocaleDataPlaceholders
{
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
}
