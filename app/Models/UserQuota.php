<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuota extends Model
{
    protected $fillable = [
        'user_id',
        'quota_id',
        'package_id',
        'is_exhausted',
        'enabled',
        'amount',
        'max_amount',
        'last_reset_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'max_amount' => 'integer',
    ];

    public function quota(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
