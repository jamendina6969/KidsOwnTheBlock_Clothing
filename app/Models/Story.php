<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'duration',
        'date_label',
        'tag',
        'video_url',
    ];
}
