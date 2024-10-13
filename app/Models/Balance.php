<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Notifications\Notifiable;

use function auth;

class Balance extends Model
{
    use Notifiable;

    protected $fillable = [
        'order_id',
        'payment',
        'amount',
        'user_id',
        'paid_at',
        'trade_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function canPay(): bool
    {
        return ! $this->isPaid() && ! $this->isOverdue();
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->created_at->diffInDays(now()) > 1 && ! $this->isPaid();
    }
}
