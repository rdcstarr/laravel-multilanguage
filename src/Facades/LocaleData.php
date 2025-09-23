<?php

namespace Rdcstarr\Multilanguage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rdcstarr\Multilanguage\LocaleDataManager
 */
class LocaleData extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \Rdcstarr\Multilanguage\LocaleDataManager::class;
	}
}
