<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'director', 'release_year'];

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class)->withPivot('role');
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    public function publicUrl(): string
    {
        return route('movies.public', ['movieSlug' => Str::slug($this->title)]);
    }
}
