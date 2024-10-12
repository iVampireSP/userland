<?php

namespace App\Jobs;

use App\Models\UserPackage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CancelUserPackageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        UserPackage::where('status', 'active')
            ->where('expired_at', '<', now())->chunk(100, function ($userPackages) {
                $userPackages->each(function (UserPackage $userPackage) {
                    $userPackage->cancel();
                });
            });

    }
}
