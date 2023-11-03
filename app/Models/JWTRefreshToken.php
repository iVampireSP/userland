<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JWTRefreshToken extends Model
{
    use HasFactory;

    protected $table = 'jwt_refresh_tokens';

    protected $fillable = [
        'claims',
        'refresh_token',
        'user_id'
    ];

    protected $casts = [
        'claims' => 'array',
    ];

    protected $withs = [
        'user'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
