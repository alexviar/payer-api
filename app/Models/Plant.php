<?php

namespace App\Models;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    /** @use HasFactory<\Database\Factories\PlantFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
    ];

    /**
     * Get all inspections for the plant.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }
}
