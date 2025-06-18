<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DefectInstance extends Model
{
    /** @use HasFactory<\Database\Factories\DefectInstanceFactory> */
    use HasFactory;

    protected $attributes = [
        'include_in_report' => false,
        'tags' => '[]',
        'evidences' => '[]'
    ];

    protected $fillable = [
        'inspection_lot_id',
        'defect_id',
        'tags',
        'evidences',
        'include_in_report'
    ];

    protected $with = [
        'defect'
    ];

    protected $appends = ['evidences_urls'];

    protected $hidden = ['evidences'];

    protected function casts()
    {
        return [
            'evidences' => 'array',
            'tags' => 'array',
            'defect_id' => 'integer',
            'inspection_lot_id' => 'integer',
            'include_in_report' => 'boolean'
        ];
    }

    public function defect()
    {
        return $this->belongsTo(Defect::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(InspectionLot::class, 'inspection_lot_id');
    }

    /**
     * Get the evidence URLs.
     */
    public function evidencesUrls(): Attribute
    {
        return Attribute::get(function () {
            if (empty($this->evidences)) {
                return [];
            }

            return array_map(function ($path) {
                return route('defect-instances.evidences.download', [
                    'instance' => $this,
                    'evidence' => basename($path),
                ]);
            }, $this->evidences);
        });
    }
}
