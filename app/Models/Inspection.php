<?php

namespace App\Models;

use COM;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'qn',
        'pns',
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
        'collaborators'
    ];

    protected $appends = [
        'total_approved',
        'total_rejected',
        'pdf_report_url',
        'excel_report_url'
    ];

    protected $attributes = [
        'collaborators' => '[]',
        'pns' => '[]',
    ];

    #region Attributes

    public function totalApproved(): Attribute
    {
        return Attribute::get(
            get: fn() => $this->lots()->sum('total_units') - $this->total_rejected,
        );
    }

    public function totalRejected(): Attribute
    {
        return Attribute::get(
            get: fn() => (int) $this->lots()->sum('total_rejects'),
        );
    }

    public function totalReworked(): Attribute
    {
        return Attribute::get(
            get: fn() => (int) $this->lots()->sum('total_reworks'),
        );
    }

    public function pdfReportUrl(): Attribute
    {
        return Attribute::get(
            get: fn() => route('inspections.report', [
                'inspection' => $this,
                'format' => 'pdf'
            ]),
        );
    }

    public function excelReportUrl(): Attribute
    {
        return Attribute::get(
            get: fn() => route('inspections.report', [
                'inspection' => $this,
                'format' => 'xlsx'
            ]),
        );
    }

    public function statusText(): Attribute
    {
        $statusMap = [
            Inspection::PENDING_STATUS => 'Pendiente',
            Inspection::ACTIVE_STATUS => 'Activo',
            Inspection::ON_HOLD_STATUS => 'En Espera',
            Inspection::UNDER_REVIEW_STATUS => 'En Revisión',
            Inspection::COMPLETED_STATUS => 'Completado',
        ];

        return Attribute::get(fn() => $statusMap[$this->status] ?? 'Desconocido');
    }

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

    public function lots(): HasMany
    {
        return $this->hasMany(InspectionLot::class);
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

    public function reviews(): HasMany
    {
        return $this->hasMany(InspectionReview::class);
    }

    public function lastReview(): HasOne
    {
        return $this->hasOne(InspectionReview::class)->latestOfMany();
    }

    public function lastInspectedLot(): HasOne
    {
        return $this->hasOne(InspectionLot::class)->latestOfMany();
    }

    #endregion

    public function casts(): array
    {
        return [
            'submit_date' => 'datetime',
            'start_date' => 'datetime',
            'complete_date' => 'datetime',
            'status' => 'integer',
            'inventory' => 'integer',
            'collaborators' => 'array',
            'pns' => 'array'
        ];
    }
}
