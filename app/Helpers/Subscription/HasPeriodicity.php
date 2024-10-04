<?php

namespace App\Helpers\Subscription;

use Illuminate\Http\Request;

trait HasPeriodicity
{
    private function matchType(string $type): string
    {
        return match ($type) {
            default => null,
            'day' => 'Day',
            'week' => 'Week',
            'month' => 'Month',
            'year' => 'Year',
        };
    }
}
