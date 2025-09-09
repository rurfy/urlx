<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = ['slug','target_url','expires_at'];
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
