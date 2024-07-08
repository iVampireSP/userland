<?php

namespace App\Models;

use App\Helpers\HasYubicoOTP;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasYubicoOTP;

    protected $table = 'admins';

    protected $fillable = [
        'email',
        'name',
    ];

    protected $hidden = [
        'remember_token',
    ];
}
