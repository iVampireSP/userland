<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Package extends Model
{
    use HasRoles, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'content',
        'name',
        'category_id',
        //        'currency',

        'enable_day',
        'enable_week',
        'enable_month',
        'enable_year',
        'enable_forever',

        'price_day',
        'price_week',
        'price_month',
        'price_year',
        'price_forever',

        'sold_out',

        'hidden',
        //        'enable_quota',
        //        'max_active_count',
    ];

    protected array $guard_name = ['web', 'api'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'category_id');
    }

    //    public function quotas(): HasMany
    //    {
    //        return $this->hasMany(PackageQuota::class);
    //    }

    public function upgrades(): HasMany
    {
        return $this->hasMany(PackageUpgrade::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    //    public function
}
