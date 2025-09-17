<?php

namespace Rdcstarr\Multilanguage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rdcstarr\Multilanguage\MetadataManager
 */
class Metadata extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \Rdcstarr\Multilanguage\MetadataManager::class;
	}
}
