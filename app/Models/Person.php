<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'date_of_birth', 'nationality'];

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }
}
