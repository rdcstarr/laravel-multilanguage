<?php

namespace Rdcstarr\Multilanguage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mldata extends Model
{
	protected $fillable = [
		'language_id',
		'key',
		'value',
	];

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	public function language(): BelongsTo
	{
		return $this->belongsTo(Language::class);
	}

	public function scopeByLanguageCode($query, string $languageCode)
	{
		return $query->whereHas('language', function ($q) use ($languageCode)
		{
			$q->where('code', $languageCode);
		});
	}
}
