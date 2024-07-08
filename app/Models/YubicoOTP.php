<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YubicoOTP extends Model
{
    protected $table = 'yubico_otps';

    protected $fillable = [
        'device_id',
        'model_type',
        'model_id',
    ];
}
