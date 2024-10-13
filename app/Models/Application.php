<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Application extends Authenticatable
{
    public $fillable = [
        'name',
        'description',
        'api_token',
    ];
}
