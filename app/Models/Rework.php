<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rework extends Model
{
    /** @use HasFactory<\Database\Factories\ReworkFactory> */
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(InspectionLot::class, 'inspection_lot_id');
    }
}
