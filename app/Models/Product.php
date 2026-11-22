<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'price',
        'unit_cost',
        'category',
        'bands',
        'sold_out',
        'stock_qty',
        'lead_time_days',
        'ordering_cost',
        'holding_cost_rate',
        'best_seller_rank',
        'silhouette',
        'base_color',
        'accent_color',
        'ink_color',
        'motif',
        'sub_motif',
        'printful_variant_id',
        'front_image',
        'back_image',
        'size_chart_image',
        'specs',
        'sizes',
        'description',
    ];

    protected $casts = [
        'price' => 'float',
        'unit_cost' => 'float',
        'ordering_cost' => 'float',
        'holding_cost_rate' => 'float',
        'bands' => 'array',
        'sold_out' => 'boolean',
        'specs' => 'array',
        'sizes' => 'array',
    ];
}
