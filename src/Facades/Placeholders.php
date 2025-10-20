<?php

namespace Rdcstarr\Multilanguage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void setCustomPlaceholders(array $placeholders)
 * @method static void addCustomPlaceholder(string $key, mixed $value)
 * @method static void clearCustomPlaceholders()
 * @method static array getCustomPlaceholders()
 * @method static string placeholders(string $line, array $replace = [], array $stringableHandlers = [])
 * @method static string escape(string $value)
 * @method static string unescape(string $value)
 *
 * @see \Rdcstarr\Multilanguage\LocaleDataPlaceholders
 */
class Placeholders extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'localedata.placeholders';
	}
}
