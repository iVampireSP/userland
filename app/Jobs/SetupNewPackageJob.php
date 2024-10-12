<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SetupNewPackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    protected Order $order;

    protected User $user;

    protected Package $package;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $order_id,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::find($this->order_id);
        $user = User::find($order->user_id);
        $this->package = Package::find($order->package_id);

        // must be not null
        if (! $order || ! $user) {
            return;
        }

        $this->user = $user;
        $this->order = $order;

        $user_package = $this->user->packages()->where('package_id', $this->order->package_id)->first();
        $days_to_add = $this->order->calculateExpiredAt($this->order->quantity);

        echo $days_to_add;

        // 如果用户没有开通这个 package，则添加
        if (! $user_package) {

            $fields = [
                'package_id' => $this->package->id,
                'status' => 'active',
            ];
            if ($days_to_add) {
                $fields['expired_at'] = now()->addDays($days_to_add);
            }

            $this->user->packages()->create($fields);

            GrantPackagePermissionsToUserJob::dispatch($this->user->id, $this->package->id);
        }

        // 检测是否是 active
        if ($user_package->status === 'active') {
            if ($this->order->type == 'package_renewal') {
                $user_package->update(['expired_at' => $user_package->expired_at->addDays($days_to_add)]);
            }

            // if upgrade

        }

        // 如果是 cancelled
        if ($user_package->status === 'cancelled') {
            $fields = [
                'status' => 'active',
            ];
            if ($days_to_add) {
                $fields['expired_at'] = now()->addDays($days_to_add);
            }

            GrantPackagePermissionsToUserJob::dispatch($this->user->id, $this->package->id);

            $user_package->update($fields);
        }
    }
}
