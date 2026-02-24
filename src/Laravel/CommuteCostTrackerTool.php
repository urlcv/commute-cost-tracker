<?php

declare(strict_types=1);

namespace URLCV\CommuteCostTracker\Laravel;

use App\Tools\Contracts\ToolInterface;

class CommuteCostTrackerTool implements ToolInterface
{
    public function slug(): string
    {
        return 'commute-cost-tracker';
    }

    public function name(): string
    {
        return 'Commute Cost Tracker';
    }

    public function summary(): string
    {
        return 'Calculate the true annual cost of commuting — cash, time, and effective salary impact — for any office role.';
    }

    public function descriptionMd(): ?string
    {
        return <<<'MD'
## Commute Cost Tracker

Most job offers quote a gross salary, but never mention the hidden cost of getting to the office every day. This calculator reveals the **true annual cost** of commuting so candidates and recruiters can compare roles fairly.

### What it calculates

- **Annual cash cost** — fuel, tickets, parking, tolls, vehicle maintenance and depreciation
- **Annual time cost** — total hours and equivalent workdays spent commuting
- **Time value** — your commute time priced at your hourly rate
- **Effective salary** — gross salary minus cash cost minus time value cost

### Comparison mode

Toggle comparison mode to evaluate two roles side by side — for example an office role versus a remote or hybrid position. See the effective salary delta instantly.

### Use cases for recruiters
- Help candidates understand the true value of remote/hybrid offers
- Quantify the commute cost when presenting roles to candidates
- Strengthen offer negotiations by showing total compensation impact
MD;
    }

    public function categories(): array
    {
        return ['salary', 'productivity'];
    }

    public function tags(): array
    {
        return ['commute', 'salary', 'remote-work', 'calculator', 'cost'];
    }

    public function inputSchema(): array
    {
        return [];
    }

    public function run(array $input): array
    {
        return [];
    }

    public function mode(): string
    {
        return 'frontend';
    }

    public function isAsync(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function frontendView(): ?string
    {
        return 'commute-cost-tracker::commute-cost-tracker';
    }

    public function rateLimitPerMinute(): int
    {
        return 30;
    }

    public function cacheTtlSeconds(): int
    {
        return 0;
    }

    public function sortWeight(): int
    {
        return 90;
    }
}
