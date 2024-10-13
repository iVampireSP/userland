<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitPrice extends Model
{
    protected $fillable = [
        'unit',
        'name',
        'price_per_unit',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:4',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function calculatePrice(string $amount): string
    {
        return bcmul($amount, $this->price_per_unit, 2);
    }
}
