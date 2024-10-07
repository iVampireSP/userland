<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageUpgrade extends Model
{
    protected $fillable = [
        'old_package_id',
        'new_package_id',
    ];

    public function oldPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'old_package_id');
    }

    public function newPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'new_package_id');
    }
}
