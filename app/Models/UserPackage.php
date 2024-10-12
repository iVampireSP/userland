<?php

namespace App\Models;

use App\Jobs\RevokeUserPackagePermissionsJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPackage extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quotas(): HasMany
    {
        return $this->hasMany(UserQuota::class);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);

        dispatch(new RevokeUserPackagePermissionsJob($this->user_id, $this->package_id));

    }
}
