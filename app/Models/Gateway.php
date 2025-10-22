<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $fillable = [
        'name',
        'api_url',
        'api_key',
        'secret_key',
        'status',
    ];
}
