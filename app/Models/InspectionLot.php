<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionLot extends Model
{
    /** @use HasFactory<\Database\Factories\InspectionLotFactory> */
    use HasFactory;

    protected $fillable = [
        'qn',
        'pn',
        'inspect_date',
        'shift',
        'total_units',
        'total_rejects',
        'total_reworks',
        'comment'
    ];

    protected $casts = [
        'inspect_date' => 'date',
        'shift' => 'integer',
        'total_units' => 'integer',
        'total_rejects' => 'integer',
        'total_reworks' => 'integer',
    ];

    protected $with = [
        'attributes',
        'defectInstances',
        'reworkInstances',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(CustomAttribute::class, 'inspection_lot_attributes')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function defectInstances()
    {
        return $this->hasMany(DefectInstance::class);
    }

    function reworkInstances()
    {
        return $this->hasMany(ReworkInstance::class);
    }
}
