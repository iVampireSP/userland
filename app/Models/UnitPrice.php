<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitPrice extends Model
{
    protected $fillable = [
        'unit',
        'name',
        'price_per_unit',
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
