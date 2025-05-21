<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'manufacturer',
        'client_id',
    ];

    #region Relations

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function lastInspection(): HasOne
    {
        return $this->hasOne(Inspection::class)->ofMany();
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(CustomAttribute::class, 'product_attributes')
            ->withTimestamps();
    }

    #endregion
}
