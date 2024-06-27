<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ban extends Model
{
    protected $fillable = [
        'email',
        'reason',
        'client_id',
        'code',
        'expired_at',
        'pardoned',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'pardoned' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function pardon(): void
    {
        $this->expired_at = now();
        $this->update([
            'pardoned' => true,
        ]);
    }

    public static function boot(): void
    {
        parent::boot();

        static::updating(function (self $model) {
            if ($model->isDirty('expired_at')) {
                // 如果到期
                if ($model->expired_at < now()) {
                    $model->pardoned = true;
                } else {
                    $model->pardoned = false;
                }

                $model->saveQuietly();

            }
        });
    }
}
