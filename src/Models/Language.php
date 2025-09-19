<?php

namespace Rdcstarr\Multilanguage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
	protected $fillable = [
		'name',
		'code',
		'flag',
		'timezone',
	];

	public $timestamps = true;

	public function mldata(): HasMany
	{
		return $this->hasMany(Mldata::class);
	}
}
