<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    /** @use HasFactory<\Database\Factories\DefectFactory> */
    use HasFactory;

    protected $fillable = [
        'name'
    ];
}
