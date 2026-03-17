<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Actor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'date_of_birth', 'nationality'];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class)->withPivot('role');
    }
}
