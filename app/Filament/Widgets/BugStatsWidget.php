<?php

namespace App\Filament\Widgets;

use App\Models\Bug;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BugStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBugs = Bug::count();
        $resolvedBugs = Bug::where('status', 'resolved')->count();
        $criticalBugs = Bug::where('priority', 'critical')->count();

        return [
            Stat::make('Total Bugs', $totalBugs)
                ->description('All reported bugs')
                ->descriptionIcon('heroicon-m-bug-ant')
                ->color('gray'),
            Stat::make('Resolved Bugs', $resolvedBugs)
                ->description('Successfully fixed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Critical Bugs', $criticalBugs)
                ->description('High priority issues')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
