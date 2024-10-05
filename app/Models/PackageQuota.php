<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageQuota extends Model
{
    protected $fillable = [
        'quota_id',
        'package_id',
        'max_amount',
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
