<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Quote extends Model
{
	use HasFactory;

	use HasTranslations;

	public $translatable = ['quote_title'];

	protected $fillable = [
		'user_id',
		'movie_id',
		'quote_title',
		'quote_image',
	];

	protected $with = ['comments', 'likes', 'user', 'movie'];

	public function user()
	{
		return $this->belongsTo(User::class)->select(['id', 'username', 'avatar']);
	}

	public function movie()
	{
		return $this->belongsTo(Movie::class)->select(['id', 'movie_title', 'movie_image', 'year']);
	}

	public function comments()
	{
		return $this->hasMany(Comment::class)->select(['id', 'quote_id', 'user_id', 'comment', 'created_at']);
	}

	public function likes()
	{
		return $this->hasMany(Like::class)->select(['id', 'quote_id', 'user_id', 'like', 'created_at']);
	}

	public function notifications()
	{
		return $this->hasMany(Notification::class);
	}

    public function save(array $options = [])
    {
        $this->attributes['quote_title'] = json_encode($this->translations['quote_title'], JSON_UNESCAPED_UNICODE);
        return parent::save($options);
    }

    public function scopePaginateQuery($query, $per_page = 3, $sort = 'desc', $select = ['id', 'user_id', 'movie_id', 'quote_image', 'quote_title'])
	{
		return $query
			->select($select)
			->orderBy('id', $sort)
			->paginate($per_page);
	}
}
