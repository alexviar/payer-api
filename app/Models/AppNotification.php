<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    /** @use HasFactory<\Database\Factories\AppNotificationFactory> */
    use HasFactory;

    const INSPECTION_ASSIGNED = 1;
    const INSPECTION_UNDER_REVIEW = 2;

    protected $fillable = [
        'type',
        'payload',
        'user_id',
        'read'
    ];

    protected $casts = [
        'type' => 'integer',
        'payload' => 'array',
        'read' => 'boolean',
        'user_id' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
