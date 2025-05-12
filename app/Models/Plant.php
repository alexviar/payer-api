<?php

namespace App\Models;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    /** @use HasFactory<\Database\Factories\PlantFactory> */
    use HasFactory;

    const ACTIVE_STATUS = 1;
    const TEMPORARILY_UNAVAILABLE = 2;
    const CLOSED_STATUS = 3;

    protected $fillable = [
        'name',
        'address',
        'status'
    ];

    /**
     * Get all inspections for the plant.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }
}
