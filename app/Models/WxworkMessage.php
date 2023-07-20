<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WxworkMessage extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'tolist' => 'array',
        'encrypt_content' => 'array',
    ];
}
