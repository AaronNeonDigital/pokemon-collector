<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    use HasFactory;

    protected $casts = [
        'evolves_from' => 'array',
        'evolves_to' => 'array'
    ];
}
