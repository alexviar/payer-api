<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    #region Relations

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(CustomAttribute::class, 'product_attributes')
            ->withTimestamps();
    }

    #endregion
}
