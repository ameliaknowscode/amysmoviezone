<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovieList extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'description', 'is_public', 'is_ranked'];

    protected $casts = [
        'is_public'  => 'boolean',
        'is_ranked'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MovieListItem::class)->orderBy('position');
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_list_items')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function visibleTo(?User $user): bool
    {
        if ($this->is_public) {
            return true;
        }

        return $user && $this->user_id === $user->id;
    }
}
