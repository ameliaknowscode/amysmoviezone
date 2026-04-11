<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'movie_id', 'body', 'watched_at', 'is_rewatch'];

    protected $casts = [
        'watched_at' => 'date',
        'is_rewatch' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }
}
