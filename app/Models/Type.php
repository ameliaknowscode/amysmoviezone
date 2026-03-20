<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Type extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_crew'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $type) {
            $type->slug ??= Str::slug($type->name);
        });
    }

    protected $attributes = [
        'is_crew' => true,
    ];

    protected $casts = [
        'is_crew' => 'boolean',
    ];
}
