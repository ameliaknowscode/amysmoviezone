<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_crew'];

    protected $attributes = [
        'is_crew' => true,
    ];

    protected $casts = [
        'is_crew' => 'boolean',
    ];
}
