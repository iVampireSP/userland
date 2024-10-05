<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quota extends Model
{
    protected $fillable = [
        'unit',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserQuota::class)->withPivot('amount', 'max_amount');
    }
}
