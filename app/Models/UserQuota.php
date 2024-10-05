<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuota extends Model
{
    protected $fillable = [
        'user_id',
        'usage_id',
        'amount',
        'max_amount',
    ];

    protected $casts = [
        'amount' => 'integer',
        'max_amount' => 'integer',
    ];

    public function usage(): BelongsTo
    {
        return $this->belongsTo(Quota::class);
    }
}
