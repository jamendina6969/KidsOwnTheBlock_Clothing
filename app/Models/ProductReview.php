<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'reviewer_name',
        'rating',
        'fit_feedback',
        'comment',
        'verified_buyer',
    ];

    protected $casts = [
        'verified_buyer' => 'boolean',
    ];
}
