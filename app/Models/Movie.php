<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'release_year', 'poster'];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->orderBy('name');
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function watchlistEntries(): HasMany
    {
        return $this->hasMany(WatchlistEntry::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function posterUrl(): ?string
    {
        return $this->poster ? Storage::url($this->poster) : null;
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
