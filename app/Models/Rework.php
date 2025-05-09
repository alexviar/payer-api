<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rework extends Model
{
    /** @use HasFactory<\Database\Factories\ReworkFactory> */
    use HasFactory;

    protected $fillable = [
        'name'
    ];
}
