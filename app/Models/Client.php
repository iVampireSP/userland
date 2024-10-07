<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    /**
     * 确定客户端是否应跳过授权提示。
     */
    public function skipsAuthorization(): bool
    {
        return $this->trusted;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
