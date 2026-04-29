<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Collection as MovieCollection;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'release_year', 'poster',
        'synopsis', 'runtime', 'country', 'language', 'imdb_url', 'letterboxd_url',
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->orderBy('name');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(MovieCollection::class, 'collection_movie')
            ->withPivot('position')
            ->orderBy('name');
    }

    /**
     * Sync this movie's collections while preserving the position of existing
     * pivot rows and appending newly-attached collections at the end of each
     * collection's order.
     */
    public function syncCollections(array $collectionIds): void
    {
        $collectionIds = array_map('intval', $collectionIds);
        $current = $this->collections()->pluck('collections.id')->all();

        $toDetach = array_diff($current, $collectionIds);
        if ($toDetach) {
            $this->collections()->detach($toDetach);
        }

        $toAttach = array_diff($collectionIds, $current);
        foreach ($toAttach as $collectionId) {
            $maxPosition = DB::table('collection_movie')
                ->where('collection_id', $collectionId)
                ->max('position') ?? 0;
            $this->collections()->attach($collectionId, ['position' => $maxPosition + 1]);
        }
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
