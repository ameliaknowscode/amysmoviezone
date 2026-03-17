<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = ['movie_id', 'person_id', 'type_id', 'character'];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function byTypeUrl(): string
    {
        return route('credits.by-type', [
            'typeSlug'   => Str::slug($this->type->name),
            'personSlug' => Str::slug($this->person->name),
        ]);
    }
}
