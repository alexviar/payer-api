<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inspection extends Model
{
    /** @use HasFactory<\Database\Factories\InspectionFactory> */
    use HasFactory;

    #region Relations

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    #endregion
}
