<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lookbook extends Model
{
    protected $fillable = [
        'collection_title',
        'season',
        'look_number',
        'title',
        'garments',
        'image_path',
        'bg_color',
        'drape_color',
        'sil_color',
        'acc_color',
    ];
}
