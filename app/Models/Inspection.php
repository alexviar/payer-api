<?php

namespace App\Models;

use COM;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inspection extends Model
{
    /** @use HasFactory<\Database\Factories\InspectionFactory> */
    use HasFactory;

    const PENDING_STATUS = 1;
    const ACTIVE_STATUS = 2;
    const ON_HOLD_STATUS = 3;
    const UNDER_REVIEW_STATUS = 4;
    const COMPLETED_STATUS = 5;

    protected $fillable = [
        'submit_date',
        'description',
        'inventory',
        'start_date',
        'complete_date',
        'status',
        'plant_id',
        'product_id',
        'group_leader_id',
        'sales_agent_id',
    ];

    #region Attributes

    public function client(): Attribute
    {
        return Attribute::get(
            get: fn() => $this->product->client,
        );
    }

    #endregion

    #region Relations

    public function groupLeader(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function salesAgents(): BelongsToMany
    {
        return $this->belongsToMany(SalesAgent::class);
    }

    public function defects(): BelongsToMany
    {
        return $this->belongsToMany(Defect::class, 'inspection_defects')
            // ->withPivot('quantity')
            ->withTimestamps();
    }

    public function reworks(): BelongsToMany
    {
        return $this->belongsToMany(Rework::class, 'inspection_reworks')
            // ->withPivot('quantity')
            ->withTimestamps();
    }

    #endregion

    public function casts(): array
    {
        return [
            'submit_date' => 'date',
            'start_date' => 'date',
            'complete_date' => 'date',
        ];
    }
}
