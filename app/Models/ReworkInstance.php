<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReworkInstance extends Model
{
    /** @use HasFactory<\Database\Factories\ReworkInstanceFactory> */
    use HasFactory;

    protected $attributes = [
        'evidences' => '[]',
        'tags' => '[]'
    ];

    protected $fillable = [
        'inspection_lot_id',
        'rework_id',
        'tags',
        'evidences'
    ];

    protected $with = [
        'rework'
    ];

    protected $appends = ['evidences_urls'];

    protected $hidden = ['evidences'];

    protected function casts()
    {
        return [
            'evidences' => 'array',
            'tags' => 'array',
            'rework_id' => 'integer',
            'inspection_lot_id' => 'integer'
        ];
    }

    public function rework()
    {
        return $this->belongsTo(Rework::class);
    }

    public function lot()
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
                return route('rework-instances.evidences.download', [
                    'instance' => $this,
                    'evidence' => basename($path),
                ]);
            }, $this->evidences);
        });
    }
}
