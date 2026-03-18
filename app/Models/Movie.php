<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'director', 'release_year'];

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
        return route('movies.public', ['movieSlug' => $this->slug ?? Str::slug($this->title)]);
    }

    public function getCast(): Collection
    {
        return $this->credits->filter(fn($c) => !$c->type->is_crew)->values();
    }

    public function getCrew(): Collection
    {
        return $this->credits->filter(fn($c) => $c->type->is_crew)->groupBy(fn($c) => $c->type->name);
    }
}
