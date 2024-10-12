<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SetupNewPackageJob;
use Illuminate\Support\Facades\Log;

class SetupPackage
{
    public function handle(OrderPlaced $event): void
    {
        //        Log::debug('OrderPlaced', [$event]);
        dispatch(new SetupNewPackageJob($event->order->id));
    }
}
