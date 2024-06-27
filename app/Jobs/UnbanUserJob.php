<?php

namespace App\Jobs;

use App\Models\Ban;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UnbanUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        Ban::where('expired_at', '<', now())->where('pardoned', false)->chunk(100, function ($bans) {
            foreach ($bans as $ban) {
                $ban->pardon();
            }
        });
    }
}
