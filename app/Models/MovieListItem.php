<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieListItem extends Model
{
    protected $fillable = ['movie_list_id', 'movie_id', 'position'];

    public function movieList(): BelongsTo
    {
        return $this->belongsTo(MovieList::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
