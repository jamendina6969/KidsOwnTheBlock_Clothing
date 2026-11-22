<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = ['email', 'order_number', 'topic', 'message', 'status'];
}
