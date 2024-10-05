<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'category_id');
    }
}
