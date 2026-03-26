<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Person extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'date_of_birth', 'date_of_death', 'nationality'];

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    /**
     * The credit whose type appears most frequently in the already-loaded credits.
     * Returns null if the person has no credits.
     */
    private function dominantCredit(): ?Credit
    {
        $dominantTypeId = $this->credits
            ->groupBy('type_id')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first();

        return $dominantTypeId
            ? $this->credits->firstWhere('type_id', $dominantTypeId)
            : null;
    }

    /**
     * Name of this person's most-credited type, using already-loaded credits.
     * Returns null if the person has no credits.
     */
    public function dominantTypeName(): ?string
    {
        return $this->dominantCredit()?->type?->name;
    }

    /**
     * URL for this person's most-credited type, using already-loaded credits.
     * Returns null if the person has no credits.
     */
    public function dominantTypeUrl(): ?string
    {
        $type = $this->dominantCredit()?->type;

        if (! $type) {
            return null;
        }

        return route('credits.by-type', [
            'typeSlug'   => Str::slug($type->name),
            'personSlug' => $this->slug ?? Str::slug($this->name),
        ]);
    }
}
