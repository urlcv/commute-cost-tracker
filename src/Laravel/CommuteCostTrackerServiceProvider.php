<?php

declare(strict_types=1);

namespace URLCV\CommuteCostTracker\Laravel;

use Illuminate\Support\ServiceProvider;

class CommuteCostTrackerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'commute-cost-tracker');
    }
}
