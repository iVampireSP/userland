<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
