<?php

namespace App\Models;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'quantity',
        'payment_method',
        'payment_id',
        'amount',
        'billing_cycle',
        'package_id',
        'upgrade_to_package_id',
        'billing_cycle',
        'expired_at',
    ];

    // mark as paid（原子操作）
    public function markAsPaid(): bool
    {
        // redis lock
        $lock = Cache::lock('order:'.$this->id, 10);
        try {
            $lock->block(10);
        } catch (LockTimeoutException $e) {
            return false;
        }

        try {
            $this->status = 'paid';

            return $this->save();
        } finally {
            $lock->release();

            return false;
        }
    }

    // mark as completed
    public function markAsCompleted(): bool
    {
        $lock = Cache::lock('order:'.$this->id, 10);
        try {
            $lock->block(10);
        } catch (LockTimeoutException $e) {
            return false;
        }
        try {
            $this->status = 'completed';

            return $this->save();
        } finally {
            $lock->release();

            return false;
        }
    }

    // 检测订单是否需要支付
    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    // 根据计费周期计算新要添加多少天
    public function calculateExpiredAt($quantity = 1)
    {
        // 如果 forever，则 null
        if ($this->billing_cycle === 'forever') {
            return null;
        }

        return match ($this->billing_cycle) {
            'day' => $quantity,
            'week' => $quantity * 7 ,
            'month' => $quantity * 30,
            'year' => $quantity * 365,
            default => 0,
        };

    }

    //    public function calculateExpiredAt(?Carbon $startFrom, $quantity = 1): ?float
    //    {
    //        // 如果 forever，则返回 null
    //        if ($this->billing_cycle === 'forever') {
    //            return null;
    //        }
    //
    //        // 根据计费周期计算天数
    //        $days = match ($this->billing_cycle) {
    //            'day' => $quantity,
    //            'week' => $quantity * 7,
    //            'month' => $quantity * 30,
    //            'year' => $quantity * 365,
    //            default => 365,
    //        };
    //
    //        if (! $startFrom) {
    //            return $days;
    //        }
    //
    //        // 计算新的到期时间
    //        $newExpiryDate = $startFrom->copy()->addDays($days);
    //
    //        // 计算相隔的天数
    //        return abs($newExpiryDate->diffInDays($startFrom));
    //    }

    public function cancel(): bool
    {
        if ($this->status !== 'unpaid') {
            return false;
        }

        $lock = Cache::lock('order:'.$this->id, 10);
        try {
            $lock->block(10);
        } catch (LockTimeoutException $e) {
            return false;
        }
        try {
            $this->status = 'cancelled';

            return $this->save();
        } finally {
            $lock->release();

            return false;
        }
    }

    public function upgradeToPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'upgrade_to_package_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
