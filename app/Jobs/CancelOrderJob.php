<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CancelOrderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //  如果订单创建时间超过 1 天，则不能支付
        Order::where('status', 'pending')
            ->where('created_at', '<', now()
                ->subDays(1))->chunk(100, function ($orders) {
                    $orders->each(function ($order) {
                        $order->cancel();
                    });
                });

    }
}
