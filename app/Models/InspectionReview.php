<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionReview extends Model
{
    /** @use HasFactory<\Database\Factories\InspectionReviewFactory> */
    use HasFactory;

    const APPROVED = 1;
    const REJECTED = 2;
    protected $table = 'inspection_reviews';

    protected $with = [
        'reviewer'
    ];

    protected $fillable = [
        'inspection_id',
        'review_date',
        'reviewer_id',
        'corrective_actions',
        'review_outcome'
    ];

    protected function casts()
    {
        return [
            'corrective_actions' => 'array',
            'review_date' => 'datetime',
            'review_outcome' => 'integer',
            'inspection_id' => 'integer',
            'reviewer_id' => 'integer'
        ];
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
