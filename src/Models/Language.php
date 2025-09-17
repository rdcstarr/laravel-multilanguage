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
	];

	public $timestamps = true;

	public function metadata(): HasMany
	{
		return $this->hasMany(Metadata::class);
	}
}
