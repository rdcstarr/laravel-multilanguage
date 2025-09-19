<?php

namespace Rdcstarr\Multilanguage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rdcstarr\Multilanguage\MldataManager
 */
class Mldata extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \Rdcstarr\Multilanguage\MldataManager::class;
	}
}
