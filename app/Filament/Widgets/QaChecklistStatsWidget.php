<?php

namespace App\Filament\Widgets;

use App\Models\QaChecklist;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QaChecklistStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeChecklists = QaChecklist::where('status', 'active')->count();
        $completedItems = QaChecklist::whereHas('items', function ($query) {
            $query->whereHas('responses', function ($q) {
                $q->where('status', 'completed');
            });
        })->count();

        return [
            Stat::make('Active Checklists', $activeChecklists)
                ->description('Currently in use')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('success')
                ->extraAttributes(['class' => 'col-span-2']),
            Stat::make('Completed Items', $completedItems)
                ->description('Successfully checked')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary')
                ->extraAttributes(['class' => 'col-span-2']),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
