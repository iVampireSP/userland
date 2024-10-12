<?php

namespace App\Jobs;

use App\Models\UserPackage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckExpiredUserPackageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        UserPackage::where('status', 'active')
            ->where('expired_at', '<', now())
            ->chunk(100, function ($user_packages) {
                foreach ($user_packages as $user_package) {
                    $user_package->status = 'cancelled';
                    $user_package->save();
                }
            });
    }
}
