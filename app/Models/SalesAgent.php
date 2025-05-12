<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalesAgent extends Model
{
    /** @use HasFactory<\Database\Factories\SalesAgentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    /**
     * Obtiene las inspecciones asociadas a este agente de ventas.
     */
    public function inspections(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Inspection::class, 'inspection_sales_agent', 'sales_agent_id', 'inspection_id');
    }
}
