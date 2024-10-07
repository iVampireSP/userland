<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageQuota extends Model
{
    protected $fillable = [
        'quota_id',
        'package_id',
        'max_amount',
        'reset_rule',
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
