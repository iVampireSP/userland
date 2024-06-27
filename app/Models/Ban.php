<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ban extends Model
{
    protected $fillable = [
        'email',
        'reason',
        'client_id',
        'code',
        'expires_at',
        'is_expired',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }
}
