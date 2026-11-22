<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'customer_email',
        'customer_name',
        'phone',
        'shipping_address',
        'city',
        'country',
        'fulfillment_provider',
        'external_pod_id',
        'pod_status',
        'notes',
        'payment_method',
        'payment_status',
        'payment_reference',
        'payment_proof',
        'promo_code',
        'subtotal',
        'discount_amount',
        'shipping_fee',
        'total',
        'items',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'shipping_fee' => 'float',
        'total' => 'float',
        'items' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
